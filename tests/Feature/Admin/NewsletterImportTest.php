<?php

use App\Mail\NewsletterMail;
use App\Models\Audience;
use App\Models\ClientProfile;
use App\Models\Newsletter;
use App\Models\NewsletterContact;
use App\Models\NewsletterImport;
use App\Models\NewsletterMonthlyUsage;
use App\Models\NewsletterOptOut;
use App\Models\NewsletterRecipient;
use App\Models\User;
use App\Services\NewsletterAudienceService;
use App\Services\NewsletterImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

function newsletterImportActors(): array
{
    return [User::factory()->create(['is_admin' => true]), User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_premium_mensuelle',
    ])];
}

function newsletterImportPreview($test, User $admin, User $therapist, ?string $csv = null): NewsletterImport
{
    $csv ??= "EMAIL;NOM;PRENOM;OPT_IN;DOUBLE_OPT-IN\n subscriber@example.test ;Test;Alice;YES;Yes\n";
    $test->actingAs($admin)->post(route('admin.therapists.newsletter-imports.preview', $therapist), [
        'csv_file' => UploadedFile::fake()->createWithContent('contacts.csv', $csv),
        'audience_name' => 'Liste importée',
    ])->assertSessionHasNoErrors()->assertRedirect();

    return NewsletterImport::where('user_id', $therapist->id)->latest('id')->firstOrFail();
}

function newsletterImportCampaign(User $therapist, ?Audience $audience = null): Newsletter
{
    return Newsletter::create([
        'user_id' => $therapist->id, 'audience_id' => $audience?->id, 'title' => 'Newsletter test',
        'subject' => 'Actualités', 'from_name' => $therapist->name, 'from_email' => 'contact@olithea.fr',
        'content_json' => json_encode([['type' => 'text', 'html' => 'Bonjour {{ client.first_name }}']]),
        'status' => 'draft',
    ]);
}

test('the admin therapist page links to a scoped newsletter import form', function () {
    [$admin, $therapist] = newsletterImportActors();
    $this->actingAs($admin)->get(route('admin.therapists.show', $therapist))->assertOk()
        ->assertSee('Importer des contacts newsletter')
        ->assertSee(route('admin.therapists.newsletter-imports.index', $therapist), false);
    $this->get(route('admin.therapists.newsletter-imports.index', $therapist))->assertOk()
        ->assertSee($therapist->email)->assertSee('Analyser le fichier');
});

test('all newsletter import endpoints reject non admins', function ($action) {
    [$admin, $therapist] = newsletterImportActors();
    $import = newsletterImportPreview($this, $admin, $therapist);
    $this->actingAs($therapist);
    $url = route('admin.therapists.newsletter-imports.'.$action,
        in_array($action, ['show', 'commit']) ? [$therapist, $import] : $therapist);
    (in_array($action, ['index', 'show']) ? $this->get($url) : $this->post($url, ['confirm' => 1]))->assertForbidden();
    expect(NewsletterContact::count())->toBe(0)->and(Audience::count())->toBe(0);
})->with(['index', 'preview', 'show', 'commit']);

test('import routes require login and a therapist target', function () {
    [$admin, $therapist] = newsletterImportActors();
    $this->get(route('admin.therapists.newsletter-imports.index', $therapist))->assertRedirect(route('login'));
    $customer = User::factory()->create(['is_therapist' => false]);
    $this->actingAs($admin)->get(route('admin.therapists.newsletter-imports.index', $customer))->assertNotFound();
});

test('CSV parsing supports separators encodings optional names and quoted values', function ($delimiter, $encoding) {
    $csv = implode($delimiter, ['EMAIL', 'NOM', 'PRENOM', 'OPT_IN'])."\n".
        implode($delimiter, [' ALICE@EXAMPLE.TEST ', '"Nom, composé"', 'Élise', 'YES'])."\n";
    $csv = $encoding === 'cp1252' ? mb_convert_encoding($csv, 'Windows-1252', 'UTF-8') : "\xEF\xBB\xBF".$csv;
    $parsed = app(NewsletterImportService::class)->parse($csv);
    expect($parsed['rows'][0])->toMatchArray([
        'email' => 'alice@example.test', 'first_name' => 'Élise', 'last_name' => 'Nom, composé', 'consent' => true,
    ]);
})->with([[',', 'utf8'], [';', 'cp1252'], ["\t", 'utf8']]);

