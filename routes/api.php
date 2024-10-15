<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Events\SignalingEvent;
use App\Http\Controllers\SignalingController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/signaling', [SignalingController::class, 'signaling']);