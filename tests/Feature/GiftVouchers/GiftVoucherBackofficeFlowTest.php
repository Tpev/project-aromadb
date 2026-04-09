<?php

use App\Jobs\SendGiftVoucherEmailsJob;
use App\Models\GiftVoucher;
use App\Models\User;
use App\Services\StripeAccountGuard;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

test('therapist can create a gift voucher with optional sale invoice', function () {
    Queue::fake();
    $this->withoutMiddleware();

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'company_name' => 'Cabinet Demo',
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.gift-vouchers.store'), [
        'amount_eur' => 120,
        'expires_at' => now()->addMonths(6)->toDateString(),
        'buyer_name' => 'Acheteur Test',
        'buyer_email' => 'buyer@example.test',
        'buyer_phone' => '0601020304',
        'recipient_name' => 'Destinataire Test',
        'recipient_email' => 'recipient@example.test',
        'message' => 'Profite bien de ce bon cadeau',
        'create_sale_invoice' => '1',
        'payment_method' => 'card',
    ]);

    $response->assertSessionHas('success');

    $voucher = GiftVoucher::query()->firstOrFail();
    $response->assertRedirect(route('pro.gift-vouchers.show', $voucher));

    expect($voucher->user_id)->toBe($therapist->id);
    expect($voucher->sale_channel)->toBe('offline_manual');
    expect($voucher->sale_status)->toBe('paid');
    expect($voucher->sale_invoice_id)->not->toBeNull();
    expect($voucher->buyer_phone)->toBe('0601020304');

    $this->assertDatabaseHas('invoices', [
        'id' => $voucher->sale_invoice_id,
        'user_id' => $therapist->id,
        'status' => 'Payée',
    ]);

    $this->assertDatabaseHas('receipts', [
        'invoice_id' => $voucher->sale_invoice_id,
        'source' => 'manual',
        'note' => 'Paiement bon cadeau ' . $voucher->code,
    ]);

    Queue::assertPushed(SendGiftVoucherEmailsJob::class, function ($job) use ($voucher) {
        return $job->voucherId === $voucher->id;
    });
});

test('therapist can update gift voucher global settings', function () {
    $this->withoutMiddleware();

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'gift_voucher_online_enabled' => false,
        'gift_voucher_background_mode' => 'custom_upload',
        'gift_voucher_background_path' => 'gift-vouchers/backgrounds/demo/background.webp',
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_online_enabled' => '1',
        'gift_voucher_background_mode' => 'default',
        'remove_gift_voucher_background' => '1',
    ]);

    $response->assertSessionHas('success');

    $therapist->refresh();
    expect($therapist->gift_voucher_online_enabled)->toBeTrue();
    expect($therapist->gift_voucher_background_mode)->toBe('default');
    expect($therapist->gift_voucher_background_path)->toBeNull();
});

test('therapist can disable online gift voucher purchase without reuploading the custom background', function () {
    $this->withoutMiddleware();

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'gift_voucher_online_enabled' => true,
        'gift_voucher_background_mode' => 'custom_upload',
        'gift_voucher_background_path' => 'gift-vouchers/backgrounds/demo/background.jpg',
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'custom_upload',
    ]);

    $response->assertSessionHas('success');

    $therapist->refresh();

    expect($therapist->gift_voucher_online_enabled)->toBeFalse();
    expect($therapist->gift_voucher_background_mode)->toBe('custom_upload');
    expect($therapist->gift_voucher_background_path)->toBe('gift-vouchers/backgrounds/demo/background.jpg');
});

test('therapist can upload a custom global gift voucher background', function () {
    $this->withoutMiddleware();
    Storage::fake('public');

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'gift_voucher_online_enabled' => true,
        'gift_voucher_background_mode' => 'default',
        'gift_voucher_background_path' => null,
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_online_enabled' => '1',
        'gift_voucher_background_mode' => 'custom_upload',
        'gift_voucher_background' => UploadedFile::fake()->image('voucher-background.png', 1800, 2400),
    ]);

    $response->assertSessionHas('success');

    $therapist->refresh();

    expect($therapist->gift_voucher_background_mode)->toBe('custom_upload');
    expect($therapist->gift_voucher_background_path)->not->toBeNull();
    expect(str_ends_with((string) $therapist->gift_voucher_background_path, 'background.jpg'))->toBeTrue();
    Storage::disk('public')->assertExists((string) $therapist->gift_voucher_background_path);
});

test('public gift voucher checkout page is reachable when online purchase is enabled and stripe guard is ready', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-voucher-checkout',
        'gift_voucher_online_enabled' => true,
        'stripe_account_id' => 'acct_ready',
    ]);

    $this->mock(StripeAccountGuard::class, function ($mock) use ($therapist) {
        $mock->shouldReceive('canAcceptOnlineCheckout')
            ->once()
            ->withArgs(function (User $user) use ($therapist) {
                return $user->id === $therapist->id;
            })
            ->andReturn(true);
    });

    $response = $this->get(route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]));

    $response->assertOk();
    $response->assertSee('Payer et envoyer le bon cadeau');
});

test('public gift voucher checkout page returns 404 when stripe guard is not ready', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-voucher-checkout-404',
        'gift_voucher_online_enabled' => true,
        'stripe_account_id' => 'acct_not_ready',
    ]);

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('canAcceptOnlineCheckout')
            ->once()
            ->andReturn(false);
    });

    $response = $this->get(route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]));

    $response->assertNotFound();
});

test('starter therapist cannot access gift vouchers backoffice', function () {
    $this->withoutMiddleware();

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_product' => 'new_starter_mensuelle',
    ]);

    $response = $this->actingAs($therapist)->get(route('pro.gift-vouchers.index'));

    $response->assertForbidden();
});

test('public gift voucher checkout returns 404 when therapist plan does not include the feature', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-voucher-checkout-starter',
        'license_product' => 'new_starter_mensuelle',
        'gift_voucher_online_enabled' => true,
        'stripe_account_id' => 'acct_ready',
    ]);

    $this->mock(StripeAccountGuard::class, function ($mock) {
        $mock->shouldReceive('canAcceptOnlineCheckout')
            ->never();
    });

    $response = $this->get(route('gift-vouchers.checkout.show', ['slug' => $therapist->slug]));

    $response->assertNotFound();
});