test('preview and commit handle invalid duplicate pending opted out and existing client addresses without sending', function () {
    Mail::fake();
    [$admin, $therapist] = newsletterImportActors();
    NewsletterOptOut::create(['user_id' => $therapist->id, 'email' => 'BLOCKED@example.test', 'unsubscribed_at' => now()]);
    ClientProfile::create(['user_id' => $therapist->id, 'first_name' => 'Client', 'last_name' => 'Test', 'email' => 'ready@example.test']);
    $csv = "EMAIL;PRENOM;OPT_IN\nready@example.test;Alice;YES\npending@example.test;;\nblocked@example.test;Bob;YES\nREADY@example.test;Alice;YES\ninvalid;;YES\nrefusal@example.test;;NO\n";
    $import = newsletterImportPreview($this, $admin, $therapist, $csv);
    expect($import->report)->toMatchArray(['total' => 6, 'ready' => 1, 'pending' => 2, 'unsubscribed' => 1, 'duplicate' => 1, 'invalid' => 1]);
    expect(NewsletterContact::count())->toBe(0)->and(Audience::count())->toBe(0);
    $this->get(route('admin.therapists.newsletter-imports.show', [$therapist, $import]))->assertOk()->assertSee('À vérifier');
    $this->post(route('admin.therapists.newsletter-imports.commit', [$therapist, $import]), ['confirm' => 1])
        ->assertSessionHasNoErrors()->assertRedirect();
    expect(NewsletterContact::count())->toBe(3)->and(ClientProfile::count())->toBe(1)
        ->and(NewsletterContact::where('status', 'active')->count())->toBe(1)
        ->and(NewsletterContact::where('status', 'pending')->count())->toBe(2)
        ->and(NewsletterOptOut::count())->toBe(1);
    $import->refresh();
    expect($import->status)->toBe('completed')->and($import->audience->newsletterContacts()->count())->toBe(3)
        ->and($import->audience->clients()->count())->toBe(0);
    Mail::assertNothingSent();
});

test('repeated preview and commit requests do not duplicate contacts or audiences', function () {
    [$admin, $therapist] = newsletterImportActors();
    $import = newsletterImportPreview($this, $admin, $therapist);
    $url = route('admin.therapists.newsletter-imports.commit', [$therapist, $import]);
    $this->post($url, ['confirm' => 1])->assertSessionHasNoErrors();
    $this->post($url, ['confirm' => 1])->assertSessionHasNoErrors();
    $again = newsletterImportPreview($this, $admin, $therapist);
    expect($again->id)->toBe($import->id)->and(NewsletterImport::count())->toBe(1)
        ->and(Audience::count())->toBe(1)->and(NewsletterContact::count())->toBe(1);
});

test('imports cannot cross therapist boundaries even for admin requests', function ($action) {
    [$admin, $therapist] = newsletterImportActors();
    $other = User::factory()->create(['is_therapist' => true]);
    $import = newsletterImportPreview($this, $admin, $therapist);
    $url = route('admin.therapists.newsletter-imports.'.$action, [$other, $import]);
    ($action === 'show' ? $this->get($url) : $this->post($url, ['confirm' => 1]))->assertNotFound();
    $second = newsletterImportPreview($this, $admin, $other);
    app(NewsletterImportService::class)->commit($import);
    app(NewsletterImportService::class)->commit($second);
    expect(NewsletterContact::count())->toBe(2)->and(Audience::count())->toBe(2);
})->with(['show', 'commit']);

