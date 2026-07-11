<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Services\OfferJourneyTransitionEditor;
use App\Models\User;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    config()->set('offer_journeys.enabled', true);
    config()->set('offer_journeys.public_pages_enabled', true);
    config()->set('offer_journeys.tracking_enabled', false);
    config()->set('offer_journeys.automation_enabled', false);
    config()->set('offer_journeys.email_enabled', false);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.allow_all_eligible_users', true);
    $this->therapist = User::factory()->create([
        'is_therapist' => true, 'slug' => 'cabinet-multistep-test',
        'license_product' => 'new_premium_mensuelle', 'license_status' => 'active',
    ]);
    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Demande découverte', 'objective' => 'contact_request',
        'public_title' => 'Parlons de votre besoin', 'summary' => 'Une première prise de contact.',
        'cta_label' => 'Envoyer ma demande',
    ]);
    $this->journey = OfferJourney::query()->firstOrFail();
});

it('publishes conditional forward-only paths with a fallback', function () {
    $main = $this->journey->pages()->where('type', 'qualification')->firstOrFail();
    $thanks = $this->journey->pages()->where('type', 'thank_you')->firstOrFail();
    $content = $this->journey->pages()->create([
        'name' => 'Conseil complémentaire', 'slug' => 'conseil', 'type' => 'content', 'position' => 1,
        'draft_content_json' => ['title' => 'Un conseil pour commencer', 'cta_label' => 'Continuer'],
        'validation_state' => 'ready',
    ]);
    $thanks->update(['position' => 2]);

    app(OfferJourneyTransitionEditor::class)->update($this->journey, $main, [
        'transition_action' => 'next_page', 'transition_page_id' => $content->id,
        'transition_condition' => 'marketing_consent', 'fallback_page_id' => $thanks->id,
    ]);
    app(OfferJourneyTransitionEditor::class)->update($this->journey, $content, [
        'transition_action' => 'next_page', 'transition_page_id' => $thanks->id,
        'transition_condition' => 'always', 'fallback_page_id' => null,
    ]);

    expect(fn () => app(OfferJourneyTransitionEditor::class)->update($this->journey, $thanks, [
        'transition_action' => 'next_page', 'transition_page_id' => $main->id,
        'transition_condition' => 'always', 'fallback_page_id' => null,
    ]))->toThrow(ValidationException::class);

    $this->actingAs($this->therapist)->post(route('offer-journeys.publish', $this->journey))->assertSessionHasNoErrors();
    auth()->logout();
    $publishedMain = $this->journey->fresh()->publishedVersion->pages->firstWhere('type', 'qualification');
    $captureUrl = route('offer-journeys.public.capture', [
        'therapist' => $this->therapist, 'journeySlug' => $this->journey->slug, 'pageSlug' => $publishedMain->slug,
    ]);

    $this->post($captureUrl, ['first_name' => 'Nadine', 'email' => 'nadine@example.test', 'privacy_ack' => '1'])
        ->assertRedirect(route('offer-journeys.public.show', [
            'therapist' => $this->therapist, 'journeySlug' => $this->journey->slug, 'pageSlug' => $thanks->slug,
        ]));
    $this->post($captureUrl, ['first_name' => 'Amandine', 'email' => 'amandine@example.test', 'privacy_ack' => '1', 'marketing_consent' => '1'])
        ->assertRedirect(route('offer-journeys.public.show', [
            'therapist' => $this->therapist, 'journeySlug' => $this->journey->slug, 'pageSlug' => $content->slug,
        ]));
});

it('lets the practitioner reduce the form while keeping email and privacy mandatory', function () {
    $main = $this->journey->pages()->where('type', 'qualification')->firstOrFail();
    $thanks = $this->journey->pages()->where('type', 'thank_you')->firstOrFail();
    $this->actingAs($this->therapist)->put(route('offer-journeys.pages.update', [$this->journey, $main]), [
        'name' => $main->name, 'slug' => $main->slug, 'title' => 'Votre demande',
        'summary' => 'Expliquez-nous votre besoin.', 'cta_label' => 'Envoyer',
        'form_fields' => ['phone'], 'form_submit_label' => 'Envoyer',
        'form_privacy_text' => 'Ces informations servent à répondre à votre demande.',
        'marketing_consent_mode' => 'disabled',
        'transition_action' => 'next_page', 'transition_page_id' => $thanks->id,
        'transition_condition' => 'always',
    ])->assertSessionHasNoErrors();

    expect($main->fresh()->form->fields()->pluck('name')->all())->toBe(['phone', 'email']);
    $this->actingAs($this->therapist)->post(route('offer-journeys.publish', $this->journey))->assertSessionHasNoErrors();
    auth()->logout();
    $page = $this->journey->fresh()->publishedVersion->pages->firstWhere('type', 'qualification');
    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist, 'journeySlug' => $this->journey->slug, 'pageSlug' => $page->slug,
    ]), ['email' => 'sans-prenom@example.test', 'phone' => '0600000000', 'privacy_ack' => '1'])->assertRedirect();

    expect(OfferJourneyContact::query()->where('email_normalized', 'sans-prenom@example.test')->exists())->toBeTrue();
});

it('duplicates pages forms transitions and follow-up drafts without copying results', function () {
    $this->actingAs($this->therapist)->post(route('offer-journeys.duplicate', $this->journey))->assertRedirect();
    $copy = OfferJourney::query()->where('id', '!=', $this->journey->id)->firstOrFail();

    expect($copy->status)->toBe('draft')
        ->and($copy->pages()->count())->toBe($this->journey->pages()->count())
        ->and($copy->pages()->whereHas('form')->exists())->toBeTrue()
        ->and($copy->automations()->whereHas('versions.nodes')->exists())->toBeTrue()
        ->and($copy->versions()->count())->toBe(0)
        ->and($copy->events()->count())->toBe(0);
});

it('offers an expiring noindex preview while public pages can stay unpublished', function () {
    config()->set('offer_journeys.public_pages_enabled', false);
    $response = $this->actingAs($this->therapist)->get(route('offer-journeys.preview', $this->journey))->assertRedirect();
    auth()->logout();

    $this->get($response->headers->get('Location'))
        ->assertOk()
        ->assertSee('Aperçu du brouillon')
        ->assertSee('noindex,nofollow', false);
});

it('renders the page editor for a form step', function () {
    $page = $this->journey->pages()->where('type', 'qualification')->firstOrFail();

    $this->actingAs($this->therapist)
        ->get(route('offer-journeys.pages.edit', [$this->journey, $page]))
        ->assertOk()
        ->assertSee('Contenu de la page')
        ->assertSee('Formulaire')
        ->assertSee('Étape suivante');
});
