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
use App\Http\Controllers\PracticeLocationController;
use App\Models\User;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\EmargementController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DocumentSigningController;
use App\Http\Controllers\SpecialAvailabilityController;
use App\Http\Controllers\GoogleReviewController;
use App\Http\Controllers\CorporateClientController;
use App\Http\Controllers\AudienceController;
use App\Http\Controllers\NewsletterUnsubscribeController;
use App\Http\Controllers\DigitalTrainingController;
use App\Http\Controllers\DigitalTrainingEnrollmentController;
use App\Http\Controllers\PublicTrainingAccessController;
use App\Http\Controllers\PackProductController;
use App\Http\Controllers\PublicPackCheckoutController;
use App\Http\Controllers\GiftVoucherController;
use App\Http\Controllers\Pro\ReferralController;
use App\Http\Controllers\Admin\DesignTemplateController as AdminDesignTemplateController;
use App\Models\DesignTemplate;

Route::middleware(['auth'])->group(function () {
    Route::get('/pro/referrals', [ReferralController::class, 'index'])->name('pro.referrals.index');
    Route::post('/pro/referrals/invite', [ReferralController::class, 'invite'])->name('pro.referrals.invite');
    Route::post('/pro/referrals/invite/{invite}/resend', [ReferralController::class, 'resend'])->name('pro.referrals.resend');
});

// Public tracking + redirect
Route::get('/pro/referrals/accept/{token}', [ReferralController::class, 'accept'])
    ->name('pro.referrals.accept');

Route::middleware(['auth'])->group(function () {
    Route::prefix('dashboard-pro')->group(function () {
        Route::get('/bons-cadeaux', [GiftVoucherController::class, 'index'])->name('pro.gift-vouchers.index');
        Route::get('/bons-cadeaux/create', [GiftVoucherController::class, 'create'])->name('pro.gift-vouchers.create');
        Route::post('/bons-cadeaux', [GiftVoucherController::class, 'store'])->name('pro.gift-vouchers.store');

        Route::get('/bons-cadeaux/{voucher}', [GiftVoucherController::class, 'show'])->name('pro.gift-vouchers.show');
        Route::get('/bons-cadeaux/{voucher}/pdf', [GiftVoucherController::class, 'downloadPdf'])->name('pro.gift-vouchers.pdf');
        Route::post('/bons-cadeaux/{voucher}/resend', [GiftVoucherController::class, 'resendEmails'])->name('pro.gift-vouchers.resend');
        Route::post('/bons-cadeaux/{voucher}/redeem', [GiftVoucherController::class, 'redeem'])->name('pro.gift-vouchers.redeem');
        Route::post('/bons-cadeaux/{voucher}/disable', [GiftVoucherController::class, 'disable'])->name('pro.gift-vouchers.disable');
    });
});


Route::middleware(['auth'])->group(function () {
    Route::post('/invoices/from-pack/{packPurchase}', [InvoiceController::class, 'createFromPackPurchase'])
        ->name('invoices.fromPackPurchase');
});

Route::get('/pro/{slug}/packs/{pack}/checkout', [PublicPackCheckoutController::class, 'show'])
    ->name('packs.checkout.show');

Route::post('/pro/{slug}/packs/{pack}/checkout', [PublicPackCheckoutController::class, 'store'])
    ->name('packs.checkout.store');

Route::get('/packs/checkout/success', [PublicPackCheckoutController::class, 'success'])
    ->name('packs.checkout.success');

Route::get('/packs/checkout/cancel', [PublicPackCheckoutController::class, 'cancel'])
    ->name('packs.checkout.cancel');




Route::middleware(['auth'])->group(function () {
    Route::resource('pack-products', PackProductController::class);

    Route::post('pack-products/{packProduct}/assign', [PackProductController::class, 'assignToClient'])
        ->name('pack-products.assign');
	Route::post('/client-profiles/{clientProfile}/packs/assign', [ClientProfilePackController::class, 'assign'])
    ->name('client_profiles.packs.assign');

});


Route::get('/beta/brand', function () {
    return view('tools.brand-assistant');
})->name('beta.brand');
Route::get('/beta/editor', function () {
    $user = auth()->user();

    $events = Event::where('user_id', $user->id)
        ->orderBy('start_date_time', 'asc')
        ->get();

    $konvaTemplates = config('konva.templates', []);

    return view('tools.konva-editor', [
        'events'         => $events,
        'konvaTemplates' => $konvaTemplates,
    ]);
})->name('konva.editor')->middleware(['auth']);


