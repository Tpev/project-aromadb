<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\PageViewLog;
use Carbon\Carbon;
use App\Services\IpInfoService;

class TrackPageViews
{
    protected $ipInfoService;

    public function __construct(IpInfoService $ipInfoService)
    {
        $this->ipInfoService = $ipInfoService;
    }

    public function handle($request, Closure $next)
    {
        $ipAddress = $request->ip(); // Get the user's IP address
        $country = $this->ipInfoService->getCountryByIp($ipAddress); // Get country

        // Only log the page view if the country is France ('FR')
        if ($country === 'FR') {
            PageViewLog::create([
                'url' => $request->path(),
                'session_id' => $request->session()->getId(),
                'ip_address' => $ipAddress,
                'referrer' => $request->headers->get('referer'),
                'viewed_at' => Carbon::now(),
                'user_agent' => $request->header('User-Agent'),
                'country' => $country, // Store the country
            ]);
        }

        return $next($request);
    }
}
