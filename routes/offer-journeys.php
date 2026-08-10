<?php

use App\Http\Controllers\OfferJourneys\OfferJourneyController;
use App\Http\Controllers\OfferJourneys\OfferJourneyPageController;
use App\Http\Controllers\OfferJourneys\PublicOfferJourneyController;
use App\Http\Controllers\OfferJourneys\OfferJourneyUnsubscribeController;
use App\Http\Controllers\OfferJourneys\OfferJourneyAnalyticsController;
use App\Http\Controllers\OfferJourneys\OfferJourneyCampaignController;
use App\Http\Controllers\OfferJourneys\OfferJourneyContactController;
use App\Http\Controllers\OfferJourneys\OfferJourneyPipelineController;
use App\Http\Controllers\OfferJourneys\OfferJourneyTaskController;
use App\Http\Controllers\OfferJourneys\OfferJourneyAutomationController;
use App\Http\Controllers\OfferJourneys\OfferJourneyContactOrganizationController;
use App\Http\Controllers\OfferJourneys\OfferJourneyAutomationNodeController;
use App\Http\Controllers\OfferJourneys\OfferJourneyPreviewController;
use App\Http\Controllers\OfferJourneys\OfferJourneyResourceController;
use App\Http\Controllers\OfferJourneys\OfferJourneyUsageController;
use App\Http\Controllers\OfferJourneys\OfferJourneySupportController;
use App\Http\Controllers\OfferJourneys\OfferJourneyGuideController;
use App\Http\Controllers\OfferJourneys\OfferJourneyWritingAssistantController;
use App\Http\Controllers\OfferJourneys\OfferJourneyReusableSectionController;
use App\Http\Controllers\OfferJourneys\OfferJourneyMessageToolController;
use App\Http\Controllers\OfferJourneys\OfferJourneyMessageCampaignController;
use App\Http\Controllers\OfferJourneys\OfferJourneyCommercialController;
use App\Http\Controllers\OfferJourneys\OfferJourneyContactImportController;
use App\Http\Controllers\OfferJourneys\OfferJourneyClientTagController;
use App\Http\Controllers\OfferJourneys\OfferJourneyEmailEditorController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')
    ->prefix('admin/offer-journeys/support')
    ->name('admin.offer-journeys.support.')
    ->group(function () {
        Route::get('/', [OfferJourneySupportController::class, 'index'])->name('index');
        Route::post('/practitioners/{user}/sender-control', [OfferJourneySupportController::class, 'senderControl'])->name('sender-control');
        Route::post('/journeys/{journey}/pause', [OfferJourneySupportController::class, 'pauseJourney'])->name('journeys.pause');
        Route::post('/runs/{run}/retry', [OfferJourneySupportController::class, 'retryRun'])->name('runs.retry');
        Route::post('/reconcile', [OfferJourneySupportController::class, 'reconcile'])->name('reconcile');
    });

Route::middleware(['auth', 'offer-journeys.available'])
    ->prefix('dashboard-pro/etiquettes-clients')
    ->name('offer-journeys.client-tags.')
    ->group(function () {
        Route::post('/actions-groupees', [OfferJourneyClientTagController::class, 'bulk'])->name('bulk');
        Route::post('/{clientProfile}', [OfferJourneyClientTagController::class, 'attach'])->name('attach');
        Route::delete('/{clientProfile}/{tag}', [OfferJourneyClientTagController::class, 'detach'])->name('detach');
    });

