<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AvailabilityController;
use App\Http\Controllers\ClientProfileController;
use App\Http\Controllers\CommunityAttachmentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Mobile\MobileAppointmentController;
use App\Http\Controllers\Mobile\MobileAudienceController;
use App\Http\Controllers\Mobile\MobileClientController;
use App\Http\Controllers\Mobile\MobileClientPortalController;
use App\Http\Controllers\Mobile\MobileCommunityController;
use App\Http\Controllers\Mobile\MobileCorporateClientController;
use App\Http\Controllers\Mobile\MobileDocumentController;
use App\Http\Controllers\Mobile\MobileEmargementController;
use App\Http\Controllers\Mobile\MobileEventController;
use App\Http\Controllers\Mobile\MobileGiftVoucherController;
use App\Http\Controllers\Mobile\MobileGoogleReviewController;
use App\Http\Controllers\Mobile\MobileDigitalTrainingController;
use App\Http\Controllers\Mobile\MobileInvoiceController;
use App\Http\Controllers\Mobile\MobileMetricController;
use App\Http\Controllers\Mobile\MobileNewsletterController;
use App\Http\Controllers\Mobile\MobilePackProductController;
use App\Http\Controllers\Mobile\MobileProductController;
use App\Http\Controllers\Mobile\MobileProfileController;
use App\Http\Controllers\Mobile\MobileQuestionnaireController;
use App\Http\Controllers\Mobile\MobileReceiptController;
use App\Http\Controllers\Mobile\MobileReceivedInvoiceController;
use App\Http\Controllers\Mobile\MobileReferralController;
use App\Http\Controllers\Mobile\MobileSessionNoteController;
use App\Http\Controllers\Mobile\MobileSubscriptionController;
use App\Http\Controllers\Mobile\MobileTherapistController;
use App\Http\Controllers\Mobile\TherapistSearchController;
use App\Http\Controllers\PracticeLocationController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')
    ->prefix('mobile')
    ->name('mobile.')
    ->group(function () {
        Route::get('/', function () {
            return view('mobile.entry');
        })->name('entry');

        Route::prefix('client')
            ->name('client.')
            ->group(function () {
                Route::get('/login', [MobileClientPortalController::class, 'showLogin'])
                    ->middleware('guest:client')
                    ->name('login');

                Route::post('/login', [MobileClientPortalController::class, 'login'])
                    ->middleware('guest:client')
                    ->name('login.store');

                Route::middleware('auth:client')->group(function () {
                    Route::get('/home', [MobileClientPortalController::class, 'home'])->name('home');
                    Route::get('/messages', [MobileClientPortalController::class, 'messages'])->name('messages.index');
                    Route::post('/messages', [MobileClientPortalController::class, 'storeMessage'])->name('messages.store');
                    Route::post('/documents', [MobileClientPortalController::class, 'storeFile'])->name('files.store');
                    Route::get('/documents/{file}/download', [\App\Http\Controllers\ClientFileController::class, 'downloadClient'])->name('files.download');
                    Route::get('/factures/{invoice}/pdf', [InvoiceController::class, 'clientPdf'])->name('invoices.pdf');
                    Route::get('/communautes', [MobileClientPortalController::class, 'communities'])->name('communities.index');
                    Route::get('/communautes/fichiers/{attachment}', [CommunityAttachmentController::class, 'downloadForClient'])->name('communities.attachments.download');
                    Route::post('/communautes/{community}/rejoindre', [MobileClientPortalController::class, 'acceptCommunity'])->name('communities.accept');
                    Route::get('/communautes/{community}', [MobileClientPortalController::class, 'showCommunity'])->name('communities.show');
                    Route::post('/communautes/{community}/messages', [MobileClientPortalController::class, 'storeCommunityMessage'])->name('communities.messages.store');
                    Route::post('/logout', [MobileClientPortalController::class, 'logout'])->name('logout');
                });
            });

        Route::get('/login', [AuthenticatedSessionController::class, 'createMobile'])
            ->name('login');

        Route::post('/login', [AuthenticatedSessionController::class, 'storeMobile'])
            ->middleware('guest')
            ->name('login.store');

        Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
            ->middleware('auth')
            ->name('logout');

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->middleware('auth')
            ->name('dashboard');

        Route::get('/menu', function () {
            return view('mobile.menu');
        })->middleware('auth')->name('menu');

        Route::redirect('/pro/more', '/mobile/menu')->name('pro.more.redirect');

        Route::middleware('auth')->group(function () {
            Route::get('/prestations', [MobileProductController::class, 'index'])->name('products.index');
            Route::get('/prestations/create', [MobileProductController::class, 'create'])->name('products.create');
            Route::post('/prestations', [MobileProductController::class, 'store'])->name('products.store');
            Route::get('/prestations/{product}', [MobileProductController::class, 'show'])->name('products.show');
            Route::get('/prestations/{product}/edit', [MobileProductController::class, 'edit'])->name('products.edit');
            Route::put('/prestations/{product}', [MobileProductController::class, 'update'])->name('products.update');
            Route::delete('/prestations/{product}', [MobileProductController::class, 'destroy'])->name('products.destroy');
            Route::get('/disponibilites', [AvailabilityController::class, 'index'])->name('availabilities.index');
            Route::get('/disponibilites/create', [AvailabilityController::class, 'create'])->name('availabilities.create');
            Route::post('/disponibilites', [AvailabilityController::class, 'store'])->name('availabilities.store');
            Route::get('/disponibilites/{availability}/edit', [AvailabilityController::class, 'edit'])->name('availabilities.edit');
            Route::put('/disponibilites/{availability}', [AvailabilityController::class, 'update'])->name('availabilities.update');
            Route::delete('/disponibilites/{availability}', [AvailabilityController::class, 'destroy'])->name('availabilities.destroy');
            Route::get('/lieux', [PracticeLocationController::class, 'index'])->name('practice-locations.index');
            Route::get('/lieux/create', [PracticeLocationController::class, 'create'])->name('practice-locations.create');
            Route::post('/lieux', [PracticeLocationController::class, 'store'])->name('practice-locations.store');
            Route::get('/lieux/{practice_location}/edit', [PracticeLocationController::class, 'edit'])->name('practice-locations.edit');
            Route::put('/lieux/{practice_location}', [PracticeLocationController::class, 'update'])->name('practice-locations.update');
            Route::delete('/lieux/{practice_location}', [PracticeLocationController::class, 'destroy'])->name('practice-locations.destroy');
            Route::get('/questionnaires', [MobileQuestionnaireController::class, 'index'])->name('questionnaires.index');
            Route::get('/questionnaires/create', [MobileQuestionnaireController::class, 'create'])->name('questionnaires.create');
            Route::post('/questionnaires', [MobileQuestionnaireController::class, 'store'])->name('questionnaires.store');
            Route::get('/questionnaires/{questionnaire}', [MobileQuestionnaireController::class, 'show'])->name('questionnaires.show');
            Route::get('/questionnaires/{questionnaire}/edit', [MobileQuestionnaireController::class, 'edit'])->name('questionnaires.edit');
            Route::put('/questionnaires/{questionnaire}', [MobileQuestionnaireController::class, 'update'])->name('questionnaires.update');
            Route::delete('/questionnaires/{questionnaire}', [MobileQuestionnaireController::class, 'destroy'])->name('questionnaires.destroy');
            Route::delete('/questionnaires/{questionnaire}/questions/{question}', [MobileQuestionnaireController::class, 'destroyQuestion'])->name('questionnaires.questions.destroy');
            Route::get('/evenements', [MobileEventController::class, 'index'])->name('events.index');
            Route::get('/evenements/create', [MobileEventController::class, 'create'])->name('events.create');
            Route::post('/evenements', [MobileEventController::class, 'store'])->name('events.store');
            Route::get('/evenements/{event}', [MobileEventController::class, 'show'])->name('events.show');
            Route::get('/evenements/{event}/edit', [MobileEventController::class, 'edit'])->name('events.edit');
            Route::put('/evenements/{event}', [MobileEventController::class, 'update'])->name('events.update');
            Route::delete('/evenements/{event}', [MobileEventController::class, 'destroy'])->name('events.destroy');
            Route::post('/evenements/{event}/participants', [MobileEventController::class, 'addClient'])->name('events.participants.add-client');
            Route::get('/documents', [MobileDocumentController::class, 'index'])->name('documents.index');
            Route::get('/documents/clients/{clientProfile}', [MobileDocumentController::class, 'showClient'])->name('documents.client');
            Route::post('/documents/clients/{clientProfile}/fichiers', [MobileDocumentController::class, 'storeFile'])->name('documents.files.store');
            Route::get('/documents/clients/{clientProfile}/fichiers/{file}/download', [MobileDocumentController::class, 'downloadFile'])->name('documents.files.download');
            Route::delete('/documents/clients/{clientProfile}/fichiers/{file}', [MobileDocumentController::class, 'destroyFile'])->name('documents.files.destroy');
            Route::post('/documents/clients/{clientProfile}/signatures', [MobileDocumentController::class, 'storeSignatureDocument'])->name('documents.signatures.store');
            Route::post('/documents/signatures/{document}/send', [MobileDocumentController::class, 'sendSignatureDocument'])->name('documents.signatures.send');
            Route::post('/documents/signatures/{signing}/resend', [MobileDocumentController::class, 'resendSignature'])->name('documents.signatures.resend');
            Route::get('/documents/signatures/{document}/original', [MobileDocumentController::class, 'downloadOriginal'])->name('documents.signatures.original');
            Route::get('/documents/signatures/{document}/final', [MobileDocumentController::class, 'downloadFinal'])->name('documents.signatures.final');
            Route::get('/clients/{clientProfile}/notes-seance', [MobileSessionNoteController::class, 'index'])->name('session-notes.index');
            Route::get('/clients/{clientProfile}/notes-seance/create', [MobileSessionNoteController::class, 'create'])->name('session-notes.create');
            Route::post('/clients/{clientProfile}/notes-seance', [MobileSessionNoteController::class, 'store'])->name('session-notes.store');
            Route::get('/notes-seance/{sessionNote}', [MobileSessionNoteController::class, 'show'])->name('session-notes.show');
            Route::get('/notes-seance/{sessionNote}/edit', [MobileSessionNoteController::class, 'edit'])->name('session-notes.edit');
            Route::put('/notes-seance/{sessionNote}', [MobileSessionNoteController::class, 'update'])->name('session-notes.update');
            Route::delete('/notes-seance/{sessionNote}', [MobileSessionNoteController::class, 'destroy'])->name('session-notes.destroy');
            Route::get('/clients/{clientProfile}/suivi-mesures', [MobileMetricController::class, 'index'])->name('metrics.index');
            Route::get('/clients/{clientProfile}/suivi-mesures/create', [MobileMetricController::class, 'create'])->name('metrics.create');
            Route::post('/clients/{clientProfile}/suivi-mesures', [MobileMetricController::class, 'store'])->name('metrics.store');
            Route::get('/clients/{clientProfile}/suivi-mesures/{metric}', [MobileMetricController::class, 'show'])->name('metrics.show');
            Route::get('/clients/{clientProfile}/suivi-mesures/{metric}/edit', [MobileMetricController::class, 'edit'])->name('metrics.edit');
            Route::put('/clients/{clientProfile}/suivi-mesures/{metric}', [MobileMetricController::class, 'update'])->name('metrics.update');
            Route::delete('/clients/{clientProfile}/suivi-mesures/{metric}', [MobileMetricController::class, 'destroy'])->name('metrics.destroy');
            Route::get('/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/create', [MobileMetricController::class, 'createEntry'])->name('metrics.entries.create');
            Route::post('/clients/{clientProfile}/suivi-mesures/{metric}/valeurs', [MobileMetricController::class, 'storeEntry'])->name('metrics.entries.store');
            Route::get('/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/{metricEntry}/edit', [MobileMetricController::class, 'editEntry'])->name('metrics.entries.edit');
            Route::put('/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/{metricEntry}', [MobileMetricController::class, 'updateEntry'])->name('metrics.entries.update');
            Route::delete('/clients/{clientProfile}/suivi-mesures/{metric}/valeurs/{metricEntry}', [MobileMetricController::class, 'destroyEntry'])->name('metrics.entries.destroy');
            Route::get('/emargements', [MobileEmargementController::class, 'index'])->name('emargements.index');
            Route::post('/emargements/rendez-vous/{appointment}/envoyer', [MobileEmargementController::class, 'send'])->name('emargements.send');
            Route::post('/emargements/{emargement}/renvoyer', [MobileEmargementController::class, 'resend'])->name('emargements.resend');
            Route::get('/emargements/{emargement}/download', [MobileEmargementController::class, 'download'])->name('emargements.download');
            Route::get('/recettes', [MobileReceiptController::class, 'index'])->name('receipts.index');
            Route::get('/recettes/create', [MobileReceiptController::class, 'create'])->name('receipts.create');
            Route::post('/recettes', [MobileReceiptController::class, 'store'])->name('receipts.store');
            Route::get('/recettes/ca-mensuel', [MobileReceiptController::class, 'monthly'])->name('receipts.monthly');
            Route::post('/recettes/{receipt}/contre-passer', [MobileReceiptController::class, 'reverse'])->name('receipts.reverse');
            Route::get('/stock', [InventoryItemController::class, 'index'])->name('inventory.index');
            Route::get('/stock/create', [InventoryItemController::class, 'create'])->name('inventory.create');
            Route::post('/stock', [InventoryItemController::class, 'store'])->name('inventory.store');
            Route::get('/stock/{inventoryItem}/edit', [InventoryItemController::class, 'edit'])->name('inventory.edit');
            Route::put('/stock/{inventoryItem}', [InventoryItemController::class, 'update'])->name('inventory.update');
            Route::delete('/stock/{id}', [InventoryItemController::class, 'destroy'])->name('inventory.destroy');
            Route::post('/stock/{inventoryItem}/consume', [InventoryItemController::class, 'consume'])->name('inventory.consume');
            Route::post('/stock/{inventoryItem}/consume-unit', [InventoryItemController::class, 'consumeUnit'])->name('inventory.consume.unit');
            Route::get('/entreprises', [MobileCorporateClientController::class, 'index'])->name('corporate-clients.index');
            Route::get('/entreprises/create', [MobileCorporateClientController::class, 'create'])->name('corporate-clients.create');
            Route::post('/entreprises', [MobileCorporateClientController::class, 'store'])->name('corporate-clients.store');
            Route::get('/entreprises/{corporateClient}', [MobileCorporateClientController::class, 'show'])->name('corporate-clients.show');
            Route::get('/entreprises/{corporateClient}/edit', [MobileCorporateClientController::class, 'edit'])->name('corporate-clients.edit');
            Route::put('/entreprises/{corporateClient}', [MobileCorporateClientController::class, 'update'])->name('corporate-clients.update');
            Route::delete('/entreprises/{corporateClient}', [MobileCorporateClientController::class, 'destroy'])->name('corporate-clients.destroy');
            Route::get('/packs', [MobilePackProductController::class, 'index'])->name('packs.index');
            Route::get('/packs/create', [MobilePackProductController::class, 'create'])->name('packs.create');
            Route::post('/packs', [MobilePackProductController::class, 'store'])->name('packs.store');
            Route::get('/packs/{packProduct}', [MobilePackProductController::class, 'show'])->name('packs.show');
            Route::get('/packs/{packProduct}/edit', [MobilePackProductController::class, 'edit'])->name('packs.edit');
            Route::put('/packs/{packProduct}', [MobilePackProductController::class, 'update'])->name('packs.update');
            Route::delete('/packs/{packProduct}', [MobilePackProductController::class, 'destroy'])->name('packs.destroy');
            Route::post('/packs/{packProduct}/assign', [MobilePackProductController::class, 'assign'])->name('packs.assign');
            Route::delete('/pack-purchases/{packPurchase}/revoke', [MobilePackProductController::class, 'revokePurchase'])->name('packs.purchases.revoke');
            Route::get('/bons-cadeaux', [MobileGiftVoucherController::class, 'index'])->name('gift-vouchers.index');
            Route::get('/bons-cadeaux/create', [MobileGiftVoucherController::class, 'create'])->name('gift-vouchers.create');
            Route::post('/bons-cadeaux', [MobileGiftVoucherController::class, 'store'])->name('gift-vouchers.store');
            Route::get('/bons-cadeaux/{voucher}', [MobileGiftVoucherController::class, 'show'])->name('gift-vouchers.show');
            Route::get('/bons-cadeaux/{voucher}/pdf', [MobileGiftVoucherController::class, 'downloadPdf'])->name('gift-vouchers.pdf');
            Route::post('/bons-cadeaux/{voucher}/resend', [MobileGiftVoucherController::class, 'resendEmails'])->name('gift-vouchers.resend');
            Route::post('/bons-cadeaux/{voucher}/redeem', [MobileGiftVoucherController::class, 'redeem'])->name('gift-vouchers.redeem');
            Route::post('/bons-cadeaux/{voucher}/disable', [MobileGiftVoucherController::class, 'disable'])->name('gift-vouchers.disable');
            Route::get('/factures-recues', [MobileReceivedInvoiceController::class, 'index'])->name('received-invoices.index');
            Route::get('/factures-recues/{receivedInvoice}', [MobileReceivedInvoiceController::class, 'show'])->name('received-invoices.show');
            Route::get('/factures-recues/{receivedInvoice}/download', [MobileReceivedInvoiceController::class, 'download'])->name('received-invoices.download');
            Route::get('/formations-digitales', [MobileDigitalTrainingController::class, 'index'])->name('digital-trainings.index');
            Route::get('/formations-digitales/create', [MobileDigitalTrainingController::class, 'create'])->name('digital-trainings.create');
            Route::post('/formations-digitales', [MobileDigitalTrainingController::class, 'store'])->name('digital-trainings.store');
            Route::get('/formations-digitales/{digitalTraining}', [MobileDigitalTrainingController::class, 'show'])->name('digital-trainings.show');
            Route::get('/formations-digitales/{digitalTraining}/edit', [MobileDigitalTrainingController::class, 'edit'])->name('digital-trainings.edit');
            Route::put('/formations-digitales/{digitalTraining}', [MobileDigitalTrainingController::class, 'update'])->name('digital-trainings.update');
            Route::delete('/formations-digitales/{digitalTraining}', [MobileDigitalTrainingController::class, 'destroy'])->name('digital-trainings.destroy');
            Route::get('/communautes', [MobileCommunityController::class, 'index'])->name('communities.index');
            Route::get('/communautes/create', [MobileCommunityController::class, 'create'])->name('communities.create');
            Route::post('/communautes', [MobileCommunityController::class, 'store'])->name('communities.store');
            Route::get('/communautes/fichiers/{attachment}', [CommunityAttachmentController::class, 'downloadForPractitioner'])->name('communities.attachments.download');
            Route::get('/communautes/{community}', [MobileCommunityController::class, 'show'])->name('communities.show');
            Route::get('/communautes/{community}/edit', [MobileCommunityController::class, 'edit'])->name('communities.edit');
            Route::put('/communautes/{community}', [MobileCommunityController::class, 'update'])->name('communities.update');
            Route::delete('/communautes/{community}', [MobileCommunityController::class, 'destroy'])->name('communities.destroy');
            Route::post('/communautes/{community}/membres', [MobileCommunityController::class, 'storeMember'])->name('communities.members.store');
            Route::delete('/communautes/{community}/membres/{member}', [MobileCommunityController::class, 'destroyMember'])->name('communities.members.destroy');
            Route::post('/communautes/{community}/messages', [MobileCommunityController::class, 'storeMessage'])->name('communities.messages.store');
            Route::get('/audiences', [MobileAudienceController::class, 'index'])->name('audiences.index');
            Route::get('/audiences/create', [MobileAudienceController::class, 'create'])->name('audiences.create');
            Route::post('/audiences', [MobileAudienceController::class, 'store'])->name('audiences.store');
            Route::get('/audiences/{audience}', [MobileAudienceController::class, 'show'])->name('audiences.show');
            Route::get('/audiences/{audience}/edit', [MobileAudienceController::class, 'edit'])->name('audiences.edit');
            Route::put('/audiences/{audience}', [MobileAudienceController::class, 'update'])->name('audiences.update');
            Route::delete('/audiences/{audience}', [MobileAudienceController::class, 'destroy'])->name('audiences.destroy');
            Route::get('/newsletters', [MobileNewsletterController::class, 'index'])->name('newsletters.index');
            Route::get('/newsletters/create', [MobileNewsletterController::class, 'create'])->name('newsletters.create');
            Route::post('/newsletters', [MobileNewsletterController::class, 'store'])->name('newsletters.store');
            Route::get('/newsletters/{newsletter}', [MobileNewsletterController::class, 'show'])->name('newsletters.show');
            Route::get('/newsletters/{newsletter}/edit', [MobileNewsletterController::class, 'edit'])->name('newsletters.edit');
            Route::put('/newsletters/{newsletter}', [MobileNewsletterController::class, 'update'])->name('newsletters.update');
            Route::delete('/newsletters/{newsletter}', [MobileNewsletterController::class, 'destroy'])->name('newsletters.destroy');
            Route::post('/newsletters/{newsletter}/send-test', [MobileNewsletterController::class, 'sendTest'])->name('newsletters.send-test');
            Route::post('/newsletters/{newsletter}/send-now', [MobileNewsletterController::class, 'sendNow'])->name('newsletters.send-now');
            Route::get('/avis-google', [MobileGoogleReviewController::class, 'index'])->name('google-reviews.index');
            Route::get('/avis-google/connect', [MobileGoogleReviewController::class, 'redirectToGoogle'])->name('google-reviews.connect');
            Route::post('/avis-google/sync', [MobileGoogleReviewController::class, 'syncReviews'])->name('google-reviews.sync');
            Route::post('/avis-google/disconnect', [MobileGoogleReviewController::class, 'disconnect'])->name('google-reviews.disconnect');
            Route::get('/parrainage', [MobileReferralController::class, 'index'])->name('referrals.index');
            Route::post('/parrainage/invitations', [MobileReferralController::class, 'invite'])->name('referrals.invite');
            Route::post('/parrainage/invitations/{invite}/renvoyer', [MobileReferralController::class, 'resend'])->name('referrals.resend');
            Route::get('/profil', [MobileProfileController::class, 'index'])->name('profile.index');
            Route::get('/profil/edit', [MobileProfileController::class, 'edit'])->name('profile.edit');
            Route::put('/profil', [MobileProfileController::class, 'update'])->name('profile.update');
            Route::get('/abonnement', [MobileSubscriptionController::class, 'index'])->name('subscription.index');
        });

        Route::get('/clients', [ClientProfileController::class, 'index'])
            ->middleware('auth')
            ->name('clients.index');

        Route::get('/clients/create', [MobileClientController::class, 'create'])
            ->middleware('auth')
            ->name('clients.create');

        Route::post('/clients', [MobileClientController::class, 'store'])
            ->middleware('auth')
            ->name('clients.store');

        Route::get('/clients/{clientProfile}/edit', [MobileClientController::class, 'edit'])
            ->middleware('auth')
            ->name('clients.edit');

        Route::put('/clients/{clientProfile}', [MobileClientController::class, 'update'])
            ->middleware('auth')
            ->name('clients.update');

        Route::get('/clients/{clientProfile}', [ClientProfileController::class, 'show'])
            ->middleware('auth')
            ->name('clients.show');

        Route::get('/invoices', [InvoiceController::class, 'index'])
            ->middleware('auth')
            ->name('invoices.index');

        Route::get('/invoices/{invoice}', [MobileInvoiceController::class, 'show'])
            ->middleware('auth')
            ->name('invoices.show');

        Route::get('/devis/{invoice}', [MobileInvoiceController::class, 'showQuote'])
            ->middleware('auth')
            ->name('quotes.show');

        Route::get('/rendez-vous', [AppointmentController::class, 'index'])
            ->middleware('auth')
            ->name('appointments.index');

        Route::get('/rendez-vous/create', [AppointmentController::class, 'create'])
            ->middleware('auth')
            ->name('appointments.create');

        Route::post('/rendez-vous', [AppointmentController::class, 'store'])
            ->middleware('auth')
            ->name('appointments.store_practitioner');

        Route::get('/rendez-vous/{appointment}/edit', [AppointmentController::class, 'edit'])
            ->middleware('auth')
            ->name('appointments.edit');

        Route::put('/rendez-vous/{appointment}', [AppointmentController::class, 'update'])
            ->middleware('auth')
            ->name('appointments.update');

        Route::get('/rendez-vous/{appointment}', [AppointmentController::class, 'show'])
            ->middleware('auth')
            ->name('appointments.show');

        Route::get('/recherche-praticien', [TherapistSearchController::class, 'index'])
            ->name('search.index');

        Route::post('/recherche-praticien', [TherapistSearchController::class, 'search'])
            ->name('search.submit');

        Route::get('/therapeute/{slug}', [MobileTherapistController::class, 'show'])
            ->name('therapists.show');

        Route::post('/therapeute/{slug}/information', [MobileTherapistController::class, 'sendInformationRequest'])
            ->name('therapists.information');

        Route::get('/therapeute/{slug}/prendre-rdv', [MobileAppointmentController::class, 'createFromTherapistSlug'])
            ->name('appointments.create_from_therapist');

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
