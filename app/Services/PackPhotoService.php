<?php

namespace App\Services;

use App\Models\PackProduct;
use App\Support\UploadLimit;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class PackPhotoService
{
    public const MAX_DIMENSION = 4096;

    public static function maxBytes(): int
    {
        return min(2 * 1024 * 1024, UploadLimit::phpUploadMaxBytes() ?? PHP_INT_MAX);
    }

    public static function rules(): array
    {
        return [
            'photo' => ['bail', 'nullable', 'prohibited_if:remove_photo,1,true', 'file', 'max:'.intdiv(self::maxBytes(), 1024), 'mimetypes:image/jpeg,image/png,image/webp', 'dimensions:max_width='.self::MAX_DIMENSION.',max_height='.self::MAX_DIMENSION],
            'remove_photo' => ['sometimes', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'photo.uploaded' => 'La photo dépasse la limite autorisée ou son transfert a échoué.',
            'photo.max' => 'La photo ne doit pas dépasser '.UploadLimit::formatBytes(self::maxBytes()).'.',
            'photo.file' => 'Choisissez un fichier JPG, PNG ou WebP.',
            'photo.mimetypes' => 'Formats acceptés : JPG, PNG ou WebP. Convertissez les photos HEIC avant de les ajouter.',
            'photo.dimensions' => 'La photo doit mesurer au maximum 4 096 pixels de chaque côté.',
            'photo.prohibited_if' => 'Choisissez une nouvelle photo ou supprimez la photo actuelle, puis enregistrez.',
        ];
    }

    public function save(int $userId, ?PackProduct $pack, array $validated, Closure $persist): PackProduct
    {
        abort_if($pack && (int) $pack->user_id !== $userId, 403);
        $newPath = isset($validated['photo']) ? $this->store($validated['photo'], $userId) : null;
        $changes = $newPath ? ['photo_path' => $newPath] : (! empty($validated['remove_photo']) ? ['photo_path' => null] : []);

        try {
            return DB::transaction(function () use ($userId, $pack, $persist, $changes) {
                $previous = $pack && $changes !== [] ? PackProduct::whereKey($pack->id)->lockForUpdate()->firstOrFail()->photo_path : null;
                $saved = $persist($changes);
                if ($previous) {
                    DB::afterCommit(fn () => $this->delete($previous, $userId));
                }

                return $saved;
            });
        } catch (Throwable $exception) {
            $this->delete($newPath, $userId);
            throw $exception;
        }
    }

    public function store(UploadedFile $file, int $userId): string
    {
        validator(['photo' => $file], self::rules(), self::messages())->validate();
        $path = 'pack-photos/'.$userId.'/'.Str::uuid().'.webp';

        try {
            // Bound dimensions before decoding, use only the first frame, and strip metadata.
            $driver = extension_loaded('imagick') ? new ImagickDriver : new GdDriver;
            $image = (new ImageManager($driver, decodeAnimation: false, strip: true))->read($file->getRealPath())->cover(1200, 675);
            $disk = Storage::disk('public');
            if (! $disk->put($path, (string) $image->encode(new WebpEncoder(quality: 80, strip: true)))) {
                throw new RuntimeException('Could not store pack photo.');
            }
            $image->resize(640, 360);
            if (! $disk->put(self::thumbnailPath($path), (string) $image->encode(new WebpEncoder(quality: 75, strip: true)))) {
                throw new RuntimeException('Could not store pack thumbnail.');
            }

            return $path;
        } catch (Throwable $exception) {
            $this->delete($path, $userId);
            report($exception);
            throw ValidationException::withMessages(['photo' => 'Impossible de traiter cette photo. Essayez un autre fichier JPG, PNG ou WebP.']);
        }
    }

    public static function thumbnailPath(string $path): string
    {
        return substr($path, 0, -5).'-small.webp';
    }

    public static function ownsPath(?string $path, int $userId): bool
    {
        return is_string($path) && preg_match('~\Apack-photos/'.$userId.'/[a-f0-9-]{36}\.webp\z~', $path) === 1;
    }

    public function delete(?string $path, int $userId): void
    {
        if (! self::ownsPath($path, $userId)) {
            return;
        }

        try {
            Storage::disk('public')->delete([$path, self::thumbnailPath($path)]);
        } catch (Throwable $exception) {
            // Cleanup must never undo a committed replacement or delete its new files.
            report($exception);
        }
    }
}
