<?php

use App\Jobs\SendGiftVoucherEmailsJob;
use App\Mail\GiftVoucherBuyerMail;
use App\Mail\GiftVoucherRecipientMail;
use App\Models\GiftVoucher;
use App\Models\GiftVoucherOrder;
use App\Models\User;
use App\Services\GiftVoucherCheckoutService;
use App\Services\GiftVoucherPdfService;
use App\Services\GiftVoucherRedeemService;
use App\Services\StripeAccountGuard;
use App\Services\StripePurchaseWebhookService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Stripe\ApiRequestor;
use Stripe\HttpClient\ClientInterface;

beforeEach(function () {
    Queue::fake();
    config(['services.stripe.secret' => 'sk_test_local']);
    $this->stripeHttp = Mockery::mock(ClientInterface::class);
    ApiRequestor::setHttpClient($this->stripeHttp);
    $this->therapist = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle',
        'slug' => 'voucher-validity', 'stripe_account_id' => 'acct_validity',
        'gift_voucher_online_enabled' => true, 'gift_voucher_background_mode' => 'default',
    ]);
    $this->mock(StripeAccountGuard::class)->shouldReceive('canAcceptOnlineCheckout')->andReturn(true);
});

afterEach(function () {
    ApiRequestor::setHttpClient(new \Stripe\HttpClient\CurlClient);
});

function validityOrder($context, array $attributes = []): GiftVoucherOrder
{
    return GiftVoucherOrder::create(array_merge([
        'user_id' => $context->therapist->id, 'amount_cents' => 5000, 'currency' => 'EUR',
        'buyer_name' => 'Alice Martin', 'buyer_email' => 'alice@example.test',
        'recipient_name' => 'Lucie Bernard', 'recipient_email' => 'lucie@example.test',
        'status' => 'pending', 'stripe_session_id' => 'cs_validity',
    ], $attributes));
}

test('therapist validity settings allow months or unlimited and old form submissions preserve the setting', function () {
    $url = route('pro.gift-vouchers.settings.update');
    $base = ['gift_voucher_background_mode' => 'default', 'gift_voucher_online_enabled' => '1'];
    expect($this->therapist->gift_voucher_validity_months)->toBeNull();
    $this->actingAs($this->therapist)->get(route('pro.gift-vouchers.index'))->assertOk()->assertSee('Validité des bons achetés en ligne');
    $this->post($url, $base + ['gift_voucher_validity_months' => '12'])->assertSessionHasNoErrors();
    expect($this->therapist->fresh()->gift_voucher_validity_months)->toBe(12);
    $this->post($url, $base)->assertSessionHasNoErrors();
    expect($this->therapist->fresh()->gift_voucher_validity_months)->toBe(12);
    $this->post($url, $base + ['gift_voucher_validity_months' => ''])->assertSessionHasNoErrors();
    expect($this->therapist->fresh()->gift_voucher_validity_months)->toBeNull();
});

test('invalid validity settings cannot overwrite a valid policy', function ($value) {
    $this->therapist->update(['gift_voucher_validity_months' => 12]);
    $this->actingAs($this->therapist)->post(route('pro.gift-vouchers.settings.update'), [
        'gift_voucher_background_mode' => 'default', 'gift_voucher_validity_months' => $value,
    ])->assertSessionHasErrors('gift_voucher_validity_months');
    expect($this->therapist->fresh()->gift_voucher_validity_months)->toBe(12);
})->with([0, -1, 1.5, 121, 'invalid']);

