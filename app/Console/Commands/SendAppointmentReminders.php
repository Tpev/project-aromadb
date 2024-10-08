<?php
// app/Console/Commands/SendAppointmentReminders.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Appointment;
use App\Mail\AppointmentReminderClientMail;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    protected $signature = 'email:send-appointment-reminders';

    protected $description = 'Send email reminders 24 hours before appointments';

    public function handle()
    {
        // Set the time window to find appointments that are between 23 and 25 hours away
        $startReminderWindow = Carbon::now()->addHours(23);
        $endReminderWindow = Carbon::now()->addHours(25);

        // Fetch all appointments happening in that window
        $appointments = Appointment::whereBetween('appointment_date', [
            $startReminderWindow->copy()->startOfMinute(),
            $endReminderWindow->copy()->endOfMinute(),
        ])->with('clientProfile', 'user', 'product')->get();

        foreach ($appointments as $appointment) {
            if ($appointment->clientProfile && $appointment->clientProfile->email) {
                // Send the email to the client
                Mail::to($appointment->clientProfile->email)
                    ->send(new AppointmentReminderClientMail($appointment));
            }
        }

        $this->info('Appointment reminder emails have been sent successfully.');
    }
}
