<?php

use App\Models\ClientProfile;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createInstallmentPurchaseFor(User $therapist, array $purchaseOverrides = []): PackPurchase
{
    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Test',
        'email' => 'client-' . uniqid() . '@example.test',
    ]);

    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack test',
        'description' => 'Pack test',
        'price' => 120.00,
        'tax_rate' => 0.00,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'installments_enabled' => true,
        'allowed_installments' => [3],
    ]);

    return PackPurchase::create(array_merge([
        'user_id' => $therapist->id,
        'pack_product_id' => $pack->id,
        'client_profile_id' => $client->id,
        'status' => 'active',
        'payment_mode' => 'installments',
        'payment_state' => 'active',
        'installments_total' => 3,
        'installments_paid' => 1,
        'stripe_subscription_id' => 'sub_' . uniqid(),
    ], $purchaseOverrides));
}

test('subscription cancel route is forbidden for non owner therapist', function () {
    $owner = User::factory()->create([
        'is_therapist' => true,
        'stripe_account_id' => 'acct_owner',
    ]);
    $another = User::factory()->create([
        'is_therapist' => true,
        'stripe_account_id' => 'acct_other',
    ]);

    $purchase = createInstallmentPurchaseFor($owner);

    $this->actingAs($another)
        ->post(route('pack-purchases.subscription.cancel', $purchase), [
            'cancel_mode' => 'end_of_period',
        ])
        ->assertForbidden();
});

test('subscription cancel route rejects non installment purchases', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'stripe_account_id' => 'acct_owner',
    ]);

    $purchase = createInstallmentPurchaseFor($therapist, [
        'payment_mode' => 'one_time',
        'payment_state' => 'completed',
    ]);

    $response = $this->actingAs($therapist)
        ->post(route('pack-purchases.subscription.cancel', $purchase), [
            'cancel_mode' => 'end_of_period',
        ]);

    $response->assertSessionHas('error');
});

test('subscription cancel route rejects completed installment purchases', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'stripe_account_id' => 'acct_owner',
    ]);

    $purchase = createInstallmentPurchaseFor($therapist, [
        'payment_state' => 'completed',
    ]);

    $response = $this->actingAs($therapist)
        ->post(route('pack-purchases.subscription.cancel', $purchase), [
            'cancel_mode' => 'end_of_period',
        ]);

    $response->assertSessionHas('error');
});

test('subscription cancel route rejects purchase without stripe subscription id', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'stripe_account_id' => 'acct_owner',
    ]);

    $purchase = createInstallmentPurchaseFor($therapist, [
        'stripe_subscription_id' => null,
    ]);

    $response = $this->actingAs($therapist)
        ->post(route('pack-purchases.subscription.cancel', $purchase), [
            'cancel_mode' => 'end_of_period',
        ]);

    $response->assertSessionHas('error');
});

