<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class AppointmentBooked extends Notification implements ShouldQueue
{
    use Queueable;

    protected $appointment;

    /**
     * Create a new notification instance.
     *
     * @param \App\Models\Appointment $appointment
     */
    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification for the database.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toDatabase($notifiable)
    {
        return [
            'appointment_id' => $this->appointment->id,
            'client_name' => $this->appointment->clientProfile->first_name . ' ' . $this->appointment->clientProfile->last_name,
            'appointment_date' => $this->appointment->appointment_date->format('d/m/Y H:i'),
            'message' => 'Nouveau RDV reservé par ' . $this->appointment->clientProfile->first_name . ' ' . $this->appointment->clientProfile->last_name,
            'url' => route('appointments.show', $this->appointment->id),
        ];
    }
}
