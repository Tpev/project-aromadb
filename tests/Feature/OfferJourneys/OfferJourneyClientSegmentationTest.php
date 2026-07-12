<?php

use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Models\OfferJourneySuppression;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Services\OfferJourneyCampaignSender;
use App\Domain\OfferJourneys\Services\OfferJourneySegmentQuery;
use App\Mail\OfferJourneyMessageMail;
use App\Models\ClientProfile;
use App\Models\NewsletterOptOut;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Jobs\SendOfferJourneyCampaign;

beforeEach(function () {
    Carbon::setTestNow('2026-07-12 10:00:00');
    foreach ([
        'enabled', 'email_enabled', 'campaigns_enabled', 'client_tags_enabled',
        'segment_campaigns_enabled', 'commercial_tools_enabled',
    ] as $flag) {
        config()->set('offer_journeys.'.$flag, true);
    }
    config()->set('offer_journeys.pause_all_marketing_emails', false);
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.contact_frequency_hours', 72);

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'client-segmentation-test',
        'company_name' => 'Cabinet Segmentation',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
        'created_at' => now()->subYear(),
    ]);
});

afterEach(fn () => Carbon::setTestNow());

function taggedJourneyContact(User $user, OfferJourneyTag $tag, string $email, bool $consent = true): OfferJourneyContact
{
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $user->id,
        'email' => $email,
        'email_normalized' => strtolower($email),
        'first_name' => 'Camille',
        'status' => 'new',
        'last_activity_at' => now(),
    ]);
    $contact->tags()->attach($tag);
    if ($consent) {
        $contact->consents()->create([
            'purpose' => 'marketing_follow_up',
            'status' => 'granted',
            'text_version' => 'test-v1',
            'text_snapshot' => 'Consentement de test',
            'source' => 'test',
            'granted_at' => now(),
        ]);
    }

    return $contact;
}

it('adds and removes owned tags on client profiles without creating consent', function () {
    $client = ClientProfile::query()->create([
        'user_id' => $this->therapist->id,
        'first_name' => 'Nadine',
        'last_name' => 'Martin',
        'email' => 'nadine-client@example.test',
    ]);
    $tag = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Atelier juillet', 'slug' => 'atelier-juillet',
    ]);

    $this->actingAs($this->therapist)->post(route('offer-journeys.client-tags.attach', $client), [
        'tag_id' => $tag->id,
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect($client->fresh()->marketingTags)->toHaveCount(1)
        ->and(OfferJourneyContact::query()->count())->toBe(0);

    $this->actingAs($this->therapist)->delete(route('offer-journeys.client-tags.detach', [$client, $tag]))
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($client->fresh()->marketingTags)->toHaveCount(0);
});

it('refuses tags and client profiles owned by another practitioner', function () {
    $other = User::factory()->create([
        'is_therapist' => true, 'slug' => 'other-tag-owner',
        'license_product' => 'new_premium_mensuelle', 'license_status' => 'active',
    ]);
    $client = ClientProfile::query()->create([
        'user_id' => $this->therapist->id, 'first_name' => 'Léa', 'last_name' => 'Test', 'email' => 'lea-owner@example.test',
    ]);
    $foreignTag = OfferJourneyTag::query()->create([
        'user_id' => $other->id, 'name' => 'Privée', 'slug' => 'privee',
    ]);

    $this->actingAs($this->therapist)->post(route('offer-journeys.client-tags.attach', $client), [
        'tag_id' => $foreignTag->id,
    ])->assertSessionHasErrors('tag_id');

    $this->actingAs($other)->post(route('offer-journeys.client-tags.attach', $client), [
        'tag_id' => $foreignTag->id,
    ])->assertForbidden();
});

