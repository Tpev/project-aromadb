<?php

namespace App\Mail;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReservationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Reservation $reservation;
    public $event;
    public ?string $visioUrl = null;
    public ?string $visioLabel = null;

    /**
     * Create a new message instance.
     */
    public function __construct(Reservation $reservation)
    {
        // Ensure we have everything needed in the email
        $reservation->loadMissing([
            'event.user',
            'event.associatedProduct',
        ]);

        $this->reservation = $reservation;
        $this->event = $reservation->event;

        // Compute visio URL (public link preferred)
        [$this->visioUrl, $this->visioLabel] = $this->computeVisioUrlAndLabel($this->event);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Confirmation de votre réservation')
            ->markdown('emails.reservation_confirmation', [
                'reservation' => $this->reservation,
                'event'       => $this->event,
                'visioUrl'    => $this->visioUrl,
                'visioLabel'  => $this->visioLabel,
            ]);
    }

    private function computeVisioUrlAndLabel($event): array
    {
        $eventType = $event->event_type ?? 'in_person';
        if ($eventType !== 'visio') {
            return [null, null];
        }

        // If you have two-link system via accessors:
        if (!empty($event->visio_public_link)) {
            return [$event->visio_public_link, 'Rejoindre la visio'];
        }

        // External visio
        if (!empty($event->visio_url)) {
            return [$event->visio_url, 'Rejoindre la visio'];
        }

        return [null, null];
    }
}
