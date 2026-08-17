<?php

namespace App\Mail;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentEarlierSlotClaimedTherapistMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public Appointment $appointment, public Carbon $oldStart)
    {
        $this->appointment->loadMissing(['clientProfile', 'user', 'product', 'practiceLocation']);
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function build()
    {
        return $this->subject('Un client a accepté un créneau plus tôt')
            ->markdown('emails.appointments.earlier-slot-claimed-therapist', [
                'appointmentUrl' => route('appointments.show', $this->appointment),
            ]);
    }
}
