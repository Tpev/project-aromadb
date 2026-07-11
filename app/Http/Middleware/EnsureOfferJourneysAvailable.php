<?php

namespace App\Http\Middleware;

use App\Support\OfferJourneys\OfferJourneyAccess;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOfferJourneysAvailable
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless(app(OfferJourneyAccess::class)->availableFor($request->user()), 404);

        return $next($request);
    }
}
