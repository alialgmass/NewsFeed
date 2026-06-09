<?php

use Illuminate\Support\Facades\Route;
use Modules\Gateway\Http\Controllers\WebhookController;

Route::post('{provider}', [WebhookController::class, 'handle'])->name('webhooks.handle');
