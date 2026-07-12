<?php

namespace App\Domain\OfferJourneys\Services;

use App\Domain\OfferJourneys\Models\OfferJourneyEmailAsset;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageCampaign;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

class OfferJourneyEmailAssetStorage
{
    public function store(UploadedFile $file, User $user, OfferJourneyMessageCampaign $campaign): OfferJourneyEmailAsset
    {
        $driver = extension_loaded('imagick') ? new ImagickDriver() : new GdDriver();
        $image = (new ImageManager($driver))->read($file->getRealPath())->orient()->scaleDown(width: 1200, height: 1200);
        $binary = (string) $image->encode(new WebpEncoder(quality: 84));
        $path = 'offer-journeys/email-assets/'.$user->id.'/'.$campaign->id.'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $binary);

        return OfferJourneyEmailAsset::query()->create([
            'user_id' => $user->id,
            'offer_journey_message_campaign_id' => $campaign->id,
            'path' => $path,
            'original_name' => Str::limit(basename($file->getClientOriginalName()), 240, ''),
            'mime_type' => 'image/webp',
            'width' => $image->width(),
            'height' => $image->height(),
            'size_bytes' => strlen($binary),
        ]);
    }

    public function delete(OfferJourneyEmailAsset $asset): void
    {
        Storage::disk('public')->delete($asset->path);
        $asset->delete();
    }
}