Route::get('/formations/{digitalTraining:slug}', [DigitalTrainingController::class, 'publicShow'])
    ->name('digital-trainings.public.show');
// === Public training access via magic token ===
Route::get('/training-access/{token}', [PublicTrainingAccessController::class, 'show'])
    ->name('digital-trainings.access.show');

Route::post('/training-access/{token}/complete', [PublicTrainingAccessController::class, 'markCompleted'])
    ->name('digital-trainings.access.complete');

Route::middleware(['auth'])->group(function () {
    // ...

    Route::prefix('digital-trainings/{digitalTraining}')->group(function () {
        Route::get('enrollments', [DigitalTrainingEnrollmentController::class, 'index'])
            ->name('digital-trainings.enrollments.index');

        Route::post('enrollments', [DigitalTrainingEnrollmentController::class, 'store'])
            ->name('digital-trainings.enrollments.store');


    });
		Route::delete('/digital-trainings/{digitalTraining}/enrollments/{enrollment}', [DigitalTrainingEnrollmentController::class, 'destroy'])
    ->name('digital-trainings.enrollments.destroy');
    // Future: public client access via token (to implement later)
    // Route::get('training-access/{token}', [PublicTrainingAccessController::class, 'show'])
    //     ->name('digital-trainings.access.show');
});



Route::middleware(['auth'])->group(function () {
    Route::get('/digital-trainings',               [DigitalTrainingController::class, 'index'])->name('digital-trainings.index');
    Route::get('/digital-trainings/create',        [DigitalTrainingController::class, 'create'])->name('digital-trainings.create');
    Route::post('/digital-trainings',              [DigitalTrainingController::class, 'store'])->name('digital-trainings.store');
	Route::get('/digital-trainings/{digitalTraining}/preview', 
		[DigitalTrainingController::class, 'preview']
	)->name('digital-trainings.preview');

    Route::get('/digital-trainings/{digitalTraining}/edit',    [DigitalTrainingController::class, 'edit'])->name('digital-trainings.edit');
    Route::put('/digital-trainings/{digitalTraining}',         [DigitalTrainingController::class, 'update'])->name('digital-trainings.update');
    Route::delete('/digital-trainings/{digitalTraining}',      [DigitalTrainingController::class, 'destroy'])->name('digital-trainings.destroy');

    // Builder (modules + blocks)
    Route::get('/digital-trainings/{digitalTraining}/builder', [DigitalTrainingController::class, 'builder'])->name('digital-trainings.builder');

    // Modules
    Route::post('/digital-trainings/{digitalTraining}/modules',                           [DigitalTrainingController::class, 'storeModule'])->name('digital-trainings.modules.store');
    Route::put('/digital-trainings/{digitalTraining}/modules/{module}',                   [DigitalTrainingController::class, 'updateModule'])->name('digital-trainings.modules.update');
    Route::delete('/digital-trainings/{digitalTraining}/modules/{module}',                [DigitalTrainingController::class, 'destroyModule'])->name('digital-trainings.modules.destroy');

    // Blocks
    Route::post('/digital-trainings/{digitalTraining}/modules/{module}/blocks',           [DigitalTrainingController::class, 'storeBlock'])->name('digital-trainings.blocks.store');
    Route::put('/digital-trainings/{digitalTraining}/modules/{module}/blocks/{block}',    [DigitalTrainingController::class, 'updateBlock'])->name('digital-trainings.blocks.update');
    Route::delete('/digital-trainings/{digitalTraining}/modules/{module}/blocks/{block}', [DigitalTrainingController::class, 'destroyBlock'])->name('digital-trainings.blocks.destroy');
});





Route::get('/newsletters/unsubscribe/{token}', [NewsletterUnsubscribeController::class, 'show'])
    ->name('unsubscribe.newsletter');

Route::post('/newsletters/unsubscribe/{token}', [NewsletterUnsubscribeController::class, 'confirm'])
    ->name('unsubscribe.newsletter.confirm');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('audiences', AudienceController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::resource('newsletters', \App\Http\Controllers\NewsletterController::class);
	Route::post('/newsletters/upload-image', [\App\Http\Controllers\NewsletterController::class, 'uploadImage'])
    ->name('newsletters.upload-image');
    Route::post('newsletters/{newsletter}/send-test', [\App\Http\Controllers\NewsletterController::class, 'sendTest'])
        ->name('newsletters.send-test');
    Route::post('newsletters/{newsletter}/send-now', [\App\Http\Controllers\NewsletterController::class, 'sendNow'])
        ->name('newsletters.send-now');
		

});



Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('corporate-clients', CorporateClientController::class);
});

