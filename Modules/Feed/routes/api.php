<?php

use Illuminate\Support\Facades\Route;
use Modules\Feed\Http\Controllers\Api\NewItemController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('feeds', NewItemController::class)->names('feed');
});
