<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class AppointmentCreatedTherapistMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    /**
     * Crée une nouvelle instance du message.
     *
     * @param  \App\Models\Appointment  $appointment
     * @return void
     */
    public function __construct(Appointment $appointment)
    {
        $appointment->loadMissing([
            'product',
            'user',
            'clientProfile',
            'practiceLocation',
        ]);

        $this->appointment = $appointment;
    }

    /**
     * Construire le message.
     *
     * @return $this
     */
    public function build()
    {
        $resolvedMode = method_exists($this->appointment, 'getResolvedMode')
            ? $this->appointment->getResolvedMode()
            : ($this->appointment->type ?? null);

        $modeLabel = method_exists($this->appointment, 'getResolvedModeLabel')
            ? $this->appointment->getResolvedModeLabel()
            : ($this->appointment->product?->getConsultationModes() ?? '—');

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

        return $this->subject('Nouveau rendez-vous programmé')
                    ->markdown('emails.appointment_created_therapist', [
                        'resolvedMode'   => $resolvedMode,
                        'modeLabel'      => $modeLabel,
                        'cabinetAddress' => $cabinetAddress,
                        'clientAddress'  => $clientAddress,
                        'appointmentUrl' => route('appointments.show', $this->appointment),
                    ]);
    }
}
