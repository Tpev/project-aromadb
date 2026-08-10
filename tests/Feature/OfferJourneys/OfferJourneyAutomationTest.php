<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationRun;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationProcessor;
use App\Mail\OfferJourneyMessageMail;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Domain\OfferJourneys\Models\OfferJourneyAutomationAction;

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-07-10 10:00:00', 'Europe/Paris'));
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.public_pages_enabled', true);
    config()->set('offer_journeys.tracking_enabled', false);
    config()->set('offer_journeys.automation_enabled', true);
    config()->set('offer_journeys.email_enabled', true);
    config()->set('offer_journeys.pause_all_marketing_emails', true);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.allow_all_eligible_users', true);

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'cabinet-automation-test',
        'company_name' => 'Cabinet Automatisation',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ]);

    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide énergie',
        'objective' => 'lead_magnet',
        'public_title' => 'Le guide énergie',
        'summary' => 'Une ressource pratique.',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide-energie.pdf',
    ])->assertRedirect();

    $this->journey = OfferJourney::query()->firstOrFail();
    $this->automation = $this->journey->automations()->with('versions.nodes')->firstOrFail();
    $this->version = $this->automation->versions->first();

    $this->actingAs($this->therapist)
        ->post(route('offer-journeys.publish', $this->journey))
        ->assertSessionHasNoErrors();
    $this->actingAs($this->therapist)
        ->post(route('offer-journeys.automation.activate', [$this->journey, $this->automation, $this->version]))
        ->assertSessionHasNoErrors();
    $this->journey->refresh();
    auth()->logout();
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates an immutable three-message draft and starts one run per captured contact', function () {
    expect($this->version->nodes)->toHaveCount(3)
        ->and($this->automation->fresh()->status)->toBe('active')
        ->and($this->version->fresh()->status)->toBe('published');

    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $payload = ['first_name' => 'Nadine', 'email' => 'nadine@example.test', 'privacy_ack' => '1'];
    $url = route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
        'pageSlug' => $page->slug,
    ]);

    $this->post($url, $payload)->assertRedirect();
    $this->post($url, $payload)->assertRedirect();

    expect(OfferJourneyAutomationRun::query()->count())->toBe(1)
        ->and(OfferJourneyAutomationRun::query()->first()->current_node_key)->toBe('message_1');
});

it('sends the requested response once and skips marketing without consent', function () {
    Mail::fake();
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
        'pageSlug' => $page->slug,
    ]), ['first_name' => 'Nadine', 'email' => 'nadine@example.test', 'privacy_ack' => '1']);

    $run = OfferJourneyAutomationRun::query()->firstOrFail();
    app(OfferJourneyAutomationProcessor::class)->process($run->id);
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect(OfferJourneyMessageDelivery::query()->where('status', 'sent')->count())->toBe(1)
        ->and($run->fresh()->current_node_key)->toBe('message_2');

    config()->set('offer_journeys.pause_all_marketing_emails', false);
    Carbon::setTestNow(Carbon::parse('2026-07-12 10:00:00', 'Europe/Paris'));
    $run->refresh()->update(['next_action_at' => now()]);
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect(OfferJourneyMessageDelivery::query()->where('status', 'skipped')->where('failure_reason', 'missing_consent')->exists())->toBeTrue();
});

it('does not send while the global email switch is disabled', function () {
    Mail::fake();
    config()->set('offer_journeys.email_enabled', false);
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $this->journey->slug,
        'pageSlug' => $page->slug,
    ]), ['first_name' => 'Nadine', 'email' => 'nadine@example.test', 'privacy_ack' => '1']);

    $run = OfferJourneyAutomationRun::query()->firstOrFail();
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    Mail::assertNothingSent();
    expect($run->fresh()->status)->toBe('running')
        ->and($run->fresh()->next_action_at->greaterThan(now()))->toBeTrue()
        ->and(OfferJourneyMessageDelivery::query()->count())->toBe(0);
});

it('simulates a sequence without sending or mutating module data', function () {
    Mail::fake();
    $beforeRuns = OfferJourneyAutomationRun::query()->count();

    $this->actingAs($this->therapist)->post(route('offer-journeys.automation.simulate', [
        $this->journey, $this->automation, $this->version,
    ]), ['marketing_consent' => '1', 'inactive_days' => 10])
        ->assertRedirect()
        ->assertSessionHas('simulation');

    Mail::assertNothingSent();
    expect(OfferJourneyAutomationRun::query()->count())->toBe($beforeRuns)
        ->and(OfferJourneyMessageDelivery::query()->count())->toBe(0)
        ->and(OfferJourneyAutomationAction::query()->count())->toBe(0);
});

it('defers messages during quiet hours', function () {
    Mail::fake();
    Carbon::setTestNow(Carbon::parse('2026-07-10 21:00:00', 'Europe/Paris'));
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist, 'journeySlug' => $this->journey->slug, 'pageSlug' => $page->slug,
    ]), ['first_name' => 'Nadine', 'email' => 'quiet@example.test', 'privacy_ack' => '1']);

    $run = OfferJourneyAutomationRun::query()->firstOrFail();
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    Mail::assertNothingSent();
    expect($run->fresh()->next_action_at->greaterThan(now()))->toBeTrue();
});

