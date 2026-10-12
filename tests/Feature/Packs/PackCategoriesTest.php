<?php

use App\Models\PackProduct;
use App\Models\Product;
use App\Models\User;

function categorizedPackPayload(Product $product, array $overrides = []): array
{
    return array_replace([
        'name' => 'Pack découverte', 'description' => 'Trois séances', 'price' => 180, 'tax_rate' => 0,
        'is_active' => 1, 'visible_in_portal' => 1, 'price_visible_in_portal' => 1, 'installments_enabled' => 0,
        'items' => [['product_id' => $product->id, 'quantity' => 3]],
    ], $overrides);
}

test('packs reuse optional owned categories in desktop and mobile forms', function (string $routes) {
    $user = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $product = Product::create(['user_id' => $user->id, 'name' => 'Séance', 'price' => 70, 'tax_rate' => 0]);
    $category = $user->productCategories()->create(['name' => 'Accompagnements']);
    $foreign = User::factory()->create()->productCategories()->create(['name' => 'Catégorie étrangère']);
    $this->actingAs($user)->get(route($routes.'.create'))->assertOk()
        ->assertSee('Accompagnements')->assertSee('Aucune catégorie')->assertDontSee('Catégorie étrangère');

    $this->post(route($routes.'.store'), categorizedPackPayload($product, ['product_category_id' => $foreign->id]))
        ->assertSessionHasErrors('product_category_id');
    expect(PackProduct::count())->toBe(0);
    $this->post(route($routes.'.store'), categorizedPackPayload($product, ['product_category_id' => $category->id]))
        ->assertSessionHasNoErrors()->assertRedirect();
    $pack = PackProduct::sole();
    expect($pack->product_category_id)->toBe($category->id);
    foreach (['index', 'show', 'edit'] as $page) {
        $this->get(route($routes.'.'.$page, $page === 'index' ? [] : $pack))->assertOk()->assertSee('Accompagnements');
    }

    $this->put(route($routes.'.update', $pack), categorizedPackPayload($product, ['product_category_id' => $foreign->id]))
        ->assertSessionHasErrors('product_category_id');
    expect($pack->fresh()->product_category_id)->toBe($category->id);
    // Older forms omit the new field; explicit blank is the only way to unassign it.
    $this->put(route($routes.'.update', $pack), categorizedPackPayload($product))->assertSessionHasNoErrors();
    expect($pack->fresh()->product_category_id)->toBe($category->id);
    $this->put(route($routes.'.update', $pack), categorizedPackPayload($product, ['product_category_id' => '']))
        ->assertSessionHasNoErrors();
    expect($pack->fresh()->product_category_id)->toBeNull()
        ->and($pack->fresh()->items->sole()->quantity)->toBe(3);
})->with(['pack-products', 'mobile.packs']);

test('legacy packs stay flat and category deletion preserves pack contents', function (string $categoryRoutes) {
    $user = User::factory()->create(['is_therapist' => true, 'license_status' => 'active', 'slug' => 'legacy-packs']);
    $product = Product::create(['user_id' => $user->id, 'name' => 'Séance', 'price' => 70, 'tax_rate' => 0]);
    $this->actingAs($user)->post(route('pack-products.store'), categorizedPackPayload($product))->assertSessionHasNoErrors();
    $pack = PackProduct::sole();
    expect($pack->product_category_id)->toBeNull();
    $this->get(route('therapist.show', $user->slug))->assertOk()->assertSee('Pack découverte')
        ->assertDontSee('data-testid="prestation-categories"', false);

    $category = $user->productCategories()->create(['name' => 'Catégorie temporaire']);
    $pack->update(['product_category_id' => $category->id]);
    $this->get(route($categoryRoutes.'.index'))->assertOk()->assertSee('1 pack');
    $this->delete(route($categoryRoutes.'.destroy', $category))->assertRedirect();
    expect($pack->fresh()->product_category_id)->toBeNull()
        ->and($pack->fresh()->items->sole()->quantity)->toBe(3)
        ->and((float) $pack->fresh()->price)->toBe(180.0);
})->with(['product-categories', 'mobile.product-categories']);

test('public categories combine packs and prestations and never reveal hidden packs', function () {
    $user = User::factory()->create(['is_therapist' => true, 'slug' => 'categorized-packs', 'accept_online_appointments' => false]);
    $mixed = $user->productCategories()->create(['name' => 'Mixte', 'display_order' => 10]);
    $packsOnly = $user->productCategories()->create(['name' => 'Packs uniquement', 'display_order' => 20]);
    $hidden = $user->productCategories()->create(['name' => 'Catégorie privée']);
    Product::create(['user_id' => $user->id, 'product_category_id' => $mixed->id, 'name' => 'Séance visible', 'price' => 60, 'tax_rate' => 0, 'visible_in_portal' => true]);
    foreach ([
        ['name' => 'Pack mixte', 'product_category_id' => $mixed->id],
        ['name' => 'Pack seul', 'product_category_id' => $packsOnly->id],
        ['name' => 'Pack non classé'],
        ['name' => 'Pack masqué', 'product_category_id' => $hidden->id, 'visible_in_portal' => false],
        ['name' => 'Pack inactif', 'product_category_id' => $hidden->id, 'is_active' => false],
    ] as $attributes) {
        PackProduct::create(array_replace(['user_id' => $user->id, 'price' => 120, 'tax_rate' => 0, 'is_active' => true, 'visible_in_portal' => true], $attributes));
    }
    $response = $this->get(route('therapist.show', $user->slug))->assertOk()
        ->assertSeeInOrder(['Pack non classé', 'Mixte', 'Pack mixte', 'Séance visible', 'Packs uniquement', 'Pack seul'])
        ->assertSee('1 prestation')->assertSee('1 pack')
        ->assertDontSee('Catégorie privée')->assertDontSee('Pack masqué')->assertDontSee('Pack inactif');
    expect(substr_count($response->getContent(), 'Pack mixte'))->toBe(1);
});
