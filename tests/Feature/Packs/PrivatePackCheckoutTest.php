<?php

use App\Mail\DigitalTrainingAccessMail;
use App\Models\ClientProfile;
use App\Models\DigitalTraining;
use App\Models\PackProduct;
use App\Models\PackPurchase;
use App\Models\Product;
use App\Models\PurchaseInstallment;
use App\Models\Receipt;
use App\Models\StripeWebhookEvent;
use App\Models\User;
use App\Services\PackDigitalTrainingAccessService;
use App\Services\PackPurchaseInvoicingService;
use App\Services\StripeAccountGuard;
use App\Services\StripePurchaseWebhookService;
use Illuminate\Support\Facades\Mail;
use Stripe\ApiRequestor;
use Stripe\Checkout\Session as StripeSession;
use Stripe\HttpClient\ClientInterface;

beforeEach(function () {
    Mail::fake();
    config(['services.stripe.secret' => 'sk_test_local']);
    $this->stripeHttp = Mockery::mock(ClientInterface::class);
    ApiRequestor::setHttpClient($this->stripeHttp);
    $this->therapist = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle',
        'slug' => 'private-pack', 'stripe_account_id' => 'acct_private',
    ]);
    $this->pack = PackProduct::create([
        'user_id' => $this->therapist->id, 'name' => 'Accompagnement confidentiel',
        'description' => "Trois séances pour vous accompagner.\nUn parcours personnalisé.",
        'price' => 180, 'tax_rate' => 0, 'is_active' => true, 'visible_in_portal' => false,
        'private_checkout_enabled' => true, 'installments_enabled' => true, 'allowed_installments' => [3, 6],
    ]);
    $this->product = Product::create(['user_id' => $this->therapist->id, 'name' => 'Séance incluse', 'duration' => 60, 'price' => 60, 'tax_rate' => 0]);
    $this->pack->items()->create(['product_id' => $this->product->id, 'quantity' => 3, 'sort_order' => 0]);
    $this->training = DigitalTraining::create([
        'user_id' => $this->therapist->id, 'title' => 'Formation incluse', 'slug' => 'private-pack-training',
        'status' => 'published', 'access_type' => 'private', 'is_free' => false, 'price_cents' => 5000,
    ]);
    $this->pack->digitalTrainings()->attach($this->training);
    $this->checkoutPayload = ['first_name' => 'Camille', 'last_name' => 'Test', 'email' => 'camille@example.test', 'payment_choice' => 'one_time'];
    $this->url = route('packs.private.show', $this->pack->private_checkout_token);
});

afterEach(function () {
    ApiRequestor::setHttpClient(new \Stripe\HttpClient\CurlClient);
});

function privatePackStripeReady($test, bool $ready = true): void
{
    $guard = Mockery::mock(StripeAccountGuard::class);
    $guard->shouldReceive('status')->andReturn(['ready' => $ready]);
    app()->instance(StripeAccountGuard::class, $guard);
}

test('private link presents a hidden pack and its contents without publishing it', function () {
    privatePackStripeReady($this);
    $this->get(route('public.checkout.show', ['slug' => $this->therapist->slug, 'item' => 'pack:'.$this->pack->id]))->assertNotFound();
    $this->get($this->url)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('Referrer-Policy', 'no-referrer')->assertSee('Accompagnement confidentiel')
        ->assertSee('Un parcours personnalisé.')->assertSee('Séance incluse')->assertSee('Formation incluse')
        ->assertSee('180,00')->assertSee('name="payment_choice"', false)->assertDontSee('id="item-select"', false);
    expect($this->pack->fresh()->visible_in_portal)->toBeFalse();
});

test('inactive disabled and unknown private links cannot be used', function (string $change) {
    if ($change === 'inactive') {
        $this->pack->update(['is_active' => false]);
    }
    if ($change === 'disabled') {
        $this->pack->update(['private_checkout_enabled' => false]);
    }
    $url = $change === 'unknown' ? route('packs.private.show', str_repeat('a', 64)) : $this->url;
    $this->get($url)->assertNotFound();
    $this->post($url, $this->checkoutPayload)->assertNotFound();
    $this->assertDatabaseCount('pack_purchases', 0);
})->with(['inactive', 'disabled', 'unknown']);