test('checkout displays the therapist policy and ignores buyer supplied expiry and duration', function ($months) {
    $this->therapist->update(['gift_voucher_validity_months' => $months]);
    $url = route('gift-vouchers.checkout.show', $this->therapist->slug);
    $this->get($url)->assertOk()->assertSee($months ? $months.' mois à compter de l’achat' : 'Sans date d’expiration')
        ->assertDontSee('name="expires_at"', false)->assertDontSee('name="validity_months"', false);
    $this->stripeHttp->shouldReceive('request')->once()->andReturn([
        json_encode(['id' => 'cs_validity', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.test/validity']), 200, [],
    ]);
    $this->post(route('gift-vouchers.checkout.store', $this->therapist->slug), [
        'amount_eur' => 50, 'buyer_name' => 'Alice Martin', 'buyer_email' => 'alice@example.test',
        'expires_at' => '2099-01-01', 'validity_months' => 120, 'gift_voucher_validity_months' => 120,
    ])->assertRedirect('https://checkout.stripe.test/validity');
    $order = GiftVoucherOrder::sole();
    expect($order->validity_months)->toBe($months)->and($order->expires_at)->toBeNull()
        ->and($order->purchased_at)->toBeNull()->and(GiftVoucher::count())->toBe(0);
    $this->therapist->update(['gift_voucher_validity_months' => 24]);
    $this->travelTo(Carbon::parse('2026-11-10 14:30:00', 'Europe/Paris'));
    $voucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder($order, 'cs_validity');
    expect($voucher->expires_at?->toDateTimeString())->toBe($months ? '2027-11-10 23:59:59' : null);
})->with([12, null]);

test('expiry starts at payment and handles month ends leap years and Paris dates', function ($purchase, $months, $expected) {
    $order = validityOrder($this, ['validity_months' => $months]);
    $paidAt = Carbon::parse($purchase);
    $this->travelTo($paidAt->copy()->timezone(config('app.timezone'))->addDays(3));
    $voucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder($order, 'cs_validity', 'pi_validity', $paidAt->timestamp);
    expect($voucher->expires_at->toDateTimeString())->toBe($expected)
        ->and($order->fresh()->expires_at->toDateTimeString())->toBe($expected)
        ->and($order->fresh()->purchased_at->timestamp)->toBe($paidAt->timestamp);
    $this->travelTo(Carbon::parse($expected, 'Europe/Paris'));
    expect($voucher->isUsable())->toBeTrue();
    $this->travel(1)->seconds();
    expect($voucher->isExpired())->toBeTrue();
    expect(fn () => app(GiftVoucherRedeemService::class)->redeem($voucher, 100, actorUserId: $this->therapist->id))
        ->toThrow(ValidationException::class, 'Ce bon cadeau est expiré.');
})->with([
    ['2026-01-31 10:00:00 Europe/Paris', 1, '2026-02-28 23:59:59'],
    ['2028-02-29 10:00:00 Europe/Paris', 12, '2029-02-28 23:59:59'],
    ['2028-01-31 10:00:00 Europe/Paris', 1, '2028-02-29 23:59:59'],
    ['2026-07-31 22:30:00 UTC', 6, '2027-02-01 23:59:59'],
]);

test('legacy pending orders keep their saved expiry even after the therapist enables a policy', function ($legacyExpiry) {
    $this->therapist->update(['gift_voucher_validity_months' => 12]);
    $order = validityOrder($this, ['expires_at' => $legacyExpiry]);
    $voucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder($order, 'cs_validity');
    expect($voucher->expires_at?->toDateTimeString())->toBe($legacyExpiry)
        ->and($order->fresh()->validity_months)->toBeNull();
})->with([null, '2027-02-14 00:00:00']);

test('webhook fulfillment uses the payment event date and retries cannot extend validity', function () {
    $order = validityOrder($this, ['validity_months' => 6]);
    $paidAt = Carbon::parse('2026-07-31 12:00:00', 'Europe/Paris');
    $this->travelTo($paidAt->copy()->addDays(5));
    $event = \Stripe\Event::constructFrom([
        'id' => 'evt_validity', 'type' => 'checkout.session.completed', 'created' => $paidAt->timestamp,
        'data' => ['object' => ['id' => 'cs_validity', 'payment_status' => 'paid', 'payment_intent' => 'pi_validity',
            'metadata' => ['purchase_kind' => 'gift_voucher', 'gift_voucher_order_id' => $order->id]]],
    ]);
    $service = app(StripePurchaseWebhookService::class);
    expect($service->handleEvent($event, 'acct_validity'))->toBeTrue();
    $this->therapist->update(['gift_voucher_validity_months' => 24]);
    $this->travel(10)->days();
    expect($service->handleEvent($event, 'acct_validity'))->toBeTrue();
    app(GiftVoucherCheckoutService::class)->finalizePaidOrder($order, 'cs_validity', 'pi_validity', now()->timestamp);
    expect(GiftVoucher::sole()->expires_at->toDateTimeString())->toBe('2027-01-31 23:59:59')
        ->and($order->fresh()->purchased_at->timestamp)->toBe($paidAt->timestamp);
    Queue::assertPushed(SendGiftVoucherEmailsJob::class, 1);
    $this->assertDatabaseCount('invoices', 1);
});

test('a policy changed while the buyer has the form open must be reviewed before checkout', function () {
    $this->therapist->update(['gift_voucher_validity_months' => 6]);
    $url = route('gift-vouchers.checkout.show', $this->therapist->slug);
    $this->from($url)->post(route('gift-vouchers.checkout.store', $this->therapist->slug), [
        'amount_eur' => 50, 'buyer_name' => 'Alice Martin', 'buyer_email' => 'alice@example.test',
        'validity_months_displayed' => 12,
    ])->assertRedirect($url)->assertSessionHasErrors('validity_months_displayed');
    expect(GiftVoucherOrder::count())->toBe(0);
    $this->get($url)->assertOk()->assertSee('6 mois à compter de l’achat');
});

test('an unpaid confirmation never starts validity or creates a voucher', function () {
    $order = validityOrder($this, ['validity_months' => 12]);
    $event = \Stripe\Event::constructFrom([
        'id' => 'evt_validity_unpaid', 'type' => 'checkout.session.completed', 'created' => now()->timestamp,
        'data' => ['object' => ['id' => 'cs_validity', 'payment_status' => 'unpaid',
            'metadata' => ['purchase_kind' => 'gift_voucher', 'gift_voucher_order_id' => $order->id]]],
    ]);
    expect(app(StripePurchaseWebhookService::class)->handleEvent($event, 'acct_validity'))->toBeFalse()
        ->and($order->fresh()->purchased_at)->toBeNull()->and($order->fresh()->expires_at)->toBeNull()
        ->and(GiftVoucher::count())->toBe(0);
    Queue::assertNothingPushed();
});

test('return and reconciliation recover the original payment date without relying on checkout start', function ($path) {
    $order = validityOrder($this, ['validity_months' => 1]);
    $paidAt = Carbon::parse('2026-01-31 15:00:00', 'Europe/Paris');
    $this->travelTo($paidAt->copy()->addDays(10));
    $session = \Stripe\Checkout\Session::constructFrom([
        'id' => 'cs_validity', 'object' => 'checkout.session', 'payment_status' => 'paid',
        'created' => $paidAt->copy()->subDay()->timestamp,
        'payment_intent' => ['id' => 'pi_validity', 'object' => 'payment_intent',
            'latest_charge' => ['id' => 'ch_validity', 'object' => 'charge', 'paid' => true, 'created' => $paidAt->timestamp]],
    ]);
    $this->stripeHttp->shouldReceive('request')->once()->andReturn([$session->toJSON(), 200, []]);
    if ($path === 'return') {
        $this->get(route('gift-vouchers.checkout.success', ['session_id' => 'cs_validity', 'account_id' => 'acct_validity']))
            ->assertRedirect(route('therapist.show', $this->therapist->slug))->assertSessionHas('success');
    } else {
        $this->artisan('gift-vouchers:reconcile-paid-stripe-orders')->assertSuccessful();
    }
    expect($order->fresh()->purchased_at->timestamp)->toBe($paidAt->timestamp)
        ->and(GiftVoucher::sole()->expires_at->toDateTimeString())->toBe('2026-02-28 23:59:59');
})->with(['return', 'reconciliation']);

test('the saved date is shown in the voucher PDF and both emails', function () {
    $order = validityOrder($this, ['validity_months' => 12]);
    $voucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder($order, 'cs_validity', null,
        Carbon::parse('2026-11-03 10:00:00', 'Europe/Paris')->timestamp);
    $pdf = app(GiftVoucherPdfService::class)->renderPdf($voucher);
    expect($pdf)->toStartWith('%PDF');
    expect(view('pdf.gift-voucher', ['voucher' => $voucher, 'therapist' => $this->therapist, 'portalUrl' => '/pro/voucher-validity', 'qrImageSrc' => null])->render())
        ->toContain('03/11/2027');
    expect((new GiftVoucherBuyerMail($voucher, $pdf))->render())->toContain('03/11/2027');
    expect((new GiftVoucherRecipientMail($voucher, $pdf))->render())->toContain('03/11/2027');
});

test('manual vouchers keep the explicit therapist expiry regardless of online policy', function () {
    $this->therapist->update(['gift_voucher_validity_months' => 12]);
    $this->actingAs($this->therapist)->post(route('pro.gift-vouchers.store'), [
        'amount_eur' => 50, 'buyer_email' => 'manual@example.test', 'expires_at' => '2028-06-30',
    ])->assertSessionHasNoErrors();
    expect(GiftVoucher::sole()->expires_at->toDateString())->toBe('2028-06-30');
});

test('the additive migration leaves legacy vouchers orders and expiry dates intact', function () {
    $order = validityOrder($this, ['expires_at' => '2027-03-15 00:00:00']);
    $voucher = app(GiftVoucherCheckoutService::class)->finalizePaidOrder($order, 'cs_validity');
    $migration = require database_path('migrations/2026_11_03_210000_add_online_gift_voucher_validity.php');
    $migration->down();
    $migration->up();
    expect($voucher->fresh()->expires_at->toDateTimeString())->toBe('2027-03-15 00:00:00')
        ->and($order->fresh()->expires_at->toDateTimeString())->toBe('2027-03-15 00:00:00')
        ->and($order->fresh()->status)->toBe('paid')
        ->and($this->therapist->fresh()->gift_voucher_validity_months)->toBeNull();
});
