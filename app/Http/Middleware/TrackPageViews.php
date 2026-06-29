<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\PageViewLog;
use Carbon\Carbon;
use App\Services\IpInfoService;
use Illuminate\Support\Facades\Log;

class TrackPageViews
{
    protected $ipInfoService;

    public function __construct(IpInfoService $ipInfoService)
    {
        $this->ipInfoService = $ipInfoService;
    }

    public function handle($request, Closure $next)
    {
        try {
            $ipAddress = $request->ip(); // Get the user's IP address
            $country = $this->ipInfoService->getCountryByIp($ipAddress); // Get country

            // Only log the page view if the country is France ('FR')
            if ($country === 'FR') {
                PageViewLog::create([
                    'url' => substr((string) $request->path(), 0, 255),
                    'session_id' => substr((string) $request->session()->getId(), 0, 255),
                    'ip_address' => substr((string) $ipAddress, 0, 255),
                    'referrer' => substr((string) $request->headers->get('referer'), 0, 4096),
                    'viewed_at' => Carbon::now(),
                    'user_agent' => substr((string) $request->header('User-Agent'), 0, 4096),
                    'country' => $country, // Store the country
                ]);
            }
        } catch (\Throwable $exception) {
            try {
                Log::warning('Page view tracking skipped.', [
                    'url' => $request->path(),
                    'error' => $exception->getMessage(),
                ]);
            } catch (\Throwable) {
                // Tracking must never break a user-facing page.
            }
        }

        return $next($request);
    }
}
