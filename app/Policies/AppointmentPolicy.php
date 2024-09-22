<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Determine whether the user can view the appointment.
     */
    public function view(User $user, Appointment $appointment)
    {
        \Log::info('Authorizing view for appointment', ['user_id' => $user->id, 'appointment_user_id' => $appointment->user_id]);

        // Check if the authenticated user is the one who created the appointment
        return $user->id === $appointment->user_id;
    }

    /**
     * Determine whether the user can update the appointment.
     */
    public function update(User $user, Appointment $appointment)
    {
        \Log::info('Authorizing update for appointment', ['user_id' => $user->id, 'appointment_user_id' => $appointment->user_id]);

        // Check if the authenticated user is the one who created the appointment
        return $user->id === $appointment->user_id;
    }

    /**
     * Determine whether the user can delete the appointment.
     */
    public function delete(User $user, Appointment $appointment)
    {
        \Log::info('Authorizing delete for appointment', ['user_id' => $user->id, 'appointment_user_id' => $appointment->user_id]);

        // Check if the authenticated user is the one who created the appointment
        return $user->id === $appointment->user_id;
    }
}
