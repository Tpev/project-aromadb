<?php

namespace App\Http\Controllers\OfferJourneys;

use App\Domain\OfferJourneys\Models\OfferJourney;
use App\Domain\OfferJourneys\Models\OfferJourneyContact;
use App\Domain\OfferJourneys\Models\OfferJourneyMessageDelivery;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OfferJourneyUsageController extends Controller
{
    use AuthorizesRequests;

    public function __invoke(Request $request): View
    {
        $this->authorize('viewAny', OfferJourney::class);
        $user = $request->user();
        $files = Storage::disk('local')->allFiles('private/offer-journeys/'.$user->id);
        $storageBytes = collect($files)->sum(fn (string $path): int => (int) Storage::disk('local')->size($path));

        return view('offer-journeys.practitioner.usage', [
            'usage' => [
                'active' => OfferJourney::query()->ownedBy($user)->whereIn('status', ['draft', 'published', 'paused'])->count(),
                'active_limit' => (int) config('offer_journeys.limits.active_per_user', 10),
                'contacts' => OfferJourneyContact::query()->ownedBy($user)->count(),
                'emails' => OfferJourneyMessageDelivery::query()->where('user_id', $user->id)->where('category', 'marketing')->where('is_test', false)->whereNotNull('sent_at')->where('sent_at', '>=', now()->startOfMonth())->count(),
                'email_limit' => (int) config('offer_journeys.limits.monthly_marketing_emails', 2000),
                'storage_bytes' => $storageBytes,
                'file_count' => count($files),
            ],
        ]);
    }
}
