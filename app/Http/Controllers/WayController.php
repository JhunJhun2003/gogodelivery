<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Way;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class WayController extends Controller
{
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