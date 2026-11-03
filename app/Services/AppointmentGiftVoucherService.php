<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\GiftVoucher;
use App\Models\Invoice;
use App\Models\Receipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentGiftVoucherService
{
    public function applied(Appointment $appointment)
    {
        return $appointment->giftVoucherRedemptions
            ->where('user_id', $appointment->user_id)->where('status', 'applied');
    }

    public function paymentLabel(Appointment $appointment): ?string
    {
        $amount = (int) $this->applied($appointment)->sum('amount_cents');
        if ($amount <= 0) {
            return $appointment->giftVoucherRedemptions->where('user_id', $appointment->user_id)
                ->where('status', 'reserved')->isNotEmpty() ? 'Bon cadeau réservé · paiement en attente' : null;
        }

        $label = 'Bon cadeau : '.$this->money($amount);
        if ($appointment->isCancelled()) {
            return 'Bon cadeau utilisé : '.$this->money($amount);
        }

        $total = $appointment->gift_voucher_total_cents;
        $remaining = $total !== null ? max(0, $total - $amount) : null;
        if ($appointment->billingInvoices->count() === 1) {
            $invoice = $appointment->billingInvoices->first();
            $total = (int) round($invoice->total_amount_with_tax * 100);
            // Old invoices may already include a manually entered payment. Do not infer
            // another deduction unless voucher payment is explicitly recorded.
            $hasVoucherReceipt = $invoice->receipts->contains(fn ($receipt) => $receipt->provider === 'gift_voucher');
            $remaining = $hasVoucherReceipt ? $this->invoiceRemaining($invoice) : null;
        } elseif ($appointment->billingInvoices->count() > 1) {
            $remaining = null;
        }

        if ($remaining === 0) {
            return $amount >= $total ? 'Réglé par bon cadeau' : 'Réglé · dont '.$this->money($amount).' en bon cadeau';
        }

        return $remaining !== null ? $label.' · Reste à régler : '.$this->money($remaining) : $label;
    }

    public function unavailableReason(Appointment $appointment): ?string
    {
        if ($appointment->external || $appointment->isCancelled()) {
            return 'Ce rendez-vous ne peut pas être réglé par bon cadeau.';
        }
        if ($appointment->consumed_pack_purchase_id) {
            return 'Ce rendez-vous utilise déjà un crédit de pack.';
        }
        if ($this->applied($appointment)->isNotEmpty()) {
            return 'Un bon cadeau a déjà été utilisé pour ce rendez-vous.';
        }
        if ($appointment->isPendingPayment() || $appointment->giftVoucherRedemptions->where('status', 'reserved')->isNotEmpty()) {
            return 'Un paiement est en attente. Finalisez ou annulez cette réservation avant d’utiliser un autre bon cadeau.';
        }
        if ((int) $appointment->gift_voucher_amount_cents > 0) {
            return 'Un bon cadeau est déjà associé à ce rendez-vous. Vérifiez son historique avant toute nouvelle utilisation.';
        }
        if ($appointment->billingInvoices->count() > 1) {
            return 'Plusieurs factures sont associées à ce rendez-vous. Vérifiez leur règlement avant d’utiliser un bon cadeau.';
        }
        if ($appointment->isPaid()) {
            return 'Ce rendez-vous est déjà réglé.';
        }
        $invoice = $appointment->billingInvoices->first();
        if ($invoice && ((int) $invoice->user_id !== (int) $appointment->user_id
            || (int) $invoice->client_profile_id !== (int) $appointment->client_profile_id)) {
            return 'La facture associée ne correspond pas à ce rendez-vous.';
        }
        if ($invoice && in_array($invoice->normalizedStatus(), ['partiellement payee', 'partially paid'], true) && ! $invoice->hasPositiveNetReceipt()) {
            return 'Vérifiez les encaissements de la facture avant d’utiliser un bon cadeau.';
        }
        if ($this->remainingBeforeVoucher($appointment) <= 0) {
            return 'Aucun montant ne reste à régler pour ce rendez-vous.';
        }

        return null;
    }

    public function quote(Appointment $appointment, string $code): array
    {
        if ($reason = $this->unavailableReason($appointment)) {
            $this->fail($reason);
        }
        $voucher = $this->findVoucher($appointment, $code);
        if (! $voucher->isUsable() || strtoupper($voucher->currency) !== 'EUR') {
            $this->fail('Ce bon cadeau n’est pas utilisable (expiré, désactivé, épuisé ou devise incompatible).');
        }

        $remaining = $this->remainingBeforeVoucher($appointment);
        $amount = min($remaining, (int) $voucher->remaining_amount_cents);

        return [
            'appointment_id' => $appointment->id,
            'code' => $voucher->code,
            'amount_cents' => $amount,
            'total_cents' => $this->total($appointment),
            'remaining_cents' => $remaining - $amount,
            'balance_after_cents' => $voucher->remaining_amount_cents - $amount,
        ];
    }

    public function apply(Appointment $appointment, string $code, int $expectedAmount, int $expectedTotal): void
    {
        DB::transaction(function () use ($appointment, $code, $expectedAmount, $expectedTotal) {
            $locked = Appointment::whereKey($appointment->id)->lockForUpdate()->firstOrFail();
            $locked->setRelation('billingInvoices', $locked->billingInvoices()->lockForUpdate()->get());
            $voucher = $this->findVoucher($locked, $code, true);

            // The appointment lock serializes repeated submissions, even after the
            // voucher has become exhausted or expired since the first confirmation.
            if ($this->applied($locked)->where('gift_voucher_id', $voucher->id)->isNotEmpty()) {
                return;
            }
            $quote = $this->quote($locked, $voucher->code);
            if ($quote['amount_cents'] !== $expectedAmount || $quote['total_cents'] !== $expectedTotal) {
                $this->fail('Le montant a changé depuis la vérification. Vérifiez à nouveau le bon cadeau avant de confirmer.');
            }

            app(GiftVoucherRedeemService::class)->redeem(
                $voucher, $quote['amount_cents'], 'Règlement du rendez-vous par bon cadeau',
                $locked->user_id, $locked->id, null, 'appointment', 'applied'
            );
            // Do not alter attendance or trigger appointment emails/calendar updates.
            $locked->forceFill([
                'gift_voucher_id' => $voucher->id,
                'gift_voucher_amount_cents' => $quote['amount_cents'],
                'gift_voucher_total_cents' => $quote['total_cents'],
            ])->saveQuietly();
            $locked->unsetRelation('giftVoucherRedemptions');
            if ($invoice = $locked->billingInvoices->first()) {
                $this->recordOnInvoice($locked, $invoice);
            }
        }, 3);
    }

    /** Record existing usage, never redeem the voucher again. Caller owns the transaction. */
    public function recordOnInvoice(Appointment $appointment, Invoice $invoice): void
    {
        $redemptions = $this->applied($appointment);
        if ($redemptions->isEmpty() || $appointment->billingInvoices()->count() !== 1) {
            return;
        }
        foreach ($redemptions->groupBy('gift_voucher_id') as $voucherId => $uses) {
            $reference = 'appointment:'.$appointment->id.':voucher:'.$voucherId;
            $receipt = Receipt::where('user_id', $appointment->user_id)->where('provider', 'gift_voucher')
                ->where('provider_reference', $reference)->first();
            if ($receipt || $uses->contains(fn ($use) => $use->invoice_id !== null)) {
                continue;
            }
            $amount = (int) $uses->sum('amount_cents');
            if ($this->invoiceRemaining($invoice) < $amount) {
                throw ValidationException::withMessages([
                    'appointment_id' => 'Le solde de la facture est inférieur au montant déjà utilisé en bon cadeau. Vérifiez la facture et ses encaissements.',
                ]);
            }
            app(ReceiptRecordingService::class)->recordInvoicePayment(
                $invoice, $amount / 100, $uses->first()->created_at->toDateString(), 'other', 'payment',
                'Paiement bon cadeau '.$uses->first()->voucher?->code, 'gift_voucher', $reference
            );
            foreach ($uses as $use) {
                $use->update(['invoice_id' => $invoice->id]);
            }
            $invoice->refresh();
        }
    }

    private function findVoucher(Appointment $appointment, string $code, bool $lock = false): GiftVoucher
    {
        $voucher = GiftVoucher::where('user_id', $appointment->user_id)
            ->where('code', strtoupper(trim($code)))->when($lock, fn ($query) => $query->lockForUpdate())->first();
        if (! $voucher) {
            $this->fail('Ce code ne correspond à aucun bon cadeau Olithea de votre cabinet.');
        }

        return $voucher;
    }

    private function total(Appointment $appointment): int
    {
        if ($invoice = $appointment->billingInvoices->first()) {
            return (int) round($invoice->total_amount_with_tax * 100);
        }
        $product = $appointment->product;

        return $product ? max(0, (int) round($product->price * (1 + $product->tax_rate / 100) * 100)) : 0;
    }

    private function remainingBeforeVoucher(Appointment $appointment): int
    {
        $invoice = $appointment->billingInvoices->first();

        return $invoice ? $this->invoiceRemaining($invoice) : $this->total($appointment);
    }

    private function invoiceRemaining(Invoice $invoice): int
    {
        $received = $invoice->receipts->sum(fn (Receipt $receipt) => $receipt->signed_amount_ttc);

        return in_array($invoice->normalizedStatus(), ['payee', 'paid'], true)
            ? 0 : max(0, (int) round(((float) $invoice->total_amount_with_tax - $received) * 100));
    }

    private function money(int $cents): string
    {
        return number_format($cents / 100, 2, ',', ' ').' €';
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['gift_voucher_code' => $message]);
    }
}