test('private purchase refuses unavailable stripe before creating a client or granting access', function () {
    privatePackStripeReady($this, false);
    $this->post($this->url, $this->checkoutPayload)->assertSessionHasErrors('payment');
    $this->assertDatabaseCount('client_profiles', 0);
    $this->assertDatabaseCount('pack_purchases', 0);
    $this->assertDatabaseCount('digital_training_enrollments', 0);
    Mail::assertNothingOutgoing();
});

test('only configured installment choices can be purchased', function () {
    privatePackStripeReady($this);
    $this->post($this->url, array_replace($this->checkoutPayload, ['payment_choice' => 'installments', 'installment_count' => 2]))
        ->assertSessionHasErrors('installment_count');
    $this->assertDatabaseCount('pack_purchases', 0);
});

test('one time and installment checkout bind the server selected pack and connected account', function (string $choice) {
    privatePackStripeReady($this);
    $this->stripeHttp->shouldReceive('request')->once()->andReturnUsing(function ($method, $url, $headers, $params) use ($choice) {
        expect($method)->toBe('post')->and($url)->toEndWith('/v1/checkout/sessions')
            ->and(implode(' ', $headers))->toContain('acct_private')
            ->and($params['metadata']['private_pack_id'])->toBe((string) $this->pack->id)
            ->and($params['cancel_url'])->toBe(route('packs.private.cancel', $this->pack->private_checkout_token))
            ->and($params['mode'])->toBe($choice === 'installments' ? 'subscription' : 'payment')
            ->and($params['line_items'][0]['price_data']['unit_amount'])->toBe($choice === 'installments' ? 6000 : 18000);

        return [json_encode(['id' => 'cs_private', 'object' => 'checkout.session', 'url' => 'https://checkout.stripe.com/test_private']), 200, []];
    });
    $this->post($this->url, array_replace($this->checkoutPayload, ['item' => 'pack:999999', 'payment_choice' => $choice, 'installment_count' => 3]))
        ->assertRedirect('https://checkout.stripe.com/test_private')->assertSessionHasNoErrors();
    $purchase = PackPurchase::firstOrFail();
    expect($purchase->pack_product_id)->toBe($this->pack->id)->and($purchase->status)->toBe('pending')
        ->and($purchase->stripe_session_id)->toBe('cs_private')->and($purchase->items->first()->quantity_remaining)->toBe(3);
    $this->assertDatabaseCount('digital_training_enrollments', 0);
})->with(['one_time', 'installments']);

test('private link can be managed from desktop and mobile without changing portal visibility', function (string $channel) {
    $prefix = $channel === 'mobile' ? 'mobile.packs.' : 'pack-products.';
    $this->actingAs($this->therapist)->get(route($prefix.'create'))->assertOk()->assertSee('name="private_checkout_enabled"', false);
    $this->get(route($prefix.'edit', $this->pack))->assertOk()->assertSee($this->pack->private_checkout_token);
    $this->get(route($prefix.'show', $this->pack))->assertOk()->assertSee($this->pack->private_checkout_token);
    $payload = [
        'name' => $this->pack->name, 'description' => $this->pack->description, 'price' => 180, 'tax_rate' => 0,
        'is_active' => 1, 'visible_in_portal' => 0, 'price_visible_in_portal' => 1, 'installments_enabled' => 1,
        'allowed_installments' => [3, 6], 'items' => [['product_id' => $this->product->id, 'quantity' => 3]],
    ];
    $token = $this->pack->private_checkout_token;
    foreach ([0, 1] as $enabled) {
        $this->put(route($prefix.'update', $this->pack), $payload + ['private_checkout_enabled' => $enabled])->assertSessionHasNoErrors();
        expect($this->pack->fresh()->private_checkout_enabled)->toBe((bool) $enabled)
            ->and($this->pack->fresh()->private_checkout_token)->toBe($token)->and($this->pack->fresh()->visible_in_portal)->toBeFalse();
    }
    $this->post(route($prefix.'store'), $payload + ['private_checkout_enabled' => 1])->assertSessionHasNoErrors();
    expect(PackProduct::latest('id')->first()->private_checkout_token)->toHaveLength(64)->not->toBe($token);
})->with(['desktop', 'mobile']);

