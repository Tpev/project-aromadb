<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\SignalingEvent;
use App\Http\Controllers\SignalingController;
use App\Http\Controllers\StripeWebhookController;

Route::post('/subscription', [StripeWebhookController::class, 'handleWebhook']);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/signaling', [SignalingController::class, 'signaling']);
