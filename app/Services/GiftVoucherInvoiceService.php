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

        $providerReference = 'sale:' . $voucher->id;
        $existingReceipt = Receipt::where('user_id', $therapist->id)
            ->where('provider', 'gift_voucher')
            ->where('provider_reference', $providerReference)
            ->with('invoice')
            ->first();

        if ($existingReceipt?->invoice) {
            return $existingReceipt->invoice;
        }

        $client = $this->resolveBuyerClientProfile($voucher);
        if (! $client) {
            return null;
        }

        return DB::transaction(function () use ($voucher, $therapist, $client, $paymentMethod, $note, $providerReference) {
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
                'status' => 'En attente',
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

            app(InvoiceActivityService::class)->record(
                $invoice,
                'created',
                'Facture créée depuis la vente d’un bon cadeau.',
                metadata: ['gift_voucher_id' => $voucher->id]
            );

            app(ReceiptRecordingService::class)->recordInvoicePayment(
                $invoice,
                $amountHt,
                now()->toDateString(),
                $this->normalizePaymentMethod($paymentMethod),
                'manual',
                'Paiement bon cadeau ' . $voucher->code,
                'gift_voucher',
                $providerReference
            );

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
