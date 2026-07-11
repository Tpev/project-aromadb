<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneySenderControl;
use App\Domain\OfferJourneys\Models\OfferJourneySupportAudit;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Services\OfferJourneyDnsDiagnostic;
use App\Models\User;

beforeEach(function () {
    config()->set('offer_journeys.support_console_enabled', true);
    $this->admin = User::factory()->create(['is_admin' => true, 'is_therapist' => false]);
    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'company_name' => 'Cabinet Test Support',
        'email' => 'support-praticien@example.test',
    ]);
    app()->instance(OfferJourneyDnsDiagnostic::class, new class extends OfferJourneyDnsDiagnostic
    {
        public function check(bool $fresh = false): array
        {
            return [
                'domain' => 'olithea.fr',
                'checked_at' => now(),
                'spf' => ['valid' => true, 'value' => 'v=spf1 include:amazonses.com -all', 'recommendation' => 'OK'],
                'dkim' => ['valid' => true, 'selectors' => [], 'recommendation' => 'OK'],
                'dmarc' => ['valid' => true, 'value' => 'v=DMARC1; p=quarantine', 'enforcement' => true, 'recommendation' => 'OK'],
            ];
        }
    });
});

it('keeps the support console behind both the feature flag and admin authorization', function () {
    $this->actingAs($this->therapist)
        ->get(route('admin.offer-journeys.support.index'))
        ->assertForbidden();

    config()->set('offer_journeys.support_console_enabled', false);
    $this->actingAs($this->admin)
        ->get(route('admin.offer-journeys.support.index'))
        ->assertNotFound();
});

it('shows the operational console to an administrator', function () {
    $this->actingAs($this->admin)
        ->get(route('admin.offer-journeys.support.index', ['q' => 'Cabinet Test']))
        ->assertOk()
        ->assertSee("Support des parcours d'offre", false)
        ->assertSee('Cabinet Test Support')
        ->assertSee('Authentification de olithea.fr');
});

it('searches contacts and scheduled campaigns without exposing message bodies', function () {
    OfferJourneyContact::query()->create([
        'user_id' => $this->therapist->id,
        'email' => 'camille-support@example.test',
        'email_normalized' => 'camille-support@example.test',
        'first_name' => 'Camille',
        'status' => 'new',
    ]);
    OfferJourneyMessageCampaign::query()->create([
        'user_id' => $this->therapist->id,
        'name' => 'Campagne atelier septembre',
        'subject' => 'Informations pratiques',
        'body' => 'Contenu confidentiel qui ne doit pas être affiché dans la liste support.',
        'status' => 'scheduled',
        'scheduled_at' => now()->addDay(),
    ]);

    $this->actingAs($this->admin)
        ->get(route('admin.offer-journeys.support.index', ['q' => 'camille-support']))
        ->assertOk()
        ->assertSee('camille-support@example.test');

    $this->actingAs($this->admin)
        ->get(route('admin.offer-journeys.support.index', ['q' => 'atelier septembre']))
        ->assertOk()
        ->assertSee('Campagne atelier septembre')
        ->assertDontSee('Contenu confidentiel');
});

it('pauses practitioner messages and records an immutable support audit', function () {
    $this->actingAs($this->admin)
        ->post(route('admin.offer-journeys.support.sender-control', $this->therapist), [
            'mode' => 'all',
            'reason' => 'Taux de plainte a verifier',
        ])
        ->assertRedirect()
        ->assertSessionHas('success');

    $control = OfferJourneySenderControl::query()->where('user_id', $this->therapist->id)->firstOrFail();
    $audit = OfferJourneySupportAudit::query()->firstOrFail();

    expect($control->all_email_paused)->toBeTrue()
        ->and($control->marketing_paused)->toBeTrue()
        ->and($audit->action)->toBe('sender_control.all')
        ->and(fn () => $audit->update(['reason' => 'altered']))->toThrow(LogicException::class);
});

it('allows support to pause a journey without deleting its published history', function () {
    $journey = OfferJourney::query()->create([
        'user_id' => $this->therapist->id,
        'name' => 'Guide sommeil',
        'slug' => 'guide-sommeil',
        'objective' => 'lead_magnet',
        'status' => 'published',
    ]);

    $this->actingAs($this->admin)
        ->post(route('admin.offer-journeys.support.journeys.pause', $journey), [
            'reason' => 'Verification demandee par le praticien',
        ])
        ->assertRedirect();

    expect($journey->fresh()->status)->toBe('paused')
        ->and($journey->fresh()->paused_at)->not->toBeNull()
        ->and(OfferJourneySupportAudit::query()->where('action', 'journey.pause')->exists())->toBeTrue();
});
