<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\PageViewLog;
use Carbon\Carbon;

class TrackPageViews
{
    public function handle($request, Closure $next)
    {
        // Log the current page view with timestamp
        PageViewLog::create([
            'url' => $request->path(),
            'viewed_at' => Carbon::now(),
        ]);

        return $next($request);
    }
}
