<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Services\OfferJourneyResourceStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    Storage::fake('local');
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.public_pages_enabled', true);
    config()->set('offer_journeys.tracking_enabled', false);
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);
    $this->therapist = offerJourneyTherapist(['slug' => 'cabinet-resource-test']);
});

it('keeps an uploaded resource private and serves it only through a signed versioned link', function () {
    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide prive', 'objective' => 'lead_magnet', 'public_title' => 'Guide prive',
        'summary' => 'Une ressource test.', 'cta_label' => 'Recevoir',
        'resource_file' => UploadedFile::fake()->create('guide.pdf', 12, 'application/pdf'),
    ])->assertRedirect();
    $journey = OfferJourney::query()->firstOrFail();
    $stored = Storage::disk('local')->allFiles('private/offer-journeys/'.$this->therapist->id);
    expect($stored)->toHaveCount(1);

    $this->actingAs($this->therapist)->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    auth()->logout();
    $pageVersion = $journey->fresh()->publishedVersion->pages->firstWhere('type', 'content');
    $public = $this->get(route('offer-journeys.public.show', [
        'therapist' => $this->therapist, 'journeySlug' => $journey->slug, 'pageSlug' => $pageVersion->slug,
    ]));
    $public->assertOk()->assertDontSee($stored[0]);

    $follow = $this->get(route('offer-journeys.public.continue', [
        'therapist' => $this->therapist, 'journeySlug' => $journey->slug, 'pageSlug' => $pageVersion->slug,
    ]));
    $follow->assertRedirect();
    expect(URL::hasValidSignature(request()->create($follow->headers->get('Location'))))->toBeTrue();
    $this->get($follow->headers->get('Location'))->assertOk();
    $this->get(route('offer-journeys.resources.download', $pageVersion))->assertForbidden();
});

it('keeps the prior published file available after replacing the draft resource', function () {
    $journey = OfferJourney::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Versions fichier', 'slug' => 'versions-fichier',
        'objective' => 'lead_magnet', 'status' => 'draft',
    ]);
    $old = app(OfferJourneyResourceStorage::class)->store(UploadedFile::fake()->create('ancien.pdf', 5, 'application/pdf'), $this->therapist, $journey);
    $page = $journey->pages()->create([
        'name' => 'Ressource', 'slug' => 'ressource', 'type' => 'content', 'position' => 0,
        'draft_content_json' => ['title' => 'Ressource', 'cta_label' => 'Telecharger', 'resource_file' => $old],
        'validation_state' => 'ready',
    ]);
    $this->actingAs($this->therapist)->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    $oldVersion = $journey->fresh()->publishedVersion->pages->first();
    $oldUrl = URL::temporarySignedRoute('offer-journeys.resources.download', now()->addHour(), ['pageVersion' => $oldVersion]);

    $new = app(OfferJourneyResourceStorage::class)->store(UploadedFile::fake()->create('nouveau.pdf', 5, 'application/pdf'), $this->therapist, $journey);
    $page->update(['draft_content_json' => ['title' => 'Ressource', 'cta_label' => 'Telecharger', 'resource_file' => $new]]);

    $this->get($oldUrl)->assertOk();
    Storage::disk('local')->assertExists($old['path']);
    Storage::disk('local')->assertExists($new['path']);
});
