<?php

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Domain\OfferJourneys\Services\OfferJourneyCampaignSender;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailContent;
use App\Domain\OfferJourneys\Services\OfferJourneyEmailRenderer;
use App\Mail\OfferJourneyMessageMail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    foreach (['enabled', 'email_enabled', 'campaigns_enabled', 'email_editor_enabled'] as $flag) {
        config()->set('offer_journeys.'.$flag, true);
    }
    config()->set('offer_journeys.pause_all_marketing_emails', false);
    config()->set('offer_journeys.allow_all_eligible_users', true);
    config()->set('offer_journeys.beta_user_ids', []);
    Storage::fake('public');
    Mail::fake();

    $this->therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'email-editor-owner',
        'company_name' => 'Cabinet Lumière',
        'company_email' => 'cabinet@example.test',
        'company_phone' => '01 02 03 04 05',
        'license_product' => 'new_premium_mensuelle',
        'license_status' => 'active',
        'created_at' => now()->subYear(),
    ]);
});

function emailEditorContent(array $blocks): array
{
    return ['blocks' => collect($blocks)->values()->map(fn ($block, $index) => [
        'id' => 'test-block-'.$index,
        'type' => $block[0],
        'data' => $block[1],
    ])->all()];
}

function emailEditorStyle(): array
{
    return app(OfferJourneyEmailContent::class)->defaultStyle();
}

function emailEditorCampaign(User $user, array $attributes = []): OfferJourneyMessageCampaign
{
    return OfferJourneyMessageCampaign::query()->create(array_merge([
        'user_id' => $user->id,
        'created_by_user_id' => $user->id,
        'name' => 'Campagne de test',
        'subject' => 'Bonjour {{prenom}}',
        'preheader' => 'Une information utile',
        'body' => 'Ancien texte',
        'status' => 'draft',
        'audience_type' => 'journeys',
    ], $attributes));
}

it('keeps the professional editor behind its dedicated feature flag', function () {
    config()->set('offer_journeys.email_editor_enabled', false);

    $this->actingAs($this->therapist)
        ->post(route('offer-journeys.email-editor.start'))
        ->assertNotFound();

    expect(OfferJourneyMessageCampaign::query()->count())->toBe(0);
});

it('starts and autosaves a bounded block campaign without changing the legacy engine', function () {
    $this->actingAs($this->therapist)
        ->post(route('offer-journeys.email-editor.start'))
        ->assertRedirect();
    $campaign = OfferJourneyMessageCampaign::query()->firstOrFail();

    expect($campaign->editor_version)->toBe('blocks-v1')
        ->and($campaign->content_json)->not->toBeNull()
        ->and($campaign->status)->toBe('draft');

    $content = emailEditorContent([
        ['heading', ['text' => 'Une nouveauté', 'level' => 'h1', 'align' => 'left']],
        ['paragraph', ['text' => 'Bonjour {{prenom}}, voici une information.', 'align' => 'left']],
        ['button', ['label' => 'Découvrir', 'url' => 'https://olithea.fr/offre', 'variant' => 'filled', 'align' => 'center']],
        ['callout', ['title' => 'À retenir', 'text' => 'Une information sobre.', 'tone' => 'olive']],
        ['divider', []],
        ['spacer', ['size' => 'small']],
        ['details', ['title' => 'Informations pratiques', 'text' => 'Le 20 juillet à 14 h']],
        ['signature', ['text' => '{{nom_praticien}}', 'show_contact' => true]],
    ]);

    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), [
        'name' => 'Actualités de juillet',
        'subject' => 'Bonjour {{prenom}}',
        'preheader' => 'Les prochaines dates',
        'content' => $content,
        'style' => emailEditorStyle(),
        'audience_type' => 'journeys',
        'journey_ids' => [],
    ])->assertOk()->assertJsonPath('quality.errors', []);

    expect($campaign->fresh()->content_json['blocks'])->toHaveCount(8)
        ->and($campaign->fresh()->body)->toContain('Une nouveauté')
        ->and($campaign->fresh()->body)->toContain('{{prenom}}');

    $this->actingAs($this->therapist)->get(route('offer-journeys.email-editor.edit', $campaign))
        ->assertOk()->assertSee('Aperçu réel')->assertSee('Ajouter une image');
});

