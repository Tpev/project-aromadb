<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCreatedPatientMail extends Mailable
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
        return $this->subject('Confirmation de votre rendez-vous')
                    ->markdown('emails.appointment_created_patient');
    }
}