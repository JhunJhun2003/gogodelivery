<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BikerController;
use App\Http\Controllers\WayController;
use App\Models\Biker;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return match (auth()->user()->role) {
        User::ROLE_ADMIN  => redirect()->route('admin.shops'),
        User::ROLE_SHOP   => redirect()->route('shop.orders'),
        User::ROLE_BIKER  => redirect()->route('bikers.ways'),
        default           => redirect()->route('login'),
    };
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::get('/order_image/{filename}', function (string $filename) {
    abort_unless(preg_match('/\A[A-Za-z0-9._-]+\z/', $filename), 404);

    $path = rtrim(config('filesystems.order_image_path'), DIRECTORY_SEPARATOR)
        . DIRECTORY_SEPARATOR . $filename;

    abort_unless(is_file($path), 404);

    return response()->file($path);
})->where('filename', '[A-Za-z0-9._-]+');

Route::middleware('auth', 'role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/shops', [AuthController::class, 'showShops'])->name('shops');
    Route::post('/shops', [AuthController::class, 'createShop'])->name('shops.create');
    Route::put('/shops/{shop}', [AuthController::class, 'updateShop'])->name('shops.update');
    Route::post('/shops/{shop}/ways', [WayController::class, 'store'])->name('shops.ways.store');
    Route::get('/users', [AuthController::class, 'showUsers'])->name('users');
    Route::post('/users', [AuthController::class, 'createUser'])->name('users.create');
    Route::get('/bikers', [BikerController::class, 'index'])->name('bikers');
    Route::post('/bikers', [BikerController::class, 'store'])->name('bikers.create');
    Route::put('/bikers/{biker}', [BikerController::class, 'update'])->name('bikers.update');
    Route::post('/bikers/{biker}/ways', [BikerController::class, 'assign'])->name('bikers.ways.assign');
    Route::post('/ways/{way}/status', [WayController::class, 'updateAdminStatus'])->name('ways.status');
    Route::get('/ways/{way}/history', [WayController::class, 'wayHistory'])->name('ways.history');
    Route::get('/history', [WayController::class, 'history'])->name('history');
    Route::get('/history/{way}', [WayController::class, 'historyDetail'])->name('history.detail');
    Route::get('/ways/{way}/edit', [WayController::class, 'editWay'])->name('ways.edit');
    Route::put('/ways/{way}', [WayController::class, 'updateWay'])->name('ways.update');
    Route::get('/way-check', [WayController::class, 'check'])->name('way-check');
    Route::post('/way-check', [WayController::class, 'storeFromCheck'])->name('way-check.store');
});

Route::middleware('auth', 'role:shop')->prefix('shop')->name('shop.')->group(function () {
    Route::get('/orders', [WayController::class, 'shopOrders'])->name('orders');
    Route::get('/history', [WayController::class, 'shopHistory'])->name('history');
    Route::get('/history/{way}', [WayController::class, 'shopHistoryDetail'])->name('history.detail');
});

Route::middleware('auth', 'role:biker')->prefix('bikers')->name('bikers.')->group(function () {
    Route::get('/ways', [WayController::class, 'bikerWays'])->name('ways');
    Route::post('/ways/{way}/status', [WayController::class, 'updateBikerStatus'])->name('ways.status');
    Route::get('/history', [WayController::class, 'bikerHistory'])->name('history');
    Route::get('/history/{way}', [WayController::class, 'bikerHistoryDetail'])->name('history.detail');
});

Route::get('/css/{file}', function (string $file) {
    abort_unless(in_array($file, ['global.css', 'components.css', 'screens.css'], true), 404);

    return response()->file(resource_path("views/css/{$file}"), ['Content-Type' => 'text/css']);
});

Route::get('/js/{file}', function (string $file) {
    abort_unless(in_array($file, ['sidebar.js', 'history-controls.js'], true), 404);

    return response()->file(resource_path("views/js/{$file}"), ['Content-Type' => 'application/javascript']);
});

Route::get('/assets/{file}', function (string $file) {
    abort_unless(in_array($file, ['logo.jpg', 'logo-nobg.png'], true), 404);

    $mime = str_ends_with($file, '.png') ? 'image/png' : 'image/jpeg';

    return response()->file(resource_path("views/assets/{$file}"), ['Content-Type' => $mime]);
});

Route::get('/animations/{file}', function (string $file) {
    abort_unless($file === 'food-courier.json', 404);

    return response()->file(resource_path("views/Food Courier.json"), [
        'Content-Type' => 'application/json',
        'Cache-Control' => 'public, max-age=86400',
    ]);
});
