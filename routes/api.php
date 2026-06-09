<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/auth/register', [\Modules\Auth\Http\Controllers\RegisterController::class, 'register'])
    ->middleware('throttle:gateway:api')
    ->name('auth.register');

Route::post('/auth/login', [\Modules\Auth\Http\Controllers\LoginController::class, 'login'])
    ->middleware('throttle:gateway:api')
    ->name('auth.login');

Route::middleware(['auth:sanctum', 'throttle:gateway:api'])->group(function () {
    Route::post('/auth/refresh', [\Modules\Auth\Http\Controllers\TokenController::class, 'refresh'])
        ->middleware('ability:refresh')
        ->name('auth.refresh');

    Route::post('/auth/logout', [\Modules\Auth\Http\Controllers\TokenController::class, 'logout'])
        ->name('auth.logout');

    Route::post('/auth/logout/all', [\Modules\Auth\Http\Controllers\TokenController::class, 'revokeAll'])
        ->name('auth.logout.all');

    Route::get('/auth/me', [\Modules\Auth\Http\Controllers\ProfileController::class, 'show'])
        ->name('auth.me');
});

Route::middleware(['gateway.api-key', 'throttle:gateway:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
