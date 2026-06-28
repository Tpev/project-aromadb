<?php

use App\Models\StripeFinanceBalanceTransaction;
use App\Models\StripeFinanceCoupon;
use App\Models\StripeFinanceCustomer;
use App\Models\StripeFinanceForecastAssumption;
use App\Models\StripeFinanceInvoice;
use App\Models\StripeFinanceNote;
use App\Models\StripeFinancePayment;
use App\Models\StripeFinancePayout;
use App\Models\StripeFinancePrice;
use App\Models\StripeFinanceProduct;
use App\Models\StripeFinancePromotionCode;
use App\Models\StripeFinanceSubscription;
use App\Models\StripeFinanceUpcomingInvoice;
use App\Models\User;
use App\Services\StripeFinanceSyncService;
use Carbon\Carbon;

function financeAdmin(): User
{
    return User::factory()->create(['is_admin' => true]);
}

function financeCustomer(array $overrides = []): StripeFinanceCustomer
{
    return StripeFinanceCustomer::create(array_merge([
        'stripe_customer_id' => 'cus_test_123',
        'name' => 'Cabinet Martin',
        'email' => 'martin@example.test',
        'phone' => '+33123456789',
        'currency' => 'eur',
        'delinquent' => false,
        'balance_cents' => 0,
        'stripe_created_at' => now()->subMonths(3),
        'last_synced_at' => now(),
    ], $overrides));
}

function financeSubscription(StripeFinanceCustomer $customer, array $overrides = []): StripeFinanceSubscription
{
    return StripeFinanceSubscription::create(array_merge([
        'stripe_subscription_id' => 'sub_test_123',
        'stripe_finance_customer_id' => $customer->id,
        'user_id' => $customer->user_id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'status' => 'active',
        'amount_cents' => 2990,
        'currency' => 'eur',
        'interval' => 'month',
        'interval_count' => 1,
        'product_name' => 'AromaDB Pro',
        'license_label' => 'AromaDB Pro mensuel',
        'current_period_start' => now()->subDays(12),
        'current_period_end' => now()->addDays(18),
        'last_synced_at' => now(),
    ], $overrides));
}

test('admin can view the finance overview with cashflow metrics', function () {
    $admin = financeAdmin();
    $customer = financeCustomer();
    $subscription = financeSubscription($customer, [
        'amount_cents' => 5900,
        'product_name' => 'AromaDB Premium',
        'license_label' => 'AromaDB Premium mensuel',
    ]);

    StripeFinanceInvoice::create([
        'stripe_invoice_id' => 'in_paid_123',
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $subscription->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'stripe_subscription_id' => $subscription->stripe_subscription_id,
        'status' => 'paid',
        'currency' => 'eur',
        'total_cents' => 5900,
        'amount_paid_cents' => 5900,
        'amount_remaining_cents' => 0,
        'paid_at' => now(),
        'stripe_created_at' => now(),
    ]);

    StripeFinanceInvoice::create([
        'stripe_invoice_id' => 'in_failed_123',
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $subscription->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'stripe_subscription_id' => $subscription->stripe_subscription_id,
        'number' => 'INV-FAILED',
        'status' => 'open',
        'currency' => 'eur',
        'total_cents' => 5900,
        'amount_due_cents' => 5900,
        'amount_remaining_cents' => 5900,
        'attempted' => true,
        'attempt_count' => 2,
        'next_payment_attempt' => now()->addDay(),
        'stripe_created_at' => now(),
    ]);

    StripeFinanceBalanceTransaction::create([
        'stripe_balance_transaction_id' => 'txn_charge_123',
        'stripe_source_id' => 'ch_123',
        'stripe_customer_id' => $customer->stripe_customer_id,
        'type' => 'charge',
        'reporting_category' => 'charge',
        'status' => 'available',
        'currency' => 'eur',
        'amount_cents' => 5900,
        'fee_cents' => 180,
        'net_cents' => 5720,
        'stripe_created_at' => now(),
    ]);

    StripeFinancePayout::create([
        'stripe_payout_id' => 'po_123',
        'status' => 'paid',
        'currency' => 'eur',
        'amount_cents' => 5720,
        'arrival_date' => now(),
        'stripe_created_at' => now(),
        'reconciliation_status' => 'rapproche',
    ]);

    financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_trial_123',
        'status' => 'trialing',
        'amount_cents' => 2990,
        'trial_end' => now()->addDays(5),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.finance.overview'));

    $response->assertOk();
    $response->assertSee('Vue finance Stripe');
    $response->assertSee('MRR');
    $response->assertSee('ARR');
    $response->assertSee('Reste à encaisser');
    $response->assertSee('Fin de mois estimée');
    $response->assertSee('Cash encaissé');
    $response->assertDontSee('Cash attendu');
    $response->assertSee('INV-FAILED');
    $response->assertSee('Cabinet Martin');
});

