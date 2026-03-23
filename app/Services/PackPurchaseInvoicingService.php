<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PackPurchase;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class PackPurchaseInvoicingService
{
    public function ensureInvoiceForPurchase(PackPurchase $purchase): ?Invoice
    {
        if (!$purchase->client_profile_id || !$purchase->user_id) {
            return null;
        }

        $purchase->loadMissing(['pack', 'items.product', 'clientProfile', 'digitalTraining']);

        return DB::transaction(function () use ($purchase) {
            $existing = Invoice::query()
                ->where('user_id', $purchase->user_id)
                ->where('pack_purchase_id', $purchase->id)
                ->first();

            if ($existing) {
                return $existing;
            }

            $lastInvoice = Invoice::query()
                ->where('user_id', $purchase->user_id)
                ->where('type', 'invoice')
                ->lockForUpdate()
                ->orderByDesc('invoice_number')
                ->first();

            $nextInvoiceNumber = $lastInvoice ? ((int) $lastInvoice->invoice_number + 1) : 1;

            [$amountHt, $taxRate, $amountTax, $amountTtc, $mainLabel] = $this->resolvePricingSnapshot($purchase);

            $invoice = Invoice::create([
                'client_profile_id' => $purchase->client_profile_id,
                'pack_purchase_id' => $purchase->id,
                'user_id' => $purchase->user_id,
                'invoice_date' => optional($purchase->purchased_at)->toDateString() ?: now()->toDateString(),
                'due_date' => null,
                'notes' => 'Facture générée automatiquement depuis un achat en ligne.',
                'invoice_number' => $nextInvoiceNumber,
                'total_amount' => round($amountHt, 2),
                'total_tax_amount' => round($amountTax, 2),
                'total_amount_with_tax' => round($amountTtc, 2),
                'status' => 'En attente',
                'type' => 'invoice',
            ]);

            $invoice->items()->create([
                'type' => 'custom',
                'label' => $mainLabel,
                'description' => $mainLabel,
                'quantity' => 1,
                'unit_price' => round($amountHt, 2),
                'tax_rate' => round($taxRate, 2),
                'tax_amount' => round($amountTax, 2),
                'total_price' => round($amountHt, 2),
                'total_price_with_tax' => round($amountTtc, 2),
            ]);

            if (($purchase->purchase_type ?? 'pack') === 'pack') {
                foreach ($purchase->items as $line) {
                    if (!$line->product) {
                        continue;
                    }

                    $invoice->items()->create([
                        'type' => 'custom',
                        'label' => 'Inclus : ' . $line->product->name,
                        'description' => 'Inclus : ' . $line->product->name . ' × ' . (int) $line->quantity_total,
                        'quantity' => 1,
                        'unit_price' => 0,
                        'tax_rate' => 0,
                        'tax_amount' => 0,
                        'total_price' => 0,
                        'total_price_with_tax' => 0,
                    ]);
                }
            }

            return $invoice;
        });
    }

    public function registerInstallmentPayment(
        PackPurchase $purchase,
        int $amountCents,
        ?string $stripeInvoiceId,
        ?int $sequenceNumber = null,
        ?int $totalInstallments = null,
        ?Carbon $paidAt = null
    ): ?Invoice {
        $invoice = $this->ensureInvoiceForPurchase($purchase);
        if (!$invoice) {
            return null;
        }

        $amountTtc = max(0, $amountCents) / 100;
        if ($amountTtc <= 0) {
            return $invoice;
        }

        $paidDate = ($paidAt ?: now())->toDateString();

        $alreadyRecorded = Receipt::query()
            ->where('invoice_id', $invoice->id)
            ->where('source', 'payment')
            ->where('payment_method', 'card')
            ->where('direction', 'credit')
            ->whereDate('encaissement_date', $paidDate)
            ->where('amount_ttc', round($amountTtc, 2))
            ->where(function ($q) use ($stripeInvoiceId) {
                if ($stripeInvoiceId) {
                    $q->where('note', 'like', '%' . $stripeInvoiceId . '%');
                } else {
                    $q->whereNull('note');
                }
            })
            ->exists();

        if ($alreadyRecorded) {
            return $invoice;
        }

        $ratioHt = ((float) $invoice->total_amount_with_tax) > 0
            ? ((float) $invoice->total_amount / (float) $invoice->total_amount_with_tax)
            : 1.0;

        $amountHt = round($amountTtc * $ratioHt, 2);
        $clientName = $invoice->clientProfile
            ? trim(($invoice->clientProfile->first_name ?? '') . ' ' . ($invoice->clientProfile->last_name ?? ''))
            : 'Client';

        $noteParts = [];
        if ($sequenceNumber && $totalInstallments) {
            $noteParts[] = "Échéance {$sequenceNumber}/{$totalInstallments}";
        } elseif ($sequenceNumber) {
            $noteParts[] = "Échéance {$sequenceNumber}";
        } else {
            $noteParts[] = 'Échéance';
        }
        if ($stripeInvoiceId) {
            $noteParts[] = "Stripe invoice {$stripeInvoiceId}";
        }

        Receipt::create([
            'user_id' => $invoice->user_id,
            'invoice_id' => $invoice->id,
            'invoice_number' => (string) $invoice->invoice_number,
            'encaissement_date' => $paidDate,
            'client_name' => $clientName !== '' ? $clientName : 'Client',
            'nature' => 'service',
            'amount_ht' => $amountHt,
            'amount_ttc' => round($amountTtc, 2),
            'payment_method' => 'card',
            'direction' => 'credit',
            'source' => 'payment',
            'note' => implode(' - ', $noteParts),
        ]);

        $invoice->refresh();
        if ((float) $invoice->solde_restant <= 0.001) {
            $invoice->update(['status' => 'Payée']);
        } else {
            $invoice->update(['status' => 'Partiellement payée']);
        }

        return $invoice;
    }

    private function resolvePricingSnapshot(PackPurchase $purchase): array
    {
        $type = (string) ($purchase->purchase_type ?? 'pack');

        if ($type === 'training' && $purchase->digitalTraining) {
            $training = $purchase->digitalTraining;
            $ttc = max(0, ((int) ($training->price_cents ?? 0)) / 100);
            $taxRate = (float) ($training->tax_rate ?? 0);
            $ht = $taxRate > 0 ? ($ttc / (1 + ($taxRate / 100))) : $ttc;
            $tax = max(0, $ttc - $ht);

            return [
                $ht,
                $taxRate,
                $tax,
                $ttc,
                'Formation : ' . ($training->title ?? ('#' . $training->id)),
            ];
        }

        $pack = $purchase->pack;
        if (!$pack) {
            $fallbackTtc = 0.0;
            if (!is_null($purchase->installment_amount_cents) && !is_null($purchase->installments_total)) {
                $fallbackTtc = ((int) $purchase->installment_amount_cents * (int) $purchase->installments_total) / 100;
            }

            return [
                $fallbackTtc,
                0.0,
                0.0,
                $fallbackTtc,
                'Achat en ligne #' . $purchase->id,
            ];
        }

        $taxRate = (float) ($pack->tax_rate ?? 0);
        $ht = (float) ($pack->price ?? 0);
        $tax = $ht * ($taxRate / 100);
        $ttc = $ht + $tax;

        return [
            $ht,
            $taxRate,
            $tax,
            $ttc,
            'Pack : ' . ($pack->name ?? ('#' . $purchase->pack_product_id)),
        ];
    }
}
