<?php

use Illuminate\Support\Facades\Route;

// Search controller
use App\Http\Controllers\Mobile\TherapistSearchController;

// Profile controller
use App\Http\Controllers\Mobile\MobileTherapistController;
use App\Http\Controllers\Mobile\MobileAppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;

// MOBILE LOGIN
Route::get('/login', [AuthenticatedSessionController::class, 'createMobile'])
    ->name('mobile.login');

Route::post('/login', [AuthenticatedSessionController::class, 'storeMobile'])
    ->name('mobile.login.store');

// ---------------------------------------------------------
// MOBILE AREA (single, unified group)test
// ---------------------------------------------------------
Route::middleware(['web'])
    ->prefix('mobile')
    ->name('mobile.')
    ->group(function () {
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');
	
	

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
			
		        Route::get('/therapeute/{slug}/prendre-rdv', [MobileAppointmentController::class, 'createFromTherapistSlug'])
            ->name('appointments.create_from_therapist');

        Route::post('/rdv', [MobileAppointmentController::class, 'store'])
            ->name('appointments.store');

        Route::get('/rdv/{token}', [MobileAppointmentController::class, 'show'])
            ->name('appointments.show');

        Route::get('/rdv/{token}/ics', [MobileAppointmentController::class, 'downloadICS'])
            ->name('appointments.ics');

        Route::get('/api/slots', [MobileAppointmentController::class, 'getAvailableSlotsForPatient'])
            ->name('appointments.slots');

        Route::get('/api/dates-concretes', [MobileAppointmentController::class, 'availableConcreteDatesPatient'])
            ->name('appointments.concrete_dates');
    });
