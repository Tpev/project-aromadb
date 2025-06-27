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
        /* ­------------------------------------------------------------ */
        $disk     = Storage::disk('public');
        $folder   = "avatars/{$userId}";
        $sizes    = [320, 640, 1024];
        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $driver   = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $manager  = new ImageManager($driver);
        $webpEnc  = new WebpEncoder(quality: 80);

        /* 1. clean slate */
        $disk->deleteDirectory($folder);
        $disk->makeDirectory($folder);

        /* 2. save untouched original */
        $origPath = "{$folder}/{$baseName}.{$file->getClientOriginalExtension()}";
        $disk->putFileAs($folder, $file, basename($origPath));

        /* 3. generate square, padded variants – never upscale */
        foreach ($sizes as $w) {
            $encoded = $manager->read($file->getRealPath())
                ->scaleDown(width: $w, height: $w)      // keep ratio, no upscale
                ->resizeCanvas($w, $w, background: 'ffffff')
                ->encode($webpEnc);                     // returns EncodedImage

            // EncodedImage implements __toString(), so this works:
            $disk->put("{$folder}/avatar-{$w}.webp", $encoded);
        }

        /* 4. smallest variant path for DB */
        return "{$folder}/avatar-320.webp";
    }
}
