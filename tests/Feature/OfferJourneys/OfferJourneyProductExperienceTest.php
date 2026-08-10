<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyConversion;
use App\Domain\OfferJourneys\Models\OfferJourneyEntry;
use App\Domain\OfferJourneys\Models\OfferJourneyEvent;
use App\Domain\OfferJourneys\Models\OfferJourneyPipelineStage;
use App\Domain\OfferJourneys\Models\OfferJourneySegment;
use App\Domain\OfferJourneys\Models\OfferJourneyTag;
use App\Domain\OfferJourneys\Services\OfferJourneyAutomationBuilder;
use App\Domain\OfferJourneys\Services\OfferJourneyPipeline;
use App\Models\Product;
use App\Models\User;

function productExperienceTherapist(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'slug' => fake()->unique()->slug(2),
        'company_name' => 'Cabinet Horizon',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
    ], $overrides));
}

function productExperienceJourney(User $user, array $attributes = []): OfferJourney
{
    $journey = OfferJourney::query()->create(array_merge([
        'user_id' => $user->id,
        'name' => 'Séance découverte',
        'slug' => 'seance-decouverte',
        'objective' => 'appointment',
        'status' => 'draft',
    ], $attributes));
    $journey->pages()->create([
        'name' => 'Présentation',
        'slug' => 'offre',
        'type' => 'sales',
        'position' => 0,
        'draft_content_json' => ['title' => 'Un premier échange', 'summary' => 'Faisons le point.', 'cta_label' => 'Continuer'],
        'validation_state' => 'ready',
    ]);

    return $journey;
}

beforeEach(function () {
    foreach (['enabled', 'public_pages_enabled', 'tracking_enabled', 'automation_enabled', 'email_enabled', 'message_tools_enabled', 'template_library_enabled', 'commercial_tools_enabled'] as $flag) {
        config()->set('offer_journeys.'.$flag, true);
    }
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);
});

it('presents one central workspace derived from the existing journey', function () {
    $therapist = productExperienceTherapist();
    $journey = productExperienceJourney($therapist);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.show', $journey))
        ->assertOk()
        ->assertSee('Vue d’ensemble')
        ->assertSee('Formulaire')
        ->assertSee('Messages')
        ->assertSee('Préparation du parcours')
        ->assertSee('Ce que vivra votre visiteur');
});

it('shows useful source details and creates truthful confirmation copy', function () {
    $therapist = productExperienceTherapist();
    Product::query()->create([
        'user_id' => $therapist->id,
        'name' => 'Consultation individuelle',
        'duration' => 75,
        'price' => 60,
        'tax_rate' => 10,
    ]);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.create'))
        ->assertOk()
        ->assertSee('Consultation individuelle - 75 min - 66,00 € TTC');

    $this->actingAs($therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide pratique',
        'objective' => 'lead_magnet',
        'public_title' => 'Votre guide pratique',
        'summary' => 'Une ressource à consulter à votre rythme.',
        'cta_label' => 'Accéder au guide',
        'resource_url' => 'https://example.test/guide.pdf',
    ])->assertRedirect();

    $confirmation = OfferJourney::query()->where('name', 'Guide pratique')->firstOrFail()
        ->pages()->where('type', 'thank_you')->firstOrFail();
    expect($confirmation->draft_content_json['summary'])
        ->toBe('Votre demande est confirmée. Utilisez le bouton ci-dessous pour accéder à la ressource.')
        ->not->toContain('envoyée par email');
});

it('publishes social metadata and never labels a confirmation as unavailable', function () {
    $therapist = productExperienceTherapist();
    $journey = OfferJourney::query()->create([
        'user_id' => $therapist->id,
        'name' => 'Confirmation test',
        'slug' => 'confirmation-test',
        'objective' => 'contact_request',
        'status' => 'draft',
    ]);
    $journey->pages()->create([
        'name' => 'Confirmation',
        'slug' => 'merci',
        'type' => 'thank_you',
        'position' => 0,
        'draft_content_json' => [
            'title' => 'Merci pour votre demande',
            'summary' => 'Votre demande a bien été prise en compte.',
            'cta_label' => 'Voir le profil',
            'social_title' => 'Échanger avec le Cabinet Horizon',
            'social_description' => 'Une première prise de contact simple.',
            'social_image' => '/partage.jpg',
        ],
        'validation_state' => 'ready',
    ]);

    $this->actingAs($therapist)->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    $journey->refresh();

    $this->get(route('offer-journeys.public.show', ['therapist' => $therapist, 'journeySlug' => $journey->slug]))
        ->assertOk()
        ->assertSee('property="og:image" content="'.url('/partage.jpg').'"', false)
        ->assertSee('Voir le profil du praticien')
        ->assertDontSee("Cette offre n'est pas disponible actuellement.");
});