test('non admins cannot access finance screens', function () {
    $user = User::factory()->create(['is_admin' => false]);

    $this->actingAs($user)
        ->get(route('admin.finance.overview'))
        ->assertForbidden();
});

test('admin can use the customer board and add finance notes', function () {
    $admin = financeAdmin();
    $aromaUser = User::factory()->create([
        'name' => 'Claire Aromadb',
        'email' => 'claire@example.test',
        'stripe_customer_id' => 'cus_board_123',
        'license_product' => 'new_pro_annuelle',
        'license_status' => 'active',
    ]);
    $customer = financeCustomer([
        'stripe_customer_id' => 'cus_board_123',
        'user_id' => $aromaUser->id,
        'name' => 'Claire Cabinet',
        'email' => 'claire@example.test',
    ]);
    $subscription = financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_board_123',
        'amount_cents' => 32890,
        'interval' => 'year',
        'product_name' => 'AromaDB Pro annuel',
        'license_label' => 'AromaDB Pro annuel',
        'coupon_name' => 'Lancement',
        'promotion_code' => 'PROMO20',
    ]);

    StripeFinanceInvoice::create([
        'stripe_invoice_id' => 'in_board_paid',
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $subscription->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'stripe_subscription_id' => $subscription->stripe_subscription_id,
        'status' => 'paid',
        'currency' => 'eur',
        'total_cents' => 32890,
        'amount_paid_cents' => 32890,
        'paid_at' => now()->subDay(),
        'stripe_created_at' => now()->subDay(),
    ]);

    $this->actingAs($admin)
        ->get(route('admin.finance.customers'))
        ->assertOk()
        ->assertSee('Clients &amp; licences', false)
        ->assertSee('Claire Cabinet')
        ->assertSee('PROMO20')
        ->assertSee('Payée');

    $this->actingAs($admin)
        ->get(route('admin.finance.customers.show', $customer))
        ->assertOk()
        ->assertSee('Fiche client finance')
        ->assertSee('AromaDB Pro annuel')
        ->assertSee('Claire Aromadb');

    $this->actingAs($admin)
        ->post(route('admin.finance.customers.notes.store', $customer), [
            'type' => 'relance',
            'body' => 'Vérifier la reconduction annuelle avant renouvellement.',
            'due_at' => now()->addWeek()->format('Y-m-d H:i:s'),
        ])
        ->assertRedirect(route('admin.finance.customers.show', $customer));

    expect(StripeFinanceNote::where('stripe_finance_customer_id', $customer->id)->count())->toBe(1);
});

