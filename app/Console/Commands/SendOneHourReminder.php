<?php
// app/Console/Commands/SendOneHourReminder.php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\Appointment;
use App\Mail\AppointmentReminderClientMail;
use Carbon\Carbon;

class SendOneHourReminder extends Command
{
    protected $signature = 'email:send-one-hour-reminder';

    protected $description = 'Send email reminders 1 hour before appointments';

    public function handle()
    {
        // Set a time window to catch appointments between 50 and 70 minutes from now
        $startReminderWindow = Carbon::now()->addMinutes(50);
        $endReminderWindow = Carbon::now()->addMinutes(70);

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

        $this->info('One-hour reminder emails have been sent successfully.');
    }
}
