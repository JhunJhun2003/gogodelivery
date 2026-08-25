<?php

use App\Http\Controllers\AuthController;
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

Route::middleware('auth', 'role:admin')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/shops', fn () => view('admin.shops'))->name('shops');
    Route::get('/users', fn () => view('admin.users'))->name('users');
    Route::get('/bikers', fn () => view('admin.bikers'))->name('bikers');
    Route::get('/history', fn () => view('admin.history'))->name('history');
    Route::get('/way-check', fn () => view('admin.way-check'))->name('way-check');
});

Route::middleware('auth', 'role:shop')->prefix('shop')->name('shop.')->group(function () {
    Route::get('/orders', fn () => view('shop.orders'))->name('orders');
    Route::get('/history', fn () => view('shop.history'))->name('history');
});

Route::middleware('auth', 'role:biker')->prefix('bikers')->name('bikers.')->group(function () {
    Route::get('/ways', fn () => view('bikers.ways'))->name('ways');
    Route::get('/history', fn () => view('bikers.history'))->name('history');
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
