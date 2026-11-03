<?php

use App\Mail\ConseilSentMail;
use App\Models\ClientProfile;
use App\Models\Conseil;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->therapist = User::factory()->create(['is_therapist' => true]);
    $this->actingAs($this->therapist);
});

function conseilWithLinks(User $therapist, array $attributes = []): Conseil
{
    return Conseil::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Conseil de démonstration',
        'content' => '<p>Votre conseil habituel.</p>',
        'links' => [['label' => 'Une ressource', 'url' => 'https://example.org/resource']],
    ], $attributes));
}

test('therapist can save multiple links alongside existing text image and PDF', function () {
    Storage::fake('public');
    $links = [
        ['label' => 'Voir la vidéo', 'url' => 'https://example.org/video?id=1&lang=fr'],
        ['label' => null, 'url' => 'http://example.org/article#lecture'],
    ];

    $this->post(route('conseils.store'), [
        'name' => 'Mes ressources',
        'content' => '<p>Le texte reste disponible.</p>',
        'tag' => 'Détente',
        'image' => UploadedFile::fake()->image('image.jpg'),
        'attachment' => UploadedFile::fake()->create('guide.pdf', 10, 'application/pdf'),
        'links' => $links,
    ])->assertSessionHasNoErrors()->assertRedirect(route('conseils.index'));

    $conseil = Conseil::sole();
    expect($conseil->links)->toBe($links)
        ->and($conseil->content)->toBe('<p>Le texte reste disponible.</p>')
        ->and($conseil->tag)->toBe('Détente')
        ->and($conseil->user_id)->toBe($this->therapist->id);
    Storage::disk('public')->assertExists([$conseil->image, $conseil->attachment]);
    $this->get(route('conseils.show', $conseil))->assertOk()
        ->assertSee('Voir la vidéo')->assertSee('http://example.org/article#lecture')
        ->assertSee('Le texte reste disponible.')->assertSee($conseil->attachment);
});

test('a conseil can contain links without text or files and ignore unused rows', function () {
    $this->post(route('conseils.store'), [
        'name' => 'Ressources seulement',
        'links' => [
            ['label' => '  ', 'url' => '  '],
            ['label' => ' Article ', 'url' => ' https://example.org/article '],
        ],
    ])->assertSessionHasNoErrors();

    expect(Conseil::sole())->content->toBeNull()->attachment->toBeNull()
        ->links->toBe([['label' => 'Article', 'url' => 'https://example.org/article']]);
});

test('legacy create and edit submissions preserve text files and saved links', function () {
    $this->post(route('conseils.store'), [
        'name' => 'Ancien formulaire',
        'content' => '<p>Ancien texte avec <a href="https://example.org">un lien intégré</a>.</p>',
    ])->assertSessionHasNoErrors();
    $legacy = Conseil::sole();
    expect($legacy->links)->toBeNull();
    $this->get(route('conseils.show', $legacy))->assertOk()->assertDontSee('Liens utiles');

    $conseil = conseilWithLinks($this->therapist, [
        'image' => 'conseils/images/existing.jpg',
        'attachment' => 'conseils/attachments/existing.pdf',
    ]);
    $this->put(route('conseils.update', $conseil), ['name' => 'Nouveau nom'])
        ->assertSessionHasNoErrors();
    $updated = $conseil->fresh();
    expect($updated->links)->toBe($conseil->links)
        ->and($updated->content)->toBe($conseil->content)
        ->and($updated->image)->toBe($conseil->image)
        ->and($updated->attachment)->toBe($conseil->attachment);
});

test('therapist can replace remove individual links and remove every link', function () {
    $conseil = conseilWithLinks($this->therapist);
    $links = [
        ['label' => 'Nouveau titre', 'url' => 'https://example.org/new'],
        ['label' => null, 'url' => 'https://example.org/second'],
    ];
    $this->put(route('conseils.update', $conseil), ['name' => $conseil->name, 'links' => $links])
        ->assertSessionHasNoErrors();
    expect($conseil->fresh()->links)->toBe($links);

    $this->put(route('conseils.update', $conseil), ['name' => $conseil->name, 'links' => [1 => $links[1]]])
        ->assertSessionHasNoErrors();
    expect($conseil->fresh()->links)->toBe([$links[1]]);

    $this->put(route('conseils.update', $conseil), ['name' => $conseil->name, 'links_present' => '1'])
        ->assertSessionHasNoErrors();
    expect($conseil->fresh()->links)->toBe([])
        ->and($conseil->fresh()->content)->toBe($conseil->content);
    $this->get(route('conseils.show', $conseil))->assertDontSee('Liens utiles');
});

