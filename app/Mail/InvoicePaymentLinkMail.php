<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
	public $therapistName; // Add this property

    /**
     * Create a new message instance.
     *
     * @return void
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
        return $this->subject('Votre lien de paiement pour la facture #' . $this->invoice->invoice_number)
                    ->markdown('emails.invoices.payment_link');
    }
}
