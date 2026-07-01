<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\GoogleReviewController;
use App\Models\GoogleBusinessAccount;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileGoogleReviewController extends GoogleReviewController
{
    protected function googleReviewsIndexRoute(): string
    {
        return 'mobile.google-reviews.index';
    }

    public function index()
    {
        $this->ensureFeatureEnabled();

        $user = Auth::user();
        $account = GoogleBusinessAccount::where('user_id', $user->id)->first();
        $availableLocations = [];

        if ($account) {
            $accessToken = $this->getValidAccessToken($account);

            if ($accessToken) {
                $availableLocations = $this->fetchAllBusinessLocations($accessToken);

                if (count($availableLocations) === 0 && $account->account_id) {
                    $availableLocations = collect($this->fetchBusinessLocations($account->account_id, $accessToken))
                        ->map(function (array $location) use ($account) {
                            $locationId = $this->extractLocationId($location);
                            if (! $locationId) {
                                return null;
                            }

                            $locationTitle = $location['title'] ?? null;
                            $readableLocationTitle = $locationTitle ?: ('Etablissement #' . $locationId);
                            $readableAccountName = $account->account_display_name ?: ('Compte #' . $account->account_id);

                            return [
                                'selection_value' => $this->buildLocationSelectionValue($account->account_id, $locationId),
                                'account_id' => $account->account_id,
                                'account_display_name' => $account->account_display_name,
                                'location_id' => $locationId,
                                'location_title' => $locationTitle,
                                'label' => "{$readableLocationTitle} ({$readableAccountName})",
                            ];
                        })
                        ->filter()
                        ->values()
                        ->all();
                }
            }
        }

        $googleTestimonials = Testimonial::where('therapist_id', $user->id)
            ->where('source', 'google')
            ->orderByDesc('external_created_at')
            ->get();

        return view('mobile.google-reviews.index', [
            'account' => $account,
            'availableLocations' => $availableLocations,
            'googleTestimonials' => $googleTestimonials,
        ]);
    }

    public function redirectToGoogle()
    {
        session(['google_reviews_return_route' => 'mobile.google-reviews.index']);

        return parent::redirectToGoogle();
    }

    public function syncReviews(Request $request)
    {
        return parent::syncReviews($request);
    }

    public function disconnect()
    {
        return parent::disconnect();
    }
}