it('separates submissions contacts and conversions in analytics', function () {
    $therapist = productExperienceTherapist();
    $journey = productExperienceJourney($therapist);
    foreach ([['session-a', 'page_viewed'], ['session-a', 'page_viewed'], ['session-b', 'page_viewed'], ['session-a', 'lead_captured'], ['session-a', 'lead_captured']] as [$session, $name]) {
        OfferJourneyEvent::query()->create(['offer_journey_id' => $journey->id, 'session_id' => $session, 'event_name' => $name, 'occurred_at' => now()]);
    }
    $contact = OfferJourneyContact::query()->create(['user_id' => $therapist->id, 'email' => 'camille@example.test', 'email_normalized' => 'camille@example.test', 'status' => 'new']);
    OfferJourneyEntry::query()->create(['offer_journey_id' => $journey->id, 'offer_journey_contact_id' => $contact->id, 'entered_at' => now()]);
    OfferJourneyConversion::query()->create([
        'offer_journey_id' => $journey->id,
        'offer_journey_contact_id' => $contact->id,
        'conversion_type' => 'appointment',
        'status' => 'confirmed',
        'idempotency_key' => 'product-experience-conversion',
        'occurred_at' => now(),
        'confirmed_at' => now(),
    ]);

    $response = $this->actingAs($therapist)->get(route('offer-journeys.analytics', $journey));
    $response->assertOk()
        ->assertSee('Formulaires reçus')
        ->assertSee('Contacts uniques')
        ->assertSee('Formulaire vers contact unique')
        ->assertSee('Contact vers action confirmée');
    $metrics = $response->viewData('metrics');
    expect($metrics['visitors'])->toBe(2)
        ->and($metrics['views'])->toBe(3)
        ->and($metrics['form_submissions'])->toBe(2)
        ->and($metrics['unique_contacts'])->toBe(1)
        ->and($metrics['form_to_contact_rate'])->toBe(50.0)
        ->and($metrics['contact_to_conversion_rate'])->toBe(100.0);
});

it('shows the contact origin and useful follow-up state instead of a journey count', function () {
    $therapist = productExperienceTherapist();
    $journey = productExperienceJourney($therapist);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $therapist->id,
        'email' => 'origine@example.test',
        'email_normalized' => 'origine@example.test',
        'status' => 'new',
        'last_activity_at' => now(),
    ]);
    OfferJourneyEntry::query()->create([
        'offer_journey_id' => $journey->id,
        'offer_journey_contact_id' => $contact->id,
        'entered_at' => now(),
    ]);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.contacts.index'))
        ->assertOk()
        ->assertSee('Origine')
        ->assertSee('Séance découverte')
        ->assertSee('Pas de suivi marketing')
        ->assertSee('Aucune action planifiée');
});

it('stores rich automation email blocks while accepting legacy text updates', function () {
    $therapist = productExperienceTherapist();
    $journey = productExperienceJourney($therapist);
    $automation = app(OfferJourneyAutomationBuilder::class)->createV1Draft($journey, $therapist);
    $node = $automation->versions()->with('nodes')->firstOrFail()->nodes->firstWhere('type', 'email');

    $this->actingAs($therapist)->put(route('offer-journeys.automation.update', [$journey, $automation]), [
        'messages' => [$node->node_key => [
            'subject' => 'Votre ressource',
            'body' => 'Bonjour {{prenom}}, votre ressource est prête.',
            'delay_days' => 0,
            'is_enabled' => 1,
            'editor_version' => 'blocks-v1',
            'preheader' => 'Le lien demandé',
            'heading' => 'Votre ressource est prête',
            'button_label' => 'Ouvrir la ressource',
            'button_url' => '{{lien_ressource}}',
            'signature' => '{{nom_praticien}}',
            'primary_color' => '#647a0b',
        ]],
    ])->assertSessionHasNoErrors();

    $config = $node->fresh()->config_json;
    expect($config['body'])->toContain('{{prenom}}')
        ->and($config['email_content']['blocks'])->toHaveCount(4)
        ->and(collect($config['email_content']['blocks'])->pluck('type')->all())->toBe(['heading', 'paragraph', 'button', 'signature']);

    $this->actingAs($therapist)->put(route('offer-journeys.automation.update', [$journey, $automation]), [
        'messages' => [$node->node_key => ['subject' => 'Objet historique', 'body' => 'Ancien format texte', 'delay_days' => 1]],
    ])->assertSessionHasNoErrors();
    expect($node->fresh()->config_json['body'])->toBe('Ancien format texte')
        ->and($node->fresh()->config_json['email_content'])->not->toBeEmpty();
});

