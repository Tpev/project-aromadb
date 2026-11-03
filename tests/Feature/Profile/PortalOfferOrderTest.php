<?php

use App\Models\PackProduct;
use App\Models\Product;
use App\Models\User;

function portalOrderTherapist(array $attributes = []): User
{
    return User::factory()->create(array_replace([
        'is_therapist' => true,
        'company_name' => 'Cabinet test',
        'slug' => 'cabinet-ordre-offres',
        'accept_online_appointments' => false,
    ], $attributes));
}

function portalOrderXPath(string $html): DOMXPath
{
    $document = new DOMDocument;
    $previous = libxml_use_internal_errors(true);
    $document->loadHTML('<?xml encoding="UTF-8">'.$html);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    return new DOMXPath($document);
}

function portalOrderCardTitles(string $html): array
{
    $nodes = portalOrderXPath($html)->query('//*[@data-testid="portal-offers"]//h4');

    return array_map(fn ($node) => trim($node->textContent), iterator_to_array($nodes));
}

test('company information shows the current portal order with a default for legacy accounts', function ($order, $checked) {
    $therapist = portalOrderTherapist(['portal_offer_order' => $order]);
    $response = $this->actingAs($therapist)->get(route('profile.editCompanyInfo'))->assertOk()
        ->assertSee('Ordre des offres sur le portail')
        ->assertSee('Prestations individuelles d’abord')
        ->assertSee('Packs d’abord')
        ->assertSee('Vos catégories conservent leur ordre.')
        ->assertSee(route('therapist.show', $therapist->slug));

    $radios = portalOrderXPath($response->getContent())->query('//input[@name="portal_offer_order" and @checked]');
    expect($radios->length)->toBe(1)
        ->and($radios->item(0)->getAttribute('value'))->toBe($checked);
})->with([
    'legacy default' => [null, 'packs_first'],
    'packs first' => ['packs_first', 'packs_first'],
    'prestations first' => ['prestations_first', 'prestations_first'],
]);

test('therapists can change and reverse their own portal order without changing another account', function () {
    $therapist = portalOrderTherapist();
    $other = portalOrderTherapist(['slug' => 'another-cabinet']);

    foreach (['prestations_first', 'packs_first'] as $order) {
        $this->actingAs($therapist)->put(route('profile.updateCompanyInfo'), [
            'company_name' => $therapist->company_name,
            'portal_offer_order' => $order,
            'user_id' => $other->id,
        ])->assertSessionHasNoErrors()->assertRedirect(route('profile.editCompanyInfo'));
        expect($therapist->fresh()->portal_offer_order)->toBe($order)
            ->and($other->fresh()->portal_offer_order)->toBeNull();
    }
});

test('older company forms preserve the saved portal order when the field is omitted', function ($order) {
    $therapist = portalOrderTherapist(['portal_offer_order' => $order]);
    $this->actingAs($therapist)->put(route('profile.updateCompanyInfo'), [
        'company_name' => $therapist->company_name,
    ])->assertSessionHasNoErrors();
    expect($therapist->fresh()->portal_offer_order)->toBe($order);
})->with([null, 'prestations_first', 'packs_first']);

test('invalid order submissions are rejected before updating company data', function ($order) {
    $therapist = portalOrderTherapist(['portal_offer_order' => 'prestations_first']);
    $before = $therapist->fresh()->getAttributes();
    $editUrl = route('profile.editCompanyInfo');
    $this->actingAs($therapist)->from($editUrl)->put(route('profile.updateCompanyInfo'), [
        'company_name' => 'Must not change', 'portal_offer_order' => $order,
    ])->assertRedirect($editUrl)->assertSessionHasErrors('portal_offer_order');
    expect($therapist->fresh()->getAttributes())->toBe($before);
    $response = $this->get($editUrl)->assertOk();
    $tabState = portalOrderXPath($response->getContent())->query('//div[starts-with(@x-data, "{ activeTab:")]')->item(0);
    expect($tabState->getAttribute('x-data'))->toBe("{ activeTab: 'public' }");
})->with(['invalid string' => 'random', 'empty' => '', 'null' => null, 'array' => [['packs_first']]]);

test('an unrelated validation error retains the selected order in the form', function () {
    $therapist = portalOrderTherapist();
    $editUrl = route('profile.editCompanyInfo');
    $this->actingAs($therapist)->from($editUrl)->put(route('profile.updateCompanyInfo'), [
        'facebook_url' => 'not-a-url', 'portal_offer_order' => 'prestations_first',
    ])->assertSessionHasErrors('facebook_url');
    $response = $this->get($editUrl)->assertOk();
    $checked = portalOrderXPath($response->getContent())->query('//input[@name="portal_offer_order" and @checked]');
    expect($checked->item(0)->getAttribute('value'))->toBe('prestations_first')
        ->and($therapist->fresh()->portal_offer_order)->toBeNull();
});

test('only authenticated therapists can change portal order', function () {
    $this->put(route('profile.updateCompanyInfo'), ['portal_offer_order' => 'prestations_first'])
        ->assertRedirect();
    $user = User::factory()->create(['is_therapist' => false]);
    $this->actingAs($user)->put(route('profile.updateCompanyInfo'), ['portal_offer_order' => 'prestations_first'])
        ->assertForbidden();
    expect($user->fresh()->portal_offer_order)->toBeNull();
});

