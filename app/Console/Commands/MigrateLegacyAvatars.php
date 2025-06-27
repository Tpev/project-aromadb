<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Services\ProfileAvatarService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class MigrateLegacyAvatars extends Command
{
    /**
     * Command name            (php artisan avatars:migrate-legacy)
     */
    protected $signature = 'avatars:migrate-legacy';

    /**
     * Command description.
     */
    protected $description = 'Convert old /profile_pictures/* files into responsive WebP avatar variants.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Migrating legacy avatars…');

        User::whereNotNull('profile_picture')
            ->chunkById(200, function ($users) {

                foreach ($users as $user) {

                    // Skip if already in new /avatars/{id}/ structure
                    if (!str_starts_with($user->profile_picture, 'profile_pictures/')) {
                        continue;
                    }

                    $disk = Storage::disk('public');

                    if (!$disk->exists($user->profile_picture)) {
                        $this->warn("⚠️  Missing file for user {$user->id}");
                        continue;
                    }

                    /* Wrap old file in an UploadedFile so the service accepts it */
                    $oldAbsPath = $disk->path($user->profile_picture);

                    $tempUpload = new UploadedFile(
                        $oldAbsPath,                // absolute path
                        basename($oldAbsPath),      // original filename
                        null,                       // mime (auto-detect)
                        null,                       // size (auto)
                        true                        // $test (file already exists)
                    );

                    // Generate WebP variants and get 320-px path
                    $path320 = ProfileAvatarService::store($tempUpload, $user->id);

                    // Clean up the legacy single file
                    $disk->delete($user->profile_picture);

                    // Save new path in DB
                    $user->profile_picture = $path320;
                    $user->save();

                    $this->info("✓  Migrated user {$user->id}");
                }
            });

        $this->info('Done ✅');

        return self::SUCCESS;
    }
}