it('isolates tag lifecycle and reports true pipeline totals', function () {
    $therapist = productExperienceTherapist();
    $other = productExperienceTherapist();
    app(OfferJourneyPipeline::class)->ensureDefaults($therapist);
    $stage = OfferJourneyPipelineStage::query()->where('user_id', $therapist->id)->orderBy('position')->firstOrFail();
    foreach (range(1, 55) as $index) {
        OfferJourneyContact::query()->create([
            'user_id' => $therapist->id,
            'pipeline_stage_id' => $stage->id,
            'email' => "contact{$index}@example.test",
            'email_normalized' => "contact{$index}@example.test",
            'status' => 'new',
            'last_activity_at' => now()->subMinutes($index),
        ]);
    }
    $tag = OfferJourneyTag::query()->create(['user_id' => $therapist->id, 'name' => 'Atelier été', 'slug' => 'atelier-ete', 'color' => 'olive']);
    $otherTag = OfferJourneyTag::query()->create(['user_id' => $other->id, 'name' => 'Privé', 'slug' => 'prive', 'color' => 'olive']);

    $this->actingAs($therapist)->put(route('offer-journeys.contacts.tags.update', $tag), ['name' => 'Atelier automne'])->assertSessionHasNoErrors();
    expect($tag->fresh()->name)->toBe('Atelier automne');
    $this->actingAs($therapist)->put(route('offer-journeys.contacts.tags.update', $otherTag), ['name' => 'Interdit'])->assertNotFound();

    $this->actingAs($therapist)->get(route('offer-journeys.contacts.pipeline'))
        ->assertOk()
        ->assertSee('50 affichés sur 55');
});

it('lets a practitioner associate an owned offer later and rejects another practitioners offer', function () {
    $therapist = productExperienceTherapist();
    $other = productExperienceTherapist();
    $journey = productExperienceJourney($therapist);
    $owned = Product::query()->create([
        'user_id' => $therapist->id,
        'name' => 'Séance découverte',
        'duration' => 45,
        'price' => 55,
        'tax_rate' => 0,
    ]);
    $foreign = Product::query()->create([
        'user_id' => $other->id,
        'name' => 'Prestation privée',
        'duration' => 60,
        'price' => 90,
        'tax_rate' => 0,
    ]);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.edit', $journey))
        ->assertOk()
        ->assertSee('Prestation proposée après la page')
        ->assertSee('Séance découverte - 45 min - 55,00 € TTC')
        ->assertDontSee('Prestation privée');

    $this->actingAs($therapist)->put(route('offer-journeys.update', $journey), [
        'name' => $journey->name,
        'slug' => $journey->slug,
        'source_ref' => 'product:'.$owned->id,
    ])->assertSessionHasNoErrors();

    expect($journey->fresh()->source_type)->toBe('product')
        ->and($journey->fresh()->source_id)->toBe($owned->id);

    $this->actingAs($therapist)->put(route('offer-journeys.update', $journey), [
        'name' => $journey->name,
        'slug' => $journey->slug,
        'source_ref' => 'product:'.$foreign->id,
    ])->assertStatus(422);
    expect($journey->fresh()->source_id)->toBe($owned->id);
});

it('keeps the published destination immutable until the practitioner republishes', function () {
    config()->set('offer_journeys.public_pages_enabled', true);
    config()->set('offer_journeys.publication_assistance_enabled', false);
    $therapist = productExperienceTherapist();
    $firstProduct = Product::query()->create([
        'user_id' => $therapist->id, 'name' => 'Première séance', 'duration' => 60, 'price' => 60, 'tax_rate' => 0,
    ]);
    $secondProduct = Product::query()->create([
        'user_id' => $therapist->id, 'name' => 'Nouvelle séance', 'duration' => 90, 'price' => 80, 'tax_rate' => 0,
    ]);
    $journey = productExperienceJourney($therapist, [
        'source_type' => 'product',
        'source_id' => $firstProduct->id,
    ]);

    $this->actingAs($therapist)->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    $journey->refresh();
    $publishedVersion = $journey->publishedVersion;
    $page = $publishedVersion->pages->first();

    $this->actingAs($therapist)->put(route('offer-journeys.update', $journey), [
        'name' => $journey->name,
        'slug' => $journey->slug,
        'source_ref' => 'product:'.$secondProduct->id,
    ])->assertSessionHasNoErrors();

    expect($journey->fresh()->source_id)->toBe($secondProduct->id)
        ->and($publishedVersion->fresh()->snapshot_json['source_id'])->toBe($firstProduct->id);

    auth()->logout();
    $this->get(route('offer-journeys.public.continue', [
        'therapist' => $therapist,
        'journeySlug' => $journey->slug,
        'pageSlug' => $page->slug,
    ]))->assertRedirect(route('appointments.createPatient', ['therapist' => $therapist->id]).'?product_id='.$firstProduct->id);
});

