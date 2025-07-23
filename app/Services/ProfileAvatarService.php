<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;

class ProfileAvatarService
{
    /**
     * Store original + responsive WebP variants, return 320-px path.
     */
public static function store(UploadedFile $file, int $userId): string
{
    $disk   = Storage::disk('public');
    $folder = "avatars/{$userId}";
    $sizes  = [320, 640, 1024];

    // Ensure base dir exists
    if (! $disk->exists('avatars')) {
        $disk->makeDirectory('avatars');
    }

    // Best-effort cleanup (ignore errors)
    try { $disk->deleteDirectory($folder); } catch (\Throwable $e) {}

    // Recreate or fallback to native FS
    try {
        $disk->makeDirectory($folder);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\File::ensureDirectoryExists(
            storage_path("app/public/{$folder}"),
            0775,
            true
        );
    }

    $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

    $driver  = extension_loaded('imagick') ? new \Intervention\Image\Drivers\Imagick\Driver()
                                           : new \Intervention\Image\Drivers\Gd\Driver();
    $manager = new \Intervention\Image\ImageManager($driver);
    $webpEnc = new \Intervention\Image\Encoders\WebpEncoder(quality: 80);

    // Save original
    $origPath = "{$folder}/{$baseName}.{$file->getClientOriginalExtension()}";
    $disk->putFileAs($folder, $file, basename($origPath));

    foreach ($sizes as $w) {
        $encoded = $manager->read($file->getRealPath())
            ->scaleDown(width: $w, height: $w)
            ->resizeCanvas($w, $w, background: 'ffffff')
            ->encode($webpEnc);

        $disk->put("{$folder}/avatar-{$w}.webp", (string) $encoded);
    }

    return "{$folder}/avatar-320.webp";
}

}
