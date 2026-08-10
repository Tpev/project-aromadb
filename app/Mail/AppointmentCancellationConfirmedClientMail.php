<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCancellationConfirmedClientMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public int $tries = 5;

    public function __construct(public Appointment $appointment)
    {
        $this->appointment->loadMissing(['clientProfile', 'user', 'product']);
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function build()
    {
        return $this->applyPractitionerReplyTo($this->appointment->user)
            ->subject('Confirmation de l’annulation de votre rendez-vous')
            ->markdown('emails.appointments.cancellation-confirmed-client', [
                'managementUrl' => route('appointments.showPatient', $this->appointment->token),
                'icsUrl' => route('appointments.downloadICS', $this->appointment->token),
            ]);
    }
}
