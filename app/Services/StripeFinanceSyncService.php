<?php

namespace App\Services;

use App\Models\StripeFinanceBalanceTransaction;
use App\Models\StripeFinanceCustomer;
use App\Models\StripeFinanceCoupon;
use App\Models\StripeFinanceInvoice;
use App\Models\StripeFinancePayment;
use App\Models\StripeFinancePayout;
use App\Models\StripeFinancePrice;
use App\Models\StripeFinanceProduct;
use App\Models\StripeFinancePromotionCode;
use App\Models\StripeFinanceSubscription;
use App\Models\StripeFinanceSyncRun;
use App\Models\StripeFinanceUpcomingInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\StripeObject;

class StripeFinanceSyncService
{
    public function isConfigured(): bool
    {
        return trim((string) config('services.stripe.finance_secret')) !== '';
    }

    public function syncAll(int $daysBack = 730, int $maxRecordsPerType = 2000): array
    {
        $run = StripeFinanceSyncRun::create([
            'type' => 'complete',
            'status' => 'running',
            'started_at' => now(),
        ]);

        try {
            $summary = [
                'products' => $this->syncProducts($maxRecordsPerType),
                'prices' => $this->syncPrices($maxRecordsPerType),
                'coupons' => $this->syncCoupons($maxRecordsPerType),
                'promotion_codes' => $this->syncPromotionCodes($maxRecordsPerType),
                'customers' => $this->syncCustomers($maxRecordsPerType),
                'subscriptions' => $this->syncSubscriptions($maxRecordsPerType),
                'invoices' => $this->syncInvoices($daysBack, $maxRecordsPerType),
                'payments' => $this->syncPayments($daysBack, $maxRecordsPerType),
                'upcoming_invoice_previews' => $this->syncUpcomingInvoicePreviews(min(500, $maxRecordsPerType)),
                'balance_transactions' => $this->syncBalanceTransactions($daysBack, $maxRecordsPerType),
                'payouts' => $this->syncPayouts($daysBack, $maxRecordsPerType),
            ];

            $run->update([
                'status' => 'success',
                'finished_at' => now(),
                'records_synced' => array_sum($summary),
                'summary' => $summary,
            ]);

            return $summary;
        } catch (\Throwable $e) {
            $run->update([
                'status' => 'failed',
                'finished_at' => now(),
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function syncCustomers(int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $customers = $stripe->customers->all([
            'limit' => 100,
            'expand' => ['data.invoice_settings.default_payment_method'],
        ]);

        foreach ($customers->autoPagingIterator() as $customer) {
            $this->upsertCustomerFromStripe($customer);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncProducts(int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $products = $stripe->products->all(['limit' => 100]);

        foreach ($products->autoPagingIterator() as $product) {
            $this->upsertProductFromStripe($product);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncPrices(int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $prices = $stripe->prices->all([
            'limit' => 100,
            'expand' => ['data.product'],
        ]);

        foreach ($prices->autoPagingIterator() as $price) {
            $this->upsertPriceFromStripe($price);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncCoupons(int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $coupons = $stripe->coupons->all(['limit' => 100]);

        foreach ($coupons->autoPagingIterator() as $coupon) {
            $this->upsertCouponFromStripe($coupon);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncPromotionCodes(int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $codes = $stripe->promotionCodes->all([
            'limit' => 100,
            'expand' => ['data.coupon'],
        ]);

        foreach ($codes->autoPagingIterator() as $code) {
            $this->upsertPromotionCodeFromStripe($code);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncSubscriptions(int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $subscriptions = $stripe->subscriptions->all([
            'limit' => 100,
            'status' => 'all',
            'expand' => [
                'data.customer',
                'data.default_payment_method',
                'data.latest_invoice',
                'data.discount.coupon',
                'data.items.data.price',
            ],
        ]);

        foreach ($subscriptions->autoPagingIterator() as $subscription) {
            $this->upsertSubscriptionFromStripe($subscription);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncInvoices(int $daysBack = 730, int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $invoices = $stripe->invoices->all([
            'limit' => 100,
            'created' => ['gte' => now()->subDays($daysBack)->timestamp],
            'expand' => [
                'data.customer',
                'data.subscription',
                'data.payment_intent',
                'data.discount.coupon',
            ],
        ]);

        foreach ($invoices->autoPagingIterator() as $invoice) {
            $this->upsertInvoiceFromStripe($invoice);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncPayments(int $daysBack = 730, int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $charges = $stripe->charges->all([
            'limit' => 100,
            'created' => ['gte' => now()->subDays($daysBack)->timestamp],
            'expand' => [
                'data.customer',
                'data.invoice',
                'data.payment_intent',
                'data.balance_transaction',
            ],
        ]);

        foreach ($charges->autoPagingIterator() as $charge) {
            $this->upsertPaymentFromCharge($charge);

            if ($balanceTransaction = data_get($charge, 'balance_transaction')) {
                if (!is_string($balanceTransaction)) {
                    $this->upsertBalanceTransactionFromStripe($balanceTransaction);
                }
            }

            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        return $count;
    }

    public function syncBalanceTransactions(int $daysBack = 730, int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $transactions = $stripe->balanceTransactions->all([
            'limit' => 100,
            'created' => ['gte' => now()->subDays($daysBack)->timestamp],
            'expand' => ['data.source'],
        ]);

        foreach ($transactions->autoPagingIterator() as $transaction) {
            $this->upsertBalanceTransactionFromStripe($transaction);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        $this->refreshPayoutReconciliationStatuses();

        return $count;
    }

    public function syncPayouts(int $daysBack = 730, int $maxRecords = 2000): int
    {
        $stripe = $this->client();
        $count = 0;
        $payouts = $stripe->payouts->all([
            'limit' => 100,
            'created' => ['gte' => now()->subDays($daysBack)->timestamp],
        ]);

        foreach ($payouts->autoPagingIterator() as $payout) {
            $this->upsertPayoutFromStripe($payout);
            $count++;

            if ($count >= $maxRecords) {
                break;
            }
        }

        $this->refreshPayoutReconciliationStatuses();

        return $count;
    }

    public function syncUpcomingInvoicePreviews(int $maxRecords = 500): int
    {
        $stripe = $this->client();
        $count = 0;

        StripeFinanceSubscription::query()
            ->revenueActive()
            ->whereNotNull('stripe_customer_id')
            ->orderBy('current_period_end')
            ->limit($maxRecords)
            ->get()
            ->each(function (StripeFinanceSubscription $subscription) use ($stripe, &$count) {
                try {
                    $invoice = $stripe->invoices->upcoming([
                        'customer' => $subscription->stripe_customer_id,
                        'subscription' => $subscription->stripe_subscription_id,
                    ]);

                    $this->upsertUpcomingInvoicePreview($subscription, $invoice);
                    $count++;
                } catch (\Throwable $e) {
                    Log::debug('Stripe finance upcoming preview skipped', [
                        'subscription_id' => $subscription->stripe_subscription_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            });

        return $count;
    }

    public function ingestWebhookEvent(object $event): bool
    {
        $type = (string) data_get($event, 'type', '');
        $object = data_get($event, 'data.object');

        if (!$object || $type === '') {
            return false;
        }

        if (str_starts_with($type, 'customer.subscription.')) {
            $this->upsertSubscriptionFromStripe($object);
            return true;
        }

        if (str_starts_with($type, 'customer.')) {
            $this->upsertCustomerFromStripe($object);
            return true;
        }

        if (str_starts_with($type, 'invoice.')) {
            $this->upsertInvoiceFromStripe($object);
            return true;
        }

        if (str_starts_with($type, 'payout.')) {
            $this->upsertPayoutFromStripe($object);

            if ($type === 'payout.reconciliation_completed') {
                $this->syncBalanceTransactions(45, 1000);
            }

            return true;
        }

        if (str_starts_with($type, 'charge.')) {
            $this->upsertPaymentFromCharge($object);

            $balanceTransactionId = $this->expandableId(data_get($object, 'balance_transaction'));
            if ($balanceTransactionId) {
                $transaction = $this->client()->balanceTransactions->retrieve(
                    $balanceTransactionId,
                    ['expand' => ['source']]
                );
                $this->upsertBalanceTransactionFromStripe($transaction);
            }

            return true;
        }

        return false;
    }

    public function upsertCustomerFromStripe(mixed $customer): ?StripeFinanceCustomer
    {
        $customerId = $this->expandableId($customer);
        if (!$customerId) {
            return null;
        }

        $email = $this->lowerEmail(data_get($customer, 'email'));
        $user = $this->resolveUser($customerId, $email);

        return StripeFinanceCustomer::updateOrCreate(
            ['stripe_customer_id' => $customerId],
            [
                'user_id' => $user?->id,
                'name' => data_get($customer, 'name'),
                'email' => $email,
                'phone' => data_get($customer, 'phone'),
                'currency' => $this->lowerString(data_get($customer, 'currency')),
                'invoice_prefix' => data_get($customer, 'invoice_prefix'),
                'default_payment_method_label' => $this->paymentMethodLabel(data_get($customer, 'invoice_settings.default_payment_method')),
                'delinquent' => (bool) data_get($customer, 'delinquent', false),
                'balance_cents' => (int) data_get($customer, 'balance', 0),
                'metadata' => $this->toArray(data_get($customer, 'metadata')),
                'stripe_created_at' => $this->timestamp(data_get($customer, 'created')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertProductFromStripe(mixed $product): ?StripeFinanceProduct
    {
        $productId = $this->expandableId($product);
        if (!$productId) {
            return null;
        }

        return StripeFinanceProduct::updateOrCreate(
            ['stripe_product_id' => $productId],
            [
                'name' => data_get($product, 'name'),
                'active' => (bool) data_get($product, 'active', true),
                'type' => data_get($product, 'type'),
                'description' => data_get($product, 'description'),
                'metadata' => $this->toArray(data_get($product, 'metadata')),
                'stripe_created_at' => $this->timestamp(data_get($product, 'created')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertPriceFromStripe(mixed $price): ?StripeFinancePrice
    {
        $priceId = $this->expandableId($price);
        if (!$priceId) {
            return null;
        }

        $product = data_get($price, 'product');
        if (!is_string($product) && $this->expandableId($product)) {
            $this->upsertProductFromStripe($product);
        }

        return StripeFinancePrice::updateOrCreate(
            ['stripe_price_id' => $priceId],
            [
                'stripe_product_id' => $this->expandableId($product),
                'nickname' => data_get($price, 'nickname'),
                'active' => (bool) data_get($price, 'active', true),
                'currency' => $this->lowerString(data_get($price, 'currency', 'eur')),
                'unit_amount_cents' => (int) data_get($price, 'unit_amount', 0),
                'billing_scheme' => data_get($price, 'billing_scheme'),
                'type' => data_get($price, 'type'),
                'interval' => data_get($price, 'recurring.interval'),
                'interval_count' => (int) data_get($price, 'recurring.interval_count', 1),
                'lookup_key' => data_get($price, 'lookup_key'),
                'metadata' => $this->toArray(data_get($price, 'metadata')),
                'stripe_created_at' => $this->timestamp(data_get($price, 'created')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertCouponFromStripe(mixed $coupon): ?StripeFinanceCoupon
    {
        $couponId = $this->expandableId($coupon);
        if (!$couponId) {
            return null;
        }

        return StripeFinanceCoupon::updateOrCreate(
            ['stripe_coupon_id' => $couponId],
            [
                'name' => data_get($coupon, 'name'),
                'valid' => (bool) data_get($coupon, 'valid', true),
                'duration' => data_get($coupon, 'duration'),
                'duration_in_months' => data_get($coupon, 'duration_in_months'),
                'percent_off' => data_get($coupon, 'percent_off'),
                'amount_off_cents' => data_get($coupon, 'amount_off'),
                'currency' => $this->lowerString(data_get($coupon, 'currency')),
                'max_redemptions' => data_get($coupon, 'max_redemptions'),
                'times_redeemed' => (int) data_get($coupon, 'times_redeemed', 0),
                'redeem_by' => $this->timestamp(data_get($coupon, 'redeem_by')),
                'metadata' => $this->toArray(data_get($coupon, 'metadata')),
                'stripe_created_at' => $this->timestamp(data_get($coupon, 'created')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertPromotionCodeFromStripe(mixed $promotionCode): ?StripeFinancePromotionCode
    {
        $promotionCodeId = $this->expandableId($promotionCode);
        if (!$promotionCodeId) {
            return null;
        }

        $coupon = data_get($promotionCode, 'coupon');
        if (!is_string($coupon) && $this->expandableId($coupon)) {
            $this->upsertCouponFromStripe($coupon);
        }

        return StripeFinancePromotionCode::updateOrCreate(
            ['stripe_promotion_code_id' => $promotionCodeId],
            [
                'code' => data_get($promotionCode, 'code'),
                'stripe_coupon_id' => $this->expandableId($coupon),
                'active' => (bool) data_get($promotionCode, 'active', true),
                'max_redemptions' => data_get($promotionCode, 'max_redemptions'),
                'times_redeemed' => (int) data_get($promotionCode, 'times_redeemed', 0),
                'expires_at' => $this->timestamp(data_get($promotionCode, 'expires_at')),
                'metadata' => $this->toArray(data_get($promotionCode, 'metadata')),
                'stripe_created_at' => $this->timestamp(data_get($promotionCode, 'created')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertSubscriptionFromStripe(mixed $subscription): ?StripeFinanceSubscription
    {
        $subscriptionId = $this->expandableId($subscription);
        if (!$subscriptionId) {
            return null;
        }

        $customerSource = data_get($subscription, 'customer');
        if (!is_string($customerSource) && $this->expandableId($customerSource)) {
            $this->upsertCustomerFromStripe($customerSource);
        }

        $customerId = $this->expandableId($customerSource);
        $customer = $customerId ? StripeFinanceCustomer::where('stripe_customer_id', $customerId)->first() : null;
        $user = $customer?->user ?: $this->resolveUser($customerId, null);
        [$amountCents, $interval, $intervalCount, $productId, $productName, $priceId, $priceNickname] = $this->subscriptionPrimaryLine($subscription);
        [$couponId, $couponName, $promotionCode, $discountPercent, $discountAmountCents] = $this->discountFields($subscription);
        $latestInvoice = data_get($subscription, 'latest_invoice');
        $latestInvoiceId = $this->expandableId($latestInvoice);
        $nextPaymentAttempt = data_get($latestInvoice, 'next_payment_attempt');

        return StripeFinanceSubscription::updateOrCreate(
            ['stripe_subscription_id' => $subscriptionId],
            [
                'stripe_finance_customer_id' => $customer?->id,
                'user_id' => $user?->id,
                'stripe_customer_id' => $customerId,
                'status' => (string) data_get($subscription, 'status', 'unknown'),
                'collection_method' => data_get($subscription, 'collection_method'),
                'cancel_at_period_end' => (bool) data_get($subscription, 'cancel_at_period_end', false),
                'cancel_at' => $this->timestamp(data_get($subscription, 'cancel_at')),
                'canceled_at' => $this->timestamp(data_get($subscription, 'canceled_at')),
                'ended_at' => $this->timestamp(data_get($subscription, 'ended_at')),
                'current_period_start' => $this->timestamp(data_get($subscription, 'current_period_start')),
                'current_period_end' => $this->timestamp(data_get($subscription, 'current_period_end')),
                'trial_start' => $this->timestamp(data_get($subscription, 'trial_start')),
                'trial_end' => $this->timestamp(data_get($subscription, 'trial_end')),
                'next_payment_attempt' => $this->timestamp($nextPaymentAttempt),
                'amount_cents' => $amountCents,
                'currency' => $this->lowerString($this->firstSubscriptionCurrency($subscription) ?: 'eur'),
                'interval' => $interval,
                'interval_count' => $intervalCount,
                'product_id' => $productId,
                'product_name' => $productName,
                'price_id' => $priceId,
                'price_nickname' => $priceNickname,
                'license_label' => $this->licenseLabel($productName, $priceNickname),
                'coupon_id' => $couponId,
                'coupon_name' => $couponName,
                'promotion_code' => $promotionCode,
                'discount_percent' => $discountPercent,
                'discount_amount_cents' => $discountAmountCents,
                'latest_invoice_id' => $latestInvoiceId,
                'default_payment_method_label' => $this->paymentMethodLabel(data_get($subscription, 'default_payment_method')),
                'metadata' => $this->toArray(data_get($subscription, 'metadata')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertInvoiceFromStripe(mixed $invoice): ?StripeFinanceInvoice
    {
        $invoiceId = $this->expandableId($invoice);
        if (!$invoiceId) {
            return null;
        }

        $customerSource = data_get($invoice, 'customer');
        if (!is_string($customerSource) && $this->expandableId($customerSource)) {
            $this->upsertCustomerFromStripe($customerSource);
        }

        $customerId = $this->expandableId($customerSource);
        $subscriptionId = $this->expandableId(data_get($invoice, 'subscription'));
        $customer = $customerId ? StripeFinanceCustomer::where('stripe_customer_id', $customerId)->first() : null;
        $subscription = $subscriptionId ? StripeFinanceSubscription::where('stripe_subscription_id', $subscriptionId)->first() : null;
        $paidAt = data_get($invoice, 'status_transitions.paid_at');
        $lastPaymentError = data_get($invoice, 'payment_intent.last_payment_error');

        return StripeFinanceInvoice::updateOrCreate(
            ['stripe_invoice_id' => $invoiceId],
            [
                'stripe_finance_customer_id' => $customer?->id,
                'stripe_finance_subscription_id' => $subscription?->id,
                'stripe_customer_id' => $customerId,
                'stripe_subscription_id' => $subscriptionId,
                'number' => data_get($invoice, 'number'),
                'status' => data_get($invoice, 'status'),
                'billing_reason' => data_get($invoice, 'billing_reason'),
                'collection_method' => data_get($invoice, 'collection_method'),
                'currency' => $this->lowerString(data_get($invoice, 'currency', 'eur')),
                'subtotal_cents' => (int) data_get($invoice, 'subtotal', 0),
                'total_cents' => (int) data_get($invoice, 'total', 0),
                'tax_cents' => (int) data_get($invoice, 'tax', 0),
                'discount_cents' => $this->invoiceDiscountCents($invoice),
                'amount_due_cents' => (int) data_get($invoice, 'amount_due', 0),
                'amount_paid_cents' => (int) data_get($invoice, 'amount_paid', 0),
                'amount_remaining_cents' => (int) data_get($invoice, 'amount_remaining', 0),
                'attempted' => (bool) data_get($invoice, 'attempted', false),
                'attempt_count' => (int) data_get($invoice, 'attempt_count', 0),
                'next_payment_attempt' => $this->timestamp(data_get($invoice, 'next_payment_attempt')),
                'due_date' => $this->timestamp(data_get($invoice, 'due_date')),
                'period_start' => $this->timestamp(data_get($invoice, 'period_start')),
                'period_end' => $this->timestamp(data_get($invoice, 'period_end')),
                'paid_at' => $this->timestamp($paidAt),
                'stripe_created_at' => $this->timestamp(data_get($invoice, 'created')),
                'hosted_invoice_url' => data_get($invoice, 'hosted_invoice_url'),
                'invoice_pdf' => data_get($invoice, 'invoice_pdf'),
                'last_payment_error_code' => data_get($lastPaymentError, 'code'),
                'last_payment_error_message' => data_get($lastPaymentError, 'message'),
                'metadata' => $this->toArray(data_get($invoice, 'metadata')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertPayoutFromStripe(mixed $payout): ?StripeFinancePayout
    {
        $payoutId = $this->expandableId($payout);
        if (!$payoutId) {
            return null;
        }

        $existingTransactions = StripeFinanceBalanceTransaction::where('stripe_payout_id', $payoutId)->count();
        $status = (string) data_get($payout, 'status', 'pending');

        return StripeFinancePayout::updateOrCreate(
            ['stripe_payout_id' => $payoutId],
            [
                'balance_transaction_id' => $this->expandableId(data_get($payout, 'balance_transaction')),
                'status' => $status,
                'type' => data_get($payout, 'type'),
                'method' => data_get($payout, 'method'),
                'currency' => $this->lowerString(data_get($payout, 'currency', 'eur')),
                'amount_cents' => (int) data_get($payout, 'amount', 0),
                'arrival_date' => $this->timestamp(data_get($payout, 'arrival_date')),
                'stripe_created_at' => $this->timestamp(data_get($payout, 'created')),
                'automatic' => (bool) data_get($payout, 'automatic', true),
                'description' => data_get($payout, 'description'),
                'statement_descriptor' => data_get($payout, 'statement_descriptor'),
                'reconciliation_status' => $status === 'paid' && $existingTransactions > 0 ? 'rapproche' : 'en_attente',
                'metadata' => $this->toArray(data_get($payout, 'metadata')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertBalanceTransactionFromStripe(mixed $transaction): ?StripeFinanceBalanceTransaction
    {
        $transactionId = $this->expandableId($transaction);
        if (!$transactionId) {
            return null;
        }

        $source = data_get($transaction, 'source');
        $sourceId = $this->expandableId($source);
        [$customerId, $invoiceId, $subscriptionId] = $this->sourceFinancialLinks($source);

        return StripeFinanceBalanceTransaction::updateOrCreate(
            ['stripe_balance_transaction_id' => $transactionId],
            [
                'stripe_source_id' => $sourceId,
                'stripe_payout_id' => $this->expandableId(data_get($transaction, 'payout')),
                'stripe_customer_id' => $customerId,
                'stripe_invoice_id' => $invoiceId,
                'stripe_subscription_id' => $subscriptionId,
                'type' => data_get($transaction, 'type'),
                'reporting_category' => data_get($transaction, 'reporting_category'),
                'status' => data_get($transaction, 'status'),
                'currency' => $this->lowerString(data_get($transaction, 'currency', 'eur')),
                'amount_cents' => (int) data_get($transaction, 'amount', 0),
                'fee_cents' => (int) data_get($transaction, 'fee', 0),
                'net_cents' => (int) data_get($transaction, 'net', 0),
                'exchange_rate' => data_get($transaction, 'exchange_rate'),
                'available_on' => $this->timestamp(data_get($transaction, 'available_on')),
                'stripe_created_at' => $this->timestamp(data_get($transaction, 'created')),
                'description' => data_get($transaction, 'description'),
                'metadata' => $this->toArray(data_get($source, 'metadata')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertPaymentFromCharge(mixed $charge): ?StripeFinancePayment
    {
        $chargeId = $this->expandableId($charge);
        if (!$chargeId) {
            return null;
        }

        $customerSource = data_get($charge, 'customer');
        if (!is_string($customerSource) && $this->expandableId($customerSource)) {
            $this->upsertCustomerFromStripe($customerSource);
        }

        $invoiceSource = data_get($charge, 'invoice');
        $paymentIntent = data_get($charge, 'payment_intent');
        $balanceTransaction = data_get($charge, 'balance_transaction');
        $balanceTransactionId = $this->expandableId($balanceTransaction);
        $feeCents = 0;
        $netCents = 0;

        if ($balanceTransaction && !is_string($balanceTransaction)) {
            $feeCents = (int) data_get($balanceTransaction, 'fee', 0);
            $netCents = (int) data_get($balanceTransaction, 'net', 0);
        }

        $subscriptionId = $this->expandableId(data_get($invoiceSource, 'subscription'));
        if (!$subscriptionId) {
            $subscriptionId = $this->expandableId(data_get($charge, 'metadata.subscription_id'));
        }

        return StripeFinancePayment::updateOrCreate(
            ['stripe_charge_id' => $chargeId],
            [
                'stripe_payment_intent_id' => $this->expandableId($paymentIntent),
                'stripe_customer_id' => $this->expandableId($customerSource),
                'stripe_invoice_id' => $this->expandableId($invoiceSource),
                'stripe_subscription_id' => $subscriptionId,
                'stripe_balance_transaction_id' => $balanceTransactionId,
                'status' => data_get($charge, 'status'),
                'paid' => (bool) data_get($charge, 'paid', false),
                'captured' => (bool) data_get($charge, 'captured', false),
                'refunded' => (bool) data_get($charge, 'refunded', false),
                'disputed' => (bool) data_get($charge, 'disputed', false),
                'currency' => $this->lowerString(data_get($charge, 'currency', 'eur')),
                'amount_cents' => (int) data_get($charge, 'amount', 0),
                'amount_captured_cents' => (int) data_get($charge, 'amount_captured', 0),
                'amount_refunded_cents' => (int) data_get($charge, 'amount_refunded', 0),
                'fee_cents' => $feeCents,
                'net_cents' => $netCents,
                'failure_code' => data_get($charge, 'failure_code'),
                'failure_message' => data_get($charge, 'failure_message'),
                'payment_method_type' => data_get($charge, 'payment_method_details.type'),
                'payment_method_label' => $this->chargePaymentMethodLabel($charge),
                'receipt_url' => data_get($charge, 'receipt_url'),
                'metadata' => $this->toArray(data_get($charge, 'metadata')),
                'stripe_created_at' => $this->timestamp(data_get($charge, 'created')),
                'last_synced_at' => now(),
            ]
        );
    }

    public function upsertUpcomingInvoicePreview(StripeFinanceSubscription $subscription, mixed $invoice): StripeFinanceUpcomingInvoice
    {
        [$couponId, $couponName, $promotionCode] = array_slice($this->discountFields($invoice), 0, 3);

        return StripeFinanceUpcomingInvoice::updateOrCreate(
            ['stripe_subscription_id' => $subscription->stripe_subscription_id],
            [
                'stripe_finance_customer_id' => $subscription->stripe_finance_customer_id,
                'stripe_finance_subscription_id' => $subscription->id,
                'stripe_customer_id' => $subscription->stripe_customer_id,
                'currency' => $this->lowerString(data_get($invoice, 'currency', $subscription->currency ?: 'eur')),
                'subtotal_cents' => (int) data_get($invoice, 'subtotal', 0),
                'total_cents' => (int) data_get($invoice, 'total', 0),
                'amount_due_cents' => (int) data_get($invoice, 'amount_due', 0),
                'discount_cents' => $this->invoiceDiscountCents($invoice),
                'period_start' => $this->timestamp(data_get($invoice, 'period_start')),
                'period_end' => $this->timestamp(data_get($invoice, 'period_end')),
                'next_payment_attempt' => $this->timestamp(data_get($invoice, 'next_payment_attempt')),
                'due_date' => $this->timestamp(data_get($invoice, 'due_date')),
                'coupon_id' => $couponId,
                'coupon_name' => $couponName,
                'promotion_code' => $promotionCode,
                'metadata' => $this->toArray(data_get($invoice, 'metadata')),
                'previewed_at' => now(),
            ]
        );
    }

    private function client(): StripeClient
    {
        $secret = (string) config('services.stripe.finance_secret');

        if (trim($secret) === '') {
            throw new \RuntimeException('STRIPE_FINANCE_SECRET ou STRIPE_SECRET est manquant.');
        }

        return new StripeClient($secret);
    }

    private function resolveUser(?string $stripeCustomerId, ?string $email): ?User
    {
        if ($stripeCustomerId) {
            $user = User::where('stripe_customer_id', $stripeCustomerId)->first();
            if ($user) {
                return $user;
            }
        }

        if ($email) {
            return User::whereRaw('LOWER(email) = ?', [$email])->first();
        }

        return null;
    }

    private function subscriptionPrimaryLine(mixed $subscription): array
    {
        $items = $this->toArray(data_get($subscription, 'items.data'));
        $amountCents = 0;
        $firstPrice = null;

        foreach ($items as $item) {
            $price = data_get($item, 'price');
            if (!$firstPrice) {
                $firstPrice = $price;
            }

            $quantity = max(1, (int) data_get($item, 'quantity', 1));
            $amountCents += ((int) data_get($price, 'unit_amount', 0)) * $quantity;
        }

        $product = data_get($firstPrice, 'product');
        $productId = $this->expandableId($product);
        $productName = is_string($product)
            ? StripeFinanceProduct::where('stripe_product_id', $product)->value('name')
            : data_get($product, 'name');

        return [
            $amountCents,
            data_get($firstPrice, 'recurring.interval'),
            (int) data_get($firstPrice, 'recurring.interval_count', 1),
            $productId,
            $productName,
            data_get($firstPrice, 'id'),
            data_get($firstPrice, 'nickname'),
        ];
    }

    private function firstSubscriptionCurrency(mixed $subscription): ?string
    {
        $items = $this->toArray(data_get($subscription, 'items.data'));
        $first = $items[0] ?? null;

        return data_get($first, 'price.currency') ?: data_get($subscription, 'currency');
    }

    private function discountFields(mixed $source): array
    {
        $discount = data_get($source, 'discount');
        $coupon = data_get($discount, 'coupon');

        return [
            $this->expandableId($coupon),
            data_get($coupon, 'name'),
            $this->expandableId(data_get($discount, 'promotion_code')),
            data_get($coupon, 'percent_off'),
            data_get($coupon, 'amount_off'),
        ];
    }

    private function invoiceDiscountCents(mixed $invoice): int
    {
        $discounts = $this->toArray(data_get($invoice, 'total_discount_amounts'));

        return (int) collect($discounts)->sum(fn ($discount) => (int) data_get($discount, 'amount', 0));
    }

    private function sourceFinancialLinks(mixed $source): array
    {
        if (!$source || is_string($source)) {
            return [null, null, null];
        }

        $customerId = $this->expandableId(data_get($source, 'customer'));
        $invoiceId = $this->expandableId(data_get($source, 'invoice'));
        $subscriptionId = $this->expandableId(data_get($source, 'subscription'));

        if (!$invoiceId) {
            $invoiceId = $this->expandableId(data_get($source, 'metadata.invoice_id'));
        }

        return [$customerId, $invoiceId, $subscriptionId];
    }

    private function refreshPayoutReconciliationStatuses(): void
    {
        StripeFinancePayout::query()->chunkById(100, function ($payouts) {
            foreach ($payouts as $payout) {
                $transactionCount = StripeFinanceBalanceTransaction::where('stripe_payout_id', $payout->stripe_payout_id)->count();
                if ($transactionCount > 0 && $payout->reconciliation_status !== 'rapproche') {
                    $payout->update(['reconciliation_status' => 'rapproche']);
                }
            }
        });
    }

    private function licenseLabel(?string $productName, ?string $priceNickname): ?string
    {
        $label = trim((string) ($productName ?: $priceNickname));
        if ($label === '') {
            return null;
        }

        return $label;
    }

    private function paymentMethodLabel(mixed $paymentMethod): ?string
    {
        if (!$paymentMethod || is_string($paymentMethod)) {
            return is_string($paymentMethod) ? $paymentMethod : null;
        }

        $type = (string) data_get($paymentMethod, 'type', '');
        if ($type === 'card') {
            $brand = strtoupper((string) data_get($paymentMethod, 'card.brand', 'CB'));
            $last4 = data_get($paymentMethod, 'card.last4');
            $expMonth = data_get($paymentMethod, 'card.exp_month');
            $expYear = data_get($paymentMethod, 'card.exp_year');

            return trim("{$brand} **** {$last4} - {$expMonth}/{$expYear}");
        }

        return $type !== '' ? ucfirst($type) : $this->expandableId($paymentMethod);
    }

    private function chargePaymentMethodLabel(mixed $charge): ?string
    {
        $type = (string) data_get($charge, 'payment_method_details.type', '');

        if ($type === 'card') {
            $brand = strtoupper((string) data_get($charge, 'payment_method_details.card.brand', 'CB'));
            $last4 = data_get($charge, 'payment_method_details.card.last4');

            return trim("{$brand} **** {$last4}");
        }

        if ($type !== '') {
            return ucfirst($type);
        }

        return $this->expandableId(data_get($charge, 'payment_method'));
    }

    private function timestamp(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::createFromTimestamp((int) $value);
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable $e) {
            Log::debug('Stripe finance timestamp ignored', ['value' => $value]);
            return null;
        }
    }

    private function expandableId(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        return data_get($value, 'id') ?: null;
    }

    private function toArray(mixed $value): array
    {
        if ($value instanceof StripeObject) {
            return $value->toArray();
        }

        if (is_array($value)) {
            return $value;
        }

        if ($value instanceof \Traversable) {
            return iterator_to_array($value);
        }

        if (is_object($value)) {
            $json = json_encode($value);
            return $json ? (json_decode($json, true) ?: []) : [];
        }

        return [];
    }

    private function lowerEmail(mixed $email): ?string
    {
        $email = strtolower(trim((string) $email));

        return $email !== '' ? $email : null;
    }

    private function lowerString(mixed $value): ?string
    {
        $value = strtolower(trim((string) $value));

        return $value !== '' ? $value : null;
    }
}
