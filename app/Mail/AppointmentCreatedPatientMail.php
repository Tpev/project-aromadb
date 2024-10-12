<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Product;
use App\Models\User;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentCreatedPatientMail extends Mailable implements ShouldQueue
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
    // Load the product relationship if it's not already eager loaded
    $appointment->load('product'); // Ensure the 'product' relationship is loaded
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
