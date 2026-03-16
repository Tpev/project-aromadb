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

        // Prefer product flag
        if ($this->appointment->product?->visio) {
            $visioUrl = $this->appointment->meeting?->join_url
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
