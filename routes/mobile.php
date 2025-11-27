<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\TherapistSearchController as MobileTherapistSearchController;

Route::middleware(['web'])->group(function () {
    Route::get('/mobile', function () {
        return view('mobile.entry');
    })->name('mobile.entry');
});

/*
Route::middleware(['web', 'mobile.app'])->group(function () {
    Route::get('/mobile', function () {
        return view('mobile.entry');
    })->name('mobile.entry');
});
*/

Route::middleware(['web'])
    ->prefix('mobile')
    ->name('mobile.')
    ->group(function () {
        // Search form (your Blade above)
        Route::get('/therapeutes', function () {
            return view('mobile.therapists.index');
        })->name('therapists.index');

        // Search action (button "Rechercher des praticiens")
        Route::post('/therapeutes/rechercher', [MobileTherapistSearchController::class, 'index'])
            ->name('therapists.search');

        // Mobile public profile (used by "Voir le profil" button)
        Route::get('/therapeute/{slug}', [MobileTherapistSearchController::class, 'show'])
            ->name('therapists.show');
    });
