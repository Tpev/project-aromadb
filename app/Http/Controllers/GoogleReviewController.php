<?php

namespace App\Http\Controllers;

use App\Models\GoogleBusinessAccount;
use App\Models\Testimonial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GoogleReviewController extends Controller
{
    /**
     * Nettoie le commentaire Google pour enlever la partie
     * "(Translated by Google) ..."
     */
protected function cleanGoogleComment(?string $raw): ?string
{
    if (! $raw) {
        return $raw;
    }

    // Normalise line endings
    $raw = str_replace(["\r\n", "\r"], "\n", $raw);

    // 1. If Google sent a block with "(Original)", KEEP ONLY the original part
    $originalMarker = '(Original)';
    $originalPos    = mb_strpos($raw, $originalMarker);

    if ($originalPos !== false) {
        // Everything after "(Original)" is the original-language text
        $raw = mb_substr($raw, $originalPos + mb_strlen($originalMarker));
    } else {
        // 2. Fallback: older pattern "(Translated by Google)" appended at the end
        $translatedMarker = '(Translated by Google)';
        $pos              = mb_strpos($raw, $translatedMarker);

        if ($pos !== false) {
            $before = trim(mb_substr($raw, 0, $pos));

            if ($before !== '') {
                // Original text before the translation → keep it
                $raw = $before;
            } else {
                // Comment starts with "(Translated by Google)" → keep translated part only
                $after = mb_substr($raw, $pos + mb_strlen($translatedMarker));
                $raw   = $after ?: $raw;
            }
        }
    }

    // 3. Remove any leftover markers just in case
    $raw = preg_replace('/\(Translated by Google\)/u', '', $raw);
    $raw = preg_replace('/\(Original\)/u', '', $raw);

    // 4. Clean wrapping quotes Google sometimes puts around
    $raw = trim($raw);
    $raw = preg_replace('/^"+|"+$/u', '', $raw);

    return trim($raw);
}



    /**
     * HTTP client helper with CA bundle / dev fallback.
     */
    protected function httpClient(bool $form = false)
    {
        $http = $form ? Http::asForm() : Http::withOptions([]);

        // Try to use the CA bundle from .env (your cacert.pem)
        $caPath = env('SSL_CERT_FILE');

        if ($caPath && file_exists($caPath)) {
            $http = $http->withOptions(['verify' => $caPath]);
        } elseif (app()->environment('local')) {
            // Dev fallback only – don't use this in prod if you can avoid it
            $http = $http->withoutVerifying();
        }

        return $http;
    }

    protected function ensureFeatureEnabled()
    {
        if (! Auth::user()->is_therapist) {
            abort(403);
        }
    }

    public function index()
    {
        $this->ensureFeatureEnabled();

        $user = Auth::user();

        $account = GoogleBusinessAccount::where('user_id', $user->id)->first();

        $googleTestimonials = Testimonial::where('therapist_id', $user->id)
            ->where('source', 'google')
            ->orderByDesc('external_created_at')
            ->get();

        return view('pro.google-reviews', [
            'account'            => $account,
            'googleTestimonials' => $googleTestimonials,
        ]);
    }

    /**
     * Redirige vers Google pour consentement OAuth (Business Profile).
     */
    public function redirectToGoogle()
    {
        $this->ensureFeatureEnabled();

        $config = config('services.google_business');

        $query = http_build_query([
            'client_id'     => $config['client_id'],
            'redirect_uri'  => $config['redirect'],
            'response_type' => 'code',
            'scope'         => implode(' ', $config['scopes']),
            'access_type'   => 'offline',
            'prompt'        => 'consent',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    /**
     * Callback OAuth après consentement.
     */
    public function handleCallback(Request $request)
    {
        $this->ensureFeatureEnabled();

        if ($request->has('error')) {
            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Connexion à Google annulée ou refusée.');
        }

        $code = $request->query('code');
        if (! $code) {
            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Code d’autorisation Google manquant.');
        }

        $config = config('services.google_business');

        // Échange code -> tokens
        $tokenResponse = $this->httpClient(true)->post('https://oauth2.googleapis.com/token', [
            'code'          => $code,
            'client_id'     => $config['client_id'],
            'client_secret' => $config['client_secret'],
            'redirect_uri'  => $config['redirect'],
            'grant_type'    => 'authorization_code',
        ]);

        if ($tokenResponse->failed()) {
            Log::error('Google OAuth token exchange failed', [
                'body' => $tokenResponse->body(),
            ]);

            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Impossible de récupérer les informations Google (token).');
        }

        $tokens       = $tokenResponse->json();
        $accessToken  = $tokens['access_token'] ?? null;
        $refreshToken = $tokens['refresh_token'] ?? null;
        $expiresIn    = $tokens['expires_in'] ?? 3600;
        $expiresAt    = now()->addSeconds($expiresIn - 60);

        $user = Auth::user();

        // 1) Récupérer le compte Business Profile
        $accountsResp = $this->httpClient()
            ->withToken($accessToken)
            ->get('https://mybusinessaccountmanagement.googleapis.com/v1/accounts');

        if ($accountsResp->failed()) {
            Log::error('Google Business accounts fetch failed', [
                'body' => $accountsResp->body(),
            ]);

            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Impossible de récupérer votre compte Business Profile.');
        }

        $accounts = $accountsResp->json('accounts') ?? [];
        if (count($accounts) === 0) {
            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Aucun compte Business Profile trouvé pour ce compte Google.');
        }

        $accountData = $accounts[0];

        // "accounts/116226129630634321894" -> "116226129630634321894"
        $accountId          = str_replace('accounts/', '', $accountData['name'] ?? '');
        $accountDisplayName = $accountData['accountName'] ?? null;

        // 2) Récupérer la première location
        $locationsResp = $this->httpClient()
            ->withToken($accessToken)
            ->get("https://mybusinessbusinessinformation.googleapis.com/v1/accounts/{$accountId}/locations", [
                'readMask' => 'name,title,storefrontAddress,websiteUri',
            ]);

        if ($locationsResp->failed()) {
            Log::error('Google Business locations fetch failed', [
                'body' => $locationsResp->body(),
            ]);

            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Impossible de récupérer vos établissements Business Profile.');
        }

        $locations = $locationsResp->json('locations') ?? [];
        if (count($locations) === 0) {
            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Aucun établissement Business Profile trouvé pour ce compte.');
        }

        $location = $locations[0];

        // "locations/15368859932155211034" -> "15368859932155211034"
        $locationId    = str_replace('locations/', '', $location['name'] ?? '');
        $locationTitle = $location['title'] ?? null;

        // 3) Sauvegarder / mettre à jour en BDD
        $googleAccount = GoogleBusinessAccount::updateOrCreate(
            ['user_id' => $user->id],
            [
                'account_id'              => $accountId,
                'account_display_name'    => $accountDisplayName,
                'location_id'             => $locationId,
                'location_title'          => $locationTitle,
                // on garde l’ancien refresh_token si Google n’en renvoie pas un nouveau
                'refresh_token'           => $refreshToken ?: GoogleBusinessAccount::where('user_id', $user->id)->value('refresh_token'),
                'access_token'            => $accessToken,
                'access_token_expires_at' => $expiresAt,
            ]
        );

        return redirect()
            ->route('pro.google-reviews.index')
            ->with('success', 'Compte Google Business connecté avec succès. Vous pouvez maintenant synchroniser vos avis.');
    }

    /**
     * Déconnexion / suppression du lien Google Business.
     */
    public function disconnect()
    {
        $this->ensureFeatureEnabled();

        $user = Auth::user();

        GoogleBusinessAccount::where('user_id', $user->id)->delete();

        return redirect()
            ->route('pro.google-reviews.index')
            ->with('success', 'Connexion Google Business supprimée. Les avis déjà importés restent visibles.');
    }

    /**
     * Appel API Reviews via file_get_contents (contourne Guzzle/Http).
     */
    protected function fetchGoogleReviewsViaStream(GoogleBusinessAccount $account, string $accessToken): array
    {
        $url = "https://mybusiness.googleapis.com/v4/accounts/{$account->account_id}/locations/{$account->location_id}/reviews";

        $opts = [
            'http' => [
                'method'  => 'GET',
                'header'  =>
                    "Authorization: Bearer {$accessToken}\r\n" .
                    "Accept: application/json\r\n",
                'timeout' => 15,
            ],
        ];

        try {
            $context = stream_context_create($opts);
            $body    = @file_get_contents($url, false, $context);

            if ($body === false) {
                Log::warning('Google Reviews: file_get_contents failed', [
                    'account_id'  => $account->id,
                    'user_id'     => $account->user_id,
                ]);
                return [];
            }

            $data = json_decode($body, true);

            if (! is_array($data)) {
                Log::warning('Google Reviews: invalid JSON body', [
                    'account_id'   => $account->id,
                    'user_id'      => $account->user_id,
                    'body_sample'  => substr($body, 0, 200),
                ]);
                return [];
            }

            return $data['reviews'] ?? [];
        } catch (\Throwable $e) {
            Log::error('Google Reviews: exception while fetching', [
                'account_id' => $account->id,
                'user_id'    => $account->user_id,
                'error'      => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Synchroniser les avis Google -> table testimonials.
     */
    public function syncReviews()
    {
        $this->ensureFeatureEnabled();

        $user = Auth::user();

        /** @var GoogleBusinessAccount|null $account */
        $account = GoogleBusinessAccount::where('user_id', $user->id)->first();

        if (! $account) {
            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Aucun compte Google Business connecté.');
        }

        // 1) S’assurer d’avoir un access_token valide
        $accessToken = $account->access_token;

        if (! $accessToken || ($account->access_token_expires_at && $account->access_token_expires_at->isPast())) {
            if (! $account->refresh_token) {
                return redirect()
                    ->route('pro.google-reviews.index')
                    ->with('error', 'Le token Google a expiré et aucun refresh token n’est disponible. Merci de reconnecter votre compte.');
            }

            $config = config('services.google_business');

            $refreshResponse = $this->httpClient(true)->post('https://oauth2.googleapis.com/token', [
                'client_id'     => $config['client_id'],
                'client_secret' => $config['client_secret'],
                'refresh_token' => $account->refresh_token,
                'grant_type'    => 'refresh_token',
            ]);

            if ($refreshResponse->failed()) {
                Log::error('Google OAuth refresh_token failed', [
                    'body' => $refreshResponse->body(),
                ]);

                return redirect()
                    ->route('pro.google-reviews.index')
                    ->with('error', 'Impossible de rafraîchir le token Google. Merci de reconnecter votre compte.');
            }

            $data        = $refreshResponse->json();
            $accessToken = $data['access_token'] ?? null;
            $expiresIn   = $data['expires_in'] ?? 3600;

            $account->update([
                'access_token'            => $accessToken,
                'access_token_expires_at' => now()->addSeconds($expiresIn - 60),
            ]);
        }

        // 2) Vérif des infos de compte
        $accountId  = $account->account_id;
        $locationId = $account->location_id;

        if (! $accountId || ! $locationId) {
            return redirect()
                ->route('pro.google-reviews.index')
                ->with('error', 'Les informations de compte ou d’établissement Google sont incomplètes.');
        }

        // 3) Appel API Reviews via stream (contourne Guzzle)
        $reviews = $this->fetchGoogleReviewsViaStream($account, $accessToken);

        $imported = 0;

        foreach ($reviews as $review) {
            $externalId = $review['reviewId'] ?? null;
            if (! $externalId) {
                continue;
            }

            $rawComment = $review['comment'] ?? '';
            $comment    = $this->cleanGoogleComment($rawComment);
            $starRating = $review['starRating'] ?? null;
            $reviewer   = $review['reviewer'] ?? [];
            $reply      = $review['reviewReply'] ?? [];

            $rating = match ($starRating) {
                'ONE'   => 1,
                'TWO'   => 2,
                'THREE' => 3,
                'FOUR'  => 4,
                'FIVE'  => 5,
                default => null,
            };

            $reviewerName = $reviewer['displayName'] ?? null;
            $photoUrl     = $reviewer['profilePhotoUrl'] ?? null;

            $createTime = isset($review['createTime'])
                ? Carbon::parse($review['createTime'])
                : null;

            $updateTime = isset($review['updateTime'])
                ? Carbon::parse($review['updateTime'])
                : null;

            $ownerReply          = $reply['comment'] ?? null;
            $ownerReplyUpdatedAt = isset($reply['updateTime'])
                ? Carbon::parse($reply['updateTime'])
                : null;

            Testimonial::updateOrCreate(
                [
                    'therapist_id'       => $user->id,
                    'source'             => 'google',
                    'external_review_id' => $externalId,
                ],
                [
                    'client_profile_id'          => null,
                    'testimonial_request_id'     => null,
                    'testimonial'                => $comment,
                    'rating'                     => $rating,
                    'reviewer_name'              => $reviewerName,
                    'reviewer_profile_photo_url' => $photoUrl,
                    'visible_on_public_profile'  => true,
                    'external_created_at'        => $createTime,
                    'external_updated_at'        => $updateTime,
                    'owner_reply'                => $ownerReply,
                    'owner_reply_updated_at'     => $ownerReplyUpdatedAt,
                ]
            );

            $imported++;
        }

        $account->update(['last_synced_at' => now()]);

        return redirect()
            ->route('pro.google-reviews.index')
            ->with('success', "Synchronisation terminée. {$imported} avis Google importés ou mis à jour.");
    }
}