test('admin can inspect failures payouts and forecast screens', function () {
    $admin = financeAdmin();
    $customer = financeCustomer(['stripe_customer_id' => 'cus_ops_123', 'name' => 'Ops Cabinet']);
    $subscription = financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_ops_123',
        'status' => 'past_due',
        'amount_cents' => 11900,
        'current_period_end' => now()->addDays(10),
    ]);
    $trial = financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_trial_ops',
        'status' => 'trialing',
        'amount_cents' => 3590,
        'trial_end' => now()->addDays(12),
    ]);

    StripeFinanceInvoice::create([
        'stripe_invoice_id' => 'in_ops_failed',
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $subscription->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'stripe_subscription_id' => $subscription->stripe_subscription_id,
        'number' => 'INV-OPS-FAILED',
        'status' => 'open',
        'currency' => 'eur',
        'total_cents' => 11900,
        'amount_due_cents' => 11900,
        'amount_remaining_cents' => 11900,
        'attempted' => true,
        'attempt_count' => 1,
        'last_payment_error_message' => 'Carte refusée',
        'next_payment_attempt' => now()->addDays(2),
        'stripe_created_at' => now(),
    ]);

    StripeFinanceUpcomingInvoice::create([
        'stripe_subscription_id' => $trial->stripe_subscription_id,
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $trial->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'currency' => 'eur',
        'amount_due_cents' => 3590,
        'total_cents' => 3590,
        'period_end' => now()->addDays(12),
        'previewed_at' => now(),
    ]);

    StripeFinanceBalanceTransaction::create([
        'stripe_balance_transaction_id' => 'txn_ops_charge',
        'stripe_source_id' => 'ch_ops',
        'type' => 'charge',
        'reporting_category' => 'charge',
        'status' => 'available',
        'currency' => 'eur',
        'amount_cents' => 11900,
        'fee_cents' => 420,
        'net_cents' => 11480,
        'stripe_created_at' => now(),
    ]);

    StripeFinanceBalanceTransaction::create([
        'stripe_balance_transaction_id' => 'txn_ops_dispute',
        'stripe_source_id' => 'dp_ops',
        'type' => 'issuing_dispute',
        'reporting_category' => 'dispute',
        'status' => 'available',
        'currency' => 'eur',
        'amount_cents' => -2000,
        'fee_cents' => 0,
        'net_cents' => -2000,
        'stripe_created_at' => now(),
    ]);

    StripeFinancePayout::create([
        'stripe_payout_id' => 'po_ops',
        'status' => 'paid',
        'currency' => 'eur',
        'amount_cents' => 11480,
        'arrival_date' => now(),
        'stripe_created_at' => now(),
        'reconciliation_status' => 'rapproche',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.finance.failures'))
        ->assertOk()
        ->assertSee('Centre paiements échoués')
        ->assertSee('INV-OPS-FAILED')
        ->assertSee('Carte refusée');

    $this->actingAs($admin)
        ->get(route('admin.finance.payouts'))
        ->assertOk()
        ->assertSee('Payouts &amp; frais', false)
        ->assertSee('po_ops')
        ->assertSee('Litiges')
        ->assertSee('20,00 €')
        ->assertSee('114,80 €');

    $this->actingAs($admin)
        ->get(route('admin.finance.forecast'))
        ->assertOk()
        ->assertSee('Prévisions cashflow')
        ->assertSee('Objectifs nouvelles licences')
        ->assertSee('Cohortes nouvelles licences')
        ->assertSee('Mix licences utilisé')
        ->assertSee('Book actuel')
        ->assertSee('atteint')
        ->assertSee('Prévisualisations des prochaines factures')
        ->assertSee('factures Stripe déjà connues')
        ->assertSee('abonnements actifs projetés')
        ->assertSee('Annuel inclus')
        ->assertSee('Paiement prévu')
        ->assertSee('Ops Cabinet');
});

test('forecast projects recurring monthly renewals after the synced preview', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-28 12:00:00'));

    $admin = financeAdmin();
    $customer = financeCustomer(['stripe_customer_id' => 'cus_forecast_123']);
    $subscription = financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_forecast_123',
        'amount_cents' => 1000,
        'interval' => 'month',
        'interval_count' => 1,
        'current_period_end' => now()->addDays(5),
    ]);

    StripeFinanceUpcomingInvoice::create([
        'stripe_subscription_id' => $subscription->stripe_subscription_id,
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $subscription->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'currency' => 'eur',
        'amount_due_cents' => 1000,
        'total_cents' => 1000,
        'period_start' => now()->addDays(5),
        'period_end' => now()->addDays(5)->addMonth(),
        'next_payment_attempt' => now()->addDays(5),
        'previewed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.finance.forecast'));

    $response->assertOk();
    $windows = $response->viewData('windows');

    expect($windows->get(30)['base_gross_cents'])->toBe(1000);
    expect($windows->get(60)['base_gross_cents'])->toBe(2000);
    expect($windows->get(90)['base_gross_cents'])->toBe(3000);

    Carbon::setTestNow();
});

