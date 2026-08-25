<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

Route::get('/login', function () {
    return view('auth.login');
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