it('rejects html unknown variables and unsafe button urls', function () {
    $campaign = emailEditorCampaign($this->therapist);
    $base = [
        'name' => 'Sécurité', 'subject' => 'Test', 'preheader' => 'Test',
        'style' => emailEditorStyle(), 'audience_type' => 'journeys', 'journey_ids' => [],
    ];

    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), $base + [
        'content' => emailEditorContent([['paragraph', ['text' => '<script>alert(1)</script>', 'align' => 'left']]]),
    ])->assertUnprocessable();
    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), $base + [
        'content' => emailEditorContent([['paragraph', ['text' => 'Bonjour {{mot_de_passe}}', 'align' => 'left']]]),
    ])->assertUnprocessable();
    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), $base + [
        'content' => emailEditorContent([['button', ['label' => 'Cliquer', 'url' => 'javascript:alert(1)', 'variant' => 'filled', 'align' => 'left']]]),
    ])->assertUnprocessable();
    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), array_merge($base, [
        'subject' => 'Bonjour {{variable_inconnue}}',
        'content' => emailEditorContent([['paragraph', ['text' => 'Message sûr', 'align' => 'left']]]),
    ]))->assertUnprocessable();
});

it('stores optimized owned images and only deletes them when unused', function () {
    $campaign = emailEditorCampaign($this->therapist, [
        'content_json' => emailEditorContent([['paragraph', ['text' => 'Avant image', 'align' => 'left']]]),
        'style_json' => emailEditorStyle(), 'editor_version' => 'blocks-v1',
    ]);
    $this->actingAs($this->therapist)->post(route('offer-journeys.email-editor.assets.store', $campaign), [
        'image' => UploadedFile::fake()->createWithContent('danger.svg', '<svg><script>alert(1)</script></svg>'),
    ], ['Accept' => 'application/json'])->assertUnprocessable();
    $response = $this->actingAs($this->therapist)->post(route('offer-journeys.email-editor.assets.store', $campaign), [
        'image' => UploadedFile::fake()->image('atelier.png', 1400, 900),
    ])->assertCreated();
    $assetId = $response->json('id');
    $asset = $campaign->emailAssets()->findOrFail($assetId);
    Storage::disk('public')->assertExists($asset->path);
    expect($asset->mime_type)->toBe('image/webp')->and($asset->width)->toBeLessThanOrEqual(1200);

    $content = emailEditorContent([['image', ['asset_id' => $assetId, 'alt' => 'Salle accueillant l’atelier', 'width' => 'full', 'align' => 'center']]]);
    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), [
        'name' => 'Image', 'subject' => 'Image', 'preheader' => 'Aperçu', 'content' => $content,
        'style' => emailEditorStyle(), 'audience_type' => 'journeys', 'journey_ids' => [],
    ])->assertOk();
    $this->actingAs($this->therapist)->deleteJson(route('offer-journeys.email-editor.assets.destroy', [$campaign, $asset]))->assertUnprocessable();

    $campaign->update(['content_json' => emailEditorContent([['paragraph', ['text' => 'Image retirée', 'align' => 'left']]])]);
    $this->actingAs($this->therapist)->deleteJson(route('offer-journeys.email-editor.assets.destroy', [$campaign, $asset]))->assertOk();
    Storage::disk('public')->assertMissing($asset->path);
});

it('isolates campaign images and editor routes between practitioners', function () {
    $campaign = emailEditorCampaign($this->therapist);
    $other = User::factory()->create([
        'is_therapist' => true, 'slug' => 'other-editor',
        'license_product' => 'new_premium_mensuelle', 'license_status' => 'active',
    ]);

    $this->actingAs($other)->get(route('offer-journeys.email-editor.edit', $campaign))->assertNotFound();
    $this->actingAs($other)->post(route('offer-journeys.email-editor.assets.store', $campaign), [
        'image' => UploadedFile::fake()->image('foreign.png'),
    ])->assertNotFound();
});

