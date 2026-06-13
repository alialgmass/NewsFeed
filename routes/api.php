<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\LoginController;
use Modules\Auth\Http\Controllers\Api\ProfileController;
use Modules\Auth\Http\Controllers\Api\RegisterController;
use Modules\Auth\Http\Controllers\Api\TokenController;

Route::post('/auth/register', [RegisterController::class, 'register'])
    ->middleware('throttle:gateway:api')
    ->name('auth.register');

Route::post('/auth/login', [LoginController::class, 'login'])
    ->middleware('throttle:gateway:api')
    ->name('auth.login');

Route::middleware(['auth:sanctum', 'throttle:gateway:api'])->group(function () {
    Route::post('/auth/refresh', [TokenController::class, 'refresh'])
        ->middleware('ability:refresh')
        ->name('auth.refresh');

    Route::post('/auth/logout', [TokenController::class, 'logout'])
        ->name('auth.logout');

    Route::post('/auth/logout/all', [TokenController::class, 'revokeAll'])
        ->name('auth.logout.all');

    Route::get('/auth/me', [ProfileController::class, 'show'])
        ->name('auth.me');
});

Route::middleware(['gateway.api-key', 'throttle:gateway:api'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    })->middleware('auth:sanctum');
});
