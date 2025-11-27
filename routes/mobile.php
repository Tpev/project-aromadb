<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mobile\TherapistSearchController as MobileTherapistSearchController;

Route::middleware(['web'])->prefix('mobile')->name('mobile.')->group(function () {
    // Mobile entry (already existing)
    Route::get('/', function () {
        return view('mobile.entry');
    })->name('entry');

        // --- Mobile Search Page (the page you just built) ---
        Route::get('/therapeutes', function () {
            return view('mobile.therapists.index');
        })->name('therapists.index');

    // Search (POST from mobile hero form)
    Route::post('/therapeutes/recherche', [MobileTherapistSearchController::class, 'index'])
        ->name('therapists.search');

    // Public profile
    Route::get('/therapeute/{slug}', [MobileTherapistSearchController::class, 'show'])
        ->name('therapists.show');

    // SEO filters by specialty
    Route::get('/praticien-{specialty}', [MobileTherapistSearchController::class, 'filterBySpecialty'])
        ->name('therapists.filter.specialty');

    // SEO filters by region
    Route::get('/region-{region}', [MobileTherapistSearchController::class, 'filterByRegion'])
        ->name('therapists.filter.region');

    // SEO filters by specialty + region
    Route::get('/praticien-{specialty}-region-{region}', [MobileTherapistSearchController::class, 'filterBySpecialtyRegion'])
        ->name('therapists.filter.specialty_region');
});