it('renders safe html and text with variables branding and mandatory unsubscribe', function () {
    $campaign = emailEditorCampaign($this->therapist);
    $normalized = app(OfferJourneyEmailContent::class)->validate(emailEditorContent([
        ['heading', ['text' => 'Bonjour {{prenom}}', 'level' => 'h1', 'align' => 'center']],
        ['button', ['label' => 'Voir {{offre}}', 'url' => 'https://olithea.fr/offre', 'variant' => 'filled', 'align' => 'center']],
        ['signature', ['text' => '{{nom_praticien}}', 'show_contact' => true]],
    ]), emailEditorStyle(), $this->therapist, $campaign);
    $campaign->update(['content_json' => $normalized['content'], 'style_json' => $normalized['style'], 'editor_version' => 'blocks-v1']);

    $rendered = app(OfferJourneyEmailRenderer::class)->render($campaign->fresh(), [
        'prenom' => 'Nadine', 'offre' => 'Atelier respiration',
        'nom_praticien' => 'Cabinet Lumière', 'lien_offre' => 'https://olithea.fr/offre',
    ], 'https://olithea.fr/desinscription');

    expect($rendered['html'])->toContain('Bonjour Nadine')
        ->and($rendered['html'])->toContain('https://olithea.fr/desinscription')
        ->and($rendered['html'])->not->toContain('{{prenom}}', '<script')
        ->and($rendered['text'])->toContain('Voir Atelier respiration : https://olithea.fr/offre')
        ->and($rendered['text'])->toContain('cabinet@example.test');

    $mail = new OfferJourneyMessageMail(
        $this->therapist, 'Bonjour Nadine', '', 'https://olithea.fr/desinscription', 'marketing', null,
        $campaign->fresh(), ['prenom' => 'Nadine', 'offre' => 'Atelier respiration', 'nom_praticien' => 'Cabinet Lumière', 'lien_offre' => 'https://olithea.fr/offre']
    );
    expect($mail->render())->toContain('Bonjour Nadine', 'Se désinscrire de ces suivis');

    $fallback = app(OfferJourneyEmailRenderer::class)->render($campaign->fresh(), [
        'offre' => 'Atelier respiration', 'nom_praticien' => 'Cabinet Lumière', 'lien_offre' => 'https://olithea.fr/offre',
    ], 'https://olithea.fr/desinscription');
    expect($fallback['html'])->toContain('Bonjour à vous');
});

it('preserves legacy rendering and converts old text only on explicit request', function () {
    $campaign = emailEditorCampaign($this->therapist, ['body' => "Bonjour Nadine,\nMessage historique.", 'content_json' => null]);
    $legacy = new OfferJourneyMessageMail($this->therapist, 'Ancien email', $campaign->body, 'https://olithea.fr/stop', 'marketing');
    expect($legacy->render())->toContain('Message historique.')->not->toContain('renderedBlocksHtml');

    $this->actingAs($this->therapist)->post(route('offer-journeys.email-editor.convert', $campaign))->assertRedirect();
    expect($campaign->fresh()->body)->toBe("Bonjour Nadine,\nMessage historique.")
        ->and(data_get($campaign->fresh()->content_json, 'blocks.0.data.text'))->toBe($campaign->body);
});

it('uses the same server renderer for preview test email and final campaign send', function () {
    $journey = OfferJourney::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Séance découverte', 'slug' => 'seance-decouverte',
        'objective' => 'appointment', 'status' => 'published',
    ]);
    $version = $journey->versions()->create(['version_number' => 1, 'schema_version' => 1, 'snapshot_json' => [], 'published_at' => now()]);
    $journey->update(['published_version_id' => $version->id]);
    $campaign = emailEditorCampaign($this->therapist, [
        'content_json' => emailEditorContent([
            ['paragraph', ['text' => 'Bonjour {{prenom}}', 'align' => 'left']],
            ['button', ['label' => 'Réserver', 'url' => '{{lien_offre}}', 'variant' => 'filled', 'align' => 'left']],
        ]),
        'style_json' => emailEditorStyle(), 'editor_version' => 'blocks-v1',
    ]);
    $campaign->journeys()->attach($journey);

    $previewPayload = ['subject' => $campaign->subject, 'preheader' => $campaign->preheader, 'content' => $campaign->content_json, 'style' => $campaign->style_json];
    $preview = $this->actingAs($this->therapist)->postJson(route('offer-journeys.email-editor.preview', $campaign), $previewPayload)->assertOk()->getContent();
    $expectedPreview = app(OfferJourneyEmailRenderer::class)->render($campaign->fresh(), [
        'prenom' => 'Camille', 'offre' => $journey->name,
        'nom_praticien' => $this->therapist->company_name,
        'lien_offre' => route('offer-journeys.public.show', ['therapist' => $this->therapist, 'journeySlug' => $journey->slug]),
    ], '#desinscription-apercu', 'marketing', $campaign->content_json, $campaign->style_json, $campaign->preheader)['html'];
    expect($preview)->toBe($expectedPreview)->and($preview)->toContain('Bonjour Camille', 'Réserver', 'seance-decouverte');

    $this->actingAs($this->therapist)->post(route('offer-journeys.message-campaigns.test', $campaign))->assertRedirect()->assertSessionHasNoErrors();
    Mail::assertSent(OfferJourneyMessageMail::class, fn ($mail) => $mail->campaign?->is($campaign) && $mail->hasTo($this->therapist->email));

    $contact = OfferJourneyContact::query()->create([
        'user_id' => $this->therapist->id, 'email' => 'nadine@example.test', 'email_normalized' => 'nadine@example.test',
        'first_name' => 'Nadine', 'status' => 'new', 'last_activity_at' => now(),
    ]);
    $contact->entries()->create(['offer_journey_id' => $journey->id, 'status' => 'active', 'entered_at' => now(), 'last_activity_at' => now()]);
    $contact->consents()->create([
        'offer_journey_id' => $journey->id, 'purpose' => 'marketing_follow_up', 'status' => 'granted',
        'text_version' => 'test', 'text_snapshot' => 'Consentement test', 'source' => 'test', 'granted_at' => now(),
    ]);
    $campaign->update(['status' => 'scheduled', 'scheduled_at' => now()->subMinute()]);
    app(OfferJourneyCampaignSender::class)->send($campaign->id);

    Mail::assertSent(OfferJourneyMessageMail::class, fn ($mail) => $mail->hasTo('nadine@example.test') && $mail->renderVariables['prenom'] === 'Nadine');
    expect($campaign->fresh()->sent_count)->toBe(1);
});

