<?php

use App\Models\PackProduct;
use App\Models\PackProductItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('pack forms distinguish same-name prestations by duration and TTC price', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $shortProduct = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Séance découverte',
        'price' => 50,
        'tax_rate' => 20,
        'duration' => 45,
    ]);
    $longProduct = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Séance découverte',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 90,
    ]);
    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack découverte',
        'price' => 200,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $shortProduct->id,
        'quantity' => 1,
        'sort_order' => 0,
    ]);

    $shortLabel = 'Séance découverte - 45 min - 60,00 € TTC';
    $longLabel = 'Séance découverte - 90 min - 80,00 € TTC';

    expect($shortProduct->pack_selection_label)->toBe($shortLabel)
        ->and($longProduct->pack_selection_label)->toBe($longLabel);

    foreach ([
        route('pack-products.create'),
        route('pack-products.edit', $pack),
    ] as $url) {
        $this->actingAs($therapist)
            ->get($url)
            ->assertOk()
            ->assertSee(json_encode($shortLabel), false)
            ->assertSee(json_encode($longLabel), false);
    }

    foreach ([
        route('mobile.packs.create'),
        route('mobile.packs.edit', $pack),
    ] as $url) {
        $this->actingAs($therapist)
            ->get($url)
            ->assertOk()
            ->assertSee($shortLabel)
            ->assertSee($longLabel);
    }
});

test('pack selection label handles prestations with missing duration or price', function () {
    $product = new Product([
        'name' => 'Prestation à préciser',
        'duration' => null,
        'price' => null,
        'tax_rate' => 0,
    ]);

    expect($product->pack_selection_label)
        ->toBe('Prestation à préciser - Durée non renseignée - Prix non renseigné');
});
