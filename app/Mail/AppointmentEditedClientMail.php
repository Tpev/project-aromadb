<?php
// app/Mail/AppointmentEditedClientMail.php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentEditedClientMail extends Mailable
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public $appointment;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment)
    {
        $appointment->loadMissing('user');
        $this->appointment = $appointment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->applyPractitionerReplyTo($this->appointment->user)
                    ->subject('Votre rendez-vous a été modifié')
                    ->markdown('emails.appointment_edited');
    }
}