it('locks scheduled rich campaigns until they are explicitly returned to draft', function () {
    $campaign = emailEditorCampaign($this->therapist, [
        'status' => 'scheduled', 'scheduled_at' => now()->addHour(),
        'content_json' => emailEditorContent([['paragraph', ['text' => 'Message', 'align' => 'left']]]),
        'style_json' => emailEditorStyle(), 'editor_version' => 'blocks-v1',
    ]);

    $this->actingAs($this->therapist)->putJson(route('offer-journeys.email-editor.autosave', $campaign), [
        'name' => 'Interdit', 'subject' => 'Interdit', 'preheader' => '', 'content' => $campaign->content_json,
        'style' => $campaign->style_json, 'audience_type' => 'journeys', 'journey_ids' => [],
    ])->assertUnprocessable();

    $this->actingAs($this->therapist)->post(route('offer-journeys.message-campaigns.return-to-draft', $campaign))->assertRedirect();
    expect($campaign->fresh()->status)->toBe('draft')->and($campaign->fresh()->scheduled_at)->toBeNull();

    $campaign->update(['status' => 'sent', 'sent_at' => now()]);
    $this->actingAs($this->therapist)->get(route('offer-journeys.email-editor.edit', $campaign))->assertUnprocessable();
});

it('programs a valid rich campaign and suspends it if the editor flag is later disabled', function () {
    $journey = OfferJourney::query()->create([
        'user_id' => $this->therapist->id, 'name' => 'Atelier', 'slug' => 'atelier-programme',
        'objective' => 'event', 'status' => 'published',
    ]);
    $version = $journey->versions()->create(['version_number' => 1, 'schema_version' => 1, 'snapshot_json' => [], 'published_at' => now()]);
    $journey->update(['published_version_id' => $version->id]);
    $campaign = emailEditorCampaign($this->therapist, [
        'content_json' => emailEditorContent([['paragraph', ['text' => 'Un message utile', 'align' => 'left']]]),
        'style_json' => emailEditorStyle(), 'editor_version' => 'blocks-v1',
    ]);
    $campaign->journeys()->attach($journey);

    $this->actingAs($this->therapist)->post(route('offer-journeys.message-campaigns.schedule', $campaign), [
        'scheduled_at' => now()->addHour()->format('Y-m-d H:i:s'),
    ])->assertRedirect()->assertSessionHasNoErrors();
    expect($campaign->fresh()->status)->toBe('scheduled');

    $campaign->update(['scheduled_at' => now()->subMinute()]);
    config()->set('offer_journeys.email_editor_enabled', false);
    app(OfferJourneyCampaignSender::class)->send($campaign->id);

    Mail::assertNothingSent();
    expect($campaign->fresh()->status)->toBe('scheduled')
        ->and(data_get($campaign->fresh()->summary_json, 'blocking_reason'))->toBe('email_editor_disabled');
});
