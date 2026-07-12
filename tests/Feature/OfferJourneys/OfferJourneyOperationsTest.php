<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomation;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationAction;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyConversion;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationProcessor;
use App\Domain\OfferJourneys\Services\OfferJourneyAttributionContext;
use App\Models\Event;
use App\Models\Reservation;
use App\Models\User;
use App\Models\Product;

beforeEach(function () {
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.public_pages_enabled', true);
    config()->set('offer_journeys.tracking_enabled', true);
    config()->set('offer_journeys.automation_enabled', true);
    config()->set('offer_journeys.email_enabled', false);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.allow_all_eligible_users', true);

    $this->owner = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'cabinet-operations-test',
        'company_name' => 'Cabinet Opérations',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ]);
});

it('attributes campaign visits and form entries without trusting overwritten UTM values', function () {
    $this->actingAs($this->owner)->post(route('offer-journeys.store'), [
        'name' => 'Guide campagne', 'objective' => 'lead_magnet', 'public_title' => 'Guide campagne',
        'summary' => 'Présentation', 'cta_label' => 'Recevoir',
        'resource_url' => 'https://example.test/guide.pdf',
    ]);
    $journey = OfferJourney::query()->firstOrFail();
    $this->actingAs($this->owner)->post(route('offer-journeys.publish', $journey));
    $this->actingAs($this->owner)->post(route('offer-journeys.campaigns.store', $journey), [
        'name' => 'Instagram juillet', 'channel' => 'instagram', 'utm_content' => 'carrousel',
    ])->assertRedirect();
    $campaign = $journey->campaignLinks()->firstOrFail();
    auth()->logout();

    $publicUrl = route('offer-journeys.public.show', [
        'therapist' => $this->owner, 'journeySlug' => $journey->slug,
        'oj_campaign' => $campaign->code, 'utm_source' => 'falsifiee',
    ]);
    $this->get($publicUrl)->assertOk()->assertCookie('oj_visitor');

    $page = $journey->fresh()->publishedVersion->pages->firstWhere('type', 'opt_in');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->owner, 'journeySlug' => $journey->slug, 'pageSlug' => $page->slug,
        'oj_campaign' => $campaign->code, 'utm_source' => 'falsifiee',
    ]), ['first_name' => 'Nadine', 'email' => 'nadine@example.test', 'privacy_ack' => '1'])->assertRedirect();

    expect(OfferJourneyEvent::query()->where('offer_journey_campaign_link_id', $campaign->id)->count())->toBe(2)
        ->and(OfferJourneyEntry::query()->first()->first_utm_source)->toBe('instagram');
});

it('keeps contact organization scoped to the practitioner and supports dynamic segments', function () {
    $other = User::factory()->create([
        'is_therapist' => true, 'slug' => 'autre-cabinet-operations',
        'license_product' => 'new_premium_mensuelle', 'license_status' => 'active',
    ]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $this->owner->id, 'email' => 'nadine@example.test',
        'email_normalized' => 'nadine@example.test', 'first_name' => 'Nadine',
        'status' => 'new', 'last_activity_at' => now(),
    ]);

    $this->actingAs($other)->get(route('offer-journeys.contacts.show', $contact))->assertForbidden();
    $this->actingAs($this->owner)->post(route('offer-journeys.contacts.tags.store'), ['name' => 'Atelier juillet']);
    $tag = OfferJourneyTag::query()->firstOrFail();
    $this->actingAs($this->owner)->post(route('offer-journeys.contacts.tags.attach', $contact), ['tag_id' => $tag->id]);
    $this->actingAs($this->owner)->post(route('offer-journeys.contacts.segments.store'), [
        'name' => 'Intéressés atelier', 'field' => 'tag', 'value' => (string) $tag->id,
    ]);

    $this->actingAs($this->owner)->get(route('offer-journeys.contacts.segments'))
        ->assertOk()->assertSee('Intéressés atelier')->assertSee('1 personne');
    expect($contact->fresh()->tags)->toHaveCount(1);
});