test('unsubscriptions after preview are rechecked at commit and existing contacts are not overwritten', function () {
    [$admin, $therapist] = newsletterImportActors();
    $existing = NewsletterContact::create(['user_id' => $therapist->id, 'email' => 'existing@example.test', 'first_name' => 'Original', 'status' => 'pending']);
    $import = newsletterImportPreview($this, $admin, $therapist, "EMAIL;PRENOM;OPT_IN\nnew@example.test;A;YES\nexisting@example.test;Changed;YES\n");
    NewsletterOptOut::create(['user_id' => $therapist->id, 'email' => 'NEW@example.test', 'unsubscribed_at' => now()]);
    app(NewsletterImportService::class)->commit($import);
    expect(NewsletterContact::count())->toBe(1)->and($existing->fresh()->first_name)->toBe('Original')
        ->and($existing->fresh()->status)->toBe('pending')->and($import->fresh()->report['unsubscribed'])->toBe(1);
});

test('an import with no usable rows cannot create an empty audience', function () {
    [$admin, $therapist] = newsletterImportActors();
    $import = newsletterImportPreview($this, $admin, $therapist, "EMAIL\nnot-an-email\n");
    $this->post(route('admin.therapists.newsletter-imports.commit', [$therapist, $import]), ['confirm' => 1])->assertSessionHasErrors();
    expect(Audience::count())->toBe(0)->and($import->fresh()->status)->toBe('preview');
});

test('missing or negative consent and conflicting duplicates are never activated', function ($flags) {
    $csv = "EMAIL;OPT_IN;DOUBLE_OPT-IN\nname@example.test;".$flags."\n";
    $parsed = app(NewsletterImportService::class)->parse($csv);
    expect($parsed['rows'][0]['consent'])->toBeFalse();
    $duplicate = app(NewsletterImportService::class)->parse("EMAIL,OPT_IN\nname@example.test,YES\nNAME@example.test,NO\n");
    expect($duplicate['rows'][0]['consent'])->toBeFalse()->and($duplicate['rows'][1]['result'])->toBe('duplicate');
})->with([';', 'NO;YES', 'YES;NO', 'unknown;YES']);

test('CSV input with missing or duplicate email headers is rejected before an import is stored', function ($csv) {
    [$admin, $therapist] = newsletterImportActors();
    $this->actingAs($admin)->post(route('admin.therapists.newsletter-imports.preview', $therapist), [
        'csv_file' => UploadedFile::fake()->createWithContent('bad.csv', $csv), 'audience_name' => 'Test',
    ])->assertSessionHasErrors('csv_file');
    expect(NewsletterImport::count())->toBe(0);
})->with(["NOM;PRENOM\nA;B\n", "EMAIL;EMAIL\na@example.test;b@example.test\n", "EMAIL\n"]);

test('newsletter sending uses imported active contacts preserves quotas and deduplicates client addresses', function ($route) {
    Mail::fake();
    [$admin, $therapist] = newsletterImportActors();
    $import = newsletterImportPreview($this, $admin, $therapist, "EMAIL;OPT_IN\none@example.test;YES\ntwo@example.test;YES\npending@example.test;\n");
    $import = app(NewsletterImportService::class)->commit($import);
    $audience = $import->audience;
    $client = ClientProfile::create(['user_id' => $therapist->id, 'first_name' => 'One', 'last_name' => 'Client', 'email' => 'ONE@example.test']);
    $audience->clients()->attach($client);
    $foreign = NewsletterContact::create(['user_id' => User::factory()->create()->id, 'email' => 'foreign@example.test', 'status' => 'active']);
    $audience->newsletterContacts()->attach($foreign);
    $newsletter = newsletterImportCampaign($therapist, $audience);
    config(['newsletters.monthly_quota' => 1]);
    $this->actingAs($therapist)->post(route($route, $newsletter))->assertStatus(429);
    Mail::assertNothingSent();
    config(['newsletters.monthly_quota' => 2]);
    $this->post(route($route, $newsletter))->assertRedirect()->assertSessionHasNoErrors();
    Mail::assertSent(NewsletterMail::class, 2);
    expect($newsletter->fresh()->recipients_count)->toBe(2)
        ->and(NewsletterMonthlyUsage::where('user_id', $therapist->id)->value('sent_count'))->toBe(2)
        ->and(NewsletterRecipient::whereNotNull('newsletter_contact_id')->count())->toBe(1);
    $this->post(route($route, $newsletter))->assertRedirect();
    Mail::assertSent(NewsletterMail::class, 2);
})->with(['newsletters.send-now', 'mobile.newsletters.send-now']);

