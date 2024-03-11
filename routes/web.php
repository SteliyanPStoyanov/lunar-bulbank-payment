<?php

use Illuminate\Support\Facades\Route;
use Lunar\BulBank\Http\Controllers\WebhookController;
use Lunar\BulBank\Http\Controllers\CheckStatusController;

Route::post('/back-ref-url', WebhookController::class);
Route::get('/check-status/{order}', CheckStatusController::class);
