<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use PDF;

class QuoteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $quote;
    public $therapistName;

    public function __construct(Invoice $quote, $therapistName)
    {
        $this->quote = $quote;
        $this->therapistName = $therapistName;
    }

    public function build()
    {
        $pdf = PDF::loadView('invoices.pdf_quote', ['invoice' => $this->quote])->output();

        return $this->subject('Votre Devis n°' . $this->quote->quote_number)
                    ->markdown('emails.quotes.mail')
                    ->attachData($pdf, 'devis_' . $this->quote->quote_number . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}

