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

        // ENTRY: /mobile  → mobile.entry
        Route::get('/', function () {
            return view('mobile.entry');
        })->name('entry');

        // ---------------------------------------------------------
        // SEARCH
        // /mobile/recherche-praticien → *NEW* route name to avoid any collision
        // ---------------------------------------------------------

        // Search form page
        Route::get('/recherche-praticien', [TherapistSearchController::class, 'index'])
            ->name('search.index');

        // Search action handler (POST from the form)
        Route::post('/recherche-praticien', [TherapistSearchController::class, 'search'])
            ->name('search.submit');

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
