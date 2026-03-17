<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\GiftVoucher;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;

class GiftVoucherInvoiceService
{
    public function createSaleInvoice(
        GiftVoucher $voucher,
        string $paymentMethod = 'other',
        string $note = 'Vente bon cadeau'
    ): ?Invoice {
        $therapist = $voucher->therapist;
        if (! $therapist) {
            return null;
        }

        $client = $this->resolveBuyerClientProfile($voucher);
        if (! $client) {
            return null;
        }

        return DB::transaction(function () use ($voucher, $therapist, $client, $paymentMethod, $note) {
            $lastInvoice = Invoice::where('user_id', $therapist->id)
                ->lockForUpdate()
                ->orderBy('invoice_number', 'desc')
                ->first();

            $nextInvoiceNumber = $lastInvoice ? ((int) $lastInvoice->invoice_number + 1) : 1;
            $amountHt = round((float) $voucher->original_amount_cents / 100, 2);
            $taxRate = 0.0;

            $invoice = Invoice::create([
                'client_profile_id' => $client->id,
                'user_id' => $therapist->id,
                'invoice_date' => now()->toDateString(),
                'due_date' => now()->toDateString(),
                'total_amount' => $amountHt,
                'total_tax_amount' => 0,
                'total_amount_with_tax' => $amountHt,
                'status' => 'Payée',
                'notes' => $note,
                'invoice_number' => $nextInvoiceNumber,
                'type' => 'invoice',
            ]);

            $invoice->items()->create([
                'type' => 'custom',
                'label' => 'Vente bon cadeau',
                'description' => 'Bon cadeau ' . $voucher->code,
                'quantity' => 1,
                'unit_price' => $amountHt,
                'tax_rate' => $taxRate,
                'tax_amount' => 0,
                'total_price' => $amountHt,
                'total_price_with_tax' => $amountHt,
            ]);

            Receipt::create([
                'user_id' => $therapist->id,
                'invoice_id' => $invoice->id,
                'invoice_number' => (string) $invoice->invoice_number,
                'encaissement_date' => now()->toDateString(),
                'client_name' => trim($client->first_name . ' ' . $client->last_name),
                'nature' => 'service',
                'amount_ht' => $amountHt,
                'amount_ttc' => $amountHt,
                'payment_method' => $this->normalizePaymentMethod($paymentMethod),
                'direction' => 'credit',
                'source' => 'manual',
                'note' => 'Paiement bon cadeau ' . $voucher->code,
            ]);

            return $invoice;
        });
    }

    private function resolveBuyerClientProfile(GiftVoucher $voucher): ?ClientProfile
    {
        $therapist = $voucher->therapist;
        if (! $therapist) {
            return null;
        }

        $fullName = trim((string) $voucher->buyer_name);
        $firstName = $fullName !== '' ? strtok($fullName, ' ') : 'Acheteur';
        $lastName = $fullName !== '' ? trim(substr($fullName, strlen((string) $firstName))) : 'Bon cadeau';
        if ($lastName === '') {
            $lastName = 'Bon cadeau';
        }

        $email = $voucher->buyer_email ? strtolower((string) $voucher->buyer_email) : null;

        if ($email) {
            return ClientProfile::firstOrCreate(
                [
                    'user_id' => $therapist->id,
                    'email' => $email,
                ],
                [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'phone' => $voucher->buyer_phone,
                ]
            );
        }

        return ClientProfile::create([
            'user_id' => $therapist->id,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => null,
            'phone' => $voucher->buyer_phone,
            'notes' => 'Profil créé automatiquement depuis une vente de bon cadeau.',
        ]);
    }

    private function normalizePaymentMethod(string $paymentMethod): string
    {
        return in_array($paymentMethod, ['transfer', 'card', 'check', 'cash', 'other'], true)
            ? $paymentMethod
            : 'other';
    }
}

