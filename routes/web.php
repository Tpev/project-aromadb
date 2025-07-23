<?php
use App\Http\Controllers\ReservationController;
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
use App\Http\Controllers\WebRTCController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\TestCertificateController;	
use App\Http\Controllers\EventController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\LicenseTierController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ConseilController;
use App\Http\Controllers\ClientConseilController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\TherapistSearchController;
use App\Models\BlogPost;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Controllers\TrainingController;
use App\Http\Controllers\MetricController;
use App\Http\Controllers\MetricEntryController;
use App\Http\Controllers\ClientFileController;
use App\Http\Controllers\ClientInviteController;
use App\Http\Controllers\ClientPasswordSetupController;
use App\Http\Controllers\ClientAuthController;
use App\Http\Controllers\ClientMessageController;
use App\Http\Controllers\Auth\ClientPasswordResetController;
use App\Http\Controllers\GoogleCalendarController;

Route::middleware(['auth'])->group(function () {
    Route::get('google/connect', [GoogleCalendarController::class, 'redirect'])
        ->name('google.connect');

    Route::get('google/oauth2callback', [GoogleCalendarController::class, 'callback'])
        ->name('google.callback');

    Route::post('google/disconnect', [GoogleCalendarController::class, 'disconnect'])
        ->name('google.disconnect');
});




// Route SEO Content-Type

    Route::get('/pro/facturation-therapeute', function () {
        return view('facturationtherapeute');
    })->name('facturationtherapeute');



Route::prefix('client')->name('client.')->group(function () {
    Route::get('forgot-password', [ClientPasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [ClientPasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');

    Route::get('reset-password/{token}', [ClientPasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [ClientPasswordResetController::class, 'reset'])->name('password.update');
});




Route::middleware('auth:client')->group(function () {
    Route::get('/client/files/{file}/download', [ClientFileController::class, 'downloadClient'])
        ->name('client_files.download');
});

// Therapist-only (protected by auth middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/client_profiles/{clientProfile}/files/{file}/download', [ClientFileController::class, 'downloadForTherapist'])
        ->name('client_profiles.files.download');
});

Route::get('/dashboard/client-profiles/{clientProfile}/messages/fetch', [ClientMessageController::class, 'fetchLatestTherapist'])
    ->name('therapist.messages.fetch')
    ->middleware('auth'); // or a more specific therapist guard

Route::middleware(['auth'])->group(function () {
    Route::post('/messages/{clientProfile}/from-therapist', [ClientMessageController::class, 'storeTherapist'])
        ->name('messages.therapist.store');
});
Route::get('/client/messages/fetch', [ClientMessageController::class, 'fetchLatest'])
    ->name('client.messages.fetch')
    ->middleware('auth:client');


Route::middleware('auth:client')->prefix('client')->group(function () {
    Route::get('/messages', [ClientMessageController::class, 'index'])->name('client.messages.index');
    Route::post('/messages', [ClientMessageController::class, 'store'])->name('client.messages.store');
});
Route::post('/client_profiles/{clientProfile}/messages', [\App\Http\Controllers\MessageController::class, 'store'])
    ->name('messages.store');


Route::get ('client/setup/{token}', [ClientPasswordSetupController::class, 'show'])
     ->name('client.setup.show');
Route::post('/client/documents/upload', [ClientProfileController::class, 'uploadDocument'])->name('client.documents.upload');
Route::post('/client/files/upload', [ClientFileController::class, 'clientUpload'])
    ->name('client.files.upload');
Route::get('/client/invoices/{invoice}/pdf', [InvoiceController::class, 'clientPdf'])
    ->name('client.invoices.pdf');

Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/lesson/{id}/edit', [AdminController::class, 'editLesson'])->name('admin.lesson.edit');
    Route::put('/lesson/{id}', [AdminController::class, 'updateLesson'])->name('admin.lesson.update');
});

