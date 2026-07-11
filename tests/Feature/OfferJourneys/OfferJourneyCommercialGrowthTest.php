<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyAbandonmentCandidate;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyContactImport;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Domain\OfferJourneys\Services\OfferJourneyAbandonmentReminder;
use App\Domain\OfferJourneys\Services\OfferJourneyAbandonmentTracker;
use App\Domain\OfferJourneys\Services\OfferJourneyCampaignSender;
use App\Mail\OfferJourneyMessageMail;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    Carbon::setTestNow('2026-07-11 10:00:00');
    foreach ([
        'enabled', 'public_pages_enabled', 'automation_enabled', 'email_enabled',
        'message_tools_enabled', 'campaigns_enabled', 'abandonment_reminders_enabled',
        'commercial_tools_enabled', 'contact_import_enabled',
    ] as $flag) {
        config()->set('offer_journeys.'.$flag, true);
    }
    config()->set('offer_journeys.pause_all_marketing_emails', false);
    config()->set('offer_journeys.tracking_enabled', false);
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.contact_frequency_hours', 72);

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'commercial-growth-test',
        'company_name' => 'Cabinet Test',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
        'created_at' => now()->subYear(),
    ]);

    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Séance découverte',
        'objective' => 'lead_magnet',
        'public_title' => 'Découvrir mon accompagnement',
        'summary' => 'Une première présentation claire.',
        'cta_label' => 'Recevoir les informations',
        'resource_url' => 'https://example.test/guide.pdf',
    ])->assertRedirect();
    $this->journey = OfferJourney::query()->firstOrFail();
    $this->actingAs($this->therapist)->post(route('offer-journeys.publish', $this->journey))->assertSessionHasNoErrors();
    auth()->logout();
});

afterEach(fn () => Carbon::setTestNow());

function captureCommercialContact($test, string $email = 'camille@example.test'): OfferJourneyContact
{
    $page = $test->journey->fresh()->publishedVersion->pages->firstWhere('type', 'opt_in');
    $test->post(route('offer-journeys.public.capture', [
        'therapist' => $test->therapist,
        'journeySlug' => $test->journey->slug,
        'pageSlug' => $page->slug,
    ]), ['first_name' => 'Camille', 'email' => $email, 'privacy_ack' => '1', 'marketing_consent' => '1'])->assertRedirect();

    return OfferJourneyContact::query()->where('email_normalized', $email)->firstOrFail();
}

it('sends one scheduled campaign and respects the cross-journey frequency cap', function () {
    Mail::fake();
    $contact = captureCommercialContact($this);
    $campaign = OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id,
        'created_by_user_id' => $this->therapist->id,
        'name' => 'Nouvelle ressource',
        'subject' => 'Bonjour {{prenom}}',
        'body' => 'Retrouvez {{offre}} ici : {{lien_offre}}',
        'status' => 'scheduled',
        'scheduled_at' => now()->subMinute(),
    ]);
    $campaign->journeys()->attach($this->journey->id);

    app(OfferJourneyCampaignSender::class)->send($campaign->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect($campaign->fresh()->status)->toBe('sent')
        ->and($campaign->fresh()->sent_count)->toBe(1)
        ->and(OfferJourneyMessageDelivery::query()->where('offer_journey_contact_id', $contact->id)->where('category', 'marketing')->where('is_test', false)->count())->toBe(1);

    $second = OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Deuxième campagne', 'subject' => 'Suite', 'body' => 'Bonjour',
        'status' => 'scheduled', 'scheduled_at' => now()->subMinute(),
    ]);
    $second->journeys()->attach($this->journey->id);
    app(OfferJourneyCampaignSender::class)->send($second->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect($second->fresh()->eligible_count)->toBe(0)->and($second->fresh()->sent_count)->toBe(0);
});