test('all clients keeps its previous scope and a foreign audience cannot be used', function () {
    [$admin, $therapist] = newsletterImportActors();
    $import = app(NewsletterImportService::class)->commit(newsletterImportPreview($this, $admin, $therapist));
    $campaign = newsletterImportCampaign($therapist);
    expect(app(NewsletterAudienceService::class)->recipients($campaign))->toHaveCount(0);
    $other = User::factory()->create(['is_therapist' => true]);
    $campaign->update(['audience_id' => Audience::create(['user_id' => $other->id, 'name' => 'Foreign'])->id]);
    $this->actingAs($therapist)->post(route('newsletters.send-now', $campaign))->assertNotFound();
});

test('imported recipients can unsubscribe and remain excluded after reimport', function () {
    Mail::fake();
    [$admin, $therapist] = newsletterImportActors();
    $import = app(NewsletterImportService::class)->commit(newsletterImportPreview($this, $admin, $therapist));
    $newsletter = newsletterImportCampaign($therapist, $import->audience);
    $this->actingAs($therapist)->post(route('newsletters.send-now', $newsletter))->assertRedirect();
    $recipient = $newsletter->recipients()->firstOrFail();
    auth()->logout();
    $this->get(route('unsubscribe.newsletter', $recipient->unsubscribe_token))->assertOk();
    $this->post(route('unsubscribe.newsletter.confirm', $recipient->unsubscribe_token))->assertOk()->assertSee('désabonnement est confirmé');
    $this->post(route('unsubscribe.newsletter.confirm', $recipient->unsubscribe_token))->assertOk();
    expect(NewsletterOptOut::where('user_id', $therapist->id)->count())->toBe(1);
    expect(NewsletterContact::first()->status)->toBe('unsubscribed');
    $reimport = newsletterImportPreview($this, $admin, $therapist, "EMAIL,OPT_IN\nsubscriber@example.test,YES\nother@example.test,YES\n");
    $reimport = app(NewsletterImportService::class)->commit($reimport);
    expect(app(NewsletterAudienceService::class)->recipients(newsletterImportCampaign($therapist, $reimport->audience))->pluck('email')->all())
        ->toBe(['other@example.test']);
});

test('imported audiences appear in desktop and mobile and editing client membership preserves contacts', function ($route) {
    [$admin, $therapist] = newsletterImportActors();
    $import = app(NewsletterImportService::class)->commit(newsletterImportPreview($this, $admin, $therapist));
    $audience = $import->audience;
    $this->actingAs($therapist)->get(route($route.'.edit', $audience))->assertOk()->assertSee('subscriber@example.test');
    $this->put(route($route.'.update', $audience), ['name' => 'Audience renommée', 'client_ids' => []])->assertRedirect();
    expect($audience->newsletterContacts()->count())->toBe(1)->and($audience->fresh()->contacts_count)->toBe(1);
    $this->get(route($route.'.index'))->assertOk()->assertSee('Audience renommée');
    $this->get(route($route === 'audiences' ? 'newsletters.create' : 'mobile.newsletters.create'))->assertOk()->assertSee('Audience renommée');
})->with(['audiences', 'mobile.audiences']);

test('new audience forms still work without an existing audience or imported contacts', function ($route) {
    [$admin, $therapist] = newsletterImportActors();
    $this->actingAs($therapist)->get(route($route))->assertOk()->assertDontSee('Voir les contacts importés');
})->with(['audiences.create', 'mobile.audiences.create']);

