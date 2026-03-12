<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\Interfaces\ImageInterface;

class ProfileAvatarService
{
    /**
     * Store responsive square WebP avatar variants and return the 320px path.
     */
    public static function store(UploadedFile $file, int $userId, ?string $cropPayload = null): string
    {
        $disk = Storage::disk('public');
        $folder = "avatars/{$userId}";
        $sizes = [320, 640, 1024];

        $disk->deleteDirectory($folder);
        $disk->makeDirectory($folder);

        $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $manager = new ImageManager($driver);

        $image = $manager->read($file->getRealPath())->orient();
        $image = self::prepareSquareImage($image, self::decodeCropPayload($cropPayload));

        foreach ($sizes as $size) {
            $variant = clone $image;
            $encoded = $variant
                ->cover($size, $size)
                ->encode(new WebpEncoder(quality: self::qualityFor($size)));

            $disk->put("{$folder}/avatar-{$size}.webp", (string) $encoded);
        }

        return "{$folder}/avatar-320.webp";
    }

    private static function qualityFor(int $size): int
    {
        return $size >= 1024 ? 84 : 82;
    }

    private static function decodeCropPayload(?string $cropPayload): ?array
    {
        if (! $cropPayload) {
            return null;
        }

        $decoded = json_decode($cropPayload, true);
        if (! is_array($decoded)) {
            return null;
        }

        $required = ['x', 'y', 'width', 'height'];
        foreach ($required as $field) {
            if (! isset($decoded[$field]) || ! is_numeric($decoded[$field])) {
                return null;
            }
        }

        if ((float) $decoded['width'] <= 0 || (float) $decoded['height'] <= 0) {
            return null;
        }

        return [
            'x' => (float) $decoded['x'],
            'y' => (float) $decoded['y'],
            'width' => (float) $decoded['width'],
            'height' => (float) $decoded['height'],
            'image_width' => (isset($decoded['image_width']) && is_numeric($decoded['image_width'])) ? (float) $decoded['image_width'] : null,
            'image_height' => (isset($decoded['image_height']) && is_numeric($decoded['image_height'])) ? (float) $decoded['image_height'] : null,
        ];
    }

    private static function prepareSquareImage(ImageInterface $image, ?array $crop): ImageInterface
    {
        $imageWidth = max(1, (int) $image->width());
        $imageHeight = max(1, (int) $image->height());

        if ($crop) {
            $x = $crop['x'];
            $y = $crop['y'];
            $cropWidth = $crop['width'];
            $cropHeight = $crop['height'];

            $cropImageWidth = $crop['image_width'] ?? null;
            $cropImageHeight = $crop['image_height'] ?? null;

            if ($cropImageWidth && $cropImageHeight && $cropImageWidth > 0 && $cropImageHeight > 0) {
                $scaleX = $imageWidth / $cropImageWidth;
                $scaleY = $imageHeight / $cropImageHeight;
                $x *= $scaleX;
                $y *= $scaleY;
                $cropWidth *= $scaleX;
                $cropHeight *= $scaleY;
            }

            $x = max(0, (int) floor($x));
            $y = max(0, (int) floor($y));
            $cropWidth = max(1, (int) floor($cropWidth));
            $cropHeight = max(1, (int) floor($cropHeight));

            if ($x >= $imageWidth) {
                $x = $imageWidth - 1;
            }
            if ($y >= $imageHeight) {
                $y = $imageHeight - 1;
            }

            $cropWidth = min($cropWidth, $imageWidth - $x);
            $cropHeight = min($cropHeight, $imageHeight - $y);

            if ($cropWidth > 0 && $cropHeight > 0) {
                return $image->crop($cropWidth, $cropHeight, $x, $y);
            }
        }

        $size = min($imageWidth, $imageHeight);
        $offsetX = (int) floor(($imageWidth - $size) / 2);
        $offsetY = (int) floor(($imageHeight - $size) / 2);

        return $image->crop($size, $size, $offsetX, $offsetY);
    }
}
