<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use PDF;

class InvoicePaymentReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public $invoice;
    public $therapistName;

    public function __construct(Invoice $invoice, string $therapistName)
    {
        $this->invoice = $invoice;
        $this->therapistName = $therapistName;
    }

    public function build()
    {
        $this->invoice->loadMissing([
            'user',
            'clientProfile.company',
            'corporateClient',
            'items.product',
            'items.inventoryItem',
        ]);

        $pdf = PDF::loadView('invoices.pdf', [
            'invoice' => $this->invoice,
        ])->output();

        return $this->applyPractitionerReplyTo($this->invoice->user)
            ->subject("Rappel de paiement - Facture #{$this->invoice->invoice_number}")
            ->markdown('emails.invoices.payment_reminder')
            ->attachData(
                $pdf,
                "facture_{$this->invoice->safe_document_number}.pdf",
                ['mime' => 'application/pdf']
            );
    }
}
