<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;

function categoryTestProduct(User $therapist, array $overrides = []): Product
{
    return Product::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Séance découverte',
        'description' => 'Une prestation de démonstration.',
        'price' => 65,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => false,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'display_order' => 0,
    ], $overrides));
}

function categoryTestProductPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Nouvelle prestation',
        'description' => 'Description',
        'price' => 70,
        'tax_rate' => 0,
        'duration' => 60,
        'mode' => 'dans_le_cabinet',
        'can_be_booked_online' => 0,
        'collect_payment' => 0,
        'requires_emargement' => 0,
        'visible_in_portal' => 1,
        'price_visible_in_portal' => 1,
    ], $overrides);
}

test('a therapist can create and update their own prestation categories', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $this->actingAs($therapist)
        ->post(route('product-categories.store'), [
            'name' => 'Accompagnements',
            'description' => str_repeat('a', 500),
            'display_order' => 2,
        ])
        ->assertRedirect(route('product-categories.index'));

    $category = ProductCategory::sole();

    expect($category->user_id)->toBe($therapist->id)
        ->and($category->description)->toHaveLength(500)
        ->and($category->display_order)->toBe(2);

    $this->actingAs($therapist)
        ->put(route('product-categories.update', $category), [
            'name' => 'Soins du corps',
            'description' => 'Des soins adaptés à votre rythme.',
            'display_order' => 1,
        ])
        ->assertRedirect(route('product-categories.index'));

    expect($category->fresh()->name)->toBe('Soins du corps')
        ->and($category->fresh()->display_order)->toBe(1);

    $this->actingAs($therapist)
        ->get(route('product-categories.index'))
        ->assertOk()
        ->assertSee('Soins du corps');

    $this->actingAs($therapist)
        ->get(route('mobile.product-categories.index'))
        ->assertOk()
        ->assertSee('Soins du corps');

    $this->actingAs($therapist)
        ->get(route('products.create'))
        ->assertOk()
        ->assertSee('Soins du corps');

    $this->actingAs($therapist)
        ->get(route('mobile.products.create'))
        ->assertOk()
        ->assertSee('Soins du corps');
});

test('category descriptions are limited to 500 characters', function () {
    $therapist = User::factory()->create(['is_therapist' => true]);

    $this->actingAs($therapist)
        ->post(route('product-categories.store'), [
            'name' => 'Trop longue',
            'description' => str_repeat('a', 501),
        ])
        ->assertSessionHasErrors('description');

    expect(ProductCategory::count())->toBe(0);
});

test('a therapist cannot manage another therapists categories', function () {
    $owner = User::factory()->create(['is_therapist' => true]);
    $other = User::factory()->create(['is_therapist' => true]);
    $category = ProductCategory::create([
        'user_id' => $owner->id,
        'name' => 'Privée',
        'description' => 'Catégorie du premier praticien.',
    ]);

    $this->actingAs($other)
        ->put(route('product-categories.update', $category), [
            'name' => 'Modifiée',
            'description' => 'Tentative',
        ])
        ->assertForbidden();

    $this->actingAs($other)
        ->delete(route('product-categories.destroy', $category))
        ->assertForbidden();

    expect($category->fresh()->name)->toBe('Privée');
});

test('a prestation can only use a category owned by its therapist', function () {
    $therapist = User::factory()->create(['is_therapist' => true]);
    $other = User::factory()->create(['is_therapist' => true]);
    $ownCategory = ProductCategory::create(['user_id' => $therapist->id, 'name' => 'La mienne']);
    $foreignCategory = ProductCategory::create(['user_id' => $other->id, 'name' => 'Étrangère']);

    $this->actingAs($therapist)
        ->post(route('products.store'), categoryTestProductPayload([
            'product_category_id' => $foreignCategory->id,
        ]))
        ->assertSessionHasErrors('product_category_id');

    $this->actingAs($therapist)
        ->post(route('products.store'), categoryTestProductPayload([
            'product_category_id' => $ownCategory->id,
        ]))
        ->assertRedirect();

    expect(Product::sole()->product_category_id)->toBe($ownCategory->id);
});

test('deleting a category keeps its prestations and makes them uncategorized', function () {
    $therapist = User::factory()->create(['is_therapist' => true]);
    $category = ProductCategory::create(['user_id' => $therapist->id, 'name' => 'À supprimer']);
    $product = categoryTestProduct($therapist, ['product_category_id' => $category->id]);

    $this->actingAs($therapist)
        ->delete(route('product-categories.destroy', $category))
        ->assertRedirect(route('product-categories.index'));

    expect($product->fresh())->not->toBeNull()
        ->and($product->fresh()->product_category_id)->toBeNull();
});

test('the public portal shows closed category accordions and keeps uncategorized prestations flat', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'cabinet-categories-test',
        'company_name' => 'Cabinet Harmonie',
        'accept_online_appointments' => false,
    ]);
    $secondCategory = ProductCategory::create([
        'user_id' => $therapist->id,
        'name' => 'Ateliers collectifs',
        'description' => 'Des ateliers pour avancer ensemble.',
        'display_order' => 20,
    ]);
    $firstCategory = ProductCategory::create([
        'user_id' => $therapist->id,
        'name' => 'Accompagnements individuels',
        'description' => "Un espace personnalisé.\nÀ votre rythme.",
        'display_order' => 10,
    ]);
    $hiddenCategory = ProductCategory::create([
        'user_id' => $therapist->id,
        'name' => 'Catégorie sans contenu public',
        'display_order' => 0,
    ]);

    categoryTestProduct($therapist, [
        'name' => 'Bilan personnalisé',
        'product_category_id' => $firstCategory->id,
    ]);
    categoryTestProduct($therapist, [
        'name' => 'Atelier respiration',
        'product_category_id' => $secondCategory->id,
    ]);
    categoryTestProduct($therapist, [
        'name' => 'Massage bien-être',
        'product_category_id' => null,
    ]);
    categoryTestProduct($therapist, [
        'name' => 'Prestation masquée',
        'product_category_id' => $hiddenCategory->id,
        'visible_in_portal' => false,
    ]);

    $response = $this->get(route('therapist.show', $therapist->slug));

    $response->assertOk()
        ->assertSee('data-testid="prestation-categories"', false)
        ->assertSee('x-data="{ open: false }"', false)
        ->assertSeeInOrder(['Accompagnements individuels', 'Ateliers collectifs', 'Massage bien-être'])
        ->assertSee('Un espace personnalisé.')
        ->assertSee('data-testid="uncategorized-prestations"', false)
        ->assertDontSee('Autres prestations')
        ->assertDontSee('Catégorie sans contenu public')
        ->assertDontSee('Prestation masquée');

    $this->get(route('mobile.therapists.show', $therapist->slug))
        ->assertOk()
        ->assertSee('data-testid="prestation-categories"', false)
        ->assertSee('x-data="{ open: false }"', false)
        ->assertSee('Massage bien-être')
        ->assertDontSee('Autres prestations');
});