Route::middleware(['auth'])->group(function () {
    Route::get('/pro/google-reviews', [GoogleReviewController::class, 'index'])
        ->name('pro.google-reviews.index');

    Route::get('/pro/google-reviews/connect', [GoogleReviewController::class, 'redirectToGoogle'])
        ->name('pro.google-reviews.connect');

    Route::get('/pro/google-reviews/callback', [GoogleReviewController::class, 'handleCallback'])
        ->name('pro.google-reviews.callback');

    Route::post('/pro/google-reviews/sync', [GoogleReviewController::class, 'syncReviews'])
        ->name('pro.google-reviews.sync');

    Route::post('/pro/google-reviews/disconnect', [GoogleReviewController::class, 'disconnect'])
        ->name('pro.google-reviews.disconnect');
});


Route::post('/onboarding/skip-step3', [DashboardController::class, 'skipStep3'])
    ->name('onboarding.skipStep3')
    ->middleware(['auth']);

Route::post('/onboarding/skip-step4', [DashboardController::class, 'skipStep4'])
    ->name('onboarding.skipStep4')
    ->middleware(['auth']);

Route::post('/onboarding/referral-done', [DashboardController::class, 'markReferralOnboardingDone'])
    ->name('onboarding.referralDone')
    ->middleware(['auth']);

Route::middleware(['auth'])->group(function () {
    Route::resource('special-availabilities', SpecialAvailabilityController::class)
        ->except(['show']);
});
Route::middleware(['auth'])->group(function () {
    Route::post('/appointments/available-slots-therapist', [AppointmentController::class, 'getAvailableSlotsForTherapist'])
        ->name('appointments.available-slots-therapist');

    Route::post('/appointments/available-dates-concrete-therapist', [AppointmentController::class, 'availableConcreteDatesTherapist'])
        ->name('appointments.available-dates-concrete-therapist');
});

Route::post(
    '/appointments/available-dates-concrete-patient',
    [AppointmentController::class, 'availableConcreteDatesPatient']
)->name('appointments.available-dates-concrete-patient');


// Upload (from client dossier)
Route::middleware(['web','auth'])->group(function () {
    Route::post('/clients/{clientProfile}/documents', [DocumentController::class,'store'])
        ->name('documents.store');
    Route::get('/documents/{doc}/download-final', [DocumentController::class,'downloadFinal'])
        ->name('documents.download.final');

    Route::post('/documents/{doc}/send',   [DocumentSigningController::class,'send'])->name('documents.send');
    Route::post('/signing/{signing}/resend',[DocumentSigningController::class,'resend'])->name('documents.resend');
});

// Public client signing (step 1), then therapist signing (step 2) using same token:
Route::get('/docs/sign/{token}',  [DocumentSigningController::class,'showForm'])
    ->name('documents.sign.form');
Route::post('/docs/sign/{token}', [DocumentSigningController::class,'submit'])
    ->name('documents.sign.submit');


Route::middleware(['auth'])->group(function () {
    Route::post('/appointments/{appointment}/emargement/send', [EmargementController::class,'send'])
        ->name('emargement.send');
    Route::post('/emargements/{emargement}/resend', [EmargementController::class,'resend'])
        ->name('emargement.resend');
    Route::get('/emargements/{emargement}/download', [EmargementController::class,'download'])
        ->name('emargement.download');
});

// Public signing (magic link)
Route::get('/sign/{token}', [EmargementController::class,'showSignForm'])->name('emargement.sign.form');
Route::post('/sign/{token}', [EmargementController::class,'submitSignature'])->name('emargement.sign.submit');




// ---  feature page
Route::view('/fonctionnalites', 'fonctionnalites.index')
    ->name('features.index');

// --- Agenda feature page
Route::view('/fonctionnalites/agenda', 'fonctionnalites.agenda')
    ->name('features.agenda');
// --- Dossiers clients feature page
Route::view('/fonctionnalites/dossiers-clients', 'fonctionnalites.dossiers-clients')
    ->name('features.dossiers');