it('matches direct and client profile tags with all any and missing rules', function () {
    $workshop = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Atelier', 'slug' => 'atelier',
    ]);
    $priority = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Prioritaire', 'slug' => 'prioritaire',
    ]);
    $profile = ClientProfile::query()->create([
        'user_id' => $this->therapist->id, 'first_name' => 'Amandine', 'last_name' => 'Test', 'email' => 'amandine-profile@example.test',
    ]);
    $profile->marketingTags()->attach($workshop);
    $fromProfile = OfferJourneyContact::query()->create([
        'user_id' => $this->therapist->id, 'client_profile_id' => $profile->id,
        'email' => $profile->email, 'email_normalized' => $profile->email, 'status' => 'new',
    ]);
    $direct = taggedJourneyContact($this->therapist, $workshop, 'direct-tag@example.test', false);
    $direct->tags()->attach($priority);
    taggedJourneyContact($this->therapist, $priority, 'priority-only@example.test', false);

    $all = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Atelier prioritaire', 'match_type' => 'all', 'is_active' => true,
    ]);
    $all->rules()->createMany([
        ['field' => 'tag', 'operator' => 'has', 'value_json' => ['value' => $workshop->id], 'position' => 0],
        ['field' => 'tag', 'operator' => 'has', 'value_json' => ['value' => $priority->id], 'position' => 1],
    ]);
    $any = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Atelier ou prioritaire', 'match_type' => 'any', 'is_active' => true,
    ]);
    $any->rules()->createMany($all->rules->map->only(['field', 'operator', 'value_json', 'position'])->all());
    $missing = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Sans atelier', 'match_type' => 'all', 'is_active' => true,
    ]);
    $missing->rules()->create(['field' => 'tag', 'operator' => 'missing', 'value_json' => ['value' => $workshop->id], 'position' => 0]);

    $service = app(OfferJourneySegmentQuery::class);
    $base = fn () => OfferJourneyContact::query()->where('user_id', $this->therapist->id);

    expect($service->apply($base(), $all->load('rules'))->pluck('id')->all())->toBe([$direct->id])
        ->and($service->apply($base(), $any->load('rules'))->count())->toBe(3)
        ->and($service->apply($base(), $missing->load('rules'))->pluck('id')->all())->not->toContain($fromProfile->id, $direct->id);
});

it('sends a segment campaign only to currently eligible contacts and remains idempotent', function () {
    Mail::fake();
    $tag = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Guide', 'slug' => 'guide',
    ]);
    $eligible = taggedJourneyContact($this->therapist, $tag, 'eligible@example.test');
    taggedJourneyContact($this->therapist, $tag, 'without-consent@example.test', false);
    $unsubscribed = taggedJourneyContact($this->therapist, $tag, 'unsubscribed@example.test');
    NewsletterOptOut::query()->create([
        'user_id' => $this->therapist->id, 'email' => $unsubscribed->email, 'reason' => 'test', 'unsubscribed_at' => now(),
    ]);
    $suppressed = taggedJourneyContact($this->therapist, $tag, 'suppressed@example.test');
    OfferJourneySuppression::query()->create([
        'user_id' => $this->therapist->id, 'offer_journey_contact_id' => $suppressed->id,
        'email_normalized' => $suppressed->email_normalized, 'type' => 'bounce', 'reason' => 'test', 'source' => 'test', 'suppressed_at' => now(),
    ]);
    taggedJourneyContact($this->therapist, $tag, 'adresse-invalide', true);

    $segment = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Guide', 'match_type' => 'all', 'is_active' => true,
    ]);
    $segment->rules()->create(['field' => 'tag', 'operator' => 'has', 'value_json' => ['value' => $tag->id], 'position' => 0]);
    $campaign = OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id,
        'audience_type' => 'segment',
        'offer_journey_segment_id' => $segment->id,
        'name' => 'Guide pratique',
        'subject' => 'Bonjour {{prenom}}',
        'body' => 'Voici quelques informations utiles.',
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinute(),
    ]);

    app(OfferJourneyCampaignSender::class)->send($campaign->id);
    app(OfferJourneyCampaignSender::class)->send($campaign->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect($campaign->fresh()->sent_count)->toBe(1)
        ->and($campaign->fresh()->eligible_count)->toBe(1)
        ->and(data_get($campaign->fresh()->summary_json, 'no_consent'))->toBe(1)
        ->and(data_get($campaign->fresh()->summary_json, 'unsubscribed'))->toBe(1)
        ->and(data_get($campaign->fresh()->summary_json, 'suppressed'))->toBe(1)
        ->and(data_get($campaign->fresh()->summary_json, 'bounce_or_complaint'))->toBe(1)
        ->and(data_get($campaign->fresh()->summary_json, 'invalid_email'))->toBe(1)
        ->and($campaign->fresh()->deliveries()->count())->toBe(1)
        ->and($eligible->messageDeliveries()->where('is_test', false)->count())->toBe(1);
});

