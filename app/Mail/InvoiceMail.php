<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class InvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $therapistName;

    public function __construct(Invoice $invoice, string $therapistName)
    {
        // we’ve already eager-loaded relations in the controller
        $this->invoice = $invoice;
        $this->therapistName = $therapistName;
    }

    public function build()
    {
        // build the PDF from the same view you use in generatePDF()
        $pdf = PDF::loadView('invoices.pdf', [
            'invoice' => $this->invoice,
        ])->output();

        return $this->subject("Votre Facture n°{$this->invoice->invoice_number}")
                    ->markdown('emails.invoices.mail')
                    ->attachData(
                        $pdf,
                        "facture_{$this->invoice->invoice_number}.pdf",
                        ['mime' => 'application/pdf']
                    );
    }
}
