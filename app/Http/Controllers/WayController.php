<?php

namespace App\Http\Controllers;

use App\Models\Biker;
use App\Models\User;
use App\Models\Way;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class WayController extends Controller
{
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