Route::prefix('client')->group(function () {
    Route::get ('login',  [ClientAuthController::class,'showLogin'])->name('client.login');
    Route::post('login',  [ClientAuthController::class,'login'])    ->name('client.login.post');

    Route::middleware('auth:client')->group(function () {
        Route::get('home', [ClientProfileController::class, 'home'])->name('client.home');

        Route::post('logout', [ClientAuthController::class,'logout'])->name('client.logout');
        // future “espace client” routes here
    });
});


// routes/web.php
Route::post('client_profiles/{clientProfile}/invite',
            [ClientInviteController::class,'store'])->name('client.invite')->middleware('auth');

Route::get ('client/setup/{token}', [ClientPasswordSetupController::class, 'show'])
     ->name('client.setup.show');

Route::post('client/setup/{token}', [ClientPasswordSetupController::class, 'store'])
     ->name('client.setup.store');



Route::get('/sitemap-practicien.xml', function () {
    return response()
        ->view('sitemap-test')
        ->header('Content-Type', 'application/xml');
})->name('sitemap-test');



Route::get('/autocomplete/regions', function (Request $request) {
$regions = [
        "Auvergne-Rhône-Alpes",
        "Bourgogne-Franche-Comté",
        "Bretagne",
        "Centre-Val de Loire",
        "Corse",
        "Grand Est",
        "Hauts-de-France",
        "Ile-de-France",
        "Normandie",
        "Nouvelle-Aquitaine",
        "Occitanie",
        "Pays de la Loire",
        "Provence Alpes Côte d’Azur",
    ];

    $term = $request->query('term', '');
    
    // Filter regions by the typed term (case-insensitive)
    $filtered = array_filter($regions, function ($region) use ($term) {
        return stripos($region, $term) !== false;
    });

    return response()->json(array_values($filtered));
})->name('autocomplete.regions');



Route::get('/autocomplete/specialties', function (Request $request) {
    $allSpecialties = [
        "Hypnothérapeute",
        "Sophrologue",
        "Massage bien-être",
        "Réflexologue",
        "Naturopathe",
        "Psychopraticien",
        "Coach de vie",
        "Ostéopathe",
        "Diététicien Nutritionniste",
        "Chiropracteur",
        "Médecin acupuncteur",
        "Psychologue",
        "Coach PNL",
        "Coach professionnel",
        "Enseignant en méditation",
        "Professeur de Yoga",
        "Praticien EFT",
        "Kinésiologue",
        "Relaxologue",
        "Aromathérapeute",
        "Énergétique Traditionnelle Chinoise",
        "Sexologue",
        "Sonothérapeute",
        "Fasciathérapeute",
        "Neurothérapeute",
        "Herboriste",
        "Psychanalyste",
        "Art-thérapeute",
        "Psychomotricien",
        "Phytothérapeute",
        "Etiopathe",
        "Posturologue",
        "Professeur de Pilates",
        "Coach parental et familial",
        "Danse-thérapeute",
        "Musicothérapeute",
        "Praticien en Ayurvéda",
        "Praticien en Gestalt",
        "Praticien en thérapies brèves",
        "Yoga thérapie",
        "Somatopathe",
        "Praticien massage Shiatsu"
    ];

    // Grab the 'term' from query string: e.g. ?term=Mas
    $term = $request->query('term', '');

    // Filter out specialties that contain the typed term (case-insensitive)
    $filtered = array_filter($allSpecialties, function ($specialty) use ($term) {
        return stripos($specialty, $term) !== false;
    });

    return response()->json(array_values($filtered));
})->name('autocomplete.specialties');



