<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyPage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use LogicException;
use RuntimeException;

class OfferJourneyPageImageStorage
{
    public function store(UploadedFile $file, User $user, OfferJourney $journey, OfferJourneyPage $page): array
    {
        if ((int) $journey->user_id !== (int) $user->id
            || (int) $page->offer_journey_id !== (int) $journey->id) {
            throw new LogicException('La page ne correspond pas au parcours du praticien.');
        }

        $driver = extension_loaded('imagick') ? new ImagickDriver : new GdDriver;
        $image = (new ImageManager($driver))
            ->read($file->getRealPath())
            ->orient()
            ->scaleDown(width: 1920, height: 1440);
        $binary = (string) $image->encode(new WebpEncoder(quality: 84));
        $directory = 'offer-journeys/'.$user->id.'/pages/'.$page->id;
        $path = $directory.'/'.Str::uuid().'.webp';

        if (! Storage::disk('public')->put($path, $binary) || ! Storage::disk('public')->exists($path)) {
            throw new RuntimeException("L'image principale n'a pas pu être enregistrée.");
        }

        return [
            'path' => $path,
            // The relative URL works from both the old and new Olithea domains.
            'url' => '/storage/'.ltrim(str_replace('\\', '/', $path), '/'),
        ];
    }

    public function delete(?string $path): void
    {
        if (is_string($path) && str_starts_with($path, 'offer-journeys/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
