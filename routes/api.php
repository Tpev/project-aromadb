<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\SignalingEvent;
use App\Http\Controllers\SignalingController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\OfferJourneys\OfferJourneySesWebhookController;

use App\Http\Controllers\Api\DesignTemplateController as ApiDesignTemplateController;

Route::get('/design-templates', [ApiDesignTemplateController::class, 'index']); // list (optional filters)
Route::get('/design-templates/{template}', [ApiDesignTemplateController::class, 'show']); // returns konva_json

Route::post('/subscription', [StripeWebhookController::class, 'handleWebhook']);
Route::post('/webhooks/offer-journeys/ses', OfferJourneySesWebhookController::class)
    ->middleware('throttle:120,1')
    ->name('api.offer-journeys.ses-webhook');

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/signaling', [SignalingController::class, 'signaling']);
