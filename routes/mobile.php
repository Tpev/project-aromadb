<?php

use Illuminate\Support\Facades\Route;

// Search controller
use App\Http\Controllers\Mobile\TherapistSearchController;

// Profile controller
use App\Http\Controllers\Mobile\MobileTherapistController;
use App\Http\Controllers\Mobile\MobileAppointmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AppointmentController;


// ---------------------------------------------------------
// MOBILE AREA (single, unified group)
// ---------------------------------------------------------
Route::middleware(['web'])
    ->prefix('mobile')
    ->name('mobile.')
    ->group(function () {

        // ---------------------------------------------------------
        // MOBILE LOGIN + LOGOUT
        // ---------------------------------------------------------
        Route::get('/login', [AuthenticatedSessionController::class, 'createMobile'])
            ->name('login');

        Route::post('/login', [AuthenticatedSessionController::class, 'storeMobile'])
            ->middleware('guest')
            ->name('login.store');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth')
            ->name('logout');

        // 📅 INDEX RDV PRO (mobile)
        Route::get('/rendez-vous', [AppointmentController::class, 'index'])
            ->middleware('auth')
            ->name('appointments.index');
			
			
		Route::get('/rendez-vous/{appointment}', [AppointmentController::class, 'show'])
			->middleware(['auth'])
			->name('appointments.show');

        // ---------------------------------------------------------
        // MOBILE DASHBOARD (PRO)
        // ---------------------------------------------------------
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware(['auth'])
            ->name('dashboard');


        // ---------------------------------------------------------
        // ENTRY: /mobile → mobile.entry
        // ---------------------------------------------------------
        Route::get('/', function () {
            return view('mobile.entry');
        })->name('entry');


        // ---------------------------------------------------------
        // SEARCH
        // ---------------------------------------------------------
        Route::get('/recherche-praticien', [TherapistSearchController::class, 'index'])
            ->name('search.index');

        Route::post('/recherche-praticien', [TherapistSearchController::class, 'search'])
            ->name('search.submit');


        // ---------------------------------------------------------
        // THERAPIST PUBLIC PROFILE (MOBILE VERSION)
        // ---------------------------------------------------------
        Route::get('/therapeute/{slug}', [MobileTherapistController::class, 'show'])
            ->name('therapists.show');

        Route::post('/therapeute/{slug}/information', [MobileTherapistController::class, 'sendInformationRequest'])
            ->name('therapists.information');

        Route::get('/therapeute/{slug}/prendre-rdv', [MobileAppointmentController::class, 'createFromTherapistSlug'])
            ->name('appointments.create_from_therapist');


        // ---------------------------------------------------------
        // APPOINTMENTS
        // ---------------------------------------------------------
        Route::post('/rdv', [MobileAppointmentController::class, 'store'])
            ->name('appointments.store');

        Route::get('/rdv/{token}', [MobileAppointmentController::class, 'show'])
            ->name('rdv.show');

        Route::get('/rdv/{token}/ics', [MobileAppointmentController::class, 'downloadICS'])
            ->name('appointments.ics');

        Route::get('/api/slots', [MobileAppointmentController::class, 'getAvailableSlotsForPatient'])
            ->name('appointments.slots');

        Route::get('/api/dates-concretes', [MobileAppointmentController::class, 'availableConcreteDatesPatient'])
            ->name('appointments.concrete_dates');
    });
