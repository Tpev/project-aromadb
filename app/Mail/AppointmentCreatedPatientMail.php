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
        $appointment->loadMissing([
            'product',
            'user',
            'clientProfile',
            'practiceLocation',
            'meeting', // requires Appointment::meeting() relationship
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

        // --- Visio link resolution (does not affect other modes) ---
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
            // Backward compat if you ever stored it directly
            $visioUrl = $this->appointment->visio_url
                ?? $this->appointment->meeting_url
                ?? null;

            // ✅ Your real setup: /webrtc/{room_token}
            if (!$visioUrl && $this->appointment->meeting && !empty($this->appointment->meeting->room_token)) {
                $visioUrl = url('/webrtc/' . $this->appointment->meeting->room_token);
            }

            // Optional fallback if you have appointment-level token somewhere
            if (!$visioUrl && !empty($this->appointment->meeting_token)) {
                $visioUrl = url('/webrtc/' . $this->appointment->meeting_token);
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
