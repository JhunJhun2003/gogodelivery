<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\User;
use App\Models\Way;
use App\Models\WayStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WayController extends Controller
{
    private function validateImageDimensionsForProcessing(string $path): void
    {
        @ini_set('memory_limit', '512M');

        $imageInfo = @getimagesize($path);

        abort_unless(is_array($imageInfo) && $imageInfo[0] > 0 && $imageInfo[1] > 0, 422, 'The uploaded file is not a valid image.');

        [$width, $height] = [$imageInfo[0], $imageInfo[1]];
        $pixelLimit = 40_000_000;

        abort_unless($width <= 8000 && $height <= 8000, 422, 'Image dimensions exceed the maximum supported size.');
        abort_unless(($width * $height) <= $pixelLimit, 422, 'Image dimensions exceed the maximum supported size.');
    }

    private function resizeImage(string $sourcePath, string $mimeType, string $destPath): void
    {
        $source = match ($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($sourcePath),
            'image/png' => imagecreatefrompng($sourcePath),
            'image/webp' => imagecreatefromwebp($sourcePath),
            default => imagecreatefromjpeg($sourcePath),
        };

        $maxWidth = 800;
        $maxHeight = 800;
        $width = imagesx($source);
        $height = imagesy($source);

        if ($width > $maxWidth || $height > $maxHeight) {
            $intermediate = 1600;
            if ($width > $intermediate || $height > $intermediate) {
                $ratio = min($intermediate / $width, $intermediate / $height);
                $tmpW = (int) ($width * $ratio);
                $tmpH = (int) ($height * $ratio);
                $tmp = imagecreatetruecolor($tmpW, $tmpH);
                imagecopyresampled($tmp, $source, 0, 0, 0, 0, $tmpW, $tmpH, $width, $height);
                imagedestroy($source);
                $source = $tmp;
                $width = $tmpW;
                $height = $tmpH;
            }

            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int) ($width * $ratio);
            $newHeight = (int) ($height * $ratio);
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            imagedestroy($source);
            $source = $resized;
        }

        imagejpeg($source, $destPath, 85);
        imagedestroy($source);
    }

    private function resolveAuthenticatedBiker(): ?Biker
    {
        $user = Auth::user();

        if (! $user || $user->role !== User::ROLE_BIKER) {
            return null;
        }

        $biker = $user->biker;

        if (! $biker) {
            $biker = Biker::query()->firstOrCreate(['name' => $user->name]);
            $user->update(['biker_id' => $biker->id]);
        }

        return $biker;
    }

    public function bikerWays(): View
    {
        $biker = $this->resolveAuthenticatedBiker();
        abort_unless($biker, 403);

        return view('bikers.ways', [
            'biker' => $biker,
            'ways' => Way::query()
                ->where('biker_id', $biker->id)
                ->with('shop')
                ->orderByDesc('assigned_at')
                ->latest('id')
                ->get(),
        ]);
    }

    public function updateBikerStatus(Request $request, Way $way): RedirectResponse
    {
        $biker = $this->resolveAuthenticatedBiker();
        abort_unless($biker && $way->biker_id === $biker->id, 403);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', [Way::STATUS_ONWAY, Way::STATUS_FAILED, Way::STATUS_DELIVERED])],
            'remark' => ['nullable', 'string', 'max:2000'],
        ]);

        $way->update([
            'status' => $data['status'],
            'remark' => $data['remark'] ?? $way->remark,
        ]);

        WayStatusHistory::create([
            'way_id' => $way->id,
            'status' => $data['status'],
            'remark' => $data['remark'] ?? null,
            'changed_by' => Auth::user()->name,
        ]);

        return redirect()->route('bikers.ways')->with('way_status', 'Way status updated.');
    }

    public function updateAdminStatus(Request $request, Way $way): \Illuminate\Http\JsonResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', [Way::STATUS_ONWAY, Way::STATUS_FAILED, Way::STATUS_DELIVERED])],
            'remark' => ['nullable', 'string', 'max:2000'],
        ]);

        $way->update([
            'status' => $data['status'],
            'remark' => $data['remark'] ?? $way->remark,
        ]);

        WayStatusHistory::create([
            'way_id' => $way->id,
            'status' => $data['status'],
            'remark' => $data['remark'] ?? null,
            'changed_by' => Auth::user()->name,
        ]);

        return response()->json([
            'success' => true,
            'status' => $way->status,
            'remark' => $way->remark,
        ]);
    }

    public function reassignBiker(Request $request, Way $way): \Illuminate\Http\JsonResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $data = $request->validate([
            'biker_id' => ['required', 'exists:bikers,id'],
        ]);

        $oldBiker = $way->biker?->name ?? 'Unassigned';
        $way->update([
            'biker_id' => $data['biker_id'],
            'assigned_at' => now(),
        ]);

        WayStatusHistory::create([
            'way_id' => $way->id,
            'status' => $way->status,
            'remark' => 'Reassigned from ' . $oldBiker . ' to ' . $way->biker->name,
            'changed_by' => Auth::user()->name,
        ]);

        return response()->json([
            'success' => true,
            'biker_name' => $way->biker->name,
        ]);
    }

    public function wayHistory(Way $way): \Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        abort_unless($user?->isAdmin() || $user?->role === User::ROLE_BIKER, 403);

        $histories = $way->histories()->get()->map(fn ($h) => [
            'status' => $h->status,
            'remark' => $h->remark,
            'changed_by' => $h->changed_by,
            'created_at' => $h->created_at?->format('d-m-Y H:i'),
        ]);

        return response()->json($histories);
    }

    public function bikerHistory(Request $request): View
    {
        $biker = $this->resolveAuthenticatedBiker();
        abort_unless($biker, 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:'.implode(',', Way::STATUSES)],
            'date' => ['nullable', 'date'],
        ]);

        $ways = Way::query()
            ->where('biker_id', $biker->id)
            ->with('shop')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('recipient_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%")
                        ->orWhereHas('shop', fn ($shop) => $shop->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date))
            ->latest('date')
            ->latest('id')
            ->get();

        return view('bikers.history', compact('biker', 'ways', 'filters'));
    }

    public function bikerHistoryDetail(Way $way): View
    {
        $biker = $this->resolveAuthenticatedBiker();
        abort_unless($biker && $way->biker_id === $biker->id, 404);
        $way->load(['shop', 'biker']);

        return view('bikers.history-detail', compact('way'));
    }

    public function exportBikerHistory(Request $request): StreamedResponse
    {
        $biker = $this->resolveAuthenticatedBiker();
        abort_unless($biker, 403);

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'date' => ['nullable', 'date'],
        ]);

        $waysQuery = Way::query()
            ->where('biker_id', $biker->id)
            ->with(['shop', 'biker'])
            ->latest('date')
            ->latest('id');

        if ($search = $filters['search'] ?? null) {
            $waysQuery->where(function ($query) use ($search) {
                $query->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($shopQuery) => $shopQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $waysQuery
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date));

        $filename = 'biker-history-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($waysQuery) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Shop', 'Date', 'Image', 'Amount', 'Deli Fees', 'Customer Name', 'Address', 'Phone', 'Biker', 'Status', 'Deli Date', 'Remark']);

            $ways = $waysQuery->get();

            foreach ($ways as $index => $way) {
                fputcsv($handle, [
                    str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    $way->shop?->name ?? 'N/A',
                    $way->date?->format('d-m-Y') ?? '',
                    $way->item_image ? asset($way->item_image) : '',
                    number_format((float) $way->amount, 2, '.', ''),
                    number_format((float) $way->delivery_fees, 2, '.', ''),
                    $way->recipient_name,
                    $way->address,
                    $way->phone_number,
                    $way->biker?->name ?? 'Unassigned',
                    $way->status === 'onway' ? 'On way' : ucfirst($way->status),
                    $way->assigned_at ? $way->assigned_at->format('d-m-Y') : ($way->date?->format('d-m-Y') ?? ''),
                    $way->remark ?: '—',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function shopOrders(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $shop = Auth::user();
        $ordersQuery = Way::query()
            ->where('shop_id', $shop->id)
            ->whereNotIn('status', [Way::STATUS_DELIVERED, Way::STATUS_FAILED])
            ->with('biker')
            ->latest('date')
            ->latest('id');

        if ($search !== '') {
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        return view('shop.orders', [
            'shop' => $shop,
            'orders' => $ordersQuery->get(),
            'search' => $search,
        ]);
    }

    public function shopHistory(Request $request): View
    {
        $shop = Auth::user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'date' => ['nullable', 'date'],
        ]);
        $search = $filters['search'] ?? null;

        $ordersQuery = Way::query()
            ->where('shop_id', $shop->id)
            ->with('biker')
            ->latest('date')
            ->latest('id')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('recipient_name', 'like', "%{$search}%")
                        ->orWhere('phone_number', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date));

        return view('shop.history', [
            'shop' => $shop,
            'orders' => $ordersQuery->get(),
            'filters' => $filters,
        ]);
    }

    public function shopHistoryDetail(Way $way): View
    {
        abort_unless($way->shop_id === Auth::id(), 404);
        $way->load(['shop', 'biker']);

        return view('shop.history-detail', compact('way'));
    }

    public function history(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'shop_id' => ['nullable', 'exists:users,id'],
            'biker_id' => ['nullable', 'exists:bikers,id'],
            'status' => ['nullable', 'string', 'max:30'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'date' => ['nullable', 'date'],
        ]);

        $waysQuery = Way::query()->with(['shop', 'biker'])->latest('date')->latest('id');

        if ($search = $filters['search'] ?? null) {
            $waysQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($shopQuery) => $shopQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('biker', fn ($bikerQuery) => $bikerQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $waysQuery
            ->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('shop_id', $shopId))
            ->when($filters['biker_id'] ?? null, fn ($query, $bikerId) => $query->where('biker_id', $bikerId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['customer_name'] ?? null, fn ($query, $name) => $query->where('recipient_name', 'like', "%{$name}%"))
            ->when($filters['customer_phone'] ?? null, fn ($query, $phone) => $query->where('phone_number', 'like', "%{$phone}%"))
            ->when($filters['min_amount'] ?? null, fn ($query, $amount) => $query->where('amount', '>=', $amount))
            ->when($filters['max_amount'] ?? null, fn ($query, $amount) => $query->where('amount', '<=', $amount))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date));

        return view('admin.history', [
            'ways' => $waysQuery->get(),
            'shops' => User::query()->where('role', User::ROLE_SHOP)->orderBy('name')->get(),
            'bikers' => Biker::query()->orderBy('name')->get(),
            'filters' => $filters,
            'totalWays' => Way::query()->whereDate('date', today())->count(),
        ]);
    }

    public function historyDetail(Way $way): View
    {
        $way->load(['shop', 'biker']);

        return view('admin.history-detail', compact('way'));
    }

    private function buildAdminHistoryPdfHtml(string $title, array $rows): string
    {
        $rowHtml = '';
        $totalAmount = 0.0;
        $totalFees = 0.0;

        foreach ($rows as $row) {
            $amount = (float) ($row['amount'] ?? 0);
            $fees = (float) ($row['fees'] ?? 0);
            $totalAmount += $amount;
            $totalFees += $fees;

            $rowHtml .= '<tr>'
                . '<td>' . e($row['no'] ?? '') . '</td>'
                . '<td>' . e($row['date'] ?? '') . '</td>'
                . '<td>' . e($row['shop'] ?? '') . '</td>'
                . '<td>' . e($row['customer'] ?? '') . '</td>'
                . '<td>' . e($row['phone'] ?? '') . '</td>'
                . '<td>' . e($row['status'] ?? '') . '</td>'
                . '<td>' . e(number_format($amount, 2, '.', '')) . '</td>'
                . '<td>' . e(number_format($fees, 2, '.', '')) . '</td>'
                . '</tr>';
        }

        if ($rowHtml === '') {
            $rowHtml = '<tr><td colspan="8" style="text-align:center;">No records found.</td></tr>';
        }

        $generatedAt = now()->format('d-m-Y H:i');

        $totalRow = '<tr style="font-weight:bold; background:#f3f4f6;">'
            . '<td colspan="6" style="text-align:right;">Total</td>'
            . '<td>' . e(number_format($totalAmount, 2, '.', '')) . '</td>'
            . '<td>' . e(number_format($totalFees, 2, '.', '')) . '</td>'
            . '</tr>';

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
    <style>
        @font-face {
            font-family: 'Noto Sans Myanmar';
            src: url('file:///c:/Users/Admin/Desktop/no end/gogodelivery/public/fonts/NotoSansMyanmar-Regular.ttf') format('truetype');
        }

        body { font-family: 'Noto Sans Myanmar', 'DejaVu Sans', 'Myanmar3', sans-serif; margin: 24px; color: #111827; }
        h1 { font-size: 20px; margin-bottom: 18px; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; vertical-align: top; }
        th { background: #f3f4f6; font-weight: bold; }
        .summary { margin-bottom: 12px; font-size: 12px; }
        .totals { margin-top: 12px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{$title}</h1>
    <div class="summary">Generated: {$generatedAt}</div>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Date</th>
                <th>Shop</th>
                <th>Customer</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Amount</th>
                <th>Fees</th>
            </tr>
        </thead>
        <tbody>
            {$rowHtml}
            {$totalRow}
        </tbody>
    </table>
    <div class="totals">Total Amount: {$totalAmount} | Total Fees: {$totalFees}</div>
</body>
</html>
HTML;
    }

    public function exportAdminHistory(Request $request): StreamedResponse
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'shop_id' => ['nullable', 'exists:users,id'],
            'biker_id' => ['nullable', 'exists:bikers,id'],
            'status' => ['nullable', 'string', 'max:30'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'date' => ['nullable', 'date'],
        ]);

        $waysQuery = Way::query()->with(['shop', 'biker'])->latest('date')->latest('id');

        if ($search = $filters['search'] ?? null) {
            $waysQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($shopQuery) => $shopQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('biker', fn ($bikerQuery) => $bikerQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $waysQuery
            ->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('shop_id', $shopId))
            ->when($filters['biker_id'] ?? null, fn ($query, $bikerId) => $query->where('biker_id', $bikerId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['customer_name'] ?? null, fn ($query, $name) => $query->where('recipient_name', 'like', "%{$name}%"))
            ->when($filters['customer_phone'] ?? null, fn ($query, $phone) => $query->where('phone_number', 'like', "%{$phone}%"))
            ->when($filters['min_amount'] ?? null, fn ($query, $amount) => $query->where('amount', '>=', $amount))
            ->when($filters['max_amount'] ?? null, fn ($query, $amount) => $query->where('amount', '<=', $amount))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date));

        $filename = 'admin-history-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($waysQuery) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Shop', 'Date', 'Image', 'Amount', 'Deli Fees', 'Customer Name', 'Address', 'Phone', 'Biker', 'Status', 'Deli Date', 'Remark']);

            $ways = $waysQuery->get();

            foreach ($ways as $index => $way) {
                fputcsv($handle, [
                    str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    $way->shop?->name ?? 'N/A',
                    $way->date?->format('d-m-Y') ?? '',
                    $way->item_image ? asset($way->item_image) : '',
                    number_format((float) $way->amount, 2, '.', ''),
                    number_format((float) $way->delivery_fees, 2, '.', ''),
                    $way->recipient_name,
                    $way->address,
                    $way->phone_number,
                    $way->biker?->name ?? 'Unassigned',
                    $way->status === 'onway' ? 'On way' : ucfirst($way->status),
                    $way->assigned_at ? $way->assigned_at->format('d-m-Y') : ($way->date?->format('d-m-Y') ?? ''),
                    $way->remark ?: '—',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportAdminHistoryPdf(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'shop_id' => ['nullable', 'exists:users,id'],
            'biker_id' => ['nullable', 'exists:bikers,id'],
            'status' => ['nullable', 'string', 'max:30'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'min_amount' => ['nullable', 'numeric', 'min:0'],
            'max_amount' => ['nullable', 'numeric', 'gte:min_amount'],
            'date' => ['nullable', 'date'],
        ]);

        $waysQuery = Way::query()->with(['shop', 'biker'])->latest('date')->latest('id');

        if ($search = $filters['search'] ?? null) {
            $waysQuery->where(function ($query) use ($search) {
                $query->where('id', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($shopQuery) => $shopQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('biker', fn ($bikerQuery) => $bikerQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $waysQuery
            ->when($filters['shop_id'] ?? null, fn ($query, $shopId) => $query->where('shop_id', $shopId))
            ->when($filters['biker_id'] ?? null, fn ($query, $bikerId) => $query->where('biker_id', $bikerId))
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['customer_name'] ?? null, fn ($query, $name) => $query->where('recipient_name', 'like', "%{$name}%"))
            ->when($filters['customer_phone'] ?? null, fn ($query, $phone) => $query->where('phone_number', 'like', "%{$phone}%"))
            ->when($filters['min_amount'] ?? null, fn ($query, $amount) => $query->where('amount', '>=', $amount))
            ->when($filters['max_amount'] ?? null, fn ($query, $amount) => $query->where('amount', '<=', $amount))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date));

        $filename = 'admin-history-' . now()->format('Ymd_His') . '.pdf';
        $rows = [];

        foreach ($waysQuery->get() as $index => $way) {
            $rows[] = [
                'no' => str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                'date' => $way->date?->format('d-m-Y') ?? '',
                'shop' => $way->shop?->name ?? 'N/A',
                'customer' => $way->recipient_name,
                'phone' => $way->phone_number,
                'status' => $way->status === 'onway' ? 'On way' : ucfirst($way->status),
                'amount' => number_format((float) $way->amount, 2, '.', ''),
                'fees' => number_format((float) $way->delivery_fees, 2, '.', ''),
            ];
        }

        $html = $this->buildAdminHistoryPdfHtml('Admin History', $rows);
        $dompdf = new \Dompdf\Dompdf();
        $dompdf->set_option('defaultFont', 'DejaVu Sans');
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function exportShopHistory(Request $request): StreamedResponse
    {
        $shop = Auth::user();
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', 'max:30'],
            'date' => ['nullable', 'date'],
        ]);

        $search = $filters['search'] ?? null;

        $ordersQuery = Way::query()
            ->where('shop_id', $shop->id)
            ->with(['shop', 'biker'])
            ->latest('date')
            ->latest('id');

        if ($search !== null && $search !== '') {
            $ordersQuery->where(function ($query) use ($search) {
                $query->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%")
                    ->orWhereHas('shop', fn ($shopQuery) => $shopQuery->where('name', 'like', "%{$search}%"));
            });
        }

        $ordersQuery
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('date', $date));

        $filename = 'shop-history-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($ordersQuery) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Shop', 'Date', 'Image', 'Amount', 'Deli Fees', 'Customer Name', 'Address', 'Phone', 'Biker', 'Status', 'Deli Date', 'Remark']);

            $orders = $ordersQuery->get();

            foreach ($orders as $index => $order) {
                fputcsv($handle, [
                    str_pad($index + 1, 2, '0', STR_PAD_LEFT),
                    $order->shop?->name ?? 'N/A',
                    $order->date?->format('d-m-Y') ?? '',
                    $order->item_image ? asset($order->item_image) : '',
                    number_format((float) $order->amount, 2, '.', ''),
                    number_format((float) $order->delivery_fees, 2, '.', ''),
                    $order->recipient_name,
                    $order->address,
                    $order->phone_number,
                    $order->biker?->name ?? 'Unassigned',
                    $order->status === 'onway' ? 'On way' : ucfirst($order->status),
                    $order->assigned_at ? $order->assigned_at->format('d-m-Y') : ($order->date?->format('d-m-Y') ?? ''),
                    $order->remark ?: '—',
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function editWay(Way $way): View
    {
        abort_unless(Auth::user()?->isAdmin(), 403);
        $way->load(['shop', 'biker']);

        return view('admin.way-edit', [
            'way' => $way,
            'shops' => User::query()->where('role', User::ROLE_SHOP)->orderBy('name')->get(),
            'bikers' => Biker::query()->orderBy('name')->get(),
        ]);
    }

    public function destroy(Way $way): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        if ($way->item_image) {
            $imagePath = public_path($way->item_image);
            if (is_file($imagePath)) {
                unlink($imagePath);
            }
        }

        $way->delete();

        if (request()->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Way deleted successfully.']);
        }

        return redirect()->route('admin.history')->with('way_status', 'Way deleted successfully.');
    }

    public function updateWay(Request $request, Way $way): RedirectResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

        $data = $request->validate([
            'shop_id' => ['required', 'exists:users,id'],
            'biker_id' => ['nullable', 'exists:bikers,id'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_fees' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'remark' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:'.implode(',', Way::STATUSES)],
            'item_image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('item_image')) {
            @ini_set('memory_limit', '512M');
            $image = $request->file('item_image');
            $directory = config('filesystems.order_image_path');
            File::ensureDirectoryExists($directory);
            abort_unless(is_dir($directory) && is_writable($directory), 500, 'The order image directory is not writable.');
            $this->validateImageDimensionsForProcessing($image->getRealPath());
            $filename = $image->hashName();
            $path = $directory . '/' . $filename;

            $this->resizeImage($image->getRealPath(), $image->getClientMimeType(), $path);

            abort_unless(is_file($path), 500, 'The order image could not be saved.');
            $data['item_image'] = 'order_image/' . $filename;
        } else {
            unset($data['item_image']);
        }

        $data['assigned_at'] = $way->assigned_at;
        $oldStatus = $way->status;
        $way->update($data);

        if ($data['status'] !== $oldStatus) {
            WayStatusHistory::create([
                'way_id' => $way->id,
                'status' => $data['status'],
                'remark' => $data['remark'] ?? null,
                'changed_by' => Auth::user()->name,
            ]);
        }

        return redirect()->route('admin.history.detail', $way)->with('way_status', 'Way updated successfully.');
    }

    public function check(): View
    {
        $today = today();
        $todayWays = Way::query()->whereDate('date', $today);

        return view('admin.way-check', [
            'today' => $today,
            'totalWays' => (clone $todayWays)->count(),
            'pendingWays' => (clone $todayWays)->where('status', Way::STATUS_PENDING)->count(),
            'completedWays' => (clone $todayWays)->where('status', Way::STATUS_DELIVERED)->count(),
            'shops' => User::query()->where('role', User::ROLE_SHOP)->orderBy('name')->get(),
            'bikers' => Biker::query()->orderBy('name')->get(),
        ]);
    }

    public function storeFromCheck(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'shop_id' => ['required', 'exists:users,id'],
            'biker_id' => ['nullable', 'exists:bikers,id'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_fees' => ['required', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'remark' => ['nullable', 'string', 'max:2000'],
            'status' => ['nullable', 'string'],
            'item_image' => ['nullable', 'image', 'max:5120'],
        ]);

        if ($request->hasFile('item_image')) {
            @ini_set('memory_limit', '512M');
            $image = $request->file('item_image');
            $directory = config('filesystems.order_image_path');
            File::ensureDirectoryExists($directory);
            abort_unless(is_dir($directory) && is_writable($directory), 500, 'The order image directory is not writable.');
            $this->validateImageDimensionsForProcessing($image->getRealPath());
            $filename = $image->hashName();
            $path = $directory . '/' . $filename;

            $this->resizeImage($image->getRealPath(), $image->getClientMimeType(), $path);

            abort_unless(is_file($path), 500, 'The order image could not be saved.');
            $data['item_image'] = 'order_image/' . $filename;
        }

        $data['status'] = $data['status'] ?: Way::STATUS_PENDING;
        $way = Way::create($data);

        WayStatusHistory::create([
            'way_id' => $way->id,
            'status' => $way->status,
            'changed_by' => Auth::user()->name,
        ]);

        return redirect()->route('admin.way-check')->with('way_status', 'Way created successfully.');
    }

    public function store(Request $request, User $shop): \Symfony\Component\HttpFoundation\Response
    {
        abort_unless($shop->role === User::ROLE_SHOP, 404);

        $data = $request->validateWithBag('way', [
            'item_image' => ['nullable', 'image', 'max:5120'],
            'amount' => ['required', 'numeric', 'min:0'],
            'delivery_fees' => ['required', 'numeric', 'min:0'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'phone_number' => ['required', 'string', 'max:30'],
            'date' => ['required', 'date'],
            'remark' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($request->hasFile('item_image')) {
            @ini_set('memory_limit', '512M');
            $image = $request->file('item_image');
            $directory = config('filesystems.order_image_path');
            File::ensureDirectoryExists($directory);
            abort_unless(is_dir($directory) && is_writable($directory), 500, 'The order image directory is not writable.');
            $this->validateImageDimensionsForProcessing($image->getRealPath());
            $filename = $image->hashName();
            $path = $directory . '/' . $filename;

            $this->resizeImage($image->getRealPath(), $image->getClientMimeType(), $path);

            abort_unless(is_file($path), 500, 'The order image could not be saved.');
            $data['item_image'] = 'order_image/' . $filename;
        }

        $data['shop_id'] = $shop->id;
        $data['status'] = Way::STATUS_PENDING;
        $way = Way::create($data);

        WayStatusHistory::create([
            'way_id' => $way->id,
            'status' => $way->status,
            'changed_by' => Auth::user()->name,
        ]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'New way created successfully.', 'way' => [
                'id' => $way->id,
                'recipient_name' => $way->recipient_name,
                'address' => $way->address,
                'phone_number' => $way->phone_number,
                'amount' => $way->amount,
                'delivery_fees' => $way->delivery_fees,
                'status' => $way->status,
                'date' => $way->date->format('d-m-Y'),
                'item_image' => $way->item_image ? asset($way->item_image) : null,
                'remark' => $way->remark,
            ]]);
        }

        return redirect()->route('admin.shops')->with('way_created', 'New way created successfully.');
    }
}