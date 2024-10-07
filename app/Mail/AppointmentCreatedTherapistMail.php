<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentCreatedTherapistMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $appointment;

    /**
     * Crée une nouvelle instance du message.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return void
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Nouveau rendez-vous programmé')
                    ->markdown('emails.appointment_created_therapist');
    }
}