it('prioritizes a blocking draft correction while reassuring that the public version stays online', function () {
    config()->set('offer_journeys.publication_assistance_enabled', false);
    $therapist = productExperienceTherapist();
    $product = Product::query()->create([
        'user_id' => $therapist->id, 'name' => 'Bilan initial', 'duration' => 60, 'price' => 65, 'tax_rate' => 0,
    ]);
    $journey = productExperienceJourney($therapist, ['source_type' => 'product', 'source_id' => $product->id]);
    $this->actingAs($therapist)->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    config()->set('offer_journeys.publication_assistance_enabled', true);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.show', $journey->fresh()))
        ->assertOk()
        ->assertSee('Brouillon à terminer')
        ->assertSee('Indiquez ce qui se passe ensuite')
        ->assertSee('La version actuellement en ligne reste inchangée')
        ->assertSee('Configurer la suite');
});

it('makes contact follow-up actionable and translates internal consent purposes', function () {
    $therapist = productExperienceTherapist();
    app(OfferJourneyPipeline::class)->ensureDefaults($therapist);
    $journey = productExperienceJourney($therapist);
    $contact = OfferJourneyContact::query()->create([
        'user_id' => $therapist->id,
        'first_name' => 'Élodie',
        'email' => 'elodie@example.test',
        'email_normalized' => 'elodie@example.test',
        'phone' => '06 12 34 56 78',
        'status' => 'new',
        'last_activity_at' => now(),
    ]);
    OfferJourneyEntry::query()->create([
        'offer_journey_id' => $journey->id,
        'offer_journey_contact_id' => $contact->id,
        'entered_at' => now(),
    ]);
    $contact->consents()->create([
        'offer_journey_id' => $journey->id,
        'purpose' => 'request_processing',
        'status' => 'granted',
        'text_version' => 'v1',
        'text_snapshot' => 'Utilisation pour répondre à la demande.',
        'granted_at' => now(),
    ]);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.contacts.show', $contact))
        ->assertOk()
        ->assertSee('href="mailto:elodie@example.test"', false)
        ->assertSee('href="tel:0612345678"', false)
        ->assertSee('Traitement de la demande')
        ->assertDontSee('request_processing');
});

it('offers a natural next step after delivering a lead magnet', function () {
    config()->set('offer_journeys.public_pages_enabled', true);
    $therapist = productExperienceTherapist();
    $this->actingAs($therapist)->post(route('offer-journeys.store'), [
        'name' => 'Guide respiration',
        'objective' => 'lead_magnet',
        'public_title' => 'Respirer plus sereinement',
        'summary' => 'Un guide simple à consulter.',
        'cta_label' => 'Recevoir le guide',
        'resource_url' => 'https://example.test/guide.pdf',
    ])->assertRedirect();
    $journey = OfferJourney::query()->where('name', 'Guide respiration')->firstOrFail();
    $this->actingAs($therapist)->post(route('offer-journeys.publish', $journey))->assertSessionHasNoErrors();
    $this->actingAs($therapist)
        ->get(route('offer-journeys.show', $journey->fresh()))
        ->assertOk()
        ->assertDontSee('Republier les modifications');
    auth()->logout();

    $this->get(route('offer-journeys.public.show', [
        'therapist' => $therapist,
        'journeySlug' => $journey->slug,
        'pageSlug' => 'merci',
    ]))
        ->assertOk()
        ->assertSee('Accéder à la ressource')
        ->assertSee('Découvrir mes accompagnements')
        ->assertSee(route('therapist.show', $therapist->slug), false);
});

it('presents message activation before advanced automation controls', function () {
    $therapist = productExperienceTherapist();
    $journey = productExperienceJourney($therapist);
    app(OfferJourneyAutomationBuilder::class)->createV1Draft($journey, $therapist);

    $this->actingAs($therapist)
        ->get(route('offer-journeys.automation', $journey))
        ->assertOk()
        ->assertSee('État des messages')
        ->assertSee('Activer ce brouillon')
        ->assertSee('Inclure dans le suivi')
        ->assertSee('Réglages avancés du suivi')
        ->assertSee('Tester un scénario sans envoyer d’email')
        ->assertDontSee('>Activé<', false);
});
