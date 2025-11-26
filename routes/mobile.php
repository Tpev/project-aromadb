<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'mobile.app'])->group(function () {
    Route::get('/mobile', function () {
        return view('mobile.entry');
    })->name('mobile.entry');
});