//// SEO PAGE FOR ANNUAIRE
// Filtrer par région uniquement (ex : /region-ile-de-france)
Route::middleware([\App\Http\Middleware\TrackPageViews::class])->group(function () {
// Combined filter: specialty and region
Route::get('/practicien-{specialty}-region-{region}', [TherapistSearchController::class, 'filterBySpecialtyRegion'])
    ->name('therapists.filter.specialty-region');

// Filter by region only
Route::get('/region-{region}', [TherapistSearchController::class, 'filterByRegion'])
    ->name('therapists.filter.region');

// Filter by specialty only
Route::get('/practicien-{specialty}', [TherapistSearchController::class, 'filterBySpecialty'])
    ->name('therapists.filter.specialty');


Route::match(['get', 'post'], '/recherche-practicien', [TherapistSearchController::class, 'index'])
    ->name('therapists.search');

Route::get('/nos-practiciens', function () {
    $blogPosts = BlogPost::latest()->take(3)->get();
    return view('nos-practiciens', compact('blogPosts'));
})->name('nos-practiciens');

});

// Public route to view the conseil via token
Route::get('conseil/view', [ClientConseilController::class, 'viewConseil'])->name('public.conseil.view');


Route::middleware(['auth'])->group(function () {
Route::resource('conseils', ConseilController::class);

Route::get('client_profiles/{clientProfile}/conseils/send', [ClientConseilController::class, 'sendForm'])->name('client_profiles.conseils.sendform');
Route::post('client_profiles/{clientProfile}/conseils/send', [ClientConseilController::class, 'send'])->name('client_profiles.conseils.send');

});
Route::prefix('help')->group(function () {
    Route::get('/', [HelpController::class, 'index'])->name('help.index');
    Route::get('/search', [HelpController::class, 'search'])->name('help.search');
    Route::get('/{category}', [HelpController::class, 'category'])->name('help.category');
    Route::get('/{category}/{slug}', [HelpController::class, 'show'])->name('help.show');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-as-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::post('/notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.markAllAsRead');
    Route::get('/notifications/fetch', [NotificationController::class, 'fetch'])->name('notifications.fetch');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/contact', [ContactController::class, 'show'])->name('contact.show');
    Route::post('/contact', [ContactController::class, 'send'])->name('contact.send');
    Route::get('/contact/confirmation', [ContactController::class, 'confirmation'])->name('contact.confirmation');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/connect/stripe', [StripeController::class, 'connect'])->name('stripe.connect');
    Route::get('/connect/stripe/refresh', [StripeController::class, 'refresh'])->name('stripe.refresh');
    Route::get('/connect/stripe/return', [StripeController::class, 'return'])->name('stripe.return');
    Route::get('/stripe/dashboard', [StripeController::class, 'redirectToStripeDashboard'])->name('stripe.dashboard')->middleware('auth');
	Route::get('/therapist/stripe', [StripeController::class, 'portal'])->name('therapist.stripe')->middleware('auth');

});

// Routes de paiement Stripe (pour les patients)
Route::post('/create-checkout-session/{token}', [StripeController::class, 'createCheckoutSession'])->name('checkout.create');
Route::get('/checkout/success', [StripeController::class, 'success'])->name('checkout.success');
Route::get('/checkout/cancel', [StripeController::class, 'cancel'])->name('checkout.cancel');

// Webhook Stripe (accessible publiquement)
Route::post('/stripe/webhook', [StripeController::class, 'handleWebhook'])->name('stripe.webhook');


// Route pour gérer le succès du paiement Stripe
Route::get('/appointments/success', [AppointmentController::class, 'success'])->name('appointments.success');

// Route pour gérer l'annulation du paiement Stripe
Route::get('/appointments/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');



Route::get('/test-certificate', [TestCertificateController::class, 'generateTestCertificate'])->name('generateTestCertificate');


// Route to show the reservation form
Route::get('events/{event}/reserve', [ReservationController::class, 'create'])->name('events.reserve.create');

// Route to handle reservation form submission
Route::post('events/{event}/reserve', [ReservationController::class, 'store'])->name('events.reserve.store');
Route::get('events/{event}/reservation-success', [ReservationController::class, 'success'])->name('reservations.success');


