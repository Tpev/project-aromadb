<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Event;
use App\Models\Reservation;
use App\Support\EventVisioJoinLink;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class EventReminderClientMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

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

    public function headers(): \Illuminate\Mail\Mailables\Headers
    {
        return new \Illuminate\Mail\Mailables\Headers(text: [
            \App\Services\EventMailDeliveryGuard::RESERVATION_HEADER => (string) $this->reservation->id,
            \App\Services\EventMailDeliveryGuard::MESSAGE_HEADER => 'reminder:'.$this->timingLabel,
        ]);
    }

    public function build()
    {
        $this->event = $this->event->fresh(['user', 'associatedProduct']) ?? $this->event;
        $subject = match ($this->timingLabel) {
            '1h' => 'Rappel : votre événement commence dans 1 heure',
            default => 'Rappel : votre événement approche',
        };

        // Determine format + link
        $isVisio = ($this->event->event_type ?? 'in_person') === 'visio';

        $visioJoinLink = $isVisio
            ? app(EventVisioJoinLink::class)->for($this->event)
            : null;

        return $this->applyPractitionerReplyTo($this->event->user)
            ->subject($subject)
            ->markdown('emails.event_reminder', [
                'visioJoinLink' => $visioJoinLink,
                'isVisio' => $isVisio,
            ]);
    }
}
