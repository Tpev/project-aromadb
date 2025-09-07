<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentCreatedPatientMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $appointment;

    public function __construct(Appointment $appointment)
    {
        // Ensure everything is loaded for the template
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
        // Pretty consultation mode string from Product helper
        $modes = $this->appointment->product
            ? $this->appointment->product->getConsultationModes()
            : '—';

        // Prefer the exact practice location if set
        $cabinetAddress = null;
        if ($this->appointment->practiceLocation) {
            $pl = $this->appointment->practiceLocation;
            $cabinetAddress = $pl->full_address
                ?? trim(collect([$pl->address_line1, $pl->postal_code.' '.$pl->city])
                    ->filter()->implode("\n"));
        } elseif (($this->appointment->type ?? null) === 'cabinet') {
            // Fallback (old behavior) if type says cabinet but no location stored
            $cabinetAddress = $this->appointment->user?->company_address;
        }

        return $this->subject('Confirmation de votre rendez-vous')
            ->markdown('emails.appointment_created_patient', [
                'modes'          => $modes,
                'cabinetAddress' => $cabinetAddress,
            ]);
    }
}
