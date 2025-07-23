<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Illuminate\Support\Facades\Log;

class ProfileAvatarService
{
    /**
     * Store original + responsive WebP variants, return 320-px path.
     */
public static function store(UploadedFile $file, int $userId): string
{
    Log::info('Avatar STORE start', [
        'userId' => $userId,
        'origName' => $file->getClientOriginalName(),
        'size' => $file->getSize(),
        'mime' => $file->getMimeType(),
    ]);

    $disk   = Storage::disk('public');
    $folder = "avatars/{$userId}";
    $sizes  = [320, 640, 1024];

    // 1) base dir
    Log::info('Check base dir', [
        'avatars_exists' => $disk->exists('avatars'),
        'path_base'      => $disk->path('avatars'),
        'is_writable'    => is_writable($disk->path('avatars'))
    ]);

    if (! $disk->exists('avatars')) {
        $mk = $disk->makeDirectory('avatars');
        Log::info('makeDirectory(avatars)', ['result' => $mk]);
    }

    // 2) delete old
    try {
        $del = $disk->deleteDirectory($folder);
        Log::info('deleteDirectory', ['folder' => $folder, 'result' => $del]);
    } catch (\Throwable $e) {
        Log::warning('deleteDirectory exception', ['msg' => $e->getMessage()]);
    }

    // 3) create folder (with fallback)
    try {
        $mk = $disk->makeDirectory($folder);
        Log::info('makeDirectory(folder)', ['folder' => $folder, 'result' => $mk]);
    } catch (\Throwable $e) {
        Log::warning('makeDirectory exception', ['msg' => $e->getMessage()]);
        \Illuminate\Support\Facades\File::ensureDirectoryExists(
            storage_path("app/public/{$folder}"),
            0775, true
        );
        Log::info('Fallback ensureDirectoryExists done');
    }

    Log::info('Folder exists after create?', [
        'exists' => $disk->exists($folder),
        'abs'    => $disk->path($folder),
        'is_writable' => is_writable($disk->path($folder))
    ]);

    // 4) save original
    $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    $origPath = "{$folder}/{$baseName}.{$file->getClientOriginalExtension()}";
    $putOrig  = $disk->putFileAs($folder, $file, basename($origPath));
    Log::info('putFileAs original', ['path' => $origPath, 'result' => $putOrig]);

    // 5) make variants
    $driver  = extension_loaded('imagick') ? new \Intervention\Image\Drivers\Imagick\Driver()
                                           : new \Intervention\Image\Drivers\Gd\Driver();
    $manager = new \Intervention\Image\ImageManager($driver);
    $webpEnc = new \Intervention\Image\Encoders\WebpEncoder(quality: 80);

    foreach ($sizes as $w) {
        try {
            $encoded = $manager->read($file->getRealPath())
                ->scaleDown(width: $w, height: $w)
                ->resizeCanvas($w, $w, background: 'ffffff')
                ->encode($webpEnc);

            $p = "{$folder}/avatar-{$w}.webp";
            $ok = $disk->put($p, (string) $encoded);
            Log::info('put variant', ['path' => $p, 'ok' => $ok]);
        } catch (\Throwable $e) {
            Log::error('Variant encode/put failed', ['width' => $w, 'error' => $e->getMessage()]);
            throw $e; // rethrow so you see it
        }
    }

    Log::info('Avatar STORE done', ['return' => "{$folder}/avatar-320.webp"]);

    return "{$folder}/avatar-320.webp";
}
}