it('attributes a confirmed event registration once and never blocks the reservation', function () {
    $event = Event::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Atelier respiration', 'description' => '',
        'start_date_time' => now()->addWeek(), 'duration' => 90, 'booking_required' => true,
        'limited_spot' => false, 'showOnPortail' => true, 'location' => 'Cabinet',
    ]);
    $journey = OfferJourney::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Parcours atelier', 'slug' => 'parcours-atelier',
        'objective' => 'event', 'status' => 'published', 'source_type' => 'event', 'source_id' => $event->id,
    ]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $this->owner->id, 'email' => 'nadine@example.test',
        'email_normalized' => 'nadine@example.test', 'status' => 'new', 'last_activity_at' => now(),
    ]);
    OfferJourneyEntry::query()->create([
        'offer_journey_id' => $journey->id, 'offer_journey_contact_id' => $contact->id,
        'status' => 'active', 'entered_at' => now(), 'last_activity_at' => now(),
    ]);

    $reservation = Reservation::query()->create([
        'event_id' => $event->id, 'full_name' => 'Nadine Test', 'email' => 'nadine@example.test',
        'status' => 'confirmed', 'amount_ttc' => 35,
    ]);
    $reservation->touch();

    expect(OfferJourneyConversion::query()->count())->toBe(1)
        ->and(OfferJourneyConversion::query()->first()->amount_cents)->toBe(3500)
        ->and($contact->fresh()->status)->toBe('converted');
});

it('carries encrypted attribution into a pending existing flow and confirms it later without the browser context', function () {
    $event = Event::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Atelier paiement', 'description' => '',
        'start_date_time' => now()->addWeek(), 'duration' => 90, 'booking_required' => true,
        'limited_spot' => false, 'showOnPortail' => true, 'location' => 'Cabinet',
    ]);
    $journey = OfferJourney::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Parcours paiement', 'slug' => 'parcours-paiement',
        'objective' => 'event', 'status' => 'published', 'source_type' => 'event', 'source_id' => $event->id,
    ]);
    $cookie = app(OfferJourneyAttributionContext::class)->cookie($journey, request());
    request()->cookies->set('oj_attribution', $cookie->getValue());

    $reservation = Reservation::query()->create([
        'event_id' => $event->id, 'full_name' => 'Nadine Test', 'email' => 'nadine-pending@example.test',
        'status' => 'pending_payment', 'amount_ttc' => 45,
    ]);
    expect(OfferJourneyContact::query()->where('email_normalized', 'nadine-pending@example.test')->exists())->toBeTrue()
        ->and(OfferJourneyConversion::query()->count())->toBe(0);

    request()->cookies->remove('oj_attribution');
    $reservation->update(['status' => 'paid']);

    expect(OfferJourneyConversion::query()->count())->toBe(1)
        ->and(OfferJourneyConversion::query()->first()->amount_cents)->toBe(4500);
});

it('executes a bounded automation action at most once', function () {
    $journey = OfferJourney::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Suivi action', 'slug' => 'suivi-action',
        'objective' => 'contact_request', 'status' => 'published',
    ]);
    $journeyVersion = $journey->versions()->create(['version_number' => 1, 'schema_version' => 1, 'snapshot_json' => [], 'published_at' => now()]);
    $journey->update(['published_version_id' => $journeyVersion->id]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $this->owner->id, 'email' => 'nadine@example.test',
        'email_normalized' => 'nadine@example.test', 'status' => 'new', 'last_activity_at' => now(),
    ]);
    $tag = OfferJourneyTag::query()->create(['user_id' => $this->owner->id, 'name' => 'À rappeler', 'slug' => 'a-rappeler']);
    $automation = OfferJourneyAutomation::query()->create([
        'user_id' => $this->owner->id, 'offer_journey_id' => $journey->id, 'name' => 'Action test',
        'status' => 'active', 'trigger_type' => 'lead_captured',
    ]);
    $version = $automation->versions()->create(['version_number' => 1, 'status' => 'published', 'definition_json' => [], 'published_at' => now()]);
    $version->nodes()->create([
        'node_key' => 'action_1', 'type' => 'action', 'name' => 'Ajouter une étiquette',
        'config_json' => ['action_type' => 'add_tag', 'value' => $tag->id, 'is_enabled' => true],
        'position_y' => 0,
    ]);
    $automation->update(['published_version_id' => $version->id, 'published_at' => now()]);
    $run = OfferJourneyAutomationRun::query()->create([
        'offer_journey_automation_id' => $automation->id,
        'offer_journey_automation_version_id' => $version->id,
        'offer_journey_version_id' => $journeyVersion->id,
        'offer_journey_contact_id' => $contact->id,
        'status' => 'running', 'current_node_key' => 'action_1',
        'idempotency_key' => 'action-test-run', 'started_at' => now(), 'next_action_at' => now(),
    ]);

    app(OfferJourneyAutomationProcessor::class)->process($run->id);
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    expect($contact->fresh()->tags)->toHaveCount(1)
        ->and(OfferJourneyAutomationAction::query()->count())->toBe(1)
        ->and($run->fresh()->status)->toBe('completed');
});

