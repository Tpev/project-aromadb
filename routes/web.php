<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HuileHEController;
use App\Http\Controllers\RecetteController;
use App\Http\Controllers\HuileHVController;
use App\Http\Controllers\FavoriteController;
use App\Models\Favorite;
use App\Http\Controllers\TisaneController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogPostController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\SessionNoteController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTherapistController;
use App\Http\Controllers\UserLicenseController;
use App\Http\Controllers\QuestionnaireController;
use App\Http\Controllers\TestimonialController;
use App\Http\Controllers\TestimonialRequestController;


// Routes accessibles publiquement via le lien dans l'email
Route::get('/testimonials/submit/{token}', [TestimonialController::class, 'showSubmitForm'])->name('testimonials.submit');
Route::post('/testimonials/submit/{token}', [TestimonialController::class, 'submit'])->name('testimonials.submit.post');
Route::get('/testimonials/thankyou', [TestimonialController::class, 'thankYou'])->name('testimonials.thankyou');


// Unavailability Routes
Route::middleware(['auth'])->group(function () {
	Route::post('/client-profiles/{clientProfile}/request-testimonial', [TestimonialRequestController::class, 'sendRequest'])->name('testimonial.request');
    // Show unavailability form
    Route::get('/unavailability/create', [AppointmentController::class, 'createUnavailability'])->name('unavailabilities.create');

    // Store unavailability
    Route::post('/unavailability/store', [AppointmentController::class, 'storeUnavailability'])->name('unavailabilities.store');

    // List unavailability
    Route::get('/unavailability', [AppointmentController::class, 'indexUnavailability'])->name('unavailabilities.index');

    // Delete unavailability
    Route::delete('/unavailability/{id}', [AppointmentController::class, 'destroyUnavailability'])->name('unavailabilities.destroy');
});


//  EPICPP

// Grouping routes that require authentication
Route::middleware(['auth'])->group(function () {
    Route::get('/questionnaires/send', [QuestionnaireController::class, 'showSendQuestionnaire'])->name('questionnaires.send.show');

    // Define the route for sending a questionnaire
    Route::post('/questionnaires/{questionnaire}/send', [QuestionnaireController::class, 'send'])->name('questionnaires.send');
 // Route to view a specific response
    Route::get('questionnaires/responses/{id}', [QuestionnaireController::class, 'showResponse'])->name('questionnaires.responses.show');

    // Routes for managing questionnaires (CRUD operations)
    Route::resource('questionnaires', QuestionnaireController::class); // All CRUD operations

    Route::delete('questionnaires/{questionnaire}', [QuestionnaireController::class, 'destroy'])->name('questionnaires.destroy'); // Delete questionnaire

    // Private access route to display all questionnaires (restricted to authenticated therapists)
    Route::get('questionnaires', [QuestionnaireController::class, 'index'])->name('questionnaires.index'); // List all questionnaires
});

// Public access route for filling out a questionnaire using a token
Route::get('questionnaires/remplir/{token}', [QuestionnaireController::class, 'fill'])->name('questionnaires.fill'); // Fill questionnaire
// Define the route for storing responses
Route::post('/questionnaires/remplir/{token}/storeResponses', [QuestionnaireController::class, 'storeResponses'])->name('questionnaires.storeResponses');
Route::get('/thank-you', function () {
    return view('thank_you'); 
})->name('thank_you');


// Routes publiques pour les pages des thérapeutes

// routes/web.php

// Routes pour l'onboarding
Route::middleware(['auth'])->group(function () {
    // Route pour afficher le formulaire d'onboarding
    Route::get('/onboarding', [ProfileController::class, 'showOnboardingForm'])->name('onboarding');

    // Route pour soumettre le formulaire d'onboarding
    Route::post('/onboarding/submit', [ProfileController::class, 'submitOnboarding'])->name('onboarding.submit');
});
Route::middleware(['auth'])->group(function () {


    Route::get('/profile/company-info', [ProfileController::class, 'editCompanyInfo'])->name('profile.editCompanyInfo');
    Route::put('/profile/company-info', [ProfileController::class, 'updateCompanyInfo'])->name('profile.updateCompanyInfo');
});
Route::middleware(['auth'])->group(function () {


    Route::get('/dashboard-pro', [DashboardController::class, 'index'])->name('dashboard-pro');
   
});


