<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyConsent;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Services\OfferJourneyRetentionService;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-07-11 10:00:00');
    config()->set('offer_journeys.retention.enabled', true);
    config()->set('offer_journeys.retention.contact_days', 30);
    config()->set('offer_journeys.retention.analytics_days', 30);
    config()->set('offer_journeys.retention.delivery_days', 30);
    config()->set('offer_journeys.retention.consent_evidence_days', 30);
});

afterEach(fn () => Carbon::setTestNow());

it('provides a dry run and anonymizes only expired marketing data', function () {
    $user = User::factory()->create(['is_therapist' => true]);
    $journey = OfferJourney::query()->create([
        'user_id' => $user->id,
        'name' => 'Guide test retention',
        'slug' => 'guide-test-retention',
        'objective' => 'lead_magnet',
    ]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $user->id,
        'email' => 'expired@example.test',
        'email_normalized' => 'expired@example.test',
        'first_name' => 'Ancien',
        'last_activity_at' => now()->subDays(60),
        'created_at' => now()->subDays(60),
        'updated_at' => now()->subDays(60),
    ]);
    $consent = OfferJourneyConsent::query()->create([
        'offer_journey_contact_id' => $contact->id,
        'offer_journey_id' => $journey->id,
        'purpose' => 'marketing_follow_up',
        'legal_basis' => 'consent',
        'status' => 'granted',
        'text_version' => 'draft-v1',
        'text_snapshot' => 'Texte accepte',
        'source' => 'public_journey_form',
        'context_json' => ['campaign_id' => 12],
        'ip_hash' => hash('sha256', '127.0.0.1'),
        'granted_at' => now()->subDays(60),
        'created_at' => now()->subDays(60),
        'updated_at' => now()->subDays(60),
    ]);
    $event = OfferJourneyEvent::query()->create([
        'offer_journey_id' => $journey->id,
        'offer_journey_contact_id' => $contact->id,
        'session_id' => 'visitor-123',
        'event_name' => 'page_view',
        'url' => 'https://olithea.fr/pro/test',
        'occurred_at' => now()->subDays(60),
    ]);
    $delivery = OfferJourneyMessageDelivery::query()->create([
        'user_id' => $user->id,
        'offer_journey_id' => $journey->id,
        'offer_journey_contact_id' => $contact->id,
        'category' => 'marketing',
        'status' => 'delivered',
        'recipient_email' => 'expired@example.test',
        'subject' => 'Ancien message',
        'idempotency_key' => 'retention-delivery',
        'sent_at' => now()->subDays(60),
        'created_at' => now()->subDays(60),
        'updated_at' => now()->subDays(60),
    ]);

    $service = app(OfferJourneyRetentionService::class);
    $dryRun = $service->apply($user->id, 100, true);

    expect($dryRun['applied'])->toBeFalse()
        ->and($dryRun['contacts'])->toBe(1)
        ->and($contact->fresh()->email)->toBe('expired@example.test');

    $result = $service->apply($user->id, 100);

    expect($result['applied'])->toBeTrue()
        ->and($contact->fresh()->status)->toBe('anonymized')
        ->and($contact->fresh()->email)->toBeNull()
        ->and($event->fresh()->session_id)->toBeNull()
        ->and($event->fresh()->offer_journey_contact_id)->toBeNull()
        ->and($delivery->fresh()->recipient_email)->toStartWith('anonymized+')
        ->and($consent->fresh()->ip_hash)->toBeNull()
        ->and($consent->fresh()->context_json)->toBeNull();
});

it('never anonymizes a marketing contact linked to a client record', function () {
    $user = User::factory()->create(['is_therapist' => true]);
    $client = ClientProfile::query()->create([
        'user_id' => $user->id,
        'first_name' => 'Cliente',
        'last_name' => 'Protegee',
        'email' => 'client@example.test',
    ]);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'email' => 'client@example.test',
        'email_normalized' => 'client@example.test',
        'last_activity_at' => now()->subYears(10),
        'created_at' => now()->subYears(10),
        'updated_at' => now()->subYears(10),
    ]);

    $result = app(OfferJourneyRetentionService::class)->apply($user->id, 100);

    expect($result['contacts'])->toBe(0)
        ->and($contact->fresh()->email)->toBe('client@example.test')
        ->and($client->fresh()->first_name)->toBe('Cliente');
});
