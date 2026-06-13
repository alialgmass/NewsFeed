<?php

use Illuminate\Support\Facades\Route;
use Modules\Gateway\Http\Controllers\Api\GatewayController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('gateways', GatewayController::class)->names('gateway');
});
