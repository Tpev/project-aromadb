<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewReservationNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $reservation;
    public $event;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation)
    {
        $this->reservation = $reservation;
        $this->event = $reservation->event;
    }

    /**
     * Build the message.
     */
    public function headers(): \Illuminate\Mail\Mailables\Headers
    {
        return new \Illuminate\Mail\Mailables\Headers(text: [
            \App\Services\EventMailDeliveryGuard::RESERVATION_HEADER => (string) $this->reservation->id,
            \App\Services\EventMailDeliveryGuard::MESSAGE_HEADER => 'therapist_confirmation',
        ]);
    }

    public function build()
    {
        return $this->subject('Nouvelle réservation pour votre événement')
                    ->markdown('emails.new_reservation_notification');
    }
}
