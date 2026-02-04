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

		// --- Visio link resolution (Jitsi + JWT) ---
		$isVisio = false;

		if ($this->appointment->product) {
			$isVisio = (bool) ($this->appointment->product->visio ?? false);
		}

		if (!$isVisio && in_array(($this->appointment->type ?? null), ['visio', 'video', 'teleconsultation'], true)) {
			$isVisio = true;
		}

		$visioUrl = null;

		if ($isVisio && $this->appointment->meeting && !empty($this->appointment->meeting->room_token)) {
			$room = $this->appointment->meeting->room_token;

			/** @var \App\Services\JitsiJwtService $jitsi */
			$jitsi = app(\App\Services\JitsiJwtService::class);

			// patient = non-moderator
			$jwt = $jitsi->makeJwtForClient([
				'room' => $room,
				'appointment' => $this->appointment,
			]);

			$base = rtrim(config('services.jitsi.base_url', 'https://visio.aromamade.com'), '/');

			$visioUrl = "{$base}/{$room}?jwt={$jwt}";
		}


        // ✅ Magic link confirmation page URL
        // You said you want to keep the GET route name "appointments.showPatient"
        $confirmationUrl = route('appointments.showPatient', ['token' => $this->appointment->token]);

        // ✅ Cancellation cutoff message (based on therapist setting)
        $cutoffHours = max(0, (int) ($this->appointment->user?->cancellation_notice_hours ?? 0));
        $latestCancelAt = null;

        if ($cutoffHours > 0 && $this->appointment->appointment_date) {
            $latestCancelAt = $this->appointment->appointment_date->copy()->subHours($cutoffHours);
        }

        return $this->subject('Confirmation de votre rendez-vous')
            ->markdown('emails.appointment_created_patient', [
                'modes'           => $modes,
                'cabinetAddress'  => $cabinetAddress,
                'visioUrl'        => $visioUrl,
                'confirmationUrl' => $confirmationUrl,
                'cutoffHours'     => $cutoffHours,
                'latestCancelAt'  => $latestCancelAt,
            ]);
    }
}
