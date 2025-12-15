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

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        // Ensure everything is loaded for the template
        $appointment->loadMissing([
            'product',
            'user',
            'clientProfile',
            'practiceLocation',
            // If you have a meeting relation, this will load it without failing if it doesn't exist
            // (Laravel will fail if relation truly doesn't exist; remove if you don't have it.)
            'meeting',
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
                ?? trim(collect([
                        $pl->address_line1,
                        trim(($pl->postal_code ?? '') . ' ' . ($pl->city ?? '')),
                    ])->filter()->implode("\n"));
        } elseif (($this->appointment->type ?? null) === 'cabinet') {
            // Fallback (old behavior) if type says cabinet but no location stored
            $cabinetAddress = $this->appointment->user?->company_address;
        }

        // --- Visio link resolution (robust) ---
        $isVisio = false;

        // 1) Based on product flags (recommended)
        if ($this->appointment->product) {
            $isVisio = (bool) ($this->appointment->product->visio ?? false);
        }

        // 2) Fallback based on appointment type/mode if you use it
        if (!$isVisio && in_array(($this->appointment->type ?? null), ['visio', 'video', 'teleconsultation'], true)) {
            $isVisio = true;
        }

        $visioUrl = null;

        if ($isVisio) {
            // If you store directly on appointment
            $visioUrl = $this->appointment->visio_url
                ?? $this->appointment->meeting_url
                ?? null;

            // If you have a Meeting model related to appointment
            if (!$visioUrl && $this->appointment->relationLoaded('meeting') && $this->appointment->meeting) {
                $meeting = $this->appointment->meeting;

                $visioUrl = $meeting->join_url
                    ?? $meeting->url
                    ?? $meeting->room_url
                    ?? null;

                // If your meeting is token-based and you generate URL from token
                if (!$visioUrl && !empty($meeting->token)) {
                    // Adjust the path to your real route
                    $visioUrl = url('/meeting/' . $meeting->token);
                }
            }

            // If appointment has a token (some setups)
            if (!$visioUrl && !empty($this->appointment->meeting_token)) {
                // Adjust the path to your real route
                $visioUrl = url('/meeting/' . $this->appointment->meeting_token);
            }
        }

        return $this->subject('Confirmation de votre rendez-vous')
            ->markdown('emails.appointment_created_patient', [
                'modes'          => $modes,
                'cabinetAddress' => $cabinetAddress,
                'visioUrl'       => $visioUrl,
            ]);
    }
}
