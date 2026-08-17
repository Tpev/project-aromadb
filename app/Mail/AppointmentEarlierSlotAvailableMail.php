<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\AppointmentEarlierSlotOffer;
use App\Services\AppointmentMailDeliveryGuard;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AppointmentEarlierSlotAvailableMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public int $tries = 5;

    public function __construct(public AppointmentEarlierSlotOffer $offer)
    {
        $this->offer->loadMissing([
            'opportunity',
            'appointment.clientProfile',
            'appointment.user',
            'appointment.product',
            'appointment.practiceLocation',
        ]);
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            AppointmentMailDeliveryGuard::APPOINTMENT_HEADER => (string) $this->offer->appointment_id,
            AppointmentMailDeliveryGuard::MESSAGE_HEADER => 'earlier-slot-offer:'.$this->offer->id,
        ]);
    }

    public function build()
    {
        $appointment = $this->offer->appointment;
        $slot = $this->offer->opportunity->slot_start;

        return $this->applyPractitionerReplyTo($appointment->user)
            ->subject('Un créneau plus tôt est disponible le '.$slot->format('d/m à H:i'))
            ->markdown('emails.appointments.earlier-slot-available', [
                'appointment' => $appointment,
                'opportunity' => $this->offer->opportunity,
                'offerUrl' => route('appointments.earlier-slot.show', $this->offer->token),
                'managementUrl' => route('appointments.showPatient', $appointment->token),
            ]);
    }
}