test('CSV names are escaped in both preview and newsletter personalization', function () {
    [$admin, $therapist] = newsletterImportActors();
    $import = newsletterImportPreview($this, $admin, $therapist, "EMAIL;PRENOM;OPT_IN\nname@example.test;<script>alert(1)</script>;YES\n");
    $this->get(route('admin.therapists.newsletter-imports.show', [$therapist, $import]))->assertOk()
        ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    $import = app(NewsletterImportService::class)->commit($import);
    $newsletter = newsletterImportCampaign($therapist, $import->audience);
    $recipient = app(NewsletterAudienceService::class)->recipients($newsletter)->first();
    $html = (new NewsletterMail($newsletter, $recipient, 'https://example.test/unsubscribe'))->render();
    expect($html)->toContain('&lt;script&gt;alert(1)&lt;/script&gt;')->not->toContain('<script>alert(1)</script>');
});

test('commit requires confirmation and only uses the stored scoped preview', function () {
    [$admin, $therapist] = newsletterImportActors();
    $import = newsletterImportPreview($this, $admin, $therapist);
    $url = route('admin.therapists.newsletter-imports.commit', [$therapist, $import]);
    $this->post($url)->assertSessionHasErrors('confirm');
    expect(NewsletterContact::count())->toBe(0)->and(Audience::count())->toBe(0);
    $other = User::factory()->create(['is_therapist' => true]);
    $this->post($url, [
        'confirm' => 1, 'user_id' => $other->id, 'audience_name' => 'Tampered',
        'rows' => [['email' => 'injected@example.test', 'consent' => true]],
    ])->assertRedirect();
    expect(NewsletterContact::first())->toMatchArray(['user_id' => $therapist->id, 'email' => 'subscriber@example.test'])
        ->and(Audience::first()->name)->toBe('Liste importée');
});

test('CSV import rejects oversized uploads and too many rows without storing contacts', function () {
    [$admin, $therapist] = newsletterImportActors();
    $this->actingAs($admin)->post(route('admin.therapists.newsletter-imports.preview', $therapist), [
        'csv_file' => UploadedFile::fake()->create('contacts.csv', 4097, 'text/csv'), 'audience_name' => 'Test',
    ])->assertSessionHasErrors('csv_file');
    expect(NewsletterImport::count())->toBe(0)->and(NewsletterContact::count())->toBe(0);
    expect(fn () => app(NewsletterImportService::class)->parse("EMAIL\n".str_repeat("one@example.test\n", 10001)))
        ->toThrow(\Illuminate\Validation\ValidationException::class);
});

test('newsletter migration rollback and upgrade preserve existing clients audiences and delivery history', function () {
    [$admin, $therapist] = newsletterImportActors();
    $client = ClientProfile::create(['user_id' => $therapist->id, 'first_name' => 'Alice', 'last_name' => 'Test', 'email' => 'legacy@example.test']);
    $audience = Audience::create(['user_id' => $therapist->id, 'name' => 'Audience existante']);
    $audience->clients()->attach($client);
    $newsletter = newsletterImportCampaign($therapist, $audience);
    $recipient = NewsletterRecipient::create([
        'newsletter_id' => $newsletter->id, 'client_profile_id' => $client->id,
        'email' => $client->email, 'status' => 'sent', 'unsubscribe_token' => 'legacy-token', 'sent_at' => now(),
    ]);
    $migration = require database_path('migrations/2026_09_21_150000_add_admin_newsletter_imports.php');
    $migration->down();
    expect(\Illuminate\Support\Facades\Schema::hasColumn('newsletter_recipients', 'newsletter_contact_id'))->toBeFalse();
    $migration->up();
    expect($recipient->fresh()->client_profile_id)->toBe($client->id)
        ->and($recipient->fresh()->newsletter_contact_id)->toBeNull()
        ->and($audience->clients()->count())->toBe(1)
        ->and(app(NewsletterAudienceService::class)->recipients($newsletter)->pluck('email')->all())->toBe([$client->email]);
});