it('enforces the monthly marketing quota immediately before sending', function () {
    Mail::fake();
    $page = $this->journey->publishedVersion->pages->firstWhere('type', 'opt_in');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist, 'journeySlug' => $this->journey->slug, 'pageSlug' => $page->slug,
    ]), ['first_name' => 'Nadine', 'email' => 'quota@example.test', 'privacy_ack' => '1', 'marketing_consent' => '1']);
    $run = OfferJourneyAutomationRun::query()->firstOrFail();
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    config()->set('offer_journeys.limits.monthly_marketing_emails', 0);
    config()->set('offer_journeys.pause_all_marketing_emails', false);
    Carbon::setTestNow(Carbon::parse('2026-07-12 10:00:00', 'Europe/Paris'));
    $run->refresh()->update(['next_action_at' => now()]);
    app(OfferJourneyAutomationProcessor::class)->process($run->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect(OfferJourneyMessageDelivery::query()->where('failure_reason', 'monthly_quota')->where('status', 'skipped')->exists())->toBeTrue();
});

it('previews messages and sends tests only to the authenticated practitioner', function () {
    Mail::fake();
    config()->set('offer_journeys.message_tools_enabled', true);
    $node = $this->version->nodes->firstWhere('type', 'email');

    $this->actingAs($this->therapist)
        ->postJson(route('offer-journeys.automation.messages.preview', [$this->journey, $this->automation, $node]), [
            'subject' => 'Votre demande {{offre}}',
            'body' => "Bonjour {{prenom}},\n\nRetrouvez votre offre ici : {{lien_offre}}",
        ])
        ->assertOk()
        ->assertJsonPath('subject', 'Votre demande Guide énergie')
        ->assertJsonPath('warnings', []);

    $this->actingAs($this->therapist)
        ->postJson(route('offer-journeys.automation.messages.test', [$this->journey, $this->automation, $node]), [
            'subject' => 'Votre demande {{offre}}',
            'body' => "Bonjour {{prenom}},\n\nRetrouvez votre offre ici : {{lien_offre}}",
        ])
        ->assertOk()
        ->assertJsonFragment(['message' => 'Message test envoyé à '.$this->therapist->email.'.']);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect(OfferJourneyMessageDelivery::query()->where('category', 'test')->where('is_test', true)->count())->toBe(1)
        ->and(OfferJourneyAutomationRun::query()->count())->toBe(0)
        ->and(OfferJourneyAutomationAction::query()->count())->toBe(0);
});

it('estimates eligible recipients without counting test messages or recent marketing contacts', function () {
    Mail::fake();
    config()->set('offer_journeys.message_tools_enabled', true);
    config()->set('offer_journeys.contact_frequency_hours', 72);
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
    ])->assertRedirect();

    $contact = OfferJourneyEntry::query()->where('offer_journey_id', $this->journey->id)->firstOrFail()->contact;
    OfferJourneyMessageDelivery::query()->create([
        'user_id' => $this->therapist->id,
        'offer_journey_id' => $this->journey->id,
        'offer_journey_contact_id' => $contact->id,
        'node_key' => 'test_preview',
        'category' => 'test',
        'status' => 'sent',
        'recipient_email' => $contact->email,
        'subject' => '[TEST] Aperçu',
        'idempotency_key' => 'test-preview-delivery',
        'is_test' => true,
        'sent_at' => now(),
    ]);

    $this->actingAs($this->therapist)
        ->get(route('offer-journeys.automation', $this->journey))
        ->assertOk()
        ->assertSee('1 destinataire potentiellement concerné');

    OfferJourneyMessageDelivery::query()->create([
        'user_id' => $this->therapist->id,
        'offer_journey_id' => $this->journey->id,
        'offer_journey_contact_id' => $contact->id,
        'node_key' => 'message_2',
        'category' => 'marketing',
        'status' => 'sent',
        'recipient_email' => $contact->email,
        'subject' => 'Un conseil',
        'idempotency_key' => 'recent-marketing-delivery',
        'is_test' => false,
        'sent_at' => now(),
    ]);

    $this->actingAs($this->therapist)
        ->get(route('offer-journeys.automation', $this->journey))
        ->assertOk()
        ->assertSee('0 destinataires potentiellement concernés');
});

it('keeps message tools unavailable while their feature flag is disabled', function () {
    config()->set('offer_journeys.message_tools_enabled', false);
    $node = $this->version->nodes->firstWhere('type', 'email');

    $this->actingAs($this->therapist)
        ->postJson(route('offer-journeys.automation.messages.preview', [$this->journey, $this->automation, $node]), [
            'subject' => 'Test',
            'body' => 'Test',
        ])
        ->assertNotFound();
});
