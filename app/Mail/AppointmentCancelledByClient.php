<?php

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentCancelledByClient extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Appointment $appointment;

    /**
     * Create a new message instance.
     */
    public function __construct(Appointment $appointment)
    {
        // If you rely on relations in the email view, it's safer to eager-load them now
        $this->appointment = $appointment->loadMissing([
            'user',
            'clientProfile',
            'product',
            'practiceLocation',
        ]);
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $a = $this->appointment;

        $clientFirst = $a->clientProfile?->first_name ?? '';
        $clientLast  = $a->clientProfile?->last_name ?? '';
        $clientName  = trim($clientFirst . ' ' . $clientLast);

        $therapistName = $a->user?->company_name ?: ($a->user?->name ?? 'Thérapeute');

        $dateStr = $a->appointment_date ? $a->appointment_date->format('d/m/Y') : '';
        $timeStr = $a->appointment_date ? $a->appointment_date->format('H:i') : '';

        $subject = __('Annulation de rendez-vous');
        if ($clientName !== '' && $dateStr !== '' && $timeStr !== '') {
            $subject = __('Annulation RDV :client — :date à :time', [
                'client' => $clientName,
                'date'   => $dateStr,
                'time'   => $timeStr,
            ]);
        } elseif ($clientName !== '') {
            $subject = __('Annulation RDV :client', ['client' => $clientName]);
        }

        $fromEmail = config('mail.from.address');
        $fromName  = config('mail.from.name');

        // Prefer the therapist company email as Reply-To (so they can reply easily if needed)
        $replyToEmail = $a->user?->company_email ?: $a->user?->email;

        $mailable = $this->subject($subject)
            ->from($fromEmail, $fromName)
            ->view('emails.appointments.cancelled-by-client')
            ->with([
                'appointment'    => $a,
                'clientName'     => $clientName,
                'therapistName'  => $therapistName,
                'dateStr'        => $dateStr,
                'timeStr'        => $timeStr,
            ]);

        if (!empty($replyToEmail)) {
            $mailable->replyTo($replyToEmail, $therapistName);
        }

        return $mailable;
    }
}
