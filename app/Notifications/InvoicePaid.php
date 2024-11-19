<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class InvoicePaid extends Notification implements ShouldQueue
{
    use Queueable;

    protected $invoice;

    /**
     * Crée une nouvelle instance de notification.
     *
     * @param \App\Models\Invoice $invoice
     */
    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Détermine les canaux de livraison de la notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Représentation de la notification pour le canal database.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number, // Assurez-vous que le modèle Invoice a un attribut 'number'
            'client_name' => $this->invoice->clientProfile->first_name . ' ' . $this->invoice->clientProfile->last_name,
            'amount_paid' => number_format($this->invoice->amount, 2, ',', ' ') . ' €',
            'message' => 'La facture n°' . $this->invoice->number . ' a été payée par ' . $this->invoice->clientProfile->first_name . ' ' . $this->invoice->clientProfile->last_name . '.',
            'url' => route('invoices.show', $this->invoice->id),
            // 'appointment_date' => ... (Supprimé)
        ];
    }
}
