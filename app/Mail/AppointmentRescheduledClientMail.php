<?php

namespace App\Mail;

use App\Mail\Concerns\RepliesToPractitioner;
use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;
use App\Services\AppointmentMailDeliveryGuard;
use App\Services\AppointmentClientVisioUrlResolver;

class AppointmentRescheduledClientMail extends Mailable implements ShouldQueue
{
    use Queueable, RepliesToPractitioner, SerializesModels;

    public int $tries = 5;

    public function __construct(public Appointment $appointment, public Carbon $oldStart)
    {
        $this->appointment->loadMissing(['clientProfile', 'user', 'product', 'meeting']);
    }

    public function backoff(): array
    {
        return [60, 300, 900, 1800];
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            AppointmentMailDeliveryGuard::APPOINTMENT_HEADER => (string) $this->appointment->id,
            AppointmentMailDeliveryGuard::MESSAGE_HEADER => 'active-update',
        ]);
    }

    public function build()
    {
        $visioUrl = app(AppointmentClientVisioUrlResolver::class)->resolve($this->appointment);

        return $this->applyPractitionerReplyTo($this->appointment->user)
            ->subject('Votre nouveau créneau est confirmé')
            ->markdown('emails.appointments.rescheduled-client', [
                'managementUrl' => route('appointments.showPatient', $this->appointment->token),
                'icsUrl' => route('appointments.downloadICS', $this->appointment->token),
                'visioUrl' => $visioUrl,
            ]);
    }
}