test('forecast surfaces annual renewal spikes separately without double counting', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-28 12:00:00'));

    $admin = financeAdmin();
    $customer = financeCustomer(['stripe_customer_id' => 'cus_annual_123']);
    financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_annual_123',
        'amount_cents' => 120000,
        'interval' => 'year',
        'interval_count' => 1,
        'license_label' => 'Premium annuel',
        'current_period_end' => Carbon::parse('2026-09-15 10:00:00'),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.finance.forecast'));

    $response->assertOk();
    $forecast = $response->viewData('monthlyForecast')
        ->first(fn (array $row) => $row['start']->format('Y-m') === '2026-09');

    expect($forecast['preview_cents'])->toBe(0);
    expect($forecast['renewal_cents'])->toBe(120000);
    expect($forecast['annual_existing_cents'])->toBe(120000);
    expect($forecast['annual_conservative_cents'])->toBe(120000);
    expect($forecast['annual_expected_cents'])->toBe(120000);
    expect($forecast['annual_optimistic_cents'])->toBe(120000);

    Carbon::setTestNow();
});

test('forecast counts annual upcoming previews in the annual breakdown', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-28 12:00:00'));

    $admin = financeAdmin();
    $customer = financeCustomer(['stripe_customer_id' => 'cus_annual_preview_123']);
    $subscription = financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_annual_preview_123',
        'amount_cents' => 90000,
        'interval' => 'year',
        'interval_count' => 1,
        'license_label' => 'Pro annuel',
        'current_period_end' => Carbon::parse('2026-08-20 10:00:00'),
    ]);

    StripeFinanceUpcomingInvoice::create([
        'stripe_subscription_id' => $subscription->stripe_subscription_id,
        'stripe_finance_customer_id' => $customer->id,
        'stripe_finance_subscription_id' => $subscription->id,
        'stripe_customer_id' => $customer->stripe_customer_id,
        'currency' => 'eur',
        'amount_due_cents' => 90000,
        'total_cents' => 90000,
        'next_payment_attempt' => Carbon::parse('2026-08-20 10:00:00'),
        'period_start' => Carbon::parse('2026-08-20 10:00:00'),
        'period_end' => Carbon::parse('2027-08-20 10:00:00'),
        'previewed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('admin.finance.forecast'));

    $response->assertOk();
    $forecast = $response->viewData('monthlyForecast')
        ->first(fn (array $row) => $row['start']->format('Y-m') === '2026-08');

    expect($forecast['preview_cents'])->toBe(90000);
    expect($forecast['renewal_cents'])->toBe(0);
    expect($forecast['annual_existing_cents'])->toBe(90000);
    expect($forecast['annual_conservative_cents'])->toBe(90000);
    expect($forecast['annual_expected_cents'])->toBe(90000);
    expect($forecast['annual_optimistic_cents'])->toBe(90000);

    Carbon::setTestNow();
});

test('forecast includes annual new license cohorts in the annual scenario column', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-28 12:00:00'));

    $admin = financeAdmin();
    $customer = financeCustomer(['stripe_customer_id' => 'cus_annual_cohort_123']);
    financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_annual_cohort_123',
        'amount_cents' => 120000,
        'interval' => 'year',
        'interval_count' => 1,
        'license_label' => 'Premium annuel',
        'current_period_end' => Carbon::parse('2026-12-15 10:00:00'),
    ]);

    $monthKey = now()->startOfMonth()->addMonth()->format('Y-m');

    $this->actingAs($admin)
        ->post(route('admin.finance.forecast.assumptions.update'), [
            'assumptions' => [
                $monthKey => [
                    'conservative_new_customers' => 2,
                    'optimistic_new_customers' => 4,
                ],
            ],
        ])
        ->assertRedirect(route('admin.finance.forecast'));

    $response = $this->actingAs($admin)->get(route('admin.finance.forecast'));

    $response->assertOk();
    $forecast = $response->viewData('monthlyForecast')
        ->first(fn (array $row) => $row['start']->format('Y-m') === $monthKey);

    expect($forecast['annual_existing_cents'])->toBe(0);
    expect($forecast['annual_conservative_cents'])->toBe(240000);
    expect($forecast['annual_expected_cents'])->toBe(360000);
    expect($forecast['annual_optimistic_cents'])->toBe(480000);

    Carbon::setTestNow();
});

