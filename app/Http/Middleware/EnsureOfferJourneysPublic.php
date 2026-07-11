<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\OfferJourneys\OfferJourneyAccess;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfferJourneysPublic
{
    public function handle(Request $request, Closure $next): Response
    {
        $therapist = $request->route('therapist');

        abort_unless(
            $therapist instanceof User
                && app(OfferJourneyAccess::class)->publicPagesAvailableFor($therapist),
            404
        );

        $visitorId = $request->cookie('oj_visitor');
        $hasValidVisitorId = is_string($visitorId)
            && preg_match('/^[a-zA-Z0-9-]{20,64}$/', $visitorId);

        if (! $hasValidVisitorId && app(OfferJourneyAccess::class)->trackingAvailable()) {
            $visitorId = (string) Str::uuid();
        }

        $request->attributes->set('offer_journey_visitor_id', $visitorId);
        $response = $next($request);

        if (! $hasValidVisitorId && is_string($visitorId)) {
            $response->headers->setCookie(cookie(
                'oj_visitor',
                $visitorId,
                60 * 24 * 30,
                '/',
                null,
                $request->isSecure(),
                true,
                false,
                'lax'
            ));
        }

        return $response;
    }
}
