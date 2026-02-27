<?php
// app/Console/Commands/SendOneHourReminder.php
//
// Retro-compatible + idempotent:
// - If column reminder_1h_sent_at exists => prevents duplicates (atomic claim)
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

class SendOneHourReminder extends Command
{
    protected $signature = 'email:send-one-hour-reminder';
    protected $description = 'Send email reminders 1 hour before appointments';

    public function handle()
    {
        // Window: appointments between 50 and 70 minutes from now
        $startReminderWindow = Carbon::now()->addMinutes(50)->startOfMinute();
        $endReminderWindow   = Carbon::now()->addMinutes(70)->endOfMinute();

        $hasSentColumn = Schema::hasColumn('appointments', 'reminder_1h_sent_at');

        $query = Appointment::whereBetween('appointment_date', [$startReminderWindow, $endReminderWindow])
            ->with('clientProfile', 'user', 'product')
            ->orderBy('id');

        if ($hasSentColumn) {
            $query->whereNull('reminder_1h_sent_at');
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
                        ->whereNull('reminder_1h_sent_at')
                        ->update(['reminder_1h_sent_at' => now()]);

                    if ($claimed !== 1) {
                        continue; // already handled by another run
                    }
                }

                $mailable = new AppointmentReminderClientMail($appointment);

                // If the mailable is queueable, prefer queue() (your mail currently implements ShouldQueue)
                if (method_exists($mailable, 'queue')) {
                    Mail::to($email)->queue($mailable);
                } else {
                    Mail::to($email)->send($mailable);
                }

                $sent++;
            }
        });

        $this->info("One-hour reminder emails processed. Sent/queued: {$sent}");
    }
}