<?php

namespace App\Console\Commands;

use App\Models\Availability;
use App\Models\SpecialAvailability;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class BackfillAvailabilityLocations extends Command
{
    /**
     * Usage:
     *   php artisan app:backfill-availability-locations
     *   php artisan app:backfill-availability-locations --user-id=123 --dry-run
     */
    protected $signature = 'app:backfill-availability-locations
        {--dry-run : Show what would change without writing}
        {--user-id=* : Limit the operation to one or more practitioner IDs}';

    protected $description = 'Assign the primary practice location to weekly and special availabilities that have no location.';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');
        $rawUserIds = collect($this->option('user-id'));
        if ($rawUserIds->contains(fn ($id): bool => ! ctype_digit((string) $id) || (int) $id <= 0)) {
            $this->error('Chaque option --user-id doit contenir un identifiant positif.');

            return self::FAILURE;
        }

        $requestedUserIds = $rawUserIds
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $this->info(($dry ? '[DRY-RUN] ' : '').'Backfilling availability practice_location_id...');

        $usersTouched = 0;
        $weeklyUpdated = 0;
        $specialUpdated = 0;
        $foundUserIds = collect();

        $users = User::query()
            ->when($requestedUserIds->isNotEmpty(), fn ($query) => $query->whereKey($requestedUserIds))
            ->lazyById(200);

        foreach ($users as $user) {
            $foundUserIds->push((int) $user->id);
            $weeklyMissing = Availability::query()
                ->where('user_id', $user->id)
                ->whereNull('practice_location_id')
                ->count();
            $specialMissing = SpecialAvailability::query()
                ->where('user_id', $user->id)
                ->whereNull('practice_location_id')
                ->count();

            if ($weeklyMissing === 0 && $specialMissing === 0) {
                continue;
            }

            $primary = $user->practiceLocations()->where('is_primary', true)->first();
            if (!$primary) {
                $this->warn(sprintf(
                    'User #%d: %d weekly and %d special availability period(s) skipped because no primary practice location exists.',
                    $user->id,
                    $weeklyMissing,
                    $specialMissing,
                ));

                continue;
            }

            $usersTouched++;
            $this->line(sprintf(
                'User #%d: %d weekly and %d special period(s) -> primary location #%d.',
                $user->id,
                $weeklyMissing,
                $specialMissing,
                $primary->id,
            ));

            if ($dry) {
                $weeklyUpdated += $weeklyMissing;
                $specialUpdated += $specialMissing;
                continue;
            }

            DB::transaction(function () use ($user, $primary, &$weeklyUpdated, &$specialUpdated): void {
                $values = [
                    'practice_location_id' => $primary->id,
                    'updated_at' => now(),
                ];

                $weeklyUpdated += Availability::query()
                    ->where('user_id', $user->id)
                    ->whereNull('practice_location_id')
                    ->update($values);
                $specialUpdated += SpecialAvailability::query()
                    ->where('user_id', $user->id)
                    ->whereNull('practice_location_id')
                    ->update($values);
            });
        }

        $missingRequestedIds = $requestedUserIds->diff($foundUserIds);
        if ($missingRequestedIds->isNotEmpty()) {
            $this->error('Utilisateur(s) introuvable(s) : '.$missingRequestedIds->implode(', '));

            return self::FAILURE;
        }

        $prefix = $dry ? '[DRY-RUN] ' : '';
        $this->info($prefix."Users touched: {$usersTouched}");
        $this->info($prefix."Weekly availabilities updated: {$weeklyUpdated}");
        $this->info($prefix."Special availabilities updated: {$specialUpdated}");

        return self::SUCCESS;
    }
}