Route::middleware('auth')->group(function () {
	Route::delete('reservations/{id}', [ReservationController::class, 'destroy'])->name('reservations.destroy');

    Route::resource('events', EventController::class);
});


Route::middleware(['auth'])->group(function () {
// Inventory Items Resource Routes
Route::resource('inventory_items', InventoryItemController::class);
Route::post('/inventory_items/{inventoryItem}/consume', [InventoryItemController::class, 'consume'])->name('inventory_items.consume');
Route::post('/inventory-items/{inventoryItem}/consume-unit', [InventoryItemController::class, 'consumeUnit'])
    ->name('inventory_items.consume.unit');

});


Route::get('/search', [SearchController::class, 'search'])->name('search');

// Meeting controller route

Route::middleware(['auth'])->group(function () {
Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
Route::post('/meetings/store', [MeetingController::class, 'store'])->name('meetings.store');
Route::get('/meetings/confirmation', [MeetingController::class, 'confirmation'])->name('meetings.confirmation');

});

// **WebRTC Static Routes**
Route::post('/webrtc/signaling', [WebRTCController::class, 'signaling']);
Route::post('/webrtc/clear-signaling', [WebRTCController::class, 'clearSignaling']);
Route::get('/webrtc/get-offer', [WebRTCController::class, 'getOffer']);
Route::get('/webrtc/get-answer', [WebRTCController::class, 'getAnswer']);

// **WebRTC Dynamic Route (Must Be Last)**
Route::get('/webrtc/{room}', [WebRTCController::class, 'room'])->name('webrtc.room');


Route::get('/webrtc-demo', function () {
    return view('webrtc.demo');
});



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
Route::get('/questionnaires/{questionnaire}/edit', [QuestionnaireController::class, 'edit'])
    ->name('questionnaires.edit');

    Route::delete('questionnaires/{questionnaire}', [QuestionnaireController::class, 'destroy'])->name('questionnaires.destroy'); // Delete questionnaire
	Route::delete(
    '/questionnaires/{questionnaire}/questions/{question}', 
    [QuestionnaireController::class, 'destroyQuestion']
)->name('question.destroy');
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


Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class])->group(function () {

	 Route::get('/profile/license', [ProfileController::class, 'license'])->name('profile.license');
    Route::get('/profile/company-info', [ProfileController::class, 'editCompanyInfo'])->name('profile.editCompanyInfo');
    Route::put('/profile/company-info', [ProfileController::class, 'updateCompanyInfo'])->name('profile.updateCompanyInfo');
});
Route::middleware(['auth', \App\Http\Middleware\TrackPageViews::class])->group(function () {


    Route::get('/dashboard-pro', [DashboardController::class, 'index'])->name('dashboard-pro');
    Route::get('/dashboard-pro/qrcode', [DashboardController::class, 'generateQrCode'])->name('dashboard-pro.qrcode');

});


Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class])->group(function () {
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

Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class])->group(function () {
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
	// Duplicate un produit
	Route::get('products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
	Route::post('products/{product}/duplicate', [ProductController::class, 'storeDuplicate'])->name('products.storeDuplicate');

});

Route::prefix('onboarding')->middleware('auth')->group(function () {
    Route::get('/step/{step}', [OnboardingController::class, 'showStep'])->name('onboarding.step');
    Route::post('/step/{step}', [OnboardingController::class, 'submitStep'])->name('onboarding.submit');
    Route::get('/skip/{step}', [OnboardingController::class, 'skipStep'])->name('onboarding.skip');
    Route::get('/complete', [OnboardingController::class, 'complete'])->name('onboarding.complete');
});


// Invoice Routes

// Invoice Routes



Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class, 'can:viewAny,App\Models\Invoice'])->group(function () {
    // Liste de toutes les factures de l'utilisateur connecté
   Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'generatePDF'])->name('invoices.pdf')->middleware('can:view,invoice');

	Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');


