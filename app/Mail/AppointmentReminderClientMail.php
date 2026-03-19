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

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        // Ensure relations are available to the template
        $appointment->loadMissing([
            'product',
            'user',
            'clientProfile',
            'practiceLocation',
            'meeting', // 👈 needed for visio link
        ]);

        $this->appointment = $appointment;
    }

    public function build()
    {
        $resolvedMode = method_exists($this->appointment, 'getResolvedMode')
            ? $this->appointment->getResolvedMode()
            : ($this->appointment->type ?? null);

        $modeLabel = method_exists($this->appointment, 'getResolvedModeLabel')
            ? $this->appointment->getResolvedModeLabel()
            : ($this->appointment->product?->getConsultationModes() ?? '—');

        // Resolve cabinet address
        $cabinetAddress = null;
        if ($resolvedMode === 'cabinet' && $this->appointment->practiceLocation) {
            $pl = $this->appointment->practiceLocation;
            $cabinetAddress = $pl->full_address
                ?? trim(collect([
                    $pl->address_line1,
                    trim(($pl->postal_code ?? '') . ' ' . ($pl->city ?? '')),
                ])->filter()->implode("\n"));
        } elseif ($resolvedMode === 'cabinet') {
            $cabinetAddress = $this->appointment->user?->company_address;
        }

        $clientAddress = null;
        if (in_array($resolvedMode, ['domicile', 'entreprise'], true)) {
            $clientAddress = trim((string) ($this->appointment->address ?: $this->appointment->clientProfile?->address ?: ''));
            $clientAddress = $clientAddress !== '' ? $clientAddress : null;
        }

        // --- Visio link resolution (SAFE & non-breaking) ---
        $visioUrl = null;
        $isVisio = false;

        if (in_array((string) $resolvedMode, ['visio', 'video', 'teleconsultation'], true)) {
            $isVisio = true;
        }

        if (!$isVisio && in_array((string) ($this->appointment->type ?? ''), ['visio', 'video', 'teleconsultation'], true)) {
            $isVisio = true;
        }

        if (!$isVisio && (bool) ($this->appointment->product?->visio ?? false)) {
            $isVisio = true;
        }

        if ($isVisio && $this->appointment->meeting && !empty($this->appointment->meeting->room_token)) {
            $room = (string) $this->appointment->meeting->room_token;

            try {
                /** @var \App\Services\JitsiJwtService $jitsi */
                $jitsi = app(\App\Services\JitsiJwtService::class);
                $jwt = $jitsi->makeJwtForClient([
                    'room' => $room,
                    'appointment' => $this->appointment,
                ]);

                $base = rtrim(config('services.jitsi.base_url', 'https://visio.aromamade.com'), '/');
                $visioUrl = "{$base}/{$room}?jwt={$jwt}";
            } catch (\Throwable $e) {
                // Fallback to non-JWT room URL so reminder still includes a usable link.
                $visioUrl = route('webrtc.room', ['room' => $room]);
            }
        }

        if ($isVisio && empty($visioUrl)) {
            $visioUrl = $this->appointment->meeting_link
                ?? $this->appointment->meeting?->join_url
                ?? $this->appointment->meeting?->url
                ?? null;
        }

        // Reply-To therapist
        $replyToEmail = $this->appointment->user?->email ?? config('mail.from.address');
        $replyToName  = $this->appointment->user?->name  ?? config('mail.from.name');

        return $this->subject('Rappel de rendez-vous')
            ->replyTo($replyToEmail, $replyToName)
            ->markdown('emails.appointment_reminder', [
                'appointment'     => $this->appointment,
                'resolvedMode'    => $resolvedMode,
                'modeLabel'       => $modeLabel,
                'cabinetAddress'  => $cabinetAddress,
                'clientAddress'   => $clientAddress,
                'visioUrl'        => $visioUrl, // 👈 NEW
            ]);
    }
}
