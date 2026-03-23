<?php

use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\User;
use App\Services\StripeAccountGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

function createTherapistPack(array $therapistOverrides = [], array $packOverrides = []): array
{
    $therapist = User::factory()->create(array_merge([
        'is_therapist' => true,
        'slug' => 'therapist-checkout-' . uniqid(),
        'stripe_account_id' => null,
    ], $therapistOverrides));

    $pack = PackProduct::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Pack découverte',
        'description' => 'Pack de test',
        'price' => 120.00,
        'tax_rate' => 0.00,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'installments_enabled' => true,
        'allowed_installments' => [3, 6],
    ], $packOverrides));

    return [$therapist, $pack];
}

test('installments checkout is rejected when stripe connect is not ready', function () {
    [$therapist, $pack] = createTherapistPack();

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(['ready' => false]);
    });

    $response = $this->post(route('public.checkout.store', ['slug' => $therapist->slug]), [
        'item' => 'pack:' . $pack->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'payment_choice' => 'installments',
        'installment_count' => 3,
    ]);

    $response->assertSessionHasErrors('payment');
});

test('installments checkout rejects installment count not allowed by product', function () {
    [$therapist, $pack] = createTherapistPack([
        'stripe_account_id' => 'acct_ready_123',
    ], [
        'allowed_installments' => [3],
    ]);

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(['ready' => true]);
    });

    $response = $this->post(route('public.checkout.store', ['slug' => $therapist->slug]), [
        'item' => 'pack:' . $pack->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'payment_choice' => 'installments',
        'installment_count' => 2,
    ]);

    $response->assertSessionHasErrors('installment_count');
});

test('one-time checkout without stripe marks purchase active', function () {
    [$therapist, $pack] = createTherapistPack();

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('status')->once()->andReturn(['ready' => false]);
    });

    $response = $this->post(route('public.checkout.store', ['slug' => $therapist->slug]), [
        'item' => 'pack:' . $pack->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice@example.test',
        'payment_choice' => 'one_time',
    ]);

    $response->assertRedirect(route('therapist.show', $therapist->slug));

    $purchase = PackPurchase::query()->firstOrFail();
    expect($purchase->status)->toBe('active');
    expect($purchase->purchased_at)->not->toBeNull();

    if (Schema::hasColumn('pack_purchases', 'payment_state')) {
        expect($purchase->payment_state)->toBe('completed');
    }
});

