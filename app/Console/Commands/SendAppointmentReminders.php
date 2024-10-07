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
        // Find appointments that are scheduled 24 hours from now
        $reminderDate = Carbon::now()->addDay();
        
        // Fetch all appointments happening in 24 hours
        $appointments = Appointment::whereBetween('appointment_date', [
            $reminderDate->copy()->startOfMinute(),
            $reminderDate->copy()->endOfMinute(),
        ])->with('clientProfile', 'user', 'product')->get();

        foreach ($appointments as $appointment) {
            // Ensure the client has an email
            if ($appointment->clientProfile && $appointment->clientProfile->email) {
                Mail::to($appointment->clientProfile->email)
                    ->send(new AppointmentReminderClientMail($appointment));
            }
        }

        $this->info('Appointment reminder emails have been sent successfully.');
    }
}

