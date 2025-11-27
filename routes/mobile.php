<?php

use Illuminate\Support\Facades\Route;

// Search controller
use App\Http\Controllers\Mobile\TherapistSearchController;

// Profile controller
use App\Http\Controllers\Mobile\MobileTherapistController;

// ---------------------------------------------------------
// MOBILE AREA (single, unified group)
// ---------------------------------------------------------
Route::middleware(['web'])
    ->prefix('mobile')
    ->name('mobile.')
    ->group(function () {

        // Entry screen: "Espace Client / Espace Praticien"
        Route::get('/', function () {
            return view('mobile.entry');
        })->name('entry');

        // ---------------------------------------------------------
        // SEARCH
        // ---------------------------------------------------------

        // Search form page
        Route::get('/therapeutes', [TherapistSearchController::class, 'index'])
            ->name('therapists.index');

        // Search action handler (POST from the form)
        Route::post('/therapeutes/rechercher', [TherapistSearchController::class, 'search'])
            ->name('therapists.search');

        // ---------------------------------------------------------
        // THERAPIST PUBLIC PROFILE (MOBILE VERSION)
        // ---------------------------------------------------------

        // Show therapist profile
        Route::get('/therapeute/{slug}', [MobileTherapistController::class, 'show'])
            ->name('therapists.show');

        // Send information request (modal form)
        Route::post('/therapeute/{slug}/information', [MobileTherapistController::class, 'sendInformationRequest'])
            ->name('therapists.information');
    });
