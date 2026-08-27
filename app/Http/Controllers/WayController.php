<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\User;
use App\Models\Way;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class WayController extends Controller
{
    public function shopOrders(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $shop = Auth::user();
        $ordersQuery = Way::query()
            ->where('shop_id', $shop->id)
            ->whereNotIn('status', ['delivered', 'failed'])
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

    public function check(): View
    {
        $today = today();

        return view('admin.way-check', [
            'today' => $today,
            'totalWays' => Way::query()->whereDate('date', $today)->count(),
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
            $directory = public_path('order_image');
            File::ensureDirectoryExists($directory);
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

            imagejpeg($source, $path, 85);
            imagedestroy($source);
            $data['item_image'] = 'order_image/' . $filename;
        }

        $data['status'] = $data['status'] ?: 'pending';
        Way::create($data);

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
            $directory = public_path('order_image');
            File::ensureDirectoryExists($directory);
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

            imagejpeg($source, $path, 85);
            imagedestroy($source);
            $data['item_image'] = 'order_image/' . $filename;
        }

        $data['shop_id'] = $shop->id;
        $data['status'] = 'pending';
        Way::create($data);

        return redirect()->route('admin.shops')->with('way_status', 'New way created successfully.');
    }
}