// Facturation page
Route::view('/fonctionnalites/facturation', 'fonctionnalites.facturation')
    ->name('features.facturation');
// Feature: Questionnaires
Route::view('/fonctionnalites/questionnaires', 'fonctionnalites.questionnaires')
    ->name('features.questionnaires');
// Portail Pro
Route::view('/fonctionnalites/portail-pro', 'fonctionnalites.portail-pro')
    ->name('features.portailpro');
// Paiements
Route::view('/fonctionnalites/paiements', 'fonctionnalites.paiements')
    ->name('features.paiements');
	
Route::middleware(['auth'])->group(function () {
    Route::get('/assistant', [AssistantController::class, 'view'])->name('assistant.view');
    Route::post('/assistant/message', [AssistantController::class, 'message'])->name('assistant.message');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('practice-locations', PracticeLocationController::class)
        ->parameters(['practice-locations' => 'practice_location'])
        ->names([
            'index'   => 'practice-locations.index',
            'create'  => 'practice-locations.create',
            'store'   => 'practice-locations.store',
            'edit'    => 'practice-locations.edit',
            'update'  => 'practice-locations.update',
            'destroy' => 'practice-locations.destroy',
        ]);
});

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
    // 👉 Nouveau : créer un profil client depuis une réservation d'événement
    Route::post(
        '/events/{event}/reservations/{reservation}/create-client',
        [ClientProfileController::class, 'storeFromReservation']
    )->name('reservations.createClient');
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
	
	// Livre de recettes : index + export CSV + CA mensuel
Route::get('/accounting/receipts', [ReceiptController::class, 'index'])->name('receipts.index')->middleware('auth');
Route::get('/accounting/receipts/export', [ReceiptController::class, 'exportCsv'])->name('receipts.export')->middleware('auth');
Route::get('/accounting/ca-monthly', [ReceiptController::class, 'caMonthly'])->name('receipts.caMonthly')->middleware('auth');
// routes/web.php
    Route::get('/receipts/create', [ReceiptController::class, 'create'])->name('receipts.create');
    Route::post('/receipts', [ReceiptController::class, 'store'])->name('receipts.store');
Route::post('/receipts/{receipt}/reverse', [\App\Http\Controllers\ReceiptController::class, 'reverse'])
    ->name('receipts.reverse');


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
Route::post('/appointment-confirmation/{token}/cancel', [AppointmentController::class, 'cancelFromMagicLink'])
    ->name('appointment.confirmation.cancel');
});



Route::get('/sitemap', [SitemapController::class, 'index']);


