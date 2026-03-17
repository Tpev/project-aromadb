<?php

namespace App\Services;

use App\Jobs\SendGiftVoucherEmailsJob;
use App\Models\GiftVoucher;
use App\Models\GiftVoucherOrder;
use Illuminate\Support\Facades\DB;

class GiftVoucherCheckoutService
{
    public function __construct(
        private readonly GiftVoucherCodeGenerator $codeGenerator,
        private readonly GiftVoucherInvoiceService $invoiceService
    ) {}

    public function finalizePaidOrder(
        GiftVoucherOrder $order,
        string $stripeSessionId,
        ?string $paymentIntentId = null
    ): GiftVoucher {
        return DB::transaction(function () use ($order, $stripeSessionId, $paymentIntentId) {
            /** @var GiftVoucherOrder $lockedOrder */
            $lockedOrder = GiftVoucherOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedOrder->status === 'paid' && $lockedOrder->gift_voucher_id) {
                return GiftVoucher::findOrFail($lockedOrder->gift_voucher_id);
            }

            $therapist = $lockedOrder->therapist;
            $snapshot = GiftVoucherBackgroundService::snapshotForVoucher($therapist);

            $voucher = GiftVoucher::create([
                'user_id' => $lockedOrder->user_id,
                'code' => $this->codeGenerator->generateUniqueCode(),
                'original_amount_cents' => $lockedOrder->amount_cents,
                'remaining_amount_cents' => $lockedOrder->amount_cents,
                'currency' => $lockedOrder->currency ?: 'EUR',
                'is_active' => true,
                'expires_at' => $lockedOrder->expires_at,
                'buyer_name' => $lockedOrder->buyer_name,
                'buyer_email' => $lockedOrder->buyer_email,
                'buyer_phone' => $lockedOrder->buyer_phone,
                'recipient_name' => $lockedOrder->recipient_name,
                'recipient_email' => $lockedOrder->recipient_email,
                'message' => $lockedOrder->message,
                'source' => 'stripe',
                'sale_channel' => 'online_stripe',
                'sale_status' => 'paid',
                'background_mode_snapshot' => $snapshot['mode'],
                'background_path_snapshot' => $snapshot['path'],
            ]);

            $invoice = $this->invoiceService->createSaleInvoice(
                $voucher,
                'card',
                'Vente bon cadeau (achat en ligne)'
            );

            if ($invoice) {
                $voucher->sale_invoice_id = $invoice->id;
                $voucher->save();
            }

            $lockedOrder->status = 'paid';
            $lockedOrder->gift_voucher_id = $voucher->id;
            $lockedOrder->sale_invoice_id = $invoice?->id;
            $lockedOrder->stripe_session_id = $stripeSessionId;
            $lockedOrder->stripe_payment_intent_id = $paymentIntentId;
            $lockedOrder->save();

            SendGiftVoucherEmailsJob::dispatch($voucher->id);

            return $voucher;
        });
    }
}