it('preselects only an owned online-bookable product in the existing appointment wizard', function () {
    $this->owner->update(['accept_online_appointments' => true]);
    $product = Product::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Séance découverte', 'description' => 'Présentation',
        'price' => 50, 'tax_rate' => 0, 'duration' => 60, 'can_be_booked_online' => true,
        'dans_le_cabinet' => true,
    ]);
    $other = User::factory()->create(['is_therapist' => true]);
    $foreignProduct = Product::query()->create([
        'user_id' => $other->id, 'name' => 'Autre séance', 'description' => '',
        'price' => 50, 'tax_rate' => 0, 'duration' => 60, 'can_be_booked_online' => true,
        'dans_le_cabinet' => true,
    ]);

    $this->get(route('appointments.createPatient', ['therapist' => $this->owner->id, 'product_id' => $product->id]))
        ->assertOk()->assertViewHas('preferredProduct', fn ($preferred) => $preferred?->id === $product->id);
    $this->get(route('appointments.createPatient', ['therapist' => $this->owner->id, 'product_id' => $foreignProduct->id]))
        ->assertOk()->assertViewHas('preferredProduct', null);
});

it('reconciles confirmed conversions idempotently after a missed observer run', function () {
    $event = Event::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Atelier reconciliation', 'description' => '',
        'start_date_time' => now()->addWeek(), 'duration' => 60, 'booking_required' => true,
        'limited_spot' => false, 'showOnPortail' => true, 'location' => 'Cabinet',
    ]);
    $journey = OfferJourney::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Reconciliation', 'slug' => 'reconciliation',
        'objective' => 'event', 'status' => 'published', 'source_type' => 'event', 'source_id' => $event->id,
    ]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $this->owner->id, 'email' => 'reconcile@example.test',
        'email_normalized' => 'reconcile@example.test', 'status' => 'new', 'last_activity_at' => now(),
    ]);
    OfferJourneyEntry::query()->create([
        'offer_journey_id' => $journey->id, 'offer_journey_contact_id' => $contact->id,
        'status' => 'active', 'entered_at' => now(), 'last_activity_at' => now(),
    ]);
    $reservation = Reservation::query()->create([
        'event_id' => $event->id, 'full_name' => 'Test Reconciliation',
        'email' => 'reconcile@example.test', 'status' => 'confirmed', 'amount_ttc' => 25,
    ]);
    OfferJourneyConversion::query()->delete();
    $contact->update(['status' => 'new', 'converted_at' => null]);

    $this->artisan('offer-journeys:reconcile-conversions', ['--days' => 2])->assertSuccessful();
    $this->artisan('offer-journeys:reconcile-conversions', ['--days' => 2])->assertSuccessful();

    expect(OfferJourneyConversion::query()->count())->toBe(1)
        ->and(OfferJourneyConversion::query()->first()->convertible_id)->toBe($reservation->id);
});

it('keeps conversion history when a confirmed registration is cancelled or refunded', function () {
    $event = Event::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Atelier annulation', 'description' => '',
        'start_date_time' => now()->addWeek(), 'duration' => 60, 'booking_required' => true,
        'limited_spot' => false, 'showOnPortail' => true, 'location' => 'Cabinet',
    ]);
    $journey = OfferJourney::query()->create([
        'user_id' => $this->owner->id, 'name' => 'Suivi annulation', 'slug' => 'suivi-annulation',
        'objective' => 'event', 'status' => 'published', 'source_type' => 'event', 'source_id' => $event->id,
    ]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $this->owner->id, 'email' => 'cancel@example.test',
        'email_normalized' => 'cancel@example.test', 'status' => 'new', 'last_activity_at' => now(),
    ]);
    OfferJourneyEntry::query()->create([
        'offer_journey_id' => $journey->id, 'offer_journey_contact_id' => $contact->id,
        'status' => 'active', 'entered_at' => now(), 'last_activity_at' => now(),
    ]);
    $reservation = Reservation::query()->create([
        'event_id' => $event->id, 'full_name' => 'Test Annulation',
        'email' => 'cancel@example.test', 'status' => 'confirmed', 'amount_ttc' => 30,
    ]);
    $reservation->update(['status' => 'cancelled']);
    expect(OfferJourneyConversion::query()->first()->status)->toBe('cancelled');
    $reservation->update(['status' => 'refunded']);
    expect(OfferJourneyConversion::query()->count())->toBe(1)
        ->and(OfferJourneyConversion::query()->first()->status)->toBe('refunded');
});
