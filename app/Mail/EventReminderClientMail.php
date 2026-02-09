<?php

namespace App\Mail;

use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventReminderClientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Event $event;
    public Reservation $reservation;
    public string $timingLabel;

    /**
     * $timingLabel example: "24h" or "1h"
     */
    public function __construct(Event $event, Reservation $reservation, string $timingLabel = '24h')
    {
        $event->loadMissing(['user']);
        $this->event = $event;
        $this->reservation = $reservation;
        $this->timingLabel = $timingLabel;
    }

    public function build()
    {
        $subject = match ($this->timingLabel) {
            '1h' => 'Rappel : votre événement commence dans 1 heure',
            default => 'Rappel : votre événement approche',
        };

        // Determine format + link
        $isVisio = ($this->event->event_type ?? 'in_person') === 'visio';

        // Participant link (external or AromaMade public link)
        // Note: for AromaMade (Jitsi+JWT), this should be generated dynamically via accessor.
        $visioJoinLink = null;

        if ($isVisio) {
            if (!empty($this->event->visio_url)) {
                $visioJoinLink = $this->event->visio_url;
            } elseif (!empty($this->event->visio_public_link)) {
                $visioJoinLink = $this->event->visio_public_link;
            }
        }

        return $this->subject($subject)
            ->markdown('emails.event_reminder', [
                'visioJoinLink' => $visioJoinLink,
                'isVisio' => $isVisio,
            ]);
    }
}
