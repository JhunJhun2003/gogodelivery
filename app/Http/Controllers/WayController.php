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

class WayController extends Controller
{
    public function bikerWays(): View
    {
        $biker = Auth::user()->biker;
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
        $biker = Auth::user()->biker;
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

    public function wayHistory(Way $way): \Illuminate\Http\JsonResponse
    {
        abort_unless(Auth::user()?->isAdmin(), 403);

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
        $biker = Auth::user()->biker;
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
        $biker = Auth::user()->biker;
        abort_unless($biker && $way->biker_id === $biker->id, 404);
        $way->load(['shop', 'biker']);

        return view('bikers.history-detail', compact('way'));
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
                $query->where('recipient_name', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%")
                    ->orWhere('remark', 'like', "%{$search}%");
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
            $image = $request->file('item_image');
            $directory = config('filesystems.order_image_path');
            File::ensureDirectoryExists($directory);
            abort_unless(is_dir($directory) && is_writable($directory), 500, 'The order image directory is not writable.');
            $filename = $image->hashName();
            $path = $directory . '/' . $filename;

            $source = match ($image->getClientMimeType()) {
                'image/jpeg' => imagecreatefromjpeg($image->getRealPath()),
                'image/png' => imagecreatefrompng($image->getRealPath()),
                'image/webp' => imagecreatefromwebp($image->getRealPath()),
                default => imagecreatefromjpeg($image->getRealPath()),
            };

            $maxWidth = 800;
            $maxHeight = 800;
            $width = imagesx($source);
            $height = imagesy($source);

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            abort_unless(imagejpeg($source, $path, 85) && is_file($path), 500, 'The order image could not be saved.');
            imagedestroy($source);
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
            $image = $request->file('item_image');
            $directory = config('filesystems.order_image_path');
            File::ensureDirectoryExists($directory);
            abort_unless(is_dir($directory) && is_writable($directory), 500, 'The order image directory is not writable.');
            $filename = $image->hashName();
            $path = $directory . '/' . $filename;

            $source = match ($image->getClientMimeType()) {
                'image/jpeg' => imagecreatefromjpeg($image->getRealPath()),
                'image/png' => imagecreatefrompng($image->getRealPath()),
                'image/webp' => imagecreatefromwebp($image->getRealPath()),
                default => imagecreatefromjpeg($image->getRealPath()),
            };

            $maxWidth = 800;
            $maxHeight = 800;
            $width = imagesx($source);
            $height = imagesy($source);

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            abort_unless(imagejpeg($source, $path, 85) && is_file($path), 500, 'The order image could not be saved.');
            imagedestroy($source);
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

    public function store(Request $request, User $shop): RedirectResponse
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
            $image = $request->file('item_image');
            $directory = config('filesystems.order_image_path');
            File::ensureDirectoryExists($directory);
            abort_unless(is_dir($directory) && is_writable($directory), 500, 'The order image directory is not writable.');
            $filename = $image->hashName();
            $path = $directory . '/' . $filename;

            $source = match ($image->getClientMimeType()) {
                'image/jpeg' => imagecreatefromjpeg($image->getRealPath()),
                'image/png' => imagecreatefrompng($image->getRealPath()),
                'image/webp' => imagecreatefromwebp($image->getRealPath()),
                default => imagecreatefromjpeg($image->getRealPath()),
            };

            $maxWidth = 800;
            $maxHeight = 800;
            $width = imagesx($source);
            $height = imagesy($source);

            if ($width > $maxWidth || $height > $maxHeight) {
                $ratio = min($maxWidth / $width, $maxHeight / $height);
                $newWidth = (int) ($width * $ratio);
                $newHeight = (int) ($height * $ratio);
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($source);
                $source = $resized;
            }

            abort_unless(imagejpeg($source, $path, 85) && is_file($path), 500, 'The order image could not be saved.');
            imagedestroy($source);
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

        return redirect()->route('admin.shops')->with('way_status', 'New way created successfully.');
    }
}