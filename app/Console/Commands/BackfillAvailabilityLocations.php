<?php

namespace App\Console\Commands;

use App\Models\Availability;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAvailabilityLocations extends Command
{
    /**
     * Usage:
     *   php artisan app:backfill-availability-locations
     *   php artisan app:backfill-availability-locations --dry-run
     */
    protected $signature   = 'app:backfill-availability-locations {--dry-run : Show what would change without writing}';
    protected $description = 'Assign the primary practice location to all existing availabilities that have no practice_location_id.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $this->info(($dry ? '[DRY-RUN] ' : '') . 'Backfilling availability practice_location_id …');

        $totalUsers   = 0;
        $totalUpdated = 0;

        // Work user-by-user to respect multi-tenant data ownership
        User::query()->chunkById(200, function ($users) use (&$totalUsers, &$totalUpdated, $dry) {
            foreach ($users as $u) {
                $primary = $u->practiceLocations()->where('is_primary', true)->first();

                if (!$primary) {
                    // No primary location for this user → skip
                    continue;
                }

                $totalUsers++;

                // Count how many availabilities are missing a location
                $missingCount = Availability::where('user_id', $u->id)
                    ->whereNull('practice_location_id')
                    ->count();

                if ($missingCount === 0) {
                    continue;
                }

                $this->line(sprintf(
                    'User #%d: %d availability(ies) without location → will set to primary #%d',
                    $u->id,
                    $missingCount,
                    $primary->id
                ));

                if ($dry) {
                    $totalUpdated += $missingCount;
                    continue;
                }

                // Update in chunks for memory safety
                DB::transaction(function () use ($u, $primary, &$totalUpdated) {
                    Availability::where('user_id', $u->id)
                        ->whereNull('practice_location_id')
                        ->chunkById(500, function ($rows) use ($primary, &$totalUpdated) {
                            $ids = $rows->pluck('id');
                            $affected = Availability::whereIn('id', $ids)->update([
                                'practice_location_id' => $primary->id,
                                'updated_at'           => now(),
                            ]);
                            $totalUpdated += $affected;
                        });
                });
            }
        });

        $this->info(($dry ? '[DRY-RUN] ' : '') . "Users touched: {$totalUsers}");
        $this->info(($dry ? '[DRY-RUN] ' : '') . "Availabilities updated: {$totalUpdated}");

        return self::SUCCESS;
    }
}
