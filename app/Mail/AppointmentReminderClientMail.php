<?php
// app/Mail/AppointmentReminderClientMail.php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderClientMail extends Mailable
{
    use Queueable, SerializesModels;

    public $appointment;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Rappel de rendez-vous')
                    ->markdown('emails.appointment_reminder');
    }
}