Route::middleware([\App\Http\Middleware\TrackPageViews::class])->group(function () {
    // Route to the pro landing page 
    Route::get('/pro', function () {
        return view('prolanding');
    })->name('prolanding');
 
Route::get('/', function () {
    // Upcoming events
    $events = Event::with('user')
        ->whereHas('user', fn($q) => $q->where('visible_annuarire_admin_set', true))
        ->where('start_date_time', '>', now())
        ->orderBy('start_date_time', 'asc')
        ->take(5)
        ->get();

    // Featured therapists (use profile_picture — your real column)
    $featuredTherapists = User::query()
        ->where('is_therapist', true)
        ->where('visible_annuarire_admin_set', true) // optional
        ->where('is_featured', true)
        ->where(function ($q) {
            $q->whereNull('featured_until')
              ->orWhere('featured_until', '>', now());
        })
        ->whereNotNull('slug')
        ->whereNotNull('profile_picture')   // ✅ keep this, drop cover_photo_path entirely
        ->orderByDesc('featured_weight')

        ->latest('id')
        ->limit(9)
        ->get();

    return view('welcome', compact('events', 'featuredTherapists'));
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

// routes/web.php
Route::middleware(['auth'])->group(function () {
    Route::post('/admin/impersonate/{user}', [\App\Http\Controllers\AdminImpersonationController::class, 'start'])
        ->name('admin.impersonate.start');

    Route::post('/admin/impersonate/stop', [\App\Http\Controllers\AdminImpersonationController::class, 'stop'])
        ->name('admin.impersonate.stop');
		
	    Route::get('/admin/usage/weekly', [AdminController::class, 'weeklyUsage'])
        ->name('admin.usage.weekly');
});


// Admin License routes
Route::get('/admin', [AdminController::class, 'welcome'])->name('admin.welcome');
Route::get('/admin/users', [AdminController::class, 'index'])->name('admin.index');
Route::get('/admin/license', [AdminController::class, 'showLicenseManagement'])->name('admin.license');
Route::post('/admin/license/{therapist}', [AdminController::class, 'assignLicense'])->name('admin.license.assign');
Route::get('/admin/therapists', [AdminController::class, 'indexTherapists'])->name('admin.therapists.index');
Route::get('/admin/therapists/{id}', [AdminController::class, 'showTherapist'])->name('admin.therapists.show');
Route::put('/admin/therapists/{therapist}/featured', [\App\Http\Controllers\AdminController::class, 'updateFeatured'])
        ->name('admin.therapists.updateFeatured');
Route::put(
    '/admin/therapists/{therapist}/picture',
    [AdminController::class, 'updateTherapistPicture']
)->name('admin.therapists.updatePicture');

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

Route::get('/metiers/naturopathe', function () {
    return view('metiers.naturopathe');
})->name('metiers.naturopathe');

Route::get('/metiers/sophrologue', function () {
    return view('metiers.sophrologue');
})->name('metiers.sophrologue');

// Aide AGENDA

Route::view(
    '/aide/agenda/creer-un-rendez-vous-en-ligne',
    'aide.agenda.creer-un-rendez-vous-en-ligne'
)->name('aide.agenda.creer-rendez-vous');

Route::view(
    '/aide/agenda/configurer-disponibilites',
    'aide.agenda.configurer-disponibilites'
)->name('aide.agenda.configurer-disponibilites');

Route::view(
    '/aide/agenda/gerer-indisponibilites',
    'aide.agenda.gerer-indisponibilites'
)->name('aide.agenda.gerer-indisponibilites');

Route::view(
    '/aide/agenda/duree-prestation-temps-de-pause',
    'aide.agenda.duree-prestation-temps-de-pause'
)->name('aide.agenda.duree-prestation-temps-de-pause');

Route::view(
    '/aide/agenda/creer-un-atelier-ou-evenement',
    'aide.agenda.creer-un-atelier-ou-evenement'
)->name('aide.agenda.creer-atelier-evenement');

Route::view(
    '/aide/agenda/synchroniser-calendrier',
    'aide.agenda.synchroniser-calendrier'
)->name('aide.agenda.synchroniser-calendrier');








Route::get('/tools/konva', function () {
    // You can keep your existing logic; just ensure $templates is passed to the view
    $templates = DesignTemplate::active()
        ->orderBy('sort_order')
        ->orderBy('id', 'desc')
        ->get()
        ->map(function ($t) {
            return [
                'id' => $t->id,
                'name' => $t->name,
                'category' => $t->category,
                'format_id' => $t->format_id,
                'preview_url' => $t->previewUrl(),
            ];
        });

    return view('tools.konva.konva-editor', [
        'templatesDb' => $templates,
        // keep your config formats as-is
        'events' => collect(), // or your events
    ]);
})->name('konva.editor');

// -------------------------
// ADMIN: Design Templates
// -------------------------

Route::get('/admin/design-templates', [AdminDesignTemplateController::class, 'index'])
    ->name('admin.design-templates.index');

Route::get('/admin/design-templates/create', [AdminDesignTemplateController::class, 'create'])
    ->name('admin.design-templates.create');

Route::get('/admin/design-templates/{template}/edit', [AdminDesignTemplateController::class, 'edit'])
    ->name('admin.design-templates.edit');

Route::post('/admin/design-templates', [AdminDesignTemplateController::class, 'store'])
    ->name('admin.design-templates.store');

Route::put('/admin/design-templates/{template}', [AdminDesignTemplateController::class, 'update'])
    ->name('admin.design-templates.update');

Route::post('/admin/design-templates/{template}/toggle', [AdminDesignTemplateController::class, 'toggle'])
    ->name('admin.design-templates.toggle');

Route::post('/admin/design-templates/reorder', [AdminDesignTemplateController::class, 'reorder'])
    ->name('admin.design-templates.reorder');

Route::delete('/admin/design-templates/{template}', [AdminDesignTemplateController::class, 'destroy'])
    ->name('admin.design-templates.destroy');


Route::get('/b/{token}', [AppointmentController::class, 'createByToken'])
    ->name('bookingLinks.create');

Route::post('/b/{token}', [AppointmentController::class, 'storeByToken'])
    ->name('bookingLinks.store');




require __DIR__.'/auth.php';
require __DIR__.'/mobile.php';
