<?php

namespace App\Services;

use App\Models\PackPurchase;
use App\Models\PurchaseInstallment;
use App\Models\GiftVoucherOrder;
use App\Models\StripeWebhookEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripePurchaseWebhookService
{
    public function __construct(
        private readonly PackPurchaseInvoicingService $purchaseInvoicingService,
        private readonly GiftVoucherCheckoutService $giftVoucherCheckoutService,
        private readonly PackDigitalTrainingAccessService $digitalTrainingAccessService
    ) {
    }

    public function handleEvent(object $event, ?string $connectedAccountId = null): bool
    {
        $type = (string) ($event->type ?? '');
        $eventId = (string) ($event->id ?? '');
        $object = $event->data->object ?? null;

        if (!$object || $eventId === '') {
            return false;
        }

        if (in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            $meta = $this->extractMetadata($object);
            $purchaseKind = (string) ($meta['purchase_kind'] ?? '');

            if ($purchaseKind === 'gift_voucher') {
                if ($this->alreadyProcessed($eventId)) {
                    return true;
                }

                $handled = $this->handleGiftVoucherCheckoutSessionCompleted($object, $connectedAccountId);
                if ($handled) {
                    $this->markProcessed($eventId, $type, $connectedAccountId);
                }

                return $handled;
            }

            if (!in_array($purchaseKind, ['pack', 'training'], true)) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            $handled = $this->fulfillCheckoutSession($object, $connectedAccountId);
            if ($handled) {
                $this->markProcessed($eventId, $type, $connectedAccountId);
            }
            return $handled;
        }

        if ($type === 'invoice.paid' || $type === 'invoice.payment_succeeded') {
            $purchase = $this->resolvePurchaseFromInvoice($object, $connectedAccountId);
            if (!$purchase) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            $handled = $this->handleInvoicePaid($object, $purchase, $connectedAccountId);
            if ($handled) {
                $this->markProcessed($eventId, $type, $connectedAccountId);
            }
            return $handled;
        }

        if ($type === 'invoice.payment_failed') {
            $purchase = $this->resolvePurchaseFromInvoice($object, $connectedAccountId);
            if (!$purchase || !$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            // A delayed failure event must not undo a subsequently paid invoice.
            if (!$this->installmentsComplete($purchase)
                && !in_array($purchase->status, ['cancelled', 'revoked'], true)
                && !PurchaseInstallment::where('pack_purchase_id', $purchase->id)->where('stripe_invoice_id', (string) ($object->id ?? ''))->exists()) {
                $purchase->update(['payment_state' => 'past_due']);
            }
            $this->markProcessed($eventId, $type, $connectedAccountId);
            return true;
        }

        if ($type === 'customer.subscription.updated') {
            $subscriptionId = (string) ($object->id ?? '');
            if ($subscriptionId === '') {
                return false;
            }

            $purchase = PackPurchase::where('stripe_subscription_id', $subscriptionId)->first();
            if (!$purchase || !$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            // Ending the billing schedule after its last payment is not revocation.
            if ($this->installmentsComplete($purchase)) {
                $this->markProcessed($eventId, $type, $connectedAccountId);
                return true;
            }

            $updates = [];
            $cancelAtPeriodEnd = (bool) ($object->cancel_at_period_end ?? false);
            if ($cancelAtPeriodEnd) {
                $updates['payment_state'] = 'cancel_scheduled';
                if (!empty($object->current_period_end)) {
                    $updates['canceled_effective_at'] = Carbon::createFromTimestamp((int) $object->current_period_end);
                }
            }

            if (($object->status ?? null) === 'canceled') {
                $updates['payment_state'] = 'canceled';
                $updates['status'] = 'cancelled';
                $updates['canceled_effective_at'] = Carbon::now();
            }

            if (!empty($updates)) {
                $purchase->update($updates);
                if (($updates['status'] ?? null) === 'cancelled') {
                    $this->digitalTrainingAccessService->revoke($purchase);
                }
            }

            $this->markProcessed($eventId, $type, $connectedAccountId);
            return true;
        }

        if ($type === 'customer.subscription.deleted') {
            $subscriptionId = (string) ($object->id ?? '');
            if ($subscriptionId === '') {
                return false;
            }

            $purchase = PackPurchase::where('stripe_subscription_id', $subscriptionId)->first();
            if (!$purchase || !$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            if ($this->installmentsComplete($purchase)) {
                $this->markProcessed($eventId, $type, $connectedAccountId);
                return true;
            }

            $purchase->update([
                'payment_state' => 'canceled',
                'status' => 'cancelled',
                'canceled_effective_at' => Carbon::now(),
            ]);
            $this->digitalTrainingAccessService->revoke($purchase);
            $this->markProcessed($eventId, $type, $connectedAccountId);
            return true;
        }

        return false;
    }

    private function handleGiftVoucherCheckoutSessionCompleted(object $session, ?string $connectedAccountId): bool
    {
        if (($session->payment_status ?? null) !== 'paid') {
            return false;
        }

        $meta = $this->extractMetadata($session);
        $orderId = isset($meta['gift_voucher_order_id']) ? (int) $meta['gift_voucher_order_id'] : 0;
        if ($orderId <= 0) {
            return false;
        }

        $order = GiftVoucherOrder::find($orderId);
        if (!$order) {
            Log::warning('Gift voucher webhook received for missing order', [
                'order_id' => $orderId,
                'session_id' => $session->id ?? null,
            ]);

            return false;
        }

        if (!$this->accountMatchesGiftVoucherOrder($order, $connectedAccountId)) {
            Log::warning('Gift voucher webhook account mismatch', [
                'order_id' => $order->id,
                'connected_account_id' => $connectedAccountId,
                'therapist_stripe_account_id' => $order->therapist?->stripe_account_id,
            ]);

            return false;
        }

        $paymentIntentId = '';
        if (!empty($session->payment_intent)) {
            $paymentIntentId = is_object($session->payment_intent)
                ? (string) ($session->payment_intent->id ?? '')
                : (string) $session->payment_intent;
        }

        $this->giftVoucherCheckoutService->finalizePaidOrder(
            $order,
            (string) ($session->id ?? ''),
            $paymentIntentId !== '' ? $paymentIntentId : null
        );

        return true;
    }

    public function fulfillCheckoutSession(object $session, ?string $connectedAccountId): bool
    {
        // Serialize browser-return and webhook fulfillment for the same purchase.
        $handled = DB::transaction(fn () => $this->fulfillLockedCheckoutSession($session, $connectedAccountId));
        if ($handled && ($session->payment_status ?? null) === 'paid') {
            $purchase = PackPurchase::find((int) ($this->extractMetadata($session)['pack_purchase_id'] ?? 0));
            if ($purchase) {
                // The accounting transaction must succeed before access emails leave.
                $this->digitalTrainingAccessService->grant($purchase);
            }
        }

        return $handled;
    }

    private function fulfillLockedCheckoutSession(object $session, ?string $connectedAccountId): bool
    {
        $meta = $this->extractMetadata($session);
        $purchaseId = isset($meta['pack_purchase_id']) ? (int) $meta['pack_purchase_id'] : 0;
        if ($purchaseId <= 0) {
            return false;
        }

        $purchase = PackPurchase::lockForUpdate()->find($purchaseId);
        if (!$purchase) {
            return false;
        }

        if (!$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
            return false;
        }

        if (($purchase->stripe_session_id && $purchase->stripe_session_id !== (string) ($session->id ?? ''))
            || in_array($purchase->status, ['cancelled', 'revoked'], true)
            || ($purchase->payment_state ?? null) === 'canceled') {
            return false;
        }
        // Unpaid/delayed sessions must never grant access or overwrite a paid state.
        if (($session->payment_status ?? null) !== 'paid') {
            return true;
        }

        $paymentMode = (string) ($meta['payment_mode'] ?? 'one_time');
        $paid = (($session->payment_status ?? null) === 'paid');

        if ($paymentMode === 'installments') {
            $payload = [
                'status' => in_array($purchase->status, ['pending', 'failed'], true) ? 'active' : $purchase->status,
                'payment_state' => in_array($purchase->payment_state, [null, 'pending', 'failed'], true) ? 'active' : $purchase->payment_state,
            ];

            if (!empty($session->subscription)) {
                $payload['stripe_subscription_id'] = is_object($session->subscription)
                    ? (string) ($session->subscription->id ?? '')
                    : (string) $session->subscription;
            }

            if (!empty($session->customer)) {
                $payload['stripe_customer_id'] = (string) $session->customer;
            }

            if ($paid && !$purchase->purchased_at) {
                $payload['purchased_at'] = Carbon::now();
            }
            if ($paid && !$purchase->activated_at) {
                $payload['activated_at'] = Carbon::now();
            }

            $purchase->update($payload);

            // Create/refresh the invoice shell early so therapist sees it immediately.
            $this->purchaseInvoicingService->ensureInvoiceForPurchase($purchase->fresh());
            return true;
        }

        $purchase->update([
            'status' => in_array($purchase->status, ['pending', 'failed'], true) ? 'active' : $purchase->status,
            'payment_state' => $paid ? 'completed' : 'failed',
            'purchased_at' => $purchase->purchased_at ?: Carbon::now(),
            'activated_at' => $purchase->activated_at ?: Carbon::now(),
            'completed_at' => $purchase->completed_at ?: Carbon::now(),
        ]);

        if ($paid) {
            $providerReference = !empty($session->payment_intent)
                ? (is_object($session->payment_intent)
                    ? (string) ($session->payment_intent->id ?? '')
                    : (string) $session->payment_intent)
                : (string) ($session->id ?? '');

            $this->purchaseInvoicingService->registerInstallmentPayment(
                $purchase->fresh(),
                (int) ($session->amount_total ?? 0),
                $providerReference !== '' ? $providerReference : null,
                null,
                null,
                Carbon::now()
            );
        }

        return true;
    }

    private function handleInvoicePaid(object $invoice, PackPurchase $purchase, ?string $connectedAccountId): bool
    {
        if (($purchase->payment_mode ?? 'one_time') !== 'installments') {
            return false;
        }

        if (!$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
            return false;
        }

        $invoiceId = (string) ($invoice->id ?? '');
        $amountCents = (int) (($invoice->amount_paid ?? $invoice->amount_due ?? 0) ?: 0);
        $currency = strtoupper((string) ($invoice->currency ?? 'eur'));
        $paymentIntentId = (string) ($invoice->payment_intent ?? '');
        $sequenceNumber = null;
        $installmentCreated = false;

        DB::transaction(function () use (
            $purchase,
            $invoiceId,
            $amountCents,
            $currency,
            $paymentIntentId,
            &$sequenceNumber,
            &$installmentCreated
        ) {
            $locked = PackPurchase::query()->lockForUpdate()->find($purchase->id);
            if (!$locked) {
                return;
            }

            // Check under the purchase lock. Retried events must finish accounting
            // and schedule cancellation even when the installment already exists.
            $existing = $invoiceId !== '' ? PurchaseInstallment::where('stripe_invoice_id', $invoiceId)->first() : null;
            if ($existing) {
                if ((int) $existing->pack_purchase_id === (int) $locked->id) {
                    $sequenceNumber = $existing->sequence_number;
                    $installmentCreated = true;
                }
                return;
            }

            if (($locked->payment_state ?? null) === 'canceled') {
                return;
            }

            $nextSequence = ((int) ($locked->installments_paid ?? 0)) + 1;
            if ($locked->installments_total && $nextSequence > (int) $locked->installments_total) {
                return;
            }

            PurchaseInstallment::create([
                'pack_purchase_id' => $locked->id,
                'sequence_number' => $nextSequence,
                'amount_cents' => max(0, $amountCents),
                'currency' => $currency ?: 'EUR',
                'status' => 'paid',
                'paid_at' => Carbon::now(),
                'stripe_invoice_id' => $invoiceId !== '' ? $invoiceId : null,
                'stripe_payment_intent_id' => $paymentIntentId !== '' ? $paymentIntentId : null,
            ]);
            $installmentCreated = true;
            $sequenceNumber = $nextSequence;

            $newPaidCount = ((int) ($locked->installments_paid ?? 0)) + 1;
            $updates = [
                'status' => in_array($locked->status, ['pending', 'failed'], true) ? 'active' : $locked->status,
                'payment_state' => 'active',
                'installments_paid' => $newPaidCount,
                'purchased_at' => $locked->purchased_at ?: Carbon::now(),
                'activated_at' => $locked->activated_at ?: Carbon::now(),
            ];

            if ($locked->installments_total && $newPaidCount >= (int) $locked->installments_total) {
                $updates['payment_state'] = 'completed';
                $updates['completed_at'] = Carbon::now();
            }

            $locked->update($updates);
        });

        if (!$installmentCreated) {
            return true;
        }

        $purchase->refresh();

        $paidAt = null;
        if (!empty($invoice->status_transitions?->paid_at)) {
            $paidAt = Carbon::createFromTimestamp((int) $invoice->status_transitions->paid_at);
        }

        $this->purchaseInvoicingService->registerInstallmentPayment(
            $purchase,
            $amountCents,
            $invoiceId !== '' ? $invoiceId : null,
            $sequenceNumber,
            (int) ($purchase->installments_total ?? 0),
            $paidAt
        );

        $this->digitalTrainingAccessService->grant($purchase);

        if (
            ($purchase->payment_mode === 'installments')
            && $purchase->stripe_subscription_id
            && $purchase->installments_total
            && ((int) $purchase->installments_paid >= (int) $purchase->installments_total)
        ) {
            try {
                $stripe = new StripeClient((string) config('services.stripe.secret'));
                $stripe->subscriptions->update(
                    $purchase->stripe_subscription_id,
                    ['cancel_at_period_end' => true],
                    $connectedAccountId ? ['stripe_account' => $connectedAccountId] : []
                );
            } catch (\Throwable $e) {
                Log::warning('Unable to set cancel_at_period_end on completed installments', [
                    'pack_purchase_id' => $purchase->id,
                    'subscription_id' => $purchase->stripe_subscription_id,
                    'error' => $e->getMessage(),
                ]);
                // Leave the webhook unprocessed so Stripe can retry stopping billing.
                throw $e;
            }
        }

        return true;
    }

    private function resolvePurchaseFromInvoice(object $invoice, ?string $connectedAccountId): ?PackPurchase
    {
        $subscriptionId = (string) ($invoice->subscription ?? '');
        if ($subscriptionId === '') {
            return null;
        }

        $purchase = PackPurchase::where('stripe_subscription_id', $subscriptionId)->first();
        if ($purchase) {
            return $purchase;
        }

        try {
            $stripe = new StripeClient((string) config('services.stripe.secret'));
            $options = $connectedAccountId ? ['stripe_account' => $connectedAccountId] : [];
            $subscription = $stripe->subscriptions->retrieve($subscriptionId, [], $options);
            $meta = $this->extractMetadata($subscription);
            $purchaseId = isset($meta['pack_purchase_id']) ? (int) $meta['pack_purchase_id'] : 0;
            if ($purchaseId <= 0) {
                return null;
            }

            $purchase = PackPurchase::find($purchaseId);
            if (!$purchase || !$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
                return null;
            }

            if (!$purchase->stripe_subscription_id) {
                PackPurchase::whereKey($purchase->id)->whereNull('stripe_subscription_id')->update(['stripe_subscription_id' => $subscriptionId]);
                $purchase->refresh();
            }

            return $purchase->stripe_subscription_id === $subscriptionId ? $purchase : null;
        } catch (\Throwable $e) {
            Log::warning('Unable to resolve purchase from Stripe invoice subscription', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    private function installmentsComplete(PackPurchase $purchase): bool
    {
        return $purchase->payment_mode === 'installments'
            && (int) $purchase->installments_total > 0
            && (int) $purchase->installments_paid >= (int) $purchase->installments_total;
    }

    private function extractMetadata(object $source): array
    {
        $metadata = $source->metadata ?? [];
        $meta = $metadata instanceof \Stripe\StripeObject ? $metadata->toArray() : (array) $metadata;
        if (!empty($meta)) {
            return $meta;
        }

        if (!empty($source->payment_intent) && is_object($source->payment_intent)) {
            $metadata = $source->payment_intent->metadata ?? [];
            return $metadata instanceof \Stripe\StripeObject ? $metadata->toArray() : (array) $metadata;
        }

        return [];
    }

    private function accountMatchesPurchase(PackPurchase $purchase, ?string $connectedAccountId): bool
    {
        if (!$connectedAccountId) {
            return true;
        }

        $therapist = $purchase->user;
        if (!$therapist) {
            return false;
        }

        return (string) ($therapist->stripe_account_id ?? '') === (string) $connectedAccountId;
    }

    private function accountMatchesGiftVoucherOrder(GiftVoucherOrder $order, ?string $connectedAccountId): bool
    {
        if (!$connectedAccountId) {
            return true;
        }

        $therapist = $order->therapist;
        if (!$therapist) {
            return false;
        }

        return (string) ($therapist->stripe_account_id ?? '') === (string) $connectedAccountId;
    }

    private function alreadyProcessed(string $eventId): bool
    {
        return StripeWebhookEvent::where('event_id', $eventId)->exists();
    }

    private function markProcessed(string $eventId, string $eventType, ?string $accountId): void
    {
        try {
            StripeWebhookEvent::create([
                'event_id' => $eventId,
                'event_type' => $eventType,
                'account_id' => $accountId ?: null,
                'processed_at' => Carbon::now(),
            ]);
        } catch (\Throwable $e) {
            // Duplicate insert (or transient issue) can be ignored for webhook idempotency.
        }
    }
}
