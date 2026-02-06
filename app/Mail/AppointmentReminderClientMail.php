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
        // Pretty modes string
        $modes = $this->appointment->product
            ? $this->appointment->product->getConsultationModes()
            : '—';

        // Resolve cabinet address
        $cabinetAddress = null;
        if ($this->appointment->practiceLocation) {
            $pl = $this->appointment->practiceLocation;
            $cabinetAddress = $pl->full_address
                ?? trim(collect([
                    $pl->address_line1,
                    trim(($pl->postal_code ?? '') . ' ' . ($pl->city ?? '')),
                ])->filter()->implode("\n"));
        } elseif (($this->appointment->type ?? null) === 'cabinet') {
            $cabinetAddress = $this->appointment->user?->company_address;
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
                'modes'           => $modes,
                'cabinetAddress'  => $cabinetAddress,
                'visioUrl'        => $visioUrl, // 👈 NEW
            ]);
    }
}
