<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Services\OfferJourneyPublicationPreflight;
use App\Models\User;

beforeEach(function () {
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.publication_assistance_enabled', true);
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'publication-assistance-test',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ]);
    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide de preparation',
        'objective' => 'lead_magnet',
        'public_title' => 'Preparez votre premiere seance',
        'summary' => 'Des reperes simples avant votre rendez-vous.',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide.pdf',
    ])->assertRedirect();
    $this->journey = OfferJourney::query()->firstOrFail();
});

it('shows one shared preflight checklist and blocks incomplete publication', function () {
    $firstPage = $this->journey->pages->first();
    $content = $firstPage->draft_content_json;
    $content['title'] = '';
    $firstPage->update(['draft_content_json' => $content]);

    $result = app(OfferJourneyPublicationPreflight::class)->inspect($this->journey->fresh());

    expect($result['ready'])->toBeFalse()
        ->and($result['errors'])->toHaveKey('title');

    $this->get(route('offer-journeys.show', $this->journey))
        ->assertOk()
        ->assertSee('Controle avant publication')
        ->assertSee('Ajoutez un titre public');

    $this->post(route('offer-journeys.publish', $this->journey))
        ->assertSessionHasErrors('title');
    expect($this->journey->versions()->count())->toBe(0);
});

it('warns about risky wording without silently rewriting practitioner copy', function () {
    $page = $this->journey->pages->first();
    $content = $page->draft_content_json;
    $content['summary'] = 'Une methode qui guerit sans aucun risque.';
    $page->update(['draft_content_json' => $content]);

    $result = app(OfferJourneyPublicationPreflight::class)->inspect($this->journey->fresh());

    expect($result['warnings'])->toHaveKey('medical_claims')
        ->and($page->fresh()->draft_content_json['summary'])->toBe('Une methode qui guerit sans aucun risque.');
});

it('serves the complete PDF guide from every authenticated module screen', function () {
    $this->get(route('offer-journeys.index'))
        ->assertOk()
        ->assertSee(route('offer-journeys.guide'), false);

    $this->get(route('offer-journeys.guide'))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});
