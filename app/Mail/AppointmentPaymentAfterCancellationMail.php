<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentPaymentAfterCancellationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(
        public Appointment $appointment,
        public int $amountCents,
        public string $providerReference
    ) {
        $this->appointment->loadMissing(['clientProfile', 'user', 'product']);
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function build()
    {
        return $this->subject('Action requise : paiement reçu après une annulation')
            ->markdown('emails.appointments.payment-after-cancellation', [
                'appointmentUrl' => route('appointments.show', $this->appointment),
            ]);
    }
}