test('admin can set monthly new license assumptions using the current payer mix', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-28 12:00:00'));

    $admin = financeAdmin();
    $customer = financeCustomer(['stripe_customer_id' => 'cus_mix_123']);
    financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_mix_starter',
        'amount_cents' => 1000,
        'license_label' => 'Starter mensuel',
        'current_period_end' => now()->addMonths(2),
    ]);
    financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_mix_premium',
        'amount_cents' => 3000,
        'license_label' => 'Premium mensuel',
        'current_period_end' => now()->addMonths(2),
    ]);

    $monthKey = now()->startOfMonth()->addMonth()->format('Y-m');
    $secondMonthKey = now()->startOfMonth()->addMonths(2)->format('Y-m');
    $thirdMonthKey = now()->startOfMonth()->addMonths(3)->format('Y-m');

    $this->actingAs($admin)
        ->post(route('admin.finance.forecast.assumptions.update'), [
            'assumptions' => [
                $monthKey => [
                    'conservative_new_customers' => 6,
                    'optimistic_new_customers' => 10,
                ],
                $secondMonthKey => [
                    'conservative_new_customers' => 6,
                    'optimistic_new_customers' => 10,
                ],
            ],
        ])
        ->assertRedirect(route('admin.finance.forecast'));

    $assumption = StripeFinanceForecastAssumption::whereDate('month', now()->startOfMonth()->addMonth()->toDateString())->firstOrFail();
    expect($assumption->conservative_new_customers)->toBe(6);
    expect($assumption->optimistic_new_customers)->toBe(10);

    $response = $this->actingAs($admin)->get(route('admin.finance.forecast'));

    $response->assertOk();
    $forecast = $response->viewData('monthlyForecast')
        ->first(fn (array $row) => $row['start']->format('Y-m') === $monthKey);
    $secondForecast = $response->viewData('monthlyForecast')
        ->first(fn (array $row) => $row['start']->format('Y-m') === $secondMonthKey);
    $thirdForecast = $response->viewData('monthlyForecast')
        ->first(fn (array $row) => $row['start']->format('Y-m') === $thirdMonthKey);

    expect($forecast['new_business_conservative_cents'])->toBe(12000);
    expect($forecast['new_business_expected_cents'])->toBe(16000);
    expect($forecast['new_business_optimistic_cents'])->toBe(20000);
    expect($forecast['conservative_gross_cents'])->toBe(12000);
    expect($forecast['expected_gross_cents'])->toBe(16000);
    expect($forecast['optimistic_gross_cents'])->toBe(20000);
    expect($secondForecast['new_business_conservative_cents'])->toBe(24000);
    expect($secondForecast['new_business_expected_cents'])->toBe(32000);
    expect($secondForecast['new_business_optimistic_cents'])->toBe(40000);
    expect($thirdForecast['new_business_conservative_cents'])->toBe(24000);
    expect($thirdForecast['new_business_expected_cents'])->toBe(32000);
    expect($thirdForecast['new_business_optimistic_cents'])->toBe(40000);

    Carbon::setTestNow();
});

