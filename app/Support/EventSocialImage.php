<?php

namespace App\Support;

use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class EventSocialImage
{
    /**
     * @return array{url: string, secure_url: string, mime_type: ?string, width: ?int, height: ?int, alt: string}
     */
    public function for(Event $event): array
    {
        $relativePath = 'images/hero-background.webp';
        $localPath = public_path($relativePath);
        $version = is_file($localPath) ? filemtime($localPath) : null;

        if ($event->image && Storage::disk('public')->exists($event->image)) {
            $relativePath = 'storage/'.ltrim(str_replace('\\', '/', $event->image), '/');
            $localPath = $this->storagePath($event->image) ?? $localPath;
            $version = $event->updated_at?->getTimestamp();
        }

        $dimensions = $this->dimensions($localPath);
        $url = asset($relativePath);

        if ($version) {
            $url .= '?v='.$version;
        }

        return [
            'url' => $url,
            'secure_url' => preg_replace('/^http:/i', 'https:', $url) ?: $url,
            'mime_type' => $dimensions['mime_type'],
            'width' => $dimensions['width'],
            'height' => $dimensions['height'],
            'alt' => $event->name,
        ];
    }

    private function storagePath(string $path): ?string
    {
        try {
            return Storage::disk('public')->path($path);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array{mime_type: ?string, width: ?int, height: ?int}
     */
    private function dimensions(?string $path): array
    {
        if (! $path || ! is_file($path)) {
            return ['mime_type' => null, 'width' => null, 'height' => null];
        }

        $size = @getimagesize($path);

        if (! is_array($size)) {
            return ['mime_type' => null, 'width' => null, 'height' => null];
        }

        return [
            'mime_type' => $size['mime'] ?? null,
            'width' => isset($size[0]) ? (int) $size[0] : null,
            'height' => isset($size[1]) ? (int) $size[1] : null,
        ];
    }
}
