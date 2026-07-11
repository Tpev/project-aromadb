<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyConsent;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Models\NewsletterOptOut;
use App\Models\User;
use Illuminate\Support\Facades\URL;

beforeEach(function () {
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.public_pages_enabled', true);
    config()->set('offer_journeys.tracking_enabled', true);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.allow_all_eligible_users', true);

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'cabinet-capture-test',
        'company_name' => 'Cabinet Capture',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ]);

    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide respiration',
        'objective' => 'lead_magnet',
        'public_title' => 'Le guide respiration',
        'summary' => 'Une ressource simple.',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide-respiration.pdf',
    ])->assertRedirect();

    $this->journey = OfferJourney::query()->firstOrFail();
    $this->actingAs($this->therapist)
        ->post(route('offer-journeys.publish', $this->journey))
        ->assertSessionHasNoErrors();
    $this->journey->refresh();
    auth()->logout();
});

it('captures and deduplicates a public contact with explicit consent evidence', function () {
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $url = route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
        'pageSlug' => $page->slug,
        'utm_source' => 'instagram',
        'utm_campaign' => 'guide-juillet',
    ]);

    $payload = [
        'first_name' => 'Nadine',
        'email' => 'NADINE@example.test ',
        'phone' => '06 12 34 56 78',
        'privacy_ack' => '1',
        'marketing_consent' => '1',
    ];

    $this->post($url, $payload)->assertRedirect();
    $this->post($url, $payload)->assertRedirect();

    $contact = OfferJourneyContact::query()->firstOrFail();

    expect(OfferJourneyContact::query()->count())->toBe(1)
        ->and(OfferJourneyEntry::query()->count())->toBe(1)
        ->and($contact->email_normalized)->toBe('nadine@example.test')
        ->and($contact->phone_normalized)->toBe('0612345678')
        ->and(OfferJourneyConsent::query()->where('purpose', 'requested_response')->count())->toBe(2)
        ->and(OfferJourneyConsent::query()->where('purpose', 'marketing_follow_up')->count())->toBe(2)
        ->and($contact->activities()->count())->toBe(2);

    expect($contact->entries()->first()->first_utm_source)->toBe('instagram')
        ->and($contact->entries()->first()->first_utm_campaign)->toBe('guide-juillet');
});

it('rejects public capture without privacy acknowledgement', function () {
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');

    $this->from(route('offer-journeys.public.show', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
    ]))->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
        'pageSlug' => $page->slug,
    ]), [
        'first_name' => 'Nadine',
        'email' => 'nadine@example.test',
    ])->assertSessionHasErrors('privacy_ack');

    expect(OfferJourneyContact::query()->count())->toBe(0);
});

it('keeps signed unsubscribe working and synchronizes newsletter suppression', function () {
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
        'pageSlug' => $page->slug,
    ]), [
        'first_name' => 'Nadine',
        'email' => 'nadine@example.test',
        'privacy_ack' => '1',
        'marketing_consent' => '1',
    ]);

    $contact = OfferJourneyContact::query()->firstOrFail();
    $confirmUrl = URL::temporarySignedRoute(
        'offer-journeys.unsubscribe.confirm',
        now()->addHour(),
        ['contact' => $contact]
    );

    config()->set('offer_journeys.enabled', false);
    $this->post($confirmUrl)->assertRedirect();

    expect(OfferJourneySuppression::query()->where('email_normalized', 'nadine@example.test')->exists())->toBeTrue()
        ->and(NewsletterOptOut::query()->where('user_id', $this->therapist->id)->where('email', 'nadine@example.test')->exists())->toBeTrue()
        ->and(OfferJourneyConsent::query()->where('purpose', 'marketing_follow_up')->where('status', 'withdrawn')->exists())->toBeTrue();
});
