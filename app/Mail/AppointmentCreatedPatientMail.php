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
            'meeting', // uses Meeting::appointment_id + Appointment::meeting() hasOne
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

        /**
         * Visio link resolution (safe)
         * - Only provide a URL if appointment is truly visio
         * - Prefer Meeting.room_token (your current model)
         * - Keep compatibility with older columns if you ever had them
         */
        $visioUrl = null;

        // 1) Determine if appointment is visio
        $isVisio = false;

        // Based on product flags (recommended)
        if ($this->appointment->product) {
            $isVisio = (bool) ($this->appointment->product->visio ?? false);
        }

        // Fallback based on appointment type/mode (if you use it somewhere)
        if (!$isVisio && in_array(($this->appointment->type ?? null), ['visio', 'video', 'teleconsultation'], true)) {
            $isVisio = true;
        }

        // 2) Build URL if visio
        if ($isVisio) {
            // Backward/optional direct fields (won't break if absent)
            $visioUrl = $this->appointment->visio_url
                ?? $this->appointment->meeting_url
                ?? null;

            // Preferred: Meeting with room_token
            if (!$visioUrl && $this->appointment->meeting && !empty($this->appointment->meeting->room_token)) {
                // Adjust if your join route differs
                $visioUrl = url('/meeting/' . $this->appointment->meeting->room_token);

                // If you have a named route instead, use:
                // $visioUrl = route('meetings.join', $this->appointment->meeting->room_token);
            }

            // Optional fallback if you had appointment-level token in some older setup
            if (!$visioUrl && !empty($this->appointment->meeting_token)) {
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
