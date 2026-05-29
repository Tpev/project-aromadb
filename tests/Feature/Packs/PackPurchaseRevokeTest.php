<?php

use App\Models\ClientProfile;
use App\Models\PackProduct;
use App\Models\PackProductItem;
use App\Models\PackPurchase;
use App\Models\PackPurchaseItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAssignedPackPurchaseForRevoke(User $therapist): PackPurchase
{
    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Pack',
        'email' => 'client-pack-' . uniqid() . '@example.test',
    ]);

    $product = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Massage test',
        'description' => 'Prestation test',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 60,
    ]);

    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack test',
        'description' => 'Pack test',
        'price' => 120,
        'tax_rate' => 0,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    PackProductItem::create([
        'pack_product_id' => $pack->id,
        'product_id' => $product->id,
        'quantity' => 2,
        'sort_order' => 0,
    ]);

    $purchase = PackPurchase::create([
        'user_id' => $therapist->id,
        'pack_product_id' => $pack->id,
        'client_profile_id' => $client->id,
        'purchased_at' => now(),
        'status' => 'active',
    ]);

    PackPurchaseItem::create([
        'pack_purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity_total' => 2,
        'quantity_remaining' => 2,
    ]);

    return $purchase;
}

test('therapist can revoke an assigned client pack without deleting its history', function () {
    $therapist = User::factory()->create(['is_therapist' => true]);
    $purchase = createAssignedPackPurchaseForRevoke($therapist);

    $response = $this->actingAs($therapist)
        ->delete(route('pack-purchases.revoke', $purchase));

    $response->assertRedirect();

    $purchase->refresh();

    expect($purchase->status)->toBe('cancelled');
    expect($purchase->items)->toHaveCount(1);
    expect((int) $purchase->items->first()->quantity_remaining)->toBe(2);
});

test('therapist cannot revoke another therapist client pack', function () {
    $owner = User::factory()->create(['is_therapist' => true]);
    $otherTherapist = User::factory()->create(['is_therapist' => true]);
    $purchase = createAssignedPackPurchaseForRevoke($owner);

    $this->actingAs($otherTherapist)
        ->delete(route('pack-purchases.revoke', $purchase))
        ->assertForbidden();

    expect($purchase->fresh()->status)->toBe('active');
});
