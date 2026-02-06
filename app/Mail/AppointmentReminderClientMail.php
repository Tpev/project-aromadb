<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentReminderClientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        // Ensure relations are available to the template
        $appointment->loadMissing([
            'product',
            'user',
            'clientProfile',
            'practiceLocation',
        ]);

        $this->appointment = $appointment;
    }

    public function build()
    {
        // Pretty modes string (e.g., "En Visio", "Dans le Cabinet")
        $modes = $this->appointment->product
            ? $this->appointment->product->getConsultationModes()
            : '—';

        // Resolve the address of the actual selected cabinet (if any)
        $cabinetAddress = null;
        if ($this->appointment->practiceLocation) {
            $pl = $this->appointment->practiceLocation;
            $cabinetAddress = $pl->full_address
                ?? trim(collect([$pl->address_line1, $pl->postal_code.' '.$pl->city])
                    ->filter()->implode("\n"));
        } elseif (($this->appointment->type ?? null) === 'cabinet') {
            // Fallback if no practice_location_id stored but type indicates cabinet
            $cabinetAddress = $this->appointment->user?->company_address;
        }

        // ✅ Reply-To: therapist (fallback to platform address)
        $replyToEmail = $this->appointment->user?->email ?? config('mail.from.address');
        $replyToName  = $this->appointment->user?->name  ?? config('mail.from.name');

        return $this->subject('Rappel de rendez-vous')
            ->replyTo($replyToEmail, $replyToName)
            ->markdown('emails.appointment_reminder', [
                'modes'          => $modes,
                'cabinetAddress' => $cabinetAddress,
            ]);
    }
}
