<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\User;

function offerJourneyTherapist(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'slug' => fake()->unique()->slug(2),
        'company_name' => 'Cabinet Test',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ], $overrides));
}

beforeEach(function () {
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.public_pages_enabled', false);
    config()->set('offer_journeys.tracking_enabled', false);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.allow_all_eligible_users', true);
});

it('is invisible when the global feature flag is disabled', function () {
    $therapist = offerJourneyTherapist();
    config()->set('offer_journeys.enabled', false);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.index'))
        ->assertNotFound();

    $this->get(route('offer-journeys.public.show', [
        'therapist' => $therapist,
        'journeySlug' => 'inexistant',
    ]))->assertNotFound();
});

it('restricts the beta to explicitly allowed practitioners', function () {
    $allowed = offerJourneyTherapist();
    $other = offerJourneyTherapist();
    config()->set('offer_journeys.beta_user_ids', [$allowed->id]);
    config()->set('offer_journeys.allow_all_eligible_users', false);

    $this->actingAs($allowed)->get(route('offer-journeys.index'))->assertOk();
    $this->actingAs($other)->get(route('offer-journeys.index'))->assertNotFound();
});

it('does not expose the module when the beta allowlist is empty', function () {
    $therapist = offerJourneyTherapist();
    config()->set('offer_journeys.allow_all_eligible_users', false);
    config()->set('offer_journeys.beta_user_ids', []);

    $this->actingAs($therapist)->get(route('offer-journeys.index'))->assertNotFound();
});

it('shows the presentation to pro users but prevents creation', function () {
    $therapist = offerJourneyTherapist(['license_product' => 'new_pro_mensuelle']);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.index'))
        ->assertOk()
        ->assertSee('Une fonctionnalité Premium');

    $this->actingAs($therapist)
        ->get(route('offer-journeys.create'))
        ->assertForbidden();
});

it('creates an isolated lead magnet journey with pages and a form', function () {
    $therapist = offerJourneyTherapist();

    $response = $this->actingAs($therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide sommeil',
        'objective' => 'lead_magnet',
        'public_title' => 'Préparer des nuits plus sereines',
        'summary' => 'Un guide pratique proposé par votre praticien.',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide-sommeil.pdf',
    ]);

    $journey = OfferJourney::query()->firstOrFail();
    $response->assertRedirect(route('offer-journeys.show', $journey));

    expect($journey->user_id)->toBe($therapist->id)
        ->and($journey->status)->toBe('draft')
        ->and($journey->pages()->count())->toBe(3)
        ->and($journey->pages()->where('type', 'content')->exists())->toBeTrue()
        ->and($journey->pages()->where('type', 'opt_in')->first()->form()->exists())->toBeTrue();
});

it('prevents a practitioner from opening another practitioners journey', function () {
    $owner = offerJourneyTherapist();
    $other = offerJourneyTherapist();
    $journey = OfferJourney::query()->create([
        'user_id' => $owner->id,
        'name' => 'Parcours privé',
        'slug' => 'parcours-prive',
        'objective' => 'lead_magnet',
        'status' => 'draft',
    ]);

    $this->actingAs($other)
        ->get(route('offer-journeys.show', $journey))
        ->assertForbidden();
});

it('publishes immutable page snapshots and keeps draft changes private', function () {
    $therapist = offerJourneyTherapist();
    $journey = OfferJourney::query()->create([
        'user_id' => $therapist->id,
        'name' => 'Séance découverte',
        'slug' => 'seance-decouverte',
        'objective' => 'lead_magnet',
        'status' => 'draft',
    ]);
    $page = $journey->pages()->create([
        'name' => 'Offre',
        'slug' => 'offre',
        'type' => 'opt_in',
        'position' => 0,
        'draft_content_json' => [
            'title' => 'Premier titre publié',
            'summary' => 'Présentation initiale',
            'cta_label' => 'Continuer',
            'resource_url' => 'https://example.test/ressource.pdf',
        ],
        'validation_state' => 'ready',
    ]);

    $this->actingAs($therapist)
        ->post(route('offer-journeys.publish', $journey))
        ->assertSessionHasNoErrors();

    $journey->refresh();
    $page->update(['draft_content_json' => [
        'title' => 'Titre encore en brouillon',
        'summary' => 'Cette modification ne doit pas être publique.',
        'cta_label' => 'Continuer',
    ]]);
    $journey->update(['name' => 'Nom interne encore en brouillon']);

    config()->set('offer_journeys.public_pages_enabled', true);

    $this->get(route('offer-journeys.public.show', [
        'therapist' => $therapist,
        'journeySlug' => $journey->slug,
    ]))
        ->assertOk()
        ->assertSee('Premier titre publié')
        ->assertSee('Séance découverte')
        ->assertDontSee('Titre encore en brouillon')
        ->assertDontSee('Nom interne encore en brouillon');
});