Route::middleware(['auth', 'offer-journeys.available'])
    ->prefix('dashboard-pro/contacts-interesses')
    ->name('offer-journeys.contacts.')
    ->group(function () {
        Route::get('/', [OfferJourneyContactController::class, 'index'])->name('index');
        Route::get('/export', [OfferJourneyContactController::class, 'export'])->name('export');
        Route::get('/importer', [OfferJourneyContactImportController::class, 'index'])->name('import.index');
        Route::post('/importer/apercu', [OfferJourneyContactImportController::class, 'preview'])->name('import.preview');
        Route::post('/importer/{import}/confirmer', [OfferJourneyContactImportController::class, 'commit'])->name('import.commit');
        Route::post('/importer/{import}/annuler', [OfferJourneyContactImportController::class, 'rollback'])->name('import.rollback');
        Route::get('/pipeline', [OfferJourneyPipelineController::class, 'index'])->name('pipeline');
        Route::post('/filtres', [OfferJourneyCommercialController::class, 'saveFilter'])->name('filters.store');
        Route::delete('/filtres/{filter}', [OfferJourneyCommercialController::class, 'deleteFilter'])->name('filters.destroy');
        Route::post('/actions-groupees', [OfferJourneyCommercialController::class, 'bulk'])->name('bulk');
        Route::post('/objectifs', [OfferJourneyCommercialController::class, 'goal'])->name('goals.store');
        Route::get('/segments', [OfferJourneyContactOrganizationController::class, 'segments'])->name('segments');
        Route::post('/segments/estimation', [OfferJourneyContactOrganizationController::class, 'estimateSegment'])->name('segments.estimate');
        Route::post('/segments', [OfferJourneyContactOrganizationController::class, 'storeSegment'])->name('segments.store');
        Route::delete('/segments/{segment}', [OfferJourneyContactOrganizationController::class, 'destroySegment'])->name('segments.destroy');
        Route::post('/tags', [OfferJourneyContactOrganizationController::class, 'storeTag'])->name('tags.store');
        Route::put('/tags/{tag}', [OfferJourneyContactOrganizationController::class, 'updateTag'])->name('tags.update');
        Route::delete('/tags/{tag}', [OfferJourneyContactOrganizationController::class, 'destroyTag'])->name('tags.destroy');
        Route::get('/{contact}', [OfferJourneyContactController::class, 'show'])->name('show');
        Route::put('/{contact}/status', [OfferJourneyContactController::class, 'updateStatus'])->name('status');
        Route::post('/{contact}/notes', [OfferJourneyContactController::class, 'storeNote'])->name('notes.store');
        Route::put('/{contact}/pipeline', [OfferJourneyPipelineController::class, 'move'])->name('pipeline.move');
        Route::post('/{contact}/tasks', [OfferJourneyTaskController::class, 'store'])->name('tasks.store');
        Route::put('/{contact}/tasks/{task}', [OfferJourneyTaskController::class, 'update'])->name('tasks.update');
        Route::post('/{contact}/tags', [OfferJourneyContactOrganizationController::class, 'attachTag'])->name('tags.attach');
        Route::delete('/{contact}/tags/{tag}', [OfferJourneyContactOrganizationController::class, 'detachTag'])->name('tags.detach');
        Route::delete('/{contact}', [OfferJourneyContactOrganizationController::class, 'anonymize'])->name('anonymize');
        Route::post('/{contact}/fusionner', [OfferJourneyCommercialController::class, 'merge'])->name('merge');
    });

