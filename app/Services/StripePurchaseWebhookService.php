<?php

namespace App\Services;

use App\Models\PackPurchase;
use App\Models\PurchaseInstallment;
use App\Models\StripeWebhookEvent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripePurchaseWebhookService
{
    public function __construct(
        private readonly PackPurchaseInvoicingService $purchaseInvoicingService
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

        if ($type === 'checkout.session.completed') {
            $meta = $this->extractMetadata($object);
            if (!in_array((string) ($meta['purchase_kind'] ?? ''), ['pack', 'training'], true)) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            $handled = $this->handleCheckoutSessionCompleted($object, $connectedAccountId);
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
            if (!$purchase) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            $purchase->update([
                'payment_state' => 'past_due',
            ]);
            $this->markProcessed($eventId, $type, $connectedAccountId);
            return true;
        }

        if ($type === 'customer.subscription.updated') {
            $subscriptionId = (string) ($object->id ?? '');
            if ($subscriptionId === '') {
                return false;
            }

            $purchase = PackPurchase::where('stripe_subscription_id', $subscriptionId)->first();
            if (!$purchase) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
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
            if (!$purchase) {
                return false;
            }
            if ($this->alreadyProcessed($eventId)) {
                return true;
            }

            $purchase->update([
                'payment_state' => 'canceled',
                'status' => 'cancelled',
                'canceled_effective_at' => Carbon::now(),
            ]);
            $this->markProcessed($eventId, $type, $connectedAccountId);
            return true;
        }

        return false;
    }

    private function handleCheckoutSessionCompleted(object $session, ?string $connectedAccountId): bool
    {
        $meta = $this->extractMetadata($session);
        $purchaseId = isset($meta['pack_purchase_id']) ? (int) $meta['pack_purchase_id'] : 0;
        if ($purchaseId <= 0) {
            return false;
        }

        $purchase = PackPurchase::find($purchaseId);
        if (!$purchase) {
            return false;
        }

        if (!$this->accountMatchesPurchase($purchase, $connectedAccountId)) {
            return false;
        }

        $paymentMode = (string) ($meta['payment_mode'] ?? 'one_time');
        $paid = (($session->payment_status ?? null) === 'paid');

        if ($paymentMode === 'installments') {
            $payload = [
                'status' => $paid ? 'active' : 'pending',
                'payment_state' => $paid ? 'active' : 'pending',
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
            'status' => $paid ? 'active' : 'failed',
            'payment_state' => $paid ? 'completed' : 'failed',
            'purchased_at' => $paid ? Carbon::now() : null,
            'activated_at' => $paid ? Carbon::now() : null,
            'completed_at' => $paid ? Carbon::now() : null,
        ]);

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
            if ($invoiceId !== '' && PurchaseInstallment::where('stripe_invoice_id', $invoiceId)->exists()) {
                return;
            }

            $locked = PackPurchase::query()->lockForUpdate()->find($purchase->id);
            if (!$locked) {
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
                'status' => 'active',
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
            $meta = (array) ($subscription->metadata ?? []);
            $purchaseId = isset($meta['pack_purchase_id']) ? (int) $meta['pack_purchase_id'] : 0;
            if ($purchaseId <= 0) {
                return null;
            }

            $purchase = PackPurchase::find($purchaseId);
            if (!$purchase) {
                return null;
            }

            if (!$purchase->stripe_subscription_id) {
                $purchase->stripe_subscription_id = $subscriptionId;
                $purchase->save();
            }

            return $purchase;
        } catch (\Throwable $e) {
            Log::warning('Unable to resolve purchase from Stripe invoice subscription', [
                'subscription_id' => $subscriptionId,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function extractMetadata(object $source): array
    {
        $meta = (array) ($source->metadata ?? []);
        if (!empty($meta)) {
            return $meta;
        }

        if (!empty($source->payment_intent) && is_object($source->payment_intent)) {
            return (array) ($source->payment_intent->metadata ?? []);
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