Route::get('invoices/create-quote', [InvoiceController::class, 'createQuote'])->name('invoices.createQuote');
Route::post('invoices/store-quote', [InvoiceController::class, 'storeQuote'])->name('invoices.storeQuote');
Route::get('/quotes/{quote}/edit', [InvoiceController::class, 'editQuote'])->name('invoices.editQuote');
Route::put('/quotes/{quote}', [InvoiceController::class, 'updateQuote'])->name('invoices.updateQuote');
Route::get('/quotes/{id}', [InvoiceController::class, 'showQuote'])->name('invoices.showQuote');
Route::patch('/quotes/{quote}/status', [InvoiceController::class, 'updateQuoteStatus'])->name('quotes.updateStatus');
Route::get('/devis/{invoice}/pdf', [InvoiceController::class, 'generateQuotePDF'])->name('invoices.quotePdf');
Route::post('/quotes/{quote}/send-email', [InvoiceController::class, 'sendQuoteEmail'])->name('quotes.send.email');




// Route to list invoices for a specific client profile
Route::get('/client_profiles/{clientProfile}/invoices', [InvoiceController::class, 'clientInvoices'])->name('invoices.client');

Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::post('/invoices/{invoice}/create-payment-link', [InvoiceController::class, 'createPaymentLink'])->name('invoices.createPaymentLink');
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

Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class, 'can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/client_profiles/{clientProfile}/session_notes', [SessionNoteController::class, 'index'])->name('session_notes.index');
    Route::get('/client_profiles/{clientProfile}/session_notes/create', [SessionNoteController::class, 'create'])->name('session_notes.create');
    Route::post('/client_profiles/{clientProfile}/session_notes', [SessionNoteController::class, 'store'])->name('session_notes.store');
    Route::get('/session_notes/{sessionNote}', [SessionNoteController::class, 'show'])->name('session_notes.show');
    Route::get('/session_notes/{sessionNote}/edit', [SessionNoteController::class, 'edit'])->name('session_notes.edit');
    Route::put('/session_notes/{sessionNote}', [SessionNoteController::class, 'update'])->name('session_notes.update');
    Route::delete('/session_notes/{sessionNote}', [SessionNoteController::class, 'destroy'])->name('session_notes.destroy');
});





// Client Profiles Routes
Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class,'can:viewAny,App\Models\ClientProfile'])->group(function () {
    Route::get('/client_profiles', [ClientProfileController::class, 'index'])->name('client_profiles.index'); // Show all client profiles
    Route::get('/client_profiles/create', [ClientProfileController::class, 'create'])->name('client_profiles.create'); // Show form to create a client profile
    Route::post('/client_profiles', [ClientProfileController::class, 'store'])->name('client_profiles.store'); // Handle form submission for creating a client profile
    Route::get('/client_profiles/{clientProfile}', [ClientProfileController::class, 'show'])->name('client_profiles.show');
    Route::get('/client_profiles/{clientProfile}/edit', [ClientProfileController::class, 'edit'])->name('client_profiles.edit'); // Show form to edit a client profile
    Route::put('/client_profiles/{clientProfile}', [ClientProfileController::class, 'update'])->name('client_profiles.update'); // Handle form submission for updating a client profile
    Route::delete('/client_profiles/{clientProfile}', [ClientProfileController::class, 'destroy'])->name('client_profiles.destroy'); // Handle the deletion of a client profile

Route::resource('client_profiles.metrics', MetricController::class);

Route::resource('client_profiles.metrics.entries', MetricEntryController::class)
     ->parameters([
         'entries' => 'metricEntry', // rename the {entry} param to {metricEntry}
     ]);
Route::get('/client_profiles/{client_profile}/files/{file}/download', 
    [ClientFileController::class, 'download']
)->name('client_profiles.files.download');

Route::resource('client_profiles.files', ClientFileController::class)
    ->parameters(['files' => 'file']); 





});