it('recalculates the segment when sending and sends tests only to the practitioner', function () {
    Mail::fake();
    $tag = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Rappel', 'slug' => 'rappel',
    ]);
    $segment = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'À rappeler', 'match_type' => 'all', 'is_active' => true,
    ]);
    $segment->rules()->create(['field' => 'tag', 'operator' => 'has', 'value_json' => ['value' => $tag->id], 'position' => 0]);

    $this->actingAs($this->therapist)->post(route('offer-journeys.message-campaigns.store'), [
        'name' => 'Rappel utile', 'subject' => 'Bonjour {{prenom}}', 'body' => 'Un message utile.',
        'action' => 'draft', 'audience_type' => 'segment', 'segment_id' => $segment->id,
    ])->assertRedirect()->assertSessionHasNoErrors();
    $campaign = OfferJourneyMessageCampaign::query()->firstOrFail();

    $this->actingAs($this->therapist)->put(route('offer-journeys.message-campaigns.update', $campaign), [
        'name' => 'Rappel modifié', 'subject' => 'Bonjour {{prenom}}', 'body' => 'Un message relu.',
        'audience_type' => 'segment', 'segment_id' => $segment->id,
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($campaign->fresh()->name)->toBe('Rappel modifié');

    $this->actingAs($this->therapist)->post(route('offer-journeys.message-campaigns.test', $campaign))
        ->assertRedirect()->assertSessionHasNoErrors();
    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect($campaign->fresh()->status)->toBe('draft');

    taggedJourneyContact($this->therapist, $tag, 'added-after-draft@example.test');
    $this->actingAs($this->therapist)->postJson(route('offer-journeys.message-campaigns.estimate'), [
        'audience_type' => 'segment', 'segment_id' => $segment->id,
    ])->assertOk()->assertJsonPath('matching', 1)->assertJsonPath('eligible', 1);
    $campaign->update(['status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);
    app(OfferJourneyCampaignSender::class)->send($campaign->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 2);
    expect($campaign->fresh()->eligible_count)->toBe(1)->and($campaign->fresh()->sent_count)->toBe(1);
});

it('keeps client tags behind their flag and can cancel a scheduled segment campaign', function () {
    $client = ClientProfile::query()->create([
        'user_id' => $this->therapist->id, 'first_name' => 'Flag', 'last_name' => 'Test', 'email' => 'flag@example.test',
    ]);
    $tag = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Flag', 'slug' => 'flag',
    ]);
    config()->set('offer_journeys.client_tags_enabled', false);
    $this->actingAs($this->therapist)->post(route('offer-journeys.client-tags.attach', $client), ['tag_id' => $tag->id])
        ->assertNotFound();

    config()->set('offer_journeys.client_tags_enabled', true);
    $segment = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Annulation', 'match_type' => 'all', 'is_active' => true,
    ]);
    $segment->rules()->create(['field' => 'tag', 'operator' => 'has', 'value_json' => ['value' => $tag->id], 'position' => 0]);
    $campaign = OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id, 'audience_type' => 'segment', 'offer_journey_segment_id' => $segment->id,
        'name' => 'À annuler', 'subject' => 'Test', 'body' => 'Test', 'status' => 'scheduled', 'scheduled_at' => now()->addHour(),
    ]);
    $this->actingAs($this->therapist)->post(route('offer-journeys.message-campaigns.cancel', $campaign))
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($campaign->fresh()->status)->toBe('cancelled');
});

it('does not send a scheduled segment campaign after its feature flag is disabled', function () {
    Mail::fake();
    $tag = OfferJourneyTag::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Pilote', 'slug' => 'pilote',
    ]);
    taggedJourneyContact($this->therapist, $tag, 'flag-disabled@example.test');
    $segment = OfferJourneySegment::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Pilote', 'match_type' => 'all', 'is_active' => true,
    ]);
    $segment->rules()->create(['field' => 'tag', 'operator' => 'has', 'value_json' => ['value' => $tag->id], 'position' => 0]);
    $campaign = OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id, 'audience_type' => 'segment', 'offer_journey_segment_id' => $segment->id,
        'name' => 'Pilote désactivé', 'subject' => 'Test', 'body' => 'Test', 'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
    ]);

    config()->set('offer_journeys.segment_campaigns_enabled', false);
    app(OfferJourneyCampaignSender::class)->send($campaign->id);

    Mail::assertNothingSent();
    expect($campaign->fresh()->status)->toBe('scheduled')
        ->and($campaign->fresh()->summary_json['blocking_reason'])->toBe('segment_campaigns_disabled');
});

it('dispatches due campaigns through the queue', function () {
    Queue::fake();
    $campaign = OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'À envoyer', 'subject' => 'Test', 'body' => 'Test',
        'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
    ]);

    $this->artisan('offer-journeys:dispatch-campaigns')->assertSuccessful();

    Queue::assertPushed(SendOfferJourneyCampaign::class, fn ($job) => $job->campaignId === $campaign->id);
});
