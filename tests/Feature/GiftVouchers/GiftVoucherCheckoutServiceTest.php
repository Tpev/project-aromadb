<?php

use App\Jobs\SendGiftVoucherEmailsJob;
use App\Models\GiftVoucher;
use App\Models\GiftVoucherOrder;
use App\Models\User;
use App\Services\GiftVoucherCheckoutService;
use Illuminate\Support\Facades\Queue;

test('checkout service finalizes a paid order and creates voucher plus accounting records', function () {
    Queue::fake();

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'company_name' => 'Cabinet Service Test',
        'gift_voucher_background_mode' => 'default',
        'gift_voucher_background_path' => null,
    ]);

    $order = GiftVoucherOrder::create([
        'user_id' => $therapist->id,
        'amount_cents' => 9900,
        'currency' => 'EUR',
        'buyer_name' => 'Acheteur Checkout',
        'buyer_email' => 'checkout-buyer@example.test',
        'buyer_phone' => '0600000000',
        'recipient_name' => 'Recipient Checkout',
        'recipient_email' => 'checkout-recipient@example.test',
        'message' => 'Message test',
        'status' => 'pending',
    ]);

    $voucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder(
        $order,
        'cs_test_checkout',
        'pi_test_checkout'
    );

    $order->refresh();
    $voucher->refresh();

    expect($order->status)->toBe('paid');
    expect($order->gift_voucher_id)->toBe($voucher->id);
    expect($order->sale_invoice_id)->not->toBeNull();
    expect($order->stripe_session_id)->toBe('cs_test_checkout');
    expect($order->stripe_payment_intent_id)->toBe('pi_test_checkout');

    expect($voucher->sale_channel)->toBe('online_stripe');
    expect($voucher->sale_status)->toBe('paid');
    expect($voucher->sale_invoice_id)->toBe($order->sale_invoice_id);
    expect($voucher->original_amount_cents)->toBe(9900);
    expect($voucher->remaining_amount_cents)->toBe(9900);

    $this->assertDatabaseHas('invoices', [
        'id' => $order->sale_invoice_id,
        'user_id' => $therapist->id,
        'status' => 'Payée',
    ]);

    $this->assertDatabaseHas('receipts', [
        'invoice_id' => $order->sale_invoice_id,
        'source' => 'manual',
        'note' => 'Paiement bon cadeau ' . $voucher->code,
    ]);

    Queue::assertPushed(SendGiftVoucherEmailsJob::class, function ($job) use ($voucher) {
        return $job->voucherId === $voucher->id;
    });
});

test('checkout service is idempotent for an already paid order', function () {
    Queue::fake();

    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $firstVoucher = GiftVoucher::create([
        'user_id' => $therapist->id,
        'code' => 'AM-TEST-VOUCHER-0001',
        'original_amount_cents' => 5000,
        'remaining_amount_cents' => 5000,
        'currency' => 'EUR',
        'is_active' => true,
        'buyer_name' => 'Buyer Existing',
        'buyer_email' => 'buyer-existing@example.test',
        'source' => 'stripe',
        'sale_channel' => 'online_stripe',
        'sale_status' => 'paid',
    ]);

    $order = GiftVoucherOrder::create([
        'user_id' => $therapist->id,
        'amount_cents' => 5000,
        'currency' => 'EUR',
        'buyer_name' => 'Buyer Existing',
        'buyer_email' => 'buyer-existing@example.test',
        'status' => 'paid',
        'gift_voucher_id' => $firstVoucher->id,
    ]);

    $resolvedVoucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder(
        $order,
        'cs_idempotent',
        'pi_idempotent'
    );

    expect($resolvedVoucher->id)->toBe($firstVoucher->id);
    expect(GiftVoucher::count())->toBe(1);
    Queue::assertNothingPushed();
});