Route::middleware(['auth'])->group(function () {
    // Liste des disponibilités de l'utilisateur authentifié
    Route::get('/availabilities', [AvailabilityController::class, 'index'])->name('availabilities.index');

    // Formulaire pour créer une nouvelle disponibilité
    Route::get('/availabilities/create', [AvailabilityController::class, 'create'])->name('availabilities.create');

    // Stocker une nouvelle disponibilité
    Route::post('/availabilities', [AvailabilityController::class, 'store'])->name('availabilities.store');

    // Formulaire pour éditer une disponibilité existante
    Route::get('/availabilities/{availability}/edit', [AvailabilityController::class, 'edit'])
        ->name('availabilities.edit')
        ->middleware('can:update,availability');

    // Mettre à jour une disponibilité existante
    Route::put('/availabilities/{availability}', [AvailabilityController::class, 'update'])
        ->name('availabilities.update')
        ->middleware('can:update,availability');

    // Supprimer une disponibilité
    Route::delete('/availabilities/{availability}', [AvailabilityController::class, 'destroy'])
        ->name('availabilities.destroy')
        ->middleware('can:delete,availability');
});

Route::middleware(['auth'])->group(function () {
    // Liste des produits de l'utilisateur authentifié
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');

    // Formulaire pour créer un nouveau produit
    Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');

    // Stocker un nouveau produit
    Route::post('/products', [ProductController::class, 'store'])->name('products.store');

    // Afficher un produit spécifique
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show')->middleware('can:view,product');

    // Formulaire pour éditer un produit existant
    Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('can:update,product');

    // Mettre à jour un produit existant
    Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update')->middleware('can:update,product');

    // Supprimer un produit
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy')->middleware('can:delete,product');
});



// Invoice Routes

// Invoice Routes



Route::middleware(['auth', 'can:viewAny,App\Models\Invoice'])->group(function () {
    // Liste de toutes les factures de l'utilisateur connecté
   Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePDF'])->name('invoices.pdf')->middleware('can:view,invoice');

	Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');

// Route to list invoices for a specific client profile
Route::get('/client_profiles/{clientProfile}/invoices', [InvoiceController::class, 'clientInvoices'])->name('invoices.client');

Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');

    // Afficher une facture spécifique
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show')->middleware('can:view,invoice');
    
    // Formulaire pour éditer une facture existante
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit')->middleware('can:update,invoice');
    
    // Mettre à jour une facture existante
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update')->middleware('can:update,invoice');
    
    // Supprimer une facture
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy')->middleware('can:delete,invoice');
	// Route to send invoice via email
    Route::post('/invoices/{invoice}/send-email', [InvoiceController::class, 'sendEmail'])
         ->name('invoices.sendEmail');
    Route::put('/invoices/{invoice}/mark-as-paid', [InvoiceController::class, 'markAsPaid'])->name('invoices.markAsPaid');

});


// Session Notes Routes

Route::middleware(['auth', 'can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/client_profiles/{clientProfile}/session_notes', [SessionNoteController::class, 'index'])->name('session_notes.index');
    Route::get('/client_profiles/{clientProfile}/session_notes/create', [SessionNoteController::class, 'create'])->name('session_notes.create');
    Route::post('/client_profiles/{clientProfile}/session_notes', [SessionNoteController::class, 'store'])->name('session_notes.store');
    Route::get('/session_notes/{sessionNote}', [SessionNoteController::class, 'show'])->name('session_notes.show');
    Route::get('/session_notes/{sessionNote}/edit', [SessionNoteController::class, 'edit'])->name('session_notes.edit');
    Route::put('/session_notes/{sessionNote}', [SessionNoteController::class, 'update'])->name('session_notes.update');
    Route::delete('/session_notes/{sessionNote}', [SessionNoteController::class, 'destroy'])->name('session_notes.destroy');
});





// Client Profiles Routes
Route::middleware(['auth','can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/client_profiles', [ClientProfileController::class, 'index'])->name('client_profiles.index'); // Show all client profiles
    Route::get('/client_profiles/create', [ClientProfileController::class, 'create'])->name('client_profiles.create'); // Show form to create a client profile
    Route::post('/client_profiles', [ClientProfileController::class, 'store'])->name('client_profiles.store'); // Handle form submission for creating a client profile
    Route::get('/client_profiles/{clientProfile}', [ClientProfileController::class, 'show'])->name('client_profiles.show');
    Route::get('/client_profiles/{clientProfile}/edit', [ClientProfileController::class, 'edit'])->name('client_profiles.edit'); // Show form to edit a client profile
    Route::put('/client_profiles/{clientProfile}', [ClientProfileController::class, 'update'])->name('client_profiles.update'); // Handle form submission for updating a client profile
    Route::delete('/client_profiles/{clientProfile}', [ClientProfileController::class, 'destroy'])->name('client_profiles.destroy'); // Handle the deletion of a client profile
});


