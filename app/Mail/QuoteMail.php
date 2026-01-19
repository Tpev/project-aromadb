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

    public Invoice $quote;
    public string $therapistName;
    public string $recipientName;

    public function __construct(Invoice $quote, $therapistName)
    {
        // Important: eager-load relations so queued mail doesn't crash on lazy loading
        $quote->loadMissing([
            'clientProfile.company',
            'corporateClient',
            'items.product',
            'items.inventoryItem',
            'user',
        ]);

        $this->quote = $quote;
        $this->therapistName = (string) $therapistName;

        // Compute a safe recipient display name (corporate > client)
        $client = $quote->clientProfile;
        $company = $quote->corporateClient ?: ($client?->company);

        if ($company) {
            $name = trim(($company->main_contact_first_name ?? '') . ' ' . ($company->main_contact_last_name ?? ''));
            if (!$name) {
                $name = $company->trade_name ?: $company->name ?: 'client';
            }
            $this->recipientName = $name;
        } else {
            $billingFirst = $client?->first_name_billing ?: $client?->first_name;
            $billingLast  = $client?->last_name_billing  ?: $client?->last_name;
            $name = trim((string) $billingFirst . ' ' . (string) $billingLast);
            $this->recipientName = $name ?: 'client';
        }
    }

    public function build()
    {
        $pdf = PDF::loadView('invoices.pdf_quote', ['invoice' => $this->quote])->output();

        return $this->subject('Votre Devis n°' . ($this->quote->quote_number ?? ''))
            ->markdown('emails.quotes.mail', [
                'quote'         => $this->quote,
                'therapistName' => $this->therapistName,
                'recipientName' => $this->recipientName,
            ])
            ->attachData($pdf, 'devis_' . ($this->quote->quote_number ?? 'devis') . '.pdf', [
                'mime' => 'application/pdf',
            ]);
    }
}
