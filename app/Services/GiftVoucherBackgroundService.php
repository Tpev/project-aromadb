<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class GiftVoucherBackgroundService
{
    /**
     * Store a global brand background (A4 portrait ratio) for future vouchers.
     */
    public static function storeGlobalBackground(User $user, UploadedFile $file): string
    {
        $disk = Storage::disk('public');
        $folder = "gift-vouchers/backgrounds/{$user->id}";

        $disk->deleteDirectory($folder);
        $disk->makeDirectory($folder);

        $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $manager = new ImageManager($driver);

        $image = $manager->read($file->getRealPath())->orient();

        // A4 portrait ratio ~= 1:1.414. Keep quality high for PDF export.
        $encoded = $image
            ->cover(1240, 1754)
            ->encode(new WebpEncoder(quality: 86));

        $path = "{$folder}/background.webp";
        $disk->put($path, (string) $encoded);

        return $path;
    }

    /**
     * Snapshot user global brand settings at voucher creation time.
     *
     * @return array{mode: string, path: ?string}
     */
    public static function snapshotForVoucher(User $user): array
    {
        $mode = (string) ($user->gift_voucher_background_mode ?: 'default');
        $path = $user->gift_voucher_background_path ?: null;

        if (! $path || ! Storage::disk('public')->exists($path)) {
            return ['mode' => 'default', 'path' => null];
        }

        return ['mode' => $mode, 'path' => $path];
    }

    public static function removeGlobalBackground(User $user): void
    {
        $path = $user->gift_voucher_background_path;
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}

