<?php

use App\Models\Product;
use App\Models\User;
use App\Support\ProductDescription;

function descriptionProductPayload(array $overrides = []): array
{
    return array_replace([
        'name' => 'Séance découverte', 'description' => 'Texte historique',
        'price' => 70, 'tax_rate' => 0, 'duration' => 60, 'mode' => 'dans_le_cabinet',
        'can_be_booked_online' => 0, 'collect_payment' => 0, 'requires_emargement' => 0,
        'visible_in_portal' => 1, 'price_visible_in_portal' => 1,
    ], $overrides);
}

test('descriptions retain supported formatting and a plain text version on both editors', function (string $routes) {
    $user = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $html = '<p><strong>Gras</strong> <em>italique</em> <u>souligné</u> &amp; détente</p><p><br></p><p>Suite</p>';
    $this->actingAs($user)->post(route($routes.'.store'), descriptionProductPayload([
        'description_html' => $html,
    ]))->assertSessionHasNoErrors()->assertRedirect();

    $product = Product::sole();
    expect($product->description)->toBe("Gras italique souligné & détente\n\nSuite")
        ->and($product->description_html)->toContain('<strong>Gras</strong>', '<em>italique</em>', '<u>souligné</u>');
    $this->get(route($routes.'.show', $product))->assertOk()->assertSee('<strong>Gras</strong>', false);
    $this->get(route($routes.'.edit', $product))->assertOk()->assertSee('data-description-editor', false);

    // Simulate a cached/old form or unavailable Quill: unchanged plain text retains formatting.
    $this->put(route($routes.'.update', $product), descriptionProductPayload([
        'name' => 'Nouveau nom', 'description' => $product->description,
    ]))->assertSessionHasNoErrors()->assertRedirect();
    expect($product->fresh()->description_html)->toBe($product->description_html);

    $this->put(route($routes.'.update', $product), descriptionProductPayload([
        'description_html' => '<p><u>Nouvelle description</u></p>',
    ]))->assertSessionHasNoErrors()->assertRedirect();
    expect($product->fresh()->description)->toBe('Nouvelle description');

    $this->put(route($routes.'.update', $product), descriptionProductPayload([
        'description_html' => '<p><br></p>',
    ]))->assertSessionHasNoErrors()->assertRedirect();
    expect($product->fresh()->description)->toBeNull()->and($product->fresh()->description_html)->toBeNull();
})->with(['products', 'mobile.products']);

test('legacy descriptions keep literal markup and line breaks when edited without formatting', function (string $routes) {
    $user = User::factory()->create(['is_therapist' => true, 'license_status' => 'active', 'slug' => 'legacy-description']);
    $text = "Détente & bien-être\n\nTexte littéral <b>sans mise en forme</b>";
    $this->actingAs($user)->post(route($routes.'.store'), descriptionProductPayload(['description' => $text]))
        ->assertSessionHasNoErrors()->assertRedirect();
    $product = Product::sole();
    expect($product->description)->toBe($text)->and($product->description_html)->toBeNull();

    $this->put(route($routes.'.update', $product), descriptionProductPayload(['description' => $text]))
        ->assertSessionHasNoErrors()->assertRedirect();
    expect($product->fresh()->description)->toBe($text)->and($product->fresh()->description_html)->toBeNull();
    $this->get(route($routes.'.show', $product))->assertOk()->assertSee('&lt;b&gt;sans mise en forme&lt;/b&gt;', false);
    $this->get(route('therapist.show', $user->slug))->assertOk()->assertSee('&lt;b&gt;sans mise en forme&lt;/b&gt;', false);
})->with(['products', 'mobile.products']);

test('formatted descriptions cannot inject scripts attributes links or unsupported formats', function () {
    $payload = ProductDescription::payload(['description_html' => '<p onclick="alert(1)"><strong>Visible</strong><img src=x onerror=alert(1)><script>alert(1)</script><a href="javascript:alert(1)">Lien</a><s>Barré</s></p>',
    ]);
    expect($payload['description_html'])->toContain('<strong>Visible</strong>')
        ->not->toContain('onclick', '<img', '<script', '<a ', 'javascript:', '<s>')
        ->and($payload['description'])->toBe('VisibleLienBarré');
});

test('plain text edits clear stale formatting and unformatted editor content stays plain', function () {
    $existing = new Product(['description' => 'Avant', 'description_html' => '<p><strong>Avant</strong></p>']);
    expect(ProductDescription::payload(['description' => 'Après'], $existing))
        ->toBe(['description' => 'Après', 'description_html' => null])
        ->and(ProductDescription::payload(['description_html' => '<p>Sans format</p><p>Deuxième ligne</p>']))
        ->toBe(['description' => "Sans format\nDeuxième ligne", 'description_html' => null]);
});

test('invalid description input returns a usable form instead of failing to render old input', function (string $routes) {
    $user = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $this->actingAs($user)->from(route($routes.'.create'))
        ->post(route($routes.'.store'), descriptionProductPayload(['description_html' => ['invalid']]))
        ->assertSessionHasErrors('description_html')->assertRedirect(route($routes.'.create'));
    $this->get(route($routes.'.create'))->assertOk()->assertSee('data-description-editor', false);
    expect(Product::count())->toBe(0);
})->with(['products', 'mobile.products']);

test('duplicating a prestation preserves formatting through new and legacy forms', function (bool $sendHtml) {
    $user = User::factory()->create(['is_therapist' => true]);
    $product = Product::create([
        'user_id' => $user->id, 'name' => 'Source', 'price' => 70, 'tax_rate' => 0,
        'description' => 'Gras', 'description_html' => '<p><strong>Gras</strong></p>',
    ]);
    $payload = descriptionProductPayload(['description' => $product->description]);
    if ($sendHtml) {
        $payload['description_html'] = $product->description_html;
    }
    $this->actingAs($user)->post(route('products.storeDuplicate', $product), $payload)
        ->assertSessionHasNoErrors()->assertRedirect();
    $duplicate = Product::whereKeyNot($product->id)->sole();
    expect($duplicate->description_html)->toBe($product->description_html)
        ->and($duplicate->description)->toBe('Gras');
})->with([true, false]);

test('public desktop and mobile profiles render formatted descriptions in both category paths', function () {
    $user = User::factory()->create(['is_therapist' => true, 'slug' => 'formatted-description', 'accept_online_appointments' => false]);
    $category = $user->productCategories()->create(['name' => 'Accompagnement']);
    foreach ([null, $category->id] as $categoryId) {
        Product::create([
            'user_id' => $user->id, 'product_category_id' => $categoryId, 'name' => 'Séance '.$categoryId,
            'price' => 70, 'tax_rate' => 0, 'visible_in_portal' => true,
            'description' => str_repeat('Texte long ', 30),
            'description_html' => '<p><strong>Format visible</strong> '.str_repeat('Texte long ', 30).'</p>',
        ]);
    }
    foreach (['therapist.show', 'mobile.therapists.show'] as $route) {
        $response = $this->get(route($route, $user->slug))->assertOk()
            ->assertSee('Voir plus')->assertSee('<strong>Format visible</strong>', false);
        expect(substr_count($response->getContent(), '<strong>Format visible</strong>'))->toBe(2);
    }
});
