<?php

namespace App\Jobs;

use App\Mail\GiftVoucherBuyerMail;
use App\Mail\GiftVoucherRecipientMail;
use App\Models\GiftVoucher;
use App\Services\GiftVoucherPdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendGiftVoucherEmailsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $voucherId) {}

    public function handle(GiftVoucherPdfService $pdfService): void
    {
        $voucher = GiftVoucher::with('therapist')->findOrFail($this->voucherId);

        // Build PDF once, attach to both.
        $pdfBinary = $pdfService->renderPdf($voucher);

        // Buyer email (required)
        if ($voucher->buyer_email) {
            Mail::to($voucher->buyer_email)->send(new GiftVoucherBuyerMail($voucher, $pdfBinary));
        }

        // Recipient email (optional)
        if ($voucher->recipient_email) {
            Mail::to($voucher->recipient_email)->send(new GiftVoucherRecipientMail($voucher, $pdfBinary));
        }
    }
}