test('portal order applies inside categories and to unclassified offers without altering category or product order', function ($order) {
    $therapist = portalOrderTherapist(['portal_offer_order' => $order]);
    $later = $therapist->productCategories()->create(['name' => 'Alpha catégorie suivante', 'display_order' => 20]);
    $first = $therapist->productCategories()->create(['name' => 'Zen première catégorie', 'display_order' => 10]);
    $private = $therapist->productCategories()->create(['name' => 'Catégorie privée', 'display_order' => 0]);
    foreach ([
        ['name' => 'Prestation libre B', 'display_order' => 30],
        ['name' => 'Prestation libre A', 'display_order' => 10],
        ['name' => 'Séance guidée', 'product_category_id' => $first->id, 'display_order' => 2],
        ['name' => 'Séance guidée', 'product_category_id' => $first->id, 'display_order' => 3, 'visio' => true],
        ['name' => 'Atelier guidé', 'product_category_id' => $first->id, 'display_order' => 5],
        ['name' => 'Prestation masquée', 'product_category_id' => $private->id, 'visible_in_portal' => false],
    ] as $attributes) {
        Product::create(array_replace([
            'user_id' => $therapist->id, 'price' => 60, 'tax_rate' => 0,
            'visible_in_portal' => true, 'dans_le_cabinet' => true,
        ], $attributes));
    }
    foreach ([
        ['name' => 'Pack libre B'], ['name' => 'Pack libre A'],
        ['name' => 'Pack guidé', 'product_category_id' => $first->id],
        ['name' => 'Pack découverte', 'product_category_id' => $later->id],
        ['name' => 'Pack masqué', 'product_category_id' => $private->id, 'visible_in_portal' => false],
        ['name' => 'Pack inactif', 'product_category_id' => $private->id, 'is_active' => false],
    ] as $attributes) {
        PackProduct::create(array_replace([
            'user_id' => $therapist->id, 'price' => 120, 'tax_rate' => 0,
            'visible_in_portal' => true, 'is_active' => true,
        ], $attributes));
    }

    $response = $this->get(route('therapist.show', $therapist->slug))->assertOk()
        ->assertSeeInOrder(['Zen première catégorie', 'Alpha catégorie suivante'])
        ->assertDontSee('Catégorie privée')->assertDontSee('Prestation masquée')
        ->assertDontSee('Pack masqué')->assertDontSee('Pack inactif');
    $expected = $order === 'prestations_first'
        ? ['Prestation libre A', 'Prestation libre B', 'Séance guidée', 'Atelier guidé', 'Pack guidé', 'Pack découverte', 'Pack libre A', 'Pack libre B']
        : ['Pack libre A', 'Pack libre B', 'Pack guidé', 'Séance guidée', 'Atelier guidé', 'Pack découverte', 'Prestation libre A', 'Prestation libre B'];
    expect(portalOrderCardTitles($response->getContent()))->toBe($expected);
})->with([null, 'packs_first', 'prestations_first']);

test('changing order preserves offer content prices and checkout destinations', function () {
    $therapist = portalOrderTherapist();
    $product = Product::create([
        'user_id' => $therapist->id, 'name' => 'Séance unique', 'price' => 60,
        'tax_rate' => 0, 'duration' => 60, 'visible_in_portal' => true, 'price_visible_in_portal' => true,
    ]);
    $pack = PackProduct::create([
        'user_id' => $therapist->id, 'name' => 'Pack trois séances', 'price' => 150,
        'tax_rate' => 0, 'visible_in_portal' => true, 'is_active' => true, 'price_visible_in_portal' => true,
    ]);
    $pack->items()->create(['product_id' => $product->id, 'quantity' => 3]);
    $url = route('therapist.show', $therapist->slug);
    $before = portalOrderXPath($this->get($url)->assertOk()->getContent());
    $this->actingAs($therapist)->put(route('profile.updateCompanyInfo'), [
        'company_name' => $therapist->company_name, 'portal_offer_order' => 'prestations_first',
    ])->assertSessionHasNoErrors();
    $after = portalOrderXPath($this->get($url)->assertOk()->getContent());
    $cards = '//*[@data-testid="portal-offers"]//*[contains(concat(" ", normalize-space(@class), " "), " prestation-item ")]';
    $beforeCards = $before->query($cards);
    $afterCards = $after->query($cards);
    expect($beforeCards->length)->toBe(2)->and($afterCards->length)->toBe(2);
    expect($after->document->saveHTML($afterCards->item(0)))->toBe($before->document->saveHTML($beforeCards->item(1)))
        ->and($after->document->saveHTML($afterCards->item(1)))->toBe($before->document->saveHTML($beforeCards->item(0)))
        ->and($pack->fresh()->items->sole()->quantity)->toBe(3)
        ->and((float) $pack->fresh()->price)->toBe(150.0);
});

test('portals with only one offer type or no offers retain their usual empty state', function () {
    $therapist = portalOrderTherapist(['portal_offer_order' => 'prestations_first']);
    $url = route('therapist.show', $therapist->slug);
    $this->get($url)->assertOk()->assertSee('Aucune prestation disponible pour le moment.');
    $pack = PackProduct::create([
        'user_id' => $therapist->id, 'name' => 'Pack seul', 'price' => 100, 'tax_rate' => 0,
        'visible_in_portal' => true, 'is_active' => true,
    ]);
    expect(portalOrderCardTitles($this->get($url)->assertOk()->getContent()))->toBe(['Pack seul']);
    $pack->update(['visible_in_portal' => false]);
    Product::create(['user_id' => $therapist->id, 'name' => 'Séance seule', 'price' => 60, 'tax_rate' => 0, 'visible_in_portal' => true]);
    expect(portalOrderCardTitles($this->get($url)->assertOk()->getContent()))->toBe(['Séance seule']);
});
