<?php
// app/Console/Commands/SendAppointmentReminders.php
//
// Retro-compatible + idempotent:
// - If column reminder_24h_sent_at exists => prevents duplicates (atomic claim)
// - If column doesn't exist yet => behaves like before (sends every time command runs)
// - Uses chunkById to avoid loading everything in memory
// - Uses ->queue() when the mailable implements ShouldQueue, else ->send()

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use App\Models\Appointment;
use App\Mail\AppointmentReminderClientMail;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    protected $signature = 'email:send-appointment-reminders';
    protected $description = 'Send email reminders 24 hours before appointments';

    public function handle()
    {
        // Window: appointments between 23 and 25 hours from now
        $startReminderWindow = Carbon::now()->addHours(23)->startOfMinute();
        $endReminderWindow   = Carbon::now()->addHours(25)->endOfMinute();

        $hasSentColumn = Schema::hasColumn('appointments', 'reminder_24h_sent_at');

        $query = Appointment::whereBetween('appointment_date', [$startReminderWindow, $endReminderWindow])
            ->with('clientProfile', 'user', 'product')
            ->orderBy('id');

        if ($hasSentColumn) {
            $query->whereNull('reminder_24h_sent_at');
        }

        $sent = 0;

        $query->chunkById(200, function ($appointments) use ($hasSentColumn, &$sent) {
            foreach ($appointments as $appointment) {
                $email = $appointment->clientProfile?->email;
                if (!$email) {
                    continue;
                }

                // If column exists, claim atomically to prevent duplicates across runs/overlaps
                if ($hasSentColumn) {
                    $claimed = Appointment::whereKey($appointment->id)
                        ->whereNull('reminder_24h_sent_at')
                        ->update(['reminder_24h_sent_at' => now()]);

                    if ($claimed !== 1) {
                        continue;
                    }
                }

                $mailable = new AppointmentReminderClientMail($appointment);

                if (method_exists($mailable, 'queue')) {
                    Mail::to($email)->queue($mailable);
                } else {
                    Mail::to($email)->send($mailable);
                }

                $sent++;
            }
        });

        $this->info("24h appointment reminder emails processed. Sent/queued: {$sent}");
    }
}