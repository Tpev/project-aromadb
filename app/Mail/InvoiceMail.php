<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // Import ShouldQueue
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class InvoiceMail extends Mailable implements ShouldQueue // Implement ShouldQueue for queueing
{
    use Queueable, SerializesModels;

    public $invoice;
    public $therapistName;

    /**
     * Create a new message instance.
     *
     * @param \App\Models\Invoice $invoice
     * @param string $therapistName
     */
    public function __construct(Invoice $invoice, $therapistName)
    {
        $this->invoice = $invoice;
        $this->therapistName = $therapistName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Generate the PDF within the build method to avoid serialization issues
        $pdf = PDF::loadView('invoices.pdf', ['invoice' => $this->invoice])->output();

        return $this->subject('Votre Facture n°' . $this->invoice->invoice_number)
                    ->markdown('emails.invoices.mail')
                    ->attachData($pdf, 'facture_' . $this->invoice->invoice_number . '.pdf', [
                        'mime' => 'application/pdf',
                    ]);
    }
}
