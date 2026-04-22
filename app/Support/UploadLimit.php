<?php

namespace App\Support;

class UploadLimit
{
    public static function parseIniBytes(?string $value): ?int
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return null;
        }

        if (!preg_match('/^\s*(\d+(?:\.\d+)?)\s*([KMGTP]?)/i', $value, $matches)) {
            return null;
        }

        $number = (float) $matches[1];
        $unit = strtoupper($matches[2] ?? '');

        return match ($unit) {
            'P' => (int) round($number * 1024 * 1024 * 1024 * 1024 * 1024),
            'T' => (int) round($number * 1024 * 1024 * 1024 * 1024),
            'G' => (int) round($number * 1024 * 1024 * 1024),
            'M' => (int) round($number * 1024 * 1024),
            'K' => (int) round($number * 1024),
            default => (int) round($number),
        };
    }

    public static function formatBytes(?int $bytes): string
    {
        if ($bytes === null || $bytes <= 0) {
            return 'illimitée';
        }

        $units = ['o', 'Ko', 'Mo', 'Go', 'To'];
        $size = (float) $bytes;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        $precision = ($size >= 10 || $unitIndex === 0 || floor($size) === $size) ? 0 : 1;

        return number_format($size, $precision, ',', ' ') . ' ' . $units[$unitIndex];
    }

    public static function phpUploadMaxBytes(): ?int
    {
        $postMax = self::parseIniBytes(ini_get('post_max_size') ?: null);
        $uploadMax = self::parseIniBytes(ini_get('upload_max_filesize') ?: null);

        if ($postMax === null) {
            return $uploadMax;
        }

        if ($uploadMax === null) {
            return $postMax;
        }

        return min($postMax, $uploadMax);
    }

    public static function trainingVideoMaxBytes(): int
    {
        $appMax = 500 * 1024 * 1024;
        $phpMax = self::phpUploadMaxBytes();

        return $phpMax === null ? $appMax : min($appMax, $phpMax);
    }

    public static function trainingVideoValidationMaxKilobytes(): int
    {
        return max(1, (int) floor(self::trainingVideoMaxBytes() / 1024));
    }

    public static function trainingVideoLimitLabel(): string
    {
        return self::formatBytes(self::trainingVideoMaxBytes());
    }

    public static function trainingAudioMaxBytes(): int
    {
        $appMax = 500 * 1024 * 1024;
        $phpMax = self::phpUploadMaxBytes();

        return $phpMax === null ? $appMax : min($appMax, $phpMax);
    }

    public static function trainingAudioValidationMaxKilobytes(): int
    {
        return max(1, (int) floor(self::trainingAudioMaxBytes() / 1024));
    }

    public static function trainingAudioLimitLabel(): string
    {
        return self::formatBytes(self::trainingAudioMaxBytes());
    }

    public static function communityAttachmentMaxBytes(): int
    {
        $appMax = 20 * 1024 * 1024;
        $phpMax = self::phpUploadMaxBytes();

        return $phpMax === null ? $appMax : min($appMax, $phpMax);
    }

    public static function communityAttachmentValidationMaxKilobytes(): int
    {
        return max(1, (int) floor(self::communityAttachmentMaxBytes() / 1024));
    }

    public static function communityAttachmentLimitLabel(): string
    {
        return self::formatBytes(self::communityAttachmentMaxBytes());
    }
}
