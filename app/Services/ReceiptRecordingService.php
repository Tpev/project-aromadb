<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class ReceiptRecordingService
{
    public function __construct(
        private readonly InvoiceLifecycleService $lifecycle,
        private readonly InvoiceActivityService $activity,
    ) {}

    public function recordInvoicePayment(
        Invoice $invoice,
        float $amountTtc,
        string $encaissementDate,
        string $paymentMethod = 'other',
        string $source = 'payment',
        ?string $note = null,
        ?string $provider = null,
        ?string $providerReference = null,
        string $nature = 'service',
        ?User $actor = null
    ): Receipt {
        if ($amountTtc <= 0) {
            throw new \InvalidArgumentException('Le montant encaisse doit etre strictement positif.');
        }

        $provider = filled($provider) ? trim((string) $provider) : null;
        $providerReference = filled($providerReference) ? trim((string) $providerReference) : null;

        if (($provider === null) !== ($providerReference === null)) {
            throw new \InvalidArgumentException('Le fournisseur et sa reference doivent etre renseignes ensemble.');
        }

        if ($provider && $providerReference) {
            $existing = Receipt::where('provider', $provider)
                ->where('user_id', $invoice->user_id)
                ->where('provider_reference', $providerReference)
                ->first();

            if ($existing) {
                $this->assertSameInvoice($existing, $invoice);

                return $existing;
            }
        }

        try {
            return DB::transaction(function () use (
                $invoice,
                $amountTtc,
                $encaissementDate,
                $paymentMethod,
                $source,
                $note,
                $provider,
                $providerReference,
                $nature,
                $actor
            ) {
                $lockedInvoice = Invoice::query()->lockForUpdate()->findOrFail($invoice->id);
                $this->lifecycle->finalize($lockedInvoice, $actor, 'first_payment_recorded');
                $lockedInvoice->refresh();

                if ($provider && $providerReference) {
                    $existing = Receipt::where('provider', $provider)
                        ->where('user_id', $lockedInvoice->user_id)
                        ->where('provider_reference', $providerReference)
                        ->first();

                    if ($existing) {
                        $this->assertSameInvoice($existing, $lockedInvoice);

                        return $existing;
                    }
                }

                $totalTtc = (float) $lockedInvoice->total_amount_with_tax;
                $totalHt = (float) $lockedInvoice->total_amount;
                $ratioHt = $totalTtc > 0 ? $totalHt / $totalTtc : 1.0;

                $clientName = trim((string) optional($lockedInvoice->clientProfile)->first_name.' '
                    .(string) optional($lockedInvoice->clientProfile)->last_name);

                if ($clientName === '') {
                    $clientName = (string) ($lockedInvoice->corporateClient?->trade_name
                        ?: $lockedInvoice->corporateClient?->name
                        ?: 'Client');
                }

                $receipt = Receipt::create([
                    'user_id' => $lockedInvoice->user_id,
                    'invoice_id' => $lockedInvoice->id,
                    'invoice_number' => (string) $lockedInvoice->invoice_number,
                    'encaissement_date' => $encaissementDate,
                    'client_name' => $clientName,
                    'nature' => $nature,
                    'amount_ht' => round($amountTtc * $ratioHt, 2),
                    'amount_ttc' => round($amountTtc, 2),
                    'payment_method' => $paymentMethod,
                    'direction' => 'credit',
                    'source' => $source,
                    'provider' => $provider,
                    'provider_reference' => $providerReference,
                    'note' => $note,
                ]);

                $this->synchronizeInvoiceStatus($lockedInvoice);
                $this->activity->record(
                    $lockedInvoice,
                    'payment_recorded',
                    'Encaissement enregistré sur la facture.',
                    $actor,
                    metadata: [
                        'receipt_id' => $receipt->id,
                        'amount_ttc' => (float) $receipt->amount_ttc,
                        'provider' => $provider,
                    ]
                );

                return $receipt;
            }, 3);
        } catch (QueryException $exception) {
            if ($provider && $providerReference) {
                $existing = Receipt::where('provider', $provider)
                    ->where('user_id', $invoice->user_id)
                    ->where('provider_reference', $providerReference)
                    ->first();

                if ($existing) {
                    $this->assertSameInvoice($existing, $invoice);

                    return $existing;
                }
            }

            throw $exception;
        }
    }

    public function synchronizeInvoiceStatus(Invoice $invoice): void
    {
        if (($invoice->type ?? 'invoice') !== 'invoice') {
            return;
        }

        $netReceived = (float) Receipt::where('invoice_id', $invoice->id)
            ->selectRaw("COALESCE(SUM(CASE WHEN direction = 'credit' THEN amount_ttc ELSE -amount_ttc END), 0) as net")
            ->value('net');
        $totalTtc = (float) $invoice->total_amount_with_tax;

        $status = $netReceived + 0.001 >= $totalTtc
            ? "Pay\u{00E9}e"
            : ($netReceived > 0.001 ? "Partiellement pay\u{00E9}e" : 'En attente');

        $invoice->update(['status' => $status]);
    }

    private function assertSameInvoice(Receipt $receipt, Invoice $invoice): void
    {
        if ((int) $receipt->invoice_id !== (int) $invoice->id
            || (int) $receipt->user_id !== (int) $invoice->user_id) {
            throw new \LogicException('Cette reference de paiement est deja rattachee a une autre facture.');
        }
    }
}