// Public Routes for Patient Booking
Route::post('/appointments/available-slots-patient', [AppointmentController::class, 'getAvailableSlotsForPatient'])->name('appointments.available-slots-patient');
// Route AJAX pour récupérer les dates disponibles
Route::post('/appointments/available-dates', [AppointmentController::class, 'getAvailableDates'])->name('appointments.available-dates');
// Route pour récupérer les jours disponibles en fonction de la prestation et du thérapeute
Route::post('/appointments/available-dates-patient', [AppointmentController::class, 'availableDatesPatient'])->name('appointments.available-dates-patient');

Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class,'can:viewAny,App\Models\ClientProfile'])->group(function () {
   
   
   
    Route::post('/appointments/available-slots', [App\Http\Controllers\AppointmentController::class, 'getAvailableSlots'])->name('appointments.available-slots');
    Route::get('/appointments', [App\Http\Controllers\AppointmentController::class, 'index'])->name('appointments.index');
    Route::get('/appointments/create', [App\Http\Controllers\AppointmentController::class, 'create'])->name('appointments.create');
    Route::post('/appointments', [App\Http\Controllers\AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'show'])->name('appointments.show');
    Route::get('/appointments/{appointment}/edit', [App\Http\Controllers\AppointmentController::class, 'edit'])->name('appointments.edit');
    Route::put('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'update'])->name('appointments.update');
    Route::put('/appointments/{appointment}/complete', [AppointmentController::class, 'markAsCompleted'])->name('appointments.complete');
    Route::put('/appointments/{appointment}/completeindex', [AppointmentController::class, 'markAsCompletedIndex'])->name('appointments.completeindex');

	Route::delete('/appointments/{appointment}', [App\Http\Controllers\AppointmentController::class, 'destroy'])->name('appointments.destroy');
    
 
});


Route::middleware([\App\Http\Middleware\TrackPageViews::class])->group(function () {

Route::get('/book-appointment/{therapist}', [AppointmentController::class, 'createPatient'])->name('appointments.createPatient');
Route::post('/book-appointment', [AppointmentController::class, 'storePatient'])->name('appointments.storePatient');
Route::get('/appointment-confirmation/{token}', [AppointmentController::class, 'showPatient'])->name('appointments.showPatient');
Route::get('/appointment-ics/{token}', [AppointmentController::class, 'downloadICS'])->name('appointments.downloadICS');

});



Route::get('/sitemap', [SitemapController::class, 'index']);


Route::middleware([\App\Http\Middleware\TrackPageViews::class])->group(function () {
    // Route to the pro landing page 
    Route::get('/pro', function () {
        return view('prolanding');
    })->name('prolanding');
    // Route to the welcome page directly returning the welcome view
Route::get('/', function () {
    // Fetch upcoming events from users with visible_annuarire_admin_set = true
    $events = Event::with('user')
        ->whereHas('user', function($query) {
            $query->where('visible_annuarire_admin_set', true);
        })
        ->where('start_date_time', '>', now())
        ->orderBy('start_date_time', 'asc')
        ->take(5)
        ->get();

    return view('welcome', compact('events'));
})->name('welcome');
	Route::get('/formation/Utilisateur-Aromatherapie{numero}', [App\Http\Controllers\FormationController::class, 'show'])->name('formation.show');
	Route::get('/formation/Therapeute-Sales{numero}', [App\Http\Controllers\FormationController::class, 'show1'])->name('formation.show1');
	
    // Other routes
	Route::get('/pro/{slug}', [PublicTherapistController::class, 'show'])->name('therapist.show');
	Route::post('/therapist/{slug}/request-info', [PublicTherapistController::class, 'sendInformationRequest'])
    ->name('therapist.sendInformationRequest');

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
	
	Route::get('/IntroductionSales', function () {
        return view('formation2');
    })->name('formation2');
	
	 Route::get('/huilehe/proprietes', [HuileHEController::class, 'showhuilehepropriete'])->name('huilehes.showhuilehepropriete');
	// Route for displaying all blog posts in the index page
Route::get('/article', [BlogPostController::class, 'index'])->name('blog.index');

// Route for displaying individual blog posts using slug
Route::get('/article/{slug}', [BlogPostController::class, 'show'])->name('blog.show');


Route::get('/trainings', [TrainingController::class, 'index'])->name('trainings.index');
Route::get('/trainings/{training}', [TrainingController::class, 'show'])->name('trainings.show');

Route::get('/trainings/{training}/lesson/{lesson}', [TrainingController::class, 'showLesson'])
    ->name('trainings.show-lesson');

	    Route::get('/Formation-Pro', function () {
        return view('pro-training');
    })->name('formation3');

});



// Admin License routes
Route::get('/admin', [AdminController::class, 'welcome'])->name('admin.welcome');
Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/license', [AdminController::class, 'showLicenseManagement'])->name('admin.license');
Route::post('/admin/license/{therapist}', [AdminController::class, 'assignLicense'])->name('admin.license.assign');
Route::get('/admin/therapists', [AdminController::class, 'indexTherapists'])->name('admin.therapists.index');
Route::get('/admin/therapists/{id}', [AdminController::class, 'showTherapist'])->name('admin.therapists.show');
Route::put('/admin/therapists/{therapist}/picture', [AdminController::class, 'updateTherapistPicture']);

Route::put('/admin/therapists/{id}/settings', [AdminController::class, 'updateTherapistSettings'])->name('admin.therapists.updateSettings');
Route::put('/admin/therapists/{id}/address', [AdminController::class, 'updateTherapistAddress'])
    ->name('admin.therapists.updateAddress');
// routes/web.php
Route::put('/admin/therapists/{therapist}/toggle-license', [AdminController::class, 'toggleLicense'])
    ->name('admin.therapists.toggleLicense');
	Route::put('/admin/therapists/{therapist}/update-license-product', [AdminController::class, 'updateLicenseProduct'])
    ->name('admin.therapists.updateLicenseProduct');


// Route to display the form for uploading the CSV
Route::get('/admin/marketing/upload', [MarketingController::class, 'showUploadForm'])->name('admin.marketing.upload.form');

// Route to handle the uploaded CSV
Route::post('/admin/marketing/upload', [MarketingController::class, 'uploadCsv'])->name('admin.marketing.upload');

// Route to view the list of marketing emails
Route::get('/admin/marketing/emails', [MarketingController::class, 'viewEmails'])->name('admin.marketing.emails');


Route::get('/admin/marketing/templates', [EmailTemplateController::class, 'index'])->name('admin.marketing.templates');
Route::post('/admin/marketing/templates', [EmailTemplateController::class, 'store'])->name('admin.marketing.templates.store');
Route::get('/admin/marketing/templates/{id}', [EmailTemplateController::class, 'edit'])->name('admin.marketing.templates.edit');
Route::put('/admin/marketing/templates/{id}', [EmailTemplateController::class, 'update'])->name('admin.marketing.templates.update');
Route::post('/admin/marketing/templates/send-test-mail', [EmailTemplateController::class, 'sendTestMail'])->name('send.test.mail');


Route::middleware(['auth',\App\Http\Middleware\TrackPageViews::class])->group(function () {
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
	
	// Route to the CGU page 
    Route::get('/cgu', function () {
        return view('cgu');
    })->name('cgu');	
	// Route to the CGU page 
    Route::get('/cgv', function () {
        return view('cgv');
    })->name('cgv');

Route::get('/license-tiers/pricing', [LicenseTierController::class, 'pricing'])->name('license-tiers.pricing')->middleware('auth');



require __DIR__.'/auth.php';