it('creates only reliable abandonment candidates and stops them after completion', function () {
    Mail::fake();
    $contact = captureCommercialContact($this);
    $this->journey->update(['source_type' => 'product', 'source_id' => 99]);
    $appointment = new Appointment(['user_id' => $this->therapist->id, 'product_id' => 99, 'status' => 'pending_payment']);
    $appointment->id = 456;
    $appointment->exists = true;

    app(OfferJourneyAbandonmentTracker::class)->sync($appointment, $this->therapist->id, $contact->email, 'product', 99, 'pending_payment');
    $candidate = OfferJourneyAbandonmentCandidate::query()->firstOrFail();
    $candidate->update(['reminder_due_at' => now()->subMinute()]);
    app(OfferJourneyAbandonmentReminder::class)->send($candidate->id);

    Mail::assertSent(OfferJourneyMessageMail::class, 1);
    expect($candidate->fresh()->state)->toBe('reminded');

    $candidate->update(['state' => 'started', 'reminded_at' => null]);
    app(OfferJourneyAbandonmentTracker::class)->sync($appointment, $this->therapist->id, $contact->email, 'product', 99, 'confirmed');
    expect($candidate->fresh()->state)->toBe('completed');
    app(OfferJourneyAbandonmentReminder::class)->send($candidate->id);
    Mail::assertSent(OfferJourneyMessageMail::class, 1);
});

it('previews commits and rolls back a csv import before any message is sent', function () {
    $csv = "email;prenom;nom;telephone;consentement_marketing\nlea@example.test;Léa;Martin;0612345678;oui\ninvalid;Erreur;;;non\n";
    $this->actingAs($this->therapist)->post(route('offer-journeys.contacts.import.preview'), [
        'file' => UploadedFile::fake()->createWithContent('contacts.csv', $csv),
        'consent_proof' => 'Formulaire papier signé le 10/07/2026',
    ])->assertRedirect()->assertSessionHasNoErrors();

    $import = OfferJourneyContactImport::query()->firstOrFail();
    expect($import->status)->toBe('preview')->and(data_get($import->report_json, 'valid'))->toBe(1)
        ->and(OfferJourneyContact::query()->where('email_normalized', 'lea@example.test')->exists())->toBeFalse();

    $this->actingAs($this->therapist)->post(route('offer-journeys.contacts.import.commit', $import))->assertRedirect()->assertSessionHasNoErrors();
    $contact = OfferJourneyContact::query()->where('email_normalized', 'lea@example.test')->firstOrFail();
    expect($contact->consents()->where('purpose', 'marketing_follow_up')->exists())->toBeTrue();

    $this->actingAs($this->therapist)->post(route('offer-journeys.contacts.import.rollback', $import->fresh()))->assertRedirect()->assertSessionHasNoErrors();
    expect(OfferJourneyContact::withTrashed()->where('email_normalized', 'lea@example.test')->exists())->toBeFalse()
        ->and($import->fresh()->status)->toBe('rolled_back');
});

it('supports cautious bulk updates, saved filters and verified duplicate merges', function () {
    $first = captureCommercialContact($this, 'first@example.test');
    $second = captureCommercialContact($this, 'second@example.test');
    $first->update(['phone' => '06 12 34 56 78', 'phone_normalized' => '0612345678']);
    $second->update(['phone' => '0612345678', 'phone_normalized' => '0612345678']);

    $stage = OfferJourneyPipelineStage::query()->where('user_id', $this->therapist->id)->where('system_key', 'qualify')->firstOrFail();
    $this->actingAs($this->therapist)->post(route('offer-journeys.contacts.bulk'), [
        'contact_ids' => [$first->id, $second->id],
        'confirm_count' => 2,
        'action' => 'move_stage',
        'pipeline_stage_id' => $stage->id,
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($first->fresh()->pipeline_stage_id)->toBe($stage->id)->and($second->fresh()->pipeline_stage_id)->toBe($stage->id);

    $this->actingAs($this->therapist)->post(route('offer-journeys.contacts.filters.store'), [
        'name' => 'À qualifier', 'filters' => ['status' => 'qualifying'],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $this->actingAs($this->therapist)->post(route('offer-journeys.contacts.merge', $first), ['duplicate_id' => $second->id])
        ->assertRedirect(route('offer-journeys.contacts.show', $first))->assertSessionHasNoErrors();
    expect(OfferJourneyContact::withTrashed()->whereKey($second->id)->exists())->toBeFalse();
});
