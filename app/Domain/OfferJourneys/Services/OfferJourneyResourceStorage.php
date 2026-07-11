<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class OfferJourneyResourceStorage
{
    public function store(UploadedFile $file, User $user, ?OfferJourney $journey = null): array
    {
        $extension = Str::lower($file->guessExtension() ?: $file->extension() ?: 'bin');
        $directory = 'private/offer-journeys/'.$user->id.'/'.($journey?->id ?: 'draft');
        $path = $file->storeAs($directory, Str::uuid().'.'.$extension, 'local');

        if (! is_string($path) || ! Storage::disk('local')->exists($path)) {
            throw new RuntimeException('La ressource n’a pas pu être enregistrée.');
        }

        return [
            'disk' => 'local',
            'path' => $path,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 180, ''),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ];
    }

    public function exists(?array $resource): bool
    {
        return is_array($resource)
            && ($resource['disk'] ?? null) === 'local'
            && is_string($resource['path'] ?? null)
            && str_starts_with($resource['path'], 'private/offer-journeys/')
            && Storage::disk('local')->exists($resource['path']);
    }

    public function delete(?array $resource): void
    {
        if (is_array($resource)
            && ($resource['disk'] ?? null) === 'local'
            && is_string($resource['path'] ?? null)
            && str_starts_with($resource['path'], 'private/offer-journeys/')) {
            Storage::disk('local')->delete($resource['path']);
        }
    }
}
