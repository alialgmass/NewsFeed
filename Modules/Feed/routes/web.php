<?php

use Illuminate\Support\Facades\Route;
use Modules\Feed\Http\Controllers\FeedController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('feeds', FeedController::class)->names('feed');
});