function privatePackPendingPurchase($test): PackPurchase
{
    $client = ClientProfile::create(['user_id' => $test->therapist->id, 'first_name' => 'Camille', 'last_name' => 'Test', 'email' => 'camille@example.test']);

    return PackPurchase::create([
        'user_id' => $test->therapist->id, 'pack_product_id' => $test->pack->id, 'client_profile_id' => $client->id,
        'status' => 'pending', 'payment_state' => 'pending', 'payment_mode' => 'one_time', 'stripe_session_id' => 'cs_private',
        'digital_training_ids_snapshot' => [$test->training->id],
    ]);
}

function privatePackPaidSession($test, PackPurchase $purchase, array $overrides = []): StripeSession
{
    return StripeSession::constructFrom(array_replace([
        'id' => 'cs_private', 'object' => 'checkout.session', 'payment_status' => 'paid', 'amount_total' => 18000,
        'payment_intent' => 'pi_private', 'currency' => 'eur', 'metadata' => [
            'pack_purchase_id' => (string) $purchase->id, 'purchase_kind' => 'pack', 'payment_mode' => 'one_time',
            'private_pack_id' => (string) $test->pack->id, 'therapist_id' => (string) $test->therapist->id,
        ],
    ], $overrides));
}

test('paid return and stripe webhook share replay safe fulfillment and preserve used pack state', function () {
    $purchase = privatePackPendingPurchase($this);
    $session = privatePackPaidSession($this, $purchase);
    $service = app(StripePurchaseWebhookService::class);
    expect($service->fulfillCheckoutSession(privatePackPaidSession($this, $purchase, ['payment_status' => 'unpaid']), 'acct_private'))->toBeTrue();
    expect($purchase->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('digital_training_enrollments', 0);
    $this->stripeHttp->shouldReceive('request')->once()->andReturn([$session->toJSON(), 200, []]);
    $this->get(route('packs.checkout.success', ['session_id' => 'cs_private', 'account_id' => 'acct_private']))
        ->assertRedirect($this->url)->assertSessionHas('success');
    $activatedAt = $purchase->fresh()->activated_at;
    $purchase->update(['status' => 'exhausted']);
    foreach (['evt_private_first', 'evt_private_retry'] as $id) {
        $event = \Stripe\Event::constructFrom(['id' => $id, 'type' => 'checkout.session.completed', 'data' => ['object' => $session->toArray()]]);
        expect($service->handleEvent($event, 'acct_private'))->toBeTrue();
    }
    expect($purchase->fresh()->status)->toBe('exhausted')->and($purchase->fresh()->activated_at->equalTo($activatedAt))->toBeTrue();
    $this->assertDatabaseCount('invoices', 1);
    expect(Receipt::count())->toBe(1);
    $this->assertDatabaseCount('digital_training_enrollments', 1);
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('wrong account or session cannot activate a private purchase', function () {
    $purchase = privatePackPendingPurchase($this);
    $service = app(StripePurchaseWebhookService::class);
    expect($service->fulfillCheckoutSession(privatePackPaidSession($this, $purchase), 'acct_other'))->toBeFalse();
    expect($service->fulfillCheckoutSession(privatePackPaidSession($this, $purchase, ['id' => 'cs_other']), 'acct_private'))->toBeFalse();
    expect($purchase->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('invoices', 0);
});

test('returning from checkout cannot cancel a purchase that subsequently receives payment', function () {
    $purchase = privatePackPendingPurchase($this);
    $this->get(route('packs.checkout.cancel', ['purchase_id' => $purchase->id]))->assertRedirect();
    expect($purchase->fresh()->status)->toBe('pending');
    expect(app(StripePurchaseWebhookService::class)->fulfillCheckoutSession(privatePackPaidSession($this, $purchase), 'acct_private'))->toBeTrue();
    expect($purchase->fresh()->status)->toBe('active');
});

test('a failed accounting transaction does not send access for a rolled back purchase', function () {
    $purchase = privatePackPendingPurchase($this);
    $this->mock(PackPurchaseInvoicingService::class)->shouldReceive('registerInstallmentPayment')->once()
        ->andThrow(new RuntimeException('Simulated accounting failure'));
    expect(fn () => app(StripePurchaseWebhookService::class)->fulfillCheckoutSession(privatePackPaidSession($this, $purchase), 'acct_private'))
        ->toThrow(RuntimeException::class, 'Simulated accounting failure');
    expect($purchase->fresh()->status)->toBe('pending');
    $this->assertDatabaseCount('digital_training_enrollments', 0);
    Mail::assertNotSent(DigitalTrainingAccessMail::class);
});

function privatePackInstallmentEvent(string $id, string $invoiceId = 'in_private'): \Stripe\Event
{
    return \Stripe\Event::constructFrom(['id' => $id, 'type' => 'invoice.paid', 'data' => ['object' => [
        'id' => $invoiceId, 'object' => 'invoice', 'subscription' => 'sub_private', 'amount_paid' => 6000,
        'currency' => 'eur', 'payment_intent' => 'pi_'.$invoiceId,
    ]]]);
}

test('an installment invoice arriving before checkout completion is recorded once', function () {
    $purchase = privatePackPendingPurchase($this);
    $purchase->update(['payment_mode' => 'installments', 'installments_total' => 3, 'installment_amount_cents' => 6000, 'installments_paid' => 0]);
    $this->stripeHttp->shouldReceive('request')->once()->andReturn([json_encode([
        'id' => 'sub_private', 'object' => 'subscription', 'metadata' => ['pack_purchase_id' => (string) $purchase->id],
    ]), 200, []]);
    $service = app(StripePurchaseWebhookService::class);
    expect($service->handleEvent(privatePackInstallmentEvent('evt_early_invoice'), 'acct_private'))->toBeTrue();
    expect($purchase->fresh()->stripe_subscription_id)->toBe('sub_private')
        ->and($purchase->fresh()->installments_paid)->toBe(1);
    $session = privatePackPaidSession($this, $purchase);
    $session->metadata->payment_mode = 'installments';
    $session->subscription = 'sub_private';
    $session->amount_total = 6000;
    $service->fulfillCheckoutSession($session, 'acct_private');
    $service->handleEvent(privatePackInstallmentEvent('evt_same_invoice_again'), 'acct_private');
    expect(PurchaseInstallment::count())->toBe(1)->and(Receipt::count())->toBe(1);
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('finishing all installments keeps the purchased pack and training access active', function () {
    $purchase = privatePackPendingPurchase($this);
    $purchase->update(['status' => 'active', 'payment_mode' => 'installments', 'payment_state' => 'completed',
        'stripe_subscription_id' => 'sub_private', 'installments_total' => 3, 'installments_paid' => 3]);
    app(PackDigitalTrainingAccessService::class)->grant($purchase);
    foreach (['customer.subscription.updated', 'customer.subscription.deleted'] as $type) {
        $event = \Stripe\Event::constructFrom(['id' => 'evt_'.$type, 'type' => $type, 'data' => ['object' => [
            'id' => 'sub_private', 'object' => 'subscription', 'cancel_at_period_end' => true, 'status' => 'canceled',
        ]]]);
        expect(app(StripePurchaseWebhookService::class)->handleEvent($event, 'acct_private'))->toBeTrue();
    }
    expect($purchase->fresh()->status)->toBe('active')->and($purchase->fresh()->payment_state)->toBe('completed')
        ->and($purchase->digitalTrainingEnrollments()->first()->token_expires_at->isFuture())->toBeTrue();
});

test('final installment retries stop recurring charges without duplicating the payment', function () {
    $purchase = privatePackPendingPurchase($this);
    $purchase->update(['status' => 'active', 'payment_mode' => 'installments', 'payment_state' => 'active',
        'stripe_subscription_id' => 'sub_private', 'installments_total' => 3, 'installments_paid' => 2, 'installment_amount_cents' => 6000]);
    $this->stripeHttp->shouldReceive('request')->once()->andReturn([json_encode(['error' => ['message' => 'Temporary Stripe failure', 'type' => 'api_error']]), 500, []])->ordered();
    $this->stripeHttp->shouldReceive('request')->once()->andReturn([json_encode(['id' => 'sub_private', 'object' => 'subscription', 'cancel_at_period_end' => true]), 200, []])->ordered();
    $service = app(StripePurchaseWebhookService::class);
    $event = privatePackInstallmentEvent('evt_final_installment');
    expect(fn () => $service->handleEvent($event, 'acct_private'))->toThrow(\Stripe\Exception\ApiErrorException::class);
    expect(StripeWebhookEvent::where('event_id', $event->id)->exists())->toBeFalse();
    expect($service->handleEvent($event, 'acct_private'))->toBeTrue();
    expect($purchase->fresh()->installments_paid)->toBe(3)->and($purchase->fresh()->payment_state)->toBe('completed')
        ->and(PurchaseInstallment::count())->toBe(1)->and(Receipt::count())->toBe(1);
});

test('an installment can finish accounting after a transient failure without double counting', function () {
    $purchase = privatePackPendingPurchase($this);
    $purchase->update(['payment_mode' => 'installments', 'stripe_subscription_id' => 'sub_private',
        'installments_total' => 3, 'installments_paid' => 0, 'installment_amount_cents' => 6000]);
    $invoicing = app(PackPurchaseInvoicingService::class);
    $mock = $this->mock(PackPurchaseInvoicingService::class);
    $mock->shouldReceive('registerInstallmentPayment')->once()->andThrow(new RuntimeException('Accounting unavailable'))->ordered();
    $mock->shouldReceive('registerInstallmentPayment')->once()->andReturnUsing(fn (...$args) => $invoicing->registerInstallmentPayment(...$args))->ordered();
    $service = app(StripePurchaseWebhookService::class);
    $event = privatePackInstallmentEvent('evt_accounting_retry');
    expect(fn () => $service->handleEvent($event, 'acct_private'))->toThrow(RuntimeException::class, 'Accounting unavailable');
    Mail::assertNothingOutgoing();
    expect($service->handleEvent($event, 'acct_private'))->toBeTrue();
    expect($purchase->fresh()->installments_paid)->toBe(1)->and(PurchaseInstallment::count())->toBe(1)->and(Receipt::count())->toBe(1);
    Mail::assertSent(DigitalTrainingAccessMail::class, 1);
});

test('subscription events from another connected account cannot modify a purchase', function (string $type) {
    $purchase = privatePackPendingPurchase($this);
    $purchase->update(['status' => 'active', 'payment_mode' => 'installments', 'payment_state' => 'active', 'stripe_subscription_id' => 'sub_private']);
    $event = \Stripe\Event::constructFrom(['id' => 'evt_foreign', 'type' => $type, 'data' => ['object' => [
        'id' => 'sub_private', 'object' => 'subscription', 'status' => 'canceled',
    ]]]);
    expect(app(StripePurchaseWebhookService::class)->handleEvent($event, 'acct_other'))->toBeFalse()
        ->and($purchase->fresh()->status)->toBe('active')->and($purchase->fresh()->payment_state)->toBe('active');
})->with(['customer.subscription.updated', 'customer.subscription.deleted']);

test('a stale failed invoice does not downgrade a paid installment', function () {
    $purchase = privatePackPendingPurchase($this);
    $purchase->update(['payment_mode' => 'installments', 'stripe_subscription_id' => 'sub_private',
        'installments_total' => 3, 'installments_paid' => 0, 'installment_amount_cents' => 6000]);
    $service = app(StripePurchaseWebhookService::class);
    $service->handleEvent(privatePackInstallmentEvent('evt_success'), 'acct_private');
    $failed = privatePackInstallmentEvent('evt_late_failure');
    $failed->type = 'invoice.payment_failed';
    expect($service->handleEvent($failed, 'acct_private'))->toBeTrue()
        ->and($purchase->fresh()->payment_state)->toBe('active');
});
