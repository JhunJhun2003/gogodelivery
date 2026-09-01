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
            'ways' => $waysQuery->paginate(50)->withQueryString(),
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

    private function findBrowserBinary(): ?string
    {
        $candidates = [
            'C:/Program Files/Google/Chrome/Application/chrome.exe',
            'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
            'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
            'C:/Program Files/Microsoft/Edge/Application/msedge.exe',
            getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA') . '/Google/Chrome/Application/chrome.exe' : null,
            getenv('LOCALAPPDATA') ? getenv('LOCALAPPDATA') . '/Microsoft/Edge/Application/msedge.exe' : null,
            getenv('PROGRAMFILES') ? getenv('PROGRAMFILES') . '/Google/Chrome/Application/chrome.exe' : null,
            getenv('PROGRAMFILES(X86)') ? getenv('PROGRAMFILES(X86)') . '/Google/Chrome/Application/chrome.exe' : null,
            getenv('PROGRAMFILES(X86)') ? getenv('PROGRAMFILES(X86)') . '/Microsoft/Edge/Application/msedge.exe' : null,
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
            '/usr/bin/chromium-browser',
            '/usr/bin/chromium',
            '/snap/bin/chromium',
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate)) {
                return str_replace('\\', '/', $candidate);
            }
        }

        // Fallback: search in PATH on Windows
        $whereChrome = @shell_exec('where chrome 2>nul');
        if ($whereChrome) {
            $path = trim(explode("\n", trim($whereChrome))[0]);
            if ($path && file_exists($path)) {
                return str_replace('\\', '/', $path);
            }
        }

        $whereEdge = @shell_exec('where msedge 2>nul');
        if ($whereEdge) {
            $path = trim(explode("\n", trim($whereEdge))[0]);
            if ($path && file_exists($path)) {
                return str_replace('\\', '/', $path);
            }
        }

        $which = @shell_exec('which chromium-browser 2>/dev/null || which chromium 2>/dev/null || which google-chrome 2>/dev/null');
        if ($which) {
            $path = trim(explode("\n", trim($which))[0]);
            if ($path && file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function buildAdminHistoryPdfHtml(string $title, array $rows, bool $printable = false): string
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
                . '<td>' . e($row['shop'] ?? '') . '</td>'
                . '<td>' . e($row['date'] ?? '') . '</td>'
                . '<td style="text-align:right;">' . e(number_format($amount, 2, '.', '')) . '</td>'
                . '<td style="text-align:right;">' . e(number_format($fees, 2, '.', '')) . '</td>'
                . '<td>' . ($row['customer'] ?? '') . '</td>'
                . '<td>' . e($row['biker'] ?? '') . '</td>'
                . '<td>' . e($row['status'] ?? '') . '</td>'
                . '<td>' . e($row['deli_date'] ?? '') . '</td>'
                . '<td>' . e($row['remark'] ?? '') . '</td>'
                . '</tr>';
        }

        if ($rowHtml === '') {
            $rowHtml = '<tr><td colspan="10" style="text-align:center;">No records found.</td></tr>';
        }

        $generatedAt = now()->format('d-m-Y H:i');

        $totalRow = '<tr style="font-weight:bold; background:#e2e8f0;">'
            . '<td colspan="3" style="text-align:right;">Total</td>'
            . '<td style="text-align:right;">' . e(number_format($totalAmount, 2, '.', '')) . '</td>'
            . '<td style="text-align:right;">' . e(number_format($totalFees, 2, '.', '')) . '</td>'
            . '<td colspan="5"></td>'
            . '</tr>';

        $printButton = $printable
            ? '<div id="toolbar" style="position:fixed;top:0;left:0;right:0;background:#0f172a;color:#fff;padding:10px 24px;display:flex;justify-content:space-between;align-items:center;z-index:9999;font-family:sans-serif;"><span style="font-weight:700;">Admin History Report</span><button onclick="window.print();" style="background:#22c55e;color:#fff;border:none;padding:8px 20px;border-radius:6px;font-size:14px;cursor:pointer;font-weight:700;">Print / Save as PDF</button></div><div style="height:50px;"></div>'
            : '';

        $fontPath = public_path('fonts/NotoSansMyanmar-Regular.ttf');
        $fontSrc = '';
        if (file_exists($fontPath)) {
            $fontBase64 = base64_encode(file_get_contents($fontPath));
            $fontSrc = "@font-face {
                font-family: 'Noto Sans Myanmar';
                src: url('data:font/truetype;charset=utf-8;base64,{$fontBase64}') format('truetype');
                font-weight: normal;
                font-style: normal;
            }";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="my">
<head>
    <meta charset="UTF-8">
    <title>{$title}</title>
        <style>
            {$fontSrc}
            @page {
                size: A4 landscape;
                margin: 10mm 10mm;
            }
            * {
                box-sizing: border-box;
            }
            body {
                font-family: 'Noto Sans Myanmar', 'Pyidaungsu', 'Myanmar Text', 'Padauk', sans-serif;
                margin: 0;
                padding: 0;
                color: #0f172a;
                font-size: 11px;
                line-height: 1.4;
                -webkit-font-smoothing: antialiased;
            }
            @media print {
                #toolbar { display: none !important; }
                body { margin: 0; }
            }
        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            margin-bottom: 14px;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
        }
        h1 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #0f172a;
        }
        .meta {
            font-size: 11px;
            color: #64748b;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
        }
        th {
            background-color: #f1f5f9;
            color: #0f172a;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.04em;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
    </style>
</head>
<body>
    {$printButton}
    <div class="header-bar">
        <h1>{$title}</h1>
        <div class="meta">Generated: {$generatedAt}</div>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 32px;">No</th>
                <th>Shop</th>
                <th>Date</th>
                <th style="text-align:right;">Amount</th>
                <th style="text-align:right;">Deli Fees</th>
                <th>Customer Detail</th>
                <th>Biker</th>
                <th>Status</th>
                <th>Deli Date</th>
                <th>Remark</th>
            </tr>
        </thead>
        <tbody>
            {$rowHtml}
            {$totalRow}
        </tbody>
    </table>
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
                'shop' => $way->shop?->name ?? 'N/A',
                'date' => $way->date?->format('d-m-Y') ?? '',
                'amount' => number_format((float) $way->amount, 2, '.', ''),
                'fees' => number_format((float) $way->delivery_fees, 2, '.', ''),
                'customer' => '<div><strong>' . e($way->recipient_name) . '</strong></div>'
                    . '<div>' . e($way->address) . '</div>'
                    . '<small style="color:#64748b;">' . e($way->phone_number) . '</small>',
                'biker' => $way->biker?->name ?? 'Unassigned',
                'status' => $way->status === 'onway' ? 'On way' : ucfirst($way->status),
                'deli_date' => $way->assigned_at ? $way->assigned_at->format('d-m-Y') : $way->date->format('d-m-Y'),
                'remark' => $way->remark ?: '—',
            ];
        }

        $html = $this->buildAdminHistoryPdfHtml('Admin History Report', $rows, true);

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=utf-8',
            'Content-Disposition' => 'inline; filename="' . str_replace('.pdf', '.html', $filename) . '"',
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