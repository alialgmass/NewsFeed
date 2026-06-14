<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\AutocompleteController;

Route::prefix('search')->group(function () {
    Route::get('autocomplete', [AutocompleteController::class, 'suggest']);
    Route::post('autocomplete/track', [AutocompleteController::class, 'track']);
});
