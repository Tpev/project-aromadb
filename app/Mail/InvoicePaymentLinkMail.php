<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoicePaymentLinkMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $invoice;
    public $therapistName;
    public $recipientName;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice, string $therapistName, ?string $recipientName = null)
    {
        $this->invoice = $invoice;
        $this->therapistName = $therapistName;
        $this->recipientName = $recipientName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->invoice->loadMissing([
            'user',
            'clientProfile.company',
            'corporateClient',
            'items.product',
            'items.inventoryItem',
        ]);

        return $this->subject("{$this->therapistName} - Votre lien de paiement pour la facture #{$this->invoice->invoice_number}")
                    ->markdown('emails.invoices.payment_link');
    }
}
