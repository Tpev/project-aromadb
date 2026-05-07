<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CleanupCancelledAppointmentGoogleEvents extends Command
{
    protected $signature = 'appointments:cleanup-cancelled-google-events
        {--dry-run : Show what would be deleted without touching Google Calendar}
        {--limit= : Maximum number of appointments to inspect}';

    protected $description = 'Delete Google Calendar events still attached to cancelled appointments.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') !== null ? max(1, (int) $this->option('limit')) : null;

        $query = Appointment::query()
            ->with('user')
            ->whereNotNull('google_event_id')
            ->where(function ($q) {
                $q->whereIn('status', Appointment::CANCELLED_STATUSES);
            })
            ->orderBy('id');

        $stats = [
            'checked' => 0,
            'deleted' => 0,
            'dry_run' => 0,
            'skipped' => 0,
            'errors' => 0,
        ];

        $query->chunkById(50, function ($appointments) use ($dryRun, $limit, &$stats) {
            foreach ($appointments as $appointment) {
                if ($limit && $stats['checked'] >= $limit) {
                    return false;
                }

                $stats['checked']++;

                if (!$appointment->user?->google_access_token) {
                    $stats['skipped']++;
                    $this->warn("Appointment {$appointment->id}: skipped, therapist has no Google token.");
                    continue;
                }

                if ($dryRun) {
                    $stats['dry_run']++;
                    $this->info("Appointment {$appointment->id}: would delete Google event {$appointment->google_event_id}.");
                    continue;
                }

                try {
                    $eventId = $appointment->google_event_id;
                    $appointment->removeFromGoogle();
                    $stats['deleted']++;
                    $this->info("Appointment {$appointment->id}: deleted Google event {$eventId}.");
                } catch (\Throwable $e) {
                    $stats['errors']++;
                    Log::warning('Cancelled appointment Google event cleanup failed.', [
                        'appointment_id' => $appointment->id,
                        'google_event_id' => $appointment->google_event_id,
                        'error' => $e->getMessage(),
                    ]);
                    $this->error("Appointment {$appointment->id}: {$e->getMessage()}");
                }
            }
        });

        $this->table(
            ['Checked', 'Deleted', 'Dry run', 'Skipped', 'Errors'],
            [[$stats['checked'], $stats['deleted'], $stats['dry_run'], $stats['skipped'], $stats['errors']]]
        );

        return $stats['errors'] > 0 ? self::FAILURE : self::SUCCESS;
    }
}
