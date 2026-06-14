<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Gateway\Http\Controllers\Api\GatewayController;

Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::apiResource('keys', GatewayController::class);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