test('invalid addresses cannot be saved and leave the existing conseil unchanged', function ($url) {
    $conseil = conseilWithLinks($this->therapist);
    $original = $conseil->fresh()->getAttributes();
    $this->put(route('conseils.update', $conseil), [
        'name' => 'Should not change',
        'links' => [['label' => 'Lien', 'url' => $url]],
    ])->assertSessionHasErrors('links.0.url');
    expect($conseil->fresh()->getAttributes())->toBe($original);
})->with([
    'javascript' => 'javascript:alert(1)',
    'data' => 'data:text/html,<script>alert(1)</script>',
    'file' => 'file:///etc/passwd',
    'ftp' => 'ftp://example.org/file',
    'relative' => '/resource',
    'protocol relative' => '//example.org/resource',
    'missing protocol' => 'www.example.org/resource',
    'invalid host' => 'https://',
    'missing address' => '',
    'array' => [['https://example.org']],
    'too long' => 'https://example.org/'.str_repeat('x', 2048),
]);

test('malformed links and excessive lengths are rejected', function ($links, $field) {
    $this->post(route('conseils.store'), ['name' => 'Invalid', 'links' => $links])
        ->assertSessionHasErrors($field);
    expect(Conseil::count())->toBe(0);
})->with([
    'non array' => ['https://example.org', 'links'],
    'non object row' => [['https://example.org'], 'links.0'],
    'unexpected attribute' => [[['url' => 'https://example.org', 'onclick' => 'alert(1)']], 'links.0'],
    'long label' => [[['url' => 'https://example.org', 'label' => str_repeat('a', 256)]], 'links.0.label'],
    'invalid label' => [[['url' => 'https://example.org', 'label' => ['bad']]], 'links.0.label'],
    'too many' => [array_fill(0, 21, ['url' => 'https://example.org']), 'links'],
]);

test('validation errors keep submitted links and rich text available to correct', function () {
    $conseil = conseilWithLinks($this->therapist);
    $editUrl = route('conseils.edit', $conseil);
    $content = '<p>Apostrophe, `backticks` and ${literal}.</p>';
    $this->from($editUrl)->put(route('conseils.update', $conseil), [
        'name' => $conseil->name,
        'content' => $content,
        'links' => [['label' => 'Titre conservé', 'url' => 'invalid']],
    ])->assertRedirect($editUrl)->assertSessionHasErrors('links.0.url');
    $this->get($editUrl)->assertOk()
        ->assertSee('Titre conservé')->assertSee('value="invalid"', false)
        ->assertSee(e($content), false)
        ->assertSee('Indiquez une adresse web complète');
});

test('removing all links stays removed in the form after another field fails validation', function () {
    $conseil = conseilWithLinks($this->therapist);
    $editUrl = route('conseils.edit', $conseil);
    $this->from($editUrl)->put(route('conseils.update', $conseil), [
        'name' => '', 'links_present' => '1',
    ])->assertRedirect($editUrl)->assertSessionHasErrors('name');
    $this->get($editUrl)->assertOk()->assertDontSee('https://example.org/resource');
    expect($conseil->fresh()->links)->toBe($conseil->links);
});

test('forms show optional link fields and escaped stored content', function () {
    $conseil = conseilWithLinks($this->therapist, [
        'content' => '<p>`Example` ${literal}</p>',
    ]);
    $this->get(route('conseils.create'))->assertOk()->assertSee('Liens utiles (facultatif)')
        ->assertSee('Ajouter un lien');
    $this->get(route('conseils.edit', $conseil))->assertOk()
        ->assertSee('https://example.org/resource')
        ->assertSee('name="links[0][url]"', false)
        ->assertSee(e($conseil->content), false);
});