test('stripe finance sync service stores catalog promotion and payment snapshots', function () {
    $service = app(StripeFinanceSyncService::class);

    $service->upsertProductFromStripe((object) [
        'id' => 'prod_sync_123',
        'name' => 'AromaDB Pro',
        'active' => true,
        'type' => 'service',
        'description' => 'Licence pro',
        'metadata' => (object) ['license' => 'pro'],
        'created' => now()->timestamp,
    ]);

    $service->upsertPriceFromStripe((object) [
        'id' => 'price_sync_123',
        'product' => (object) [
            'id' => 'prod_sync_123',
            'name' => 'AromaDB Pro',
            'active' => true,
            'created' => now()->timestamp,
        ],
        'nickname' => 'Pro mensuel',
        'active' => true,
        'currency' => 'eur',
        'unit_amount' => 2990,
        'billing_scheme' => 'per_unit',
        'type' => 'recurring',
        'recurring' => (object) ['interval' => 'month', 'interval_count' => 1],
        'lookup_key' => 'pro_monthly',
        'created' => now()->timestamp,
    ]);

    $service->upsertCouponFromStripe((object) [
        'id' => 'coupon_sync_123',
        'name' => 'Lancement',
        'valid' => true,
        'duration' => 'repeating',
        'duration_in_months' => 3,
        'percent_off' => 20,
        'times_redeemed' => 4,
        'created' => now()->timestamp,
    ]);

    $service->upsertPromotionCodeFromStripe((object) [
        'id' => 'promo_sync_123',
        'code' => 'PROMO20',
        'coupon' => (object) [
            'id' => 'coupon_sync_123',
            'name' => 'Lancement',
            'valid' => true,
            'duration' => 'repeating',
            'created' => now()->timestamp,
        ],
        'active' => true,
        'times_redeemed' => 2,
        'created' => now()->timestamp,
    ]);

    $service->upsertPaymentFromCharge((object) [
        'id' => 'ch_sync_123',
        'customer' => (object) [
            'id' => 'cus_sync_123',
            'name' => 'Cabinet Sync',
            'email' => 'sync@example.test',
            'created' => now()->timestamp,
        ],
        'invoice' => (object) [
            'id' => 'in_sync_123',
            'subscription' => 'sub_sync_123',
        ],
        'payment_intent' => 'pi_sync_123',
        'balance_transaction' => (object) [
            'id' => 'txn_sync_123',
            'fee' => 120,
            'net' => 2870,
        ],
        'status' => 'succeeded',
        'paid' => true,
        'captured' => true,
        'refunded' => false,
        'disputed' => false,
        'currency' => 'eur',
        'amount' => 2990,
        'amount_captured' => 2990,
        'amount_refunded' => 0,
        'payment_method_details' => (object) [
            'type' => 'card',
            'card' => (object) ['brand' => 'visa', 'last4' => '4242'],
        ],
        'receipt_url' => 'https://pay.stripe.com/receipt/test',
        'created' => now()->timestamp,
    ]);

    $service->upsertSubscriptionFromStripe((object) [
        'id' => 'sub_sync_catalog',
        'customer' => 'cus_sync_123',
        'status' => 'active',
        'cancel_at_period_end' => false,
        'items' => (object) [
            'data' => [
                (object) [
                    'quantity' => 1,
                    'price' => (object) [
                        'id' => 'price_sync_123',
                        'unit_amount' => 2990,
                        'currency' => 'eur',
                        'product' => 'prod_sync_123',
                        'nickname' => 'Pro mensuel',
                        'recurring' => (object) [
                            'interval' => 'month',
                            'interval_count' => 1,
                        ],
                    ],
                ],
            ],
        ],
        'current_period_start' => now()->subDay()->timestamp,
        'current_period_end' => now()->addMonth()->timestamp,
        'created' => now()->timestamp,
    ]);

    expect(StripeFinanceProduct::where('stripe_product_id', 'prod_sync_123')->exists())->toBeTrue();
    expect(StripeFinancePrice::where('stripe_price_id', 'price_sync_123')->value('unit_amount_cents'))->toBe(2990);
    expect(StripeFinanceCoupon::where('stripe_coupon_id', 'coupon_sync_123')->exists())->toBeTrue();
    expect(StripeFinancePromotionCode::where('stripe_promotion_code_id', 'promo_sync_123')->value('code'))->toBe('PROMO20');
    expect(StripeFinancePayment::where('stripe_charge_id', 'ch_sync_123')->value('payment_method_label'))->toBe('VISA **** 4242');
    expect(StripeFinanceSubscription::where('stripe_subscription_id', 'sub_sync_catalog')->value('product_name'))->toBe('AromaDB Pro');
});

test('upcoming invoice previews fall back to the subscription renewal date for cash timing', function () {
    Carbon::setTestNow(Carbon::parse('2026-06-28 12:00:00'));

    $service = app(StripeFinanceSyncService::class);
    $customer = financeCustomer(['stripe_customer_id' => 'cus_preview_123']);
    $subscription = financeSubscription($customer, [
        'stripe_subscription_id' => 'sub_preview_123',
        'current_period_end' => Carbon::parse('2026-07-03 09:30:00'),
    ]);

    $service->upsertUpcomingInvoicePreview($subscription, (object) [
        'currency' => 'eur',
        'subtotal' => 2990,
        'total' => 2990,
        'amount_due' => 2990,
        'period_start' => Carbon::parse('2026-07-03 09:30:00')->timestamp,
        'period_end' => Carbon::parse('2026-08-03 09:30:00')->timestamp,
    ]);

    $preview = StripeFinanceUpcomingInvoice::where('stripe_subscription_id', 'sub_preview_123')->firstOrFail();

    expect($preview->next_payment_attempt?->format('Y-m-d H:i:s'))->toBe('2026-07-03 09:30:00');

    Carbon::setTestNow();
});
