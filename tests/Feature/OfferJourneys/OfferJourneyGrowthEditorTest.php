<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyFormAnswer;
use App\Domain\OfferJourneys\Models\OfferJourneyReusableSection;
use App\Models\User;

beforeEach(function () {
    foreach (['enabled', 'public_pages_enabled', 'template_library_enabled', 'rich_editor_enabled', 'writing_assistant_enabled', 'custom_forms_enabled', 'publication_assistance_enabled'] as $flag) {
        config()->set('offer_journeys.'.$flag, true);
    }
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);
    config()->set('offer_journeys.tracking_enabled', false);
    config()->set('offer_journeys.pause_all_marketing_emails', true);

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'growth-editor-test',
        'company_name' => 'Cabinet Test Editeur',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ]);
});

it('creates an editable journey from a profession-oriented template', function () {
    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Mon guide',
        'objective' => 'lead_magnet',
        'template_key' => 'free_guide',
        'public_title' => 'Le guide pour préparer votre première séance',
        'summary' => '',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide.pdf',
    ])->assertRedirect();

    $journey = OfferJourney::query()->firstOrFail();
    $content = $journey->pages->first()->draft_content_json;

    expect($content['template_key'])->toBe('free_guide')
        ->and($content['audience'])->not->toBeEmpty()
        ->and($content['outcomes'])->toHaveCount(3)
        ->and($content['summary'])->toContain('ressource pratique');

    $this->get(route('offer-journeys.create'))
        ->assertOk()
        ->assertSeeInOrder(['Résultat', 'Offre', 'Exemple', 'Préparation', 'Vérification'])
        ->assertSee('Choisissez un exemple adapté')
        ->assertSee('Mini-programme email');
});

it('stores bounded visual blocks custom questions and explicit writing suggestions', function () {
    $this->actingAs($this->therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide configuration',
        'objective' => 'lead_magnet',
        'template_key' => 'free_guide',
        'public_title' => 'Préparez votre rendez-vous',
        'summary' => 'Des repères simples avant votre rendez-vous.',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide.pdf',
    ]);
    $journey = OfferJourney::query()->firstOrFail();
    $page = $journey->pages()->where('type', 'opt_in')->firstOrFail();
    $nextPage = $journey->pages()->where('position', '>', $page->position)->firstOrFail();

    $this->put(route('offer-journeys.pages.update', [$journey, $page]), [
        'name' => $page->name,
        'slug' => $page->slug,
        'title' => 'Préparez sereinement votre rendez-vous',
        'summary' => 'Un guide pratique avec des repères concrets.',
        'cta_label' => 'Recevoir le guide',
        'audience' => 'Pour les personnes qui préparent un premier échange.',
        'outcomes' => "Une checklist claire\nDes questions utiles",
        'steps' => "Indiquez votre email\nRecevez le guide",
        'practical_details' => 'Téléchargement immédiat.',
        'faq' => 'Quand vais-je le recevoir ? | Immédiatement après la demande.',
        'resource_url' => 'https://example.test/guide.pdf',
        'transition_action' => 'next_page',
        'transition_page_id' => $nextPage->id,
        'transition_condition' => 'always',
        'form_fields' => ['first_name', 'email', 'contact_preference'],
        'form_submit_label' => 'Recevoir le guide',
        'form_privacy_text' => 'Ces informations servent uniquement à répondre à votre demande.',
        'marketing_consent_mode' => 'optional',
        'enabled_blocks' => ['hero_image', 'outcomes', 'video', 'testimonials', 'faq'],
        'block_order' => ['hero_image', 'video', 'outcomes', 'testimonials', 'faq'],
        'hero_image_url' => 'https://example.test/hero.jpg',
        'hero_image_alt' => 'Carnet ouvert sur une table',
        'video_url' => 'https://youtu.be/abcdefghijk',
        'testimonials' => 'N. | Les explications étaient claires et utiles.',
        'theme_style' => 'forest',
        'custom_fields' => [[
            'label' => 'Quel format préférez-vous ?',
            'type' => 'single_choice',
            'options' => "En ligne\nAu cabinet",
            'purpose' => 'proposer le format adapté',
            'is_required' => '1',
            'condition_field' => 'contact_preference',
            'condition_value' => 'phone',
        ]],
    ])->assertRedirect()->assertSessionHasNoErrors();

    $page->refresh()->load('form.fields');
    expect(collect($page->draft_content_json['blocks'])->pluck('type')->all())
        ->toBe(['hero_image', 'video', 'outcomes', 'testimonials', 'faq'])
        ->and($page->theme_json['style'])->toBe('forest');

    $customField = $page->form->fields->first(fn ($field) => str_starts_with($field->name, 'custom_'));
    expect($customField)->not->toBeNull()
        ->and($customField->type)->toBe('select')
        ->and($customField->purpose)->toBe('proposer le format adapté');

    $this->postJson(route('offer-journeys.pages.writing-assistant', [$journey, $page]), [
        'title' => 'Bien-être optimal',
        'summary' => 'Une solution révolutionnaire qui guérit sans aucun risque.',
        'cta_label' => 'Cliquez ici',
    ])->assertOk()->assertJsonCount(3, 'title_suggestions')->assertJsonPath('readability.cta_characters', 11);

    $this->post(route('offer-journeys.pages.reusable-sections.store', [$journey, $page]), [
        'name' => 'Témoignage sobre',
        'type' => 'testimonials',
    ])->assertRedirect();
    expect(OfferJourneyReusableSection::query()->where('name', 'Témoignage sobre')->exists())->toBeTrue();

    $this->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    $publishedPage = $journey->fresh()->publishedVersion->pages->firstWhere('offer_journey_page_id', $page->id);
    $publishedCustom = collect($publishedPage->content_json['_form']['fields'])->first(fn ($field) => str_starts_with($field['name'], 'custom_'));

    $this->get(route('offer-journeys.public.show', [
        'therapist' => $this->therapist,
        'journeySlug' => $journey->slug,
        'pageSlug' => $publishedPage->slug,
    ]))->assertOk()->assertSee('Présentation en vidéo')->assertSee('Les explications étaient claires');

    $this->post(route('offer-journeys.public.capture', [
        'therapist' => $this->therapist,
        'journeySlug' => $journey->slug,
        'pageSlug' => $publishedPage->slug,
    ]), [
        'email' => 'custom-answer@example.test',
        'first_name' => 'Nadine',
        'contact_preference' => 'phone',
        $publishedCustom['name'] => 'En ligne',
        'privacy_ack' => '1',
    ])->assertRedirect()->assertSessionHasNoErrors();

    expect(OfferJourneyFormAnswer::query()->count())->toBe(1)
        ->and(OfferJourneyFormAnswer::query()->first()->value_json['value'])->toBe('En ligne');
});