Route::middleware(['auth', 'offer-journeys.available'])
    ->prefix('dashboard-pro/parcours-offres')
    ->name('offer-journeys.')
    ->group(function () {
        Route::get('/', [OfferJourneyController::class, 'index'])->name('index');
        Route::get('/campagnes-messages', [OfferJourneyMessageCampaignController::class, 'index'])->name('message-campaigns.index');
        Route::post('/campagnes-messages', [OfferJourneyMessageCampaignController::class, 'store'])->name('message-campaigns.store');
        Route::post('/campagnes-messages/estimation', [OfferJourneyMessageCampaignController::class, 'estimate'])->name('message-campaigns.estimate');
        Route::put('/campagnes-messages/{campaign}', [OfferJourneyMessageCampaignController::class, 'update'])->name('message-campaigns.update');
        Route::post('/campagnes-messages/{campaign}/programmer', [OfferJourneyMessageCampaignController::class, 'schedule'])->name('message-campaigns.schedule');
        Route::post('/campagnes-messages/{campaign}/test', [OfferJourneyMessageCampaignController::class, 'sendTest'])->name('message-campaigns.test');
        Route::post('/campagnes-messages/{campaign}/annuler', [OfferJourneyMessageCampaignController::class, 'cancel'])->name('message-campaigns.cancel');
        Route::post('/campagnes-messages/{campaign}/envoyer-maintenant', [OfferJourneyMessageCampaignController::class, 'sendNow'])->name('message-campaigns.send-now');
        Route::post('/campagnes-messages/{campaign}/repasser-en-brouillon', [OfferJourneyMessageCampaignController::class, 'returnToDraft'])->name('message-campaigns.return-to-draft');
        Route::post('/editeur-email/nouveau', [OfferJourneyEmailEditorController::class, 'start'])->name('email-editor.start');
        Route::get('/editeur-email/{campaign}', [OfferJourneyEmailEditorController::class, 'edit'])->name('email-editor.edit');
        Route::put('/editeur-email/{campaign}/sauvegarde', [OfferJourneyEmailEditorController::class, 'autosave'])->name('email-editor.autosave');
        Route::post('/editeur-email/{campaign}/apercu', [OfferJourneyEmailEditorController::class, 'preview'])->name('email-editor.preview');
        Route::post('/editeur-email/{campaign}/convertir', [OfferJourneyEmailEditorController::class, 'convert'])->name('email-editor.convert');
        Route::post('/editeur-email/{campaign}/images', [OfferJourneyEmailEditorController::class, 'upload'])->name('email-editor.assets.store');
        Route::delete('/editeur-email/{campaign}/images/{asset}', [OfferJourneyEmailEditorController::class, 'destroyAsset'])->name('email-editor.assets.destroy');
        Route::get('/create', [OfferJourneyController::class, 'create'])->name('create');
        Route::get('/guide', OfferJourneyGuideController::class)->name('guide');
        Route::get('/utilisation', OfferJourneyUsageController::class)->name('usage');
        Route::post('/', [OfferJourneyController::class, 'store'])->name('store');
        Route::get('/{journey}', [OfferJourneyController::class, 'show'])->name('show');
        Route::get('/{journey}/edit', [OfferJourneyController::class, 'edit'])->name('edit');
        Route::put('/{journey}', [OfferJourneyController::class, 'update'])->name('update');
        Route::post('/{journey}/publish', [OfferJourneyController::class, 'publish'])->name('publish');
        Route::post('/{journey}/pause', [OfferJourneyController::class, 'pause'])->name('pause');
        Route::post('/{journey}/archive', [OfferJourneyController::class, 'archive'])->name('archive');
        Route::post('/{journey}/duplicate', [OfferJourneyController::class, 'duplicate'])->name('duplicate');
        Route::post('/{journey}/versions/{version}/restore', [OfferJourneyController::class, 'restore'])
            ->name('versions.restore');
        Route::get('/{journey}/resultats', [OfferJourneyAnalyticsController::class, 'show'])->name('analytics');
        Route::get('/{journey}/partage', [OfferJourneyCampaignController::class, 'show'])->name('share');
        Route::get('/{journey}/apercu', [OfferJourneyPreviewController::class, 'create'])->name('preview');
        Route::post('/{journey}/campagnes', [OfferJourneyCampaignController::class, 'store'])->name('campaigns.store');
        Route::delete('/{journey}/campagnes/{campaign}', [OfferJourneyCampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::get('/{journey}/qr-code', [OfferJourneyCampaignController::class, 'qr'])->name('qr');
        Route::get('/{journey}/suivis', [OfferJourneyAutomationController::class, 'show'])->name('automation');
        Route::put('/{journey}/suivis/{automation}', [OfferJourneyAutomationController::class, 'update'])->name('automation.update');
        Route::post('/{journey}/suivis/{automation}/messages/{node}/apercu', [OfferJourneyMessageToolController::class, 'preview'])->name('automation.messages.preview');
        Route::post('/{journey}/suivis/{automation}/messages/{node}/test', [OfferJourneyMessageToolController::class, 'sendTest'])->name('automation.messages.test');
        Route::post('/{journey}/suivis/{automation}/versions/{version}/activate', [OfferJourneyAutomationController::class, 'activate'])->name('automation.activate');
        Route::post('/{journey}/suivis/{automation}/pause', [OfferJourneyAutomationController::class, 'pause'])->name('automation.pause');
        Route::post('/{journey}/suivis/{automation}/draft', [OfferJourneyAutomationController::class, 'createDraft'])->name('automation.draft');
        Route::put('/{journey}/suivis/{automation}/settings', [OfferJourneyAutomationController::class, 'updateSettings'])->name('automation.settings');
        Route::post('/{journey}/suivis/{automation}/versions/{version}/simulate', [OfferJourneyAutomationController::class, 'simulate'])->name('automation.simulate');
        Route::post('/{journey}/suivis/{automation}/versions/{version}/nodes', [OfferJourneyAutomationNodeController::class, 'store'])->name('automation.nodes.store');
        Route::put('/{journey}/suivis/{automation}/versions/{version}/nodes/{node}', [OfferJourneyAutomationNodeController::class, 'update'])->name('automation.nodes.update');
        Route::delete('/{journey}/suivis/{automation}/versions/{version}/nodes/{node}', [OfferJourneyAutomationNodeController::class, 'destroy'])->name('automation.nodes.destroy');
        Route::post('/{journey}/pages', [OfferJourneyPageController::class, 'store'])->name('pages.store');
        Route::get('/{journey}/pages/{page}/edit', [OfferJourneyPageController::class, 'edit'])->name('pages.edit');
        Route::put('/{journey}/pages/{page}', [OfferJourneyPageController::class, 'update'])->name('pages.update');
        Route::post('/{journey}/pages/{page}/assistant-redaction', OfferJourneyWritingAssistantController::class)->name('pages.writing-assistant');
        Route::post('/{journey}/pages/{page}/sections-reutilisables', [OfferJourneyReusableSectionController::class, 'store'])->name('pages.reusable-sections.store');
        Route::post('/{journey}/pages/{page}/sections-reutilisables/{section}', [OfferJourneyReusableSectionController::class, 'apply'])->name('pages.reusable-sections.apply');
        Route::delete('/sections-reutilisables/{section}', [OfferJourneyReusableSectionController::class, 'destroy'])->name('reusable-sections.destroy');
        Route::delete('/{journey}/pages/{page}', [OfferJourneyPageController::class, 'destroy'])->name('pages.destroy');
        Route::post('/{journey}/pages/{page}/move', [OfferJourneyPageController::class, 'move'])->name('pages.move');
    });

Route::middleware('offer-journeys.public')
    ->prefix('pro/{therapist:slug}/offres')
    ->name('offer-journeys.public.')
    ->group(function () {
        Route::get('/{journeySlug}/{pageSlug}/continuer', [PublicOfferJourneyController::class, 'follow'])
            ->name('continue');
        Route::get('/{journeySlug}/{pageSlug?}', [PublicOfferJourneyController::class, 'show'])
            ->name('show');
        Route::post('/{journeySlug}/{pageSlug}/contact', [PublicOfferJourneyController::class, 'capture'])
            ->middleware('throttle:10,1')
            ->name('capture');
    });

Route::middleware('signed')->group(function () {
    Route::get('/parcours-offres/ressources/{pageVersion}', [OfferJourneyResourceController::class, 'download'])
        ->name('offer-journeys.resources.download');
    Route::get('/apercu-parcours-offres/{journey}/ressource/{page}', [OfferJourneyResourceController::class, 'preview'])
        ->name('offer-journeys.resources.preview');
    Route::get('/apercu-parcours-offres/{journey}/{pageSlug?}', [OfferJourneyPreviewController::class, 'show'])
        ->name('offer-journeys.preview.show');
    Route::get('/parcours-offres/desinscription/{contact}', [OfferJourneyUnsubscribeController::class, 'show'])
        ->name('offer-journeys.unsubscribe.show');
    Route::post('/parcours-offres/desinscription/{contact}', [OfferJourneyUnsubscribeController::class, 'confirm'])
        ->name('offer-journeys.unsubscribe.confirm');
});
