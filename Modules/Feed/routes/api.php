<?php

use Illuminate\Support\Facades\Route;
use Modules\Feed\Http\Controllers\Api\NewsItemController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('feeds', NewsItemController::class)->names('feed');
});