test('other therapists cannot read or change saved links', function () {
    $conseil = conseilWithLinks(User::factory()->create(['is_therapist' => true]));
    $this->get(route('conseils.edit', $conseil))->assertForbidden();
    $this->get(route('conseils.show', $conseil))->assertForbidden();
    $this->put(route('conseils.update', $conseil), ['name' => 'Changed', 'links_present' => 1])
        ->assertForbidden();
    expect($conseil->fresh()->links)->toBe($conseil->links);
});

test('shared client page renders links safely through the unchanged email and token flow', function () {
    Mail::fake();
    $conseil = conseilWithLinks($this->therapist, [
        'links' => [
            ['label' => '<img src=x onerror=alert(1)>', 'url' => 'https://example.org/?a=1&b=2'],
            ['url' => 'https://example.org/no-label'],
        ],
    ]);
    $client = ClientProfile::create([
        'user_id' => $this->therapist->id, 'first_name' => 'Alice',
        'last_name' => 'Test', 'email' => 'alice@example.test',
    ]);

    $this->post(route('client_profiles.conseils.send', $client), ['conseil_id' => $conseil->id])
        ->assertSessionHasNoErrors()->assertRedirect(route('client_profiles.show', $client));
    $token = $client->conseilsSent()->first()->pivot->token;
    $url = route('public.conseil.view', ['clientProfile' => $client->id, 'conseil' => $conseil->id, 'token' => $token]);
    Mail::assertSent(ConseilSentMail::class, fn ($mail) => $mail->hasTo($client->email)
        && $mail->link === $url && str_contains($mail->render(), 'Voir le Conseil'));
    $this->get($url)->assertOk()->assertSee('Liens utiles')
        ->assertSee('&lt;img src=x onerror=alert(1)&gt;', false)
        ->assertDontSee('<img src=x', false)
        ->assertSee('href="https://example.org/?a=1&amp;b=2"', false)
        ->assertSee('>https://example.org/no-label</a>', false)
        ->assertSee('target="_blank" rel="noopener noreferrer"', false);

    $this->put(route('conseils.update', $conseil), [
        'name' => $conseil->name, 'links' => [['url' => 'https://example.org/updated']],
    ])->assertSessionHasNoErrors();
    auth()->logout();
    $this->get($url)->assertOk()->assertSee('https://example.org/updated');
    $this->get(route('public.conseil.view', ['clientProfile' => $client->id, 'conseil' => $conseil->id, 'token' => 'invalid']))
        ->assertForbidden();
    $this->get(route('public.conseil.view'))->assertNotFound();
});

test('legacy client links still display text and PDF without an empty links section', function () {
    $conseil = conseilWithLinks($this->therapist, ['links' => null, 'attachment' => 'conseils/attachments/existing.pdf']);
    $client = ClientProfile::create(['user_id' => $this->therapist->id, 'first_name' => 'Alice', 'last_name' => 'Test']);
    $client->conseilsSent()->attach($conseil->id, ['token' => 'existing-token', 'sent_at' => now()->subYear()]);
    auth()->logout();
    $this->get(route('public.conseil.view', ['clientProfile' => $client->id, 'conseil' => $conseil->id, 'token' => 'existing-token']))
        ->assertOk()->assertSee('Votre conseil habituel.')->assertSee('Télécharger la pièce jointe (PDF)')
        ->assertSee('conseils/attachments/existing.pdf')->assertDontSee('Liens utiles');
});

test('display ignores unsafe or malformed stored links even if validation was bypassed', function () {
    $conseil = conseilWithLinks($this->therapist, ['links' => [
        ['url' => 'javascript:alert(1)'], ['url' => 'data:text/html,test'],
        ['url' => ['bad']], 'invalid row', ['url' => 'https://example.org/valid'],
    ]]);
    $this->get(route('conseils.show', $conseil))->assertOk()->assertSee('https://example.org/valid')
        ->assertDontSee('javascript:')->assertDontSee('data:text/html');
});
