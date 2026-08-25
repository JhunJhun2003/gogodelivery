<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->file(resource_path('views/auth/login.html'));
});

Route::get('/login', function () {
    return response()->file(resource_path('views/auth/login.html'));
});

Route::get('/css/{file}', function (string $file) {
    abort_unless(in_array($file, ['global.css', 'components.css', 'screens.css'], true), 404);

    return response()->file(resource_path("views/css/{$file}"));
});

Route::get('/js/{file}', function (string $file) {
    abort_unless(in_array($file, ['sidebar.js', 'history-controls.js'], true), 404);

    return response()->file(resource_path("views/js/{$file}"));
});