// Public Routes for Patient Booking
Route::post('/appointments/available-slots-patient', [AppointmentController::class, 'getAvailableSlotsForPatient'])->name('appointments.available-slots-patient');
// Route AJAX pour récupérer les dates disponibles
Route::post('/appointments/available-dates', [AppointmentController::class, 'getAvailableDates'])->name('appointments.available-dates');
// Route pour récupérer les jours disponibles en fonction de la prestation et du thérapeute
Route::post('/appointments/available-dates-patient', [AppointmentController::class, 'availableDatesPatient'])->name('appointments.available-dates-patient');

Route::middleware(['auth','can:viewAny,App\Models\ClientProfile'])->group(function () {
   
   
    Route::post('/appointments/available-slots', [App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])->name('appointments.available-slots');
    Route::get('/appointments', [App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [App\Http\Controllers\AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('appointments.update');
    Route::delete('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    
 
});




Route::get('/book-appointment/{therapist}', [AppointmentController::class, 'createPatient'])->name('appointments.createPatient');
Route::post('/book-appointment', [AppointmentController::class, 'storePatient'])->name('appointments.storePatient');
Route::get('/appointment-confirmation/{token}', [AppointmentController::class, 'showPatient'])->name('appointments.showPatient');
Route::get('/appointment-ics/{token}', [AppointmentController::class, 'downloadICS'])->name('appointments.downloadICS');





Route::get('/sitemap', [SitemapController::class, 'index']);


Route::middleware([\App\Http\Middleware\TrackPageViews::class])->group(function () {

    // Route to the welcome page directly returning the welcome view
    Route::get('/', function () {
        return view('welcome');
    })->name('welcome');
		
    // Other routes
	Route::get('/pro/{slug}', [PublicTherapistController::class, 'show'])->name('therapist.show');
    Route::get('tisanes', [TisaneController::class, 'index'])->name('tisanes.index');
    Route::get('/recettes', [RecetteController::class, 'index'])->name('recettes.index');
    Route::get('/huilehes', [HuileHEController::class, 'index'])->name('huilehes.index');
    Route::get('huilehvs', [HuileHVController::class, 'index'])->name('huilehvs.index');
	
    Route::get('/huilehes/{slug}', [HuileHEController::class, 'show'])->name('huilehes.show');
    Route::get('/huilehvs/{slug}', [HuileHVController::class, 'show'])->name('huilehvs.show');
    Route::get('/recettes/{slug}', [RecetteController::class, 'show'])->name('recettes.show');
    Route::get('/tisanes/{slug}', [TisaneController::class, 'show'])->name('tisanes.show');
	
	    Route::get('/IntroductionAromatherapie', function () {
        return view('formation1');
    })->name('formation1');
	
	 Route::get('/huilehe/proprietes', [HuileHEController::class, 'showhuilehepropriete'])->name('huilehes.showhuilehepropriete');
	// Route for displaying all blog posts in the index page
Route::get('/article', [BlogPostController::class, 'index'])->name('blog.index');

// Route for displaying individual blog posts using slug
Route::get('/article/{slug}', [BlogPostController::class, 'show'])->name('blog.show');
});



// Admin License routes
Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/license', [AdminController::class, 'showLicenseManagement'])->name('admin.license');
Route::post('/admin/license/{therapist}', [AdminController::class, 'assignLicense'])->name('admin.license.assign');

Route::middleware(['auth'])->group(function () {
    Route::get('/upgrade/license', [UserLicenseController::class, 'showUpgradePage'])->name('upgrade.license');
    Route::post('/upgrade/license/process', [UserLicenseController::class, 'processLicenseUpgrade'])->name('upgrade.license.process');
});



Route::post('/favorites/toggle/{type}/{id}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');




//test

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});




    // Route to the privacy policy page 
    Route::get('/privacy-policy', function () {
        return view('privacypolicy');
    })->name('privacypolicy');
    // Route to the pro landing page 
    Route::get('/pro', function () {
        return view('prolanding');
    })->name('prolanding');






require __DIR__.'/auth.php';
