<?php

use Illuminate\Support\Facades\Route;
use Lunar\BulBank\Http\Controllers\WebhookController;

Route::post('/back-ref-url', WebhookController::class);
