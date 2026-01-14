<?php

namespace App\Mail;

use App\Models\GiftVoucher;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GiftVoucherBuyerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public GiftVoucher $voucher,
        public string $pdfBinary
    ) {}

    public function build()
    {
        $therapist = $this->voucher->therapist;

        return $this->subject('Votre bon cadeau – ' . ($therapist->company_name ?? $therapist->name ?? 'AromaMade'))
            ->markdown('emails.gift-voucher.buyer')
            ->with([
                'voucher' => $this->voucher,
                'therapist' => $therapist,
            ])
            ->attachData($this->pdfBinary, 'bon-cadeau-' . $this->voucher->code . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
