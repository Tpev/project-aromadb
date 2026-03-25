<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class FrenchAddressGeocodingService
{
    public function geocodeQuery(?string $query, bool $useCache = true): ?array
    {
        $query = trim((string) $query);

        if ($query === '') {
            return null;
        }

        $resolver = function () use ($query) {
            try {
                $response = Http::timeout(6)
                    ->acceptJson()
                    ->get(config('directory_search.ign_search_endpoint'), [
                        'q' => $query,
                        'limit' => 1,
                    ]);

                if (! $response->successful()) {
                    return null;
                }

                $payload = $response->json();
                $feature = $payload['features'][0] ?? null;

                if (! is_array($feature)) {
                    return null;
                }

                $coordinates = $feature['geometry']['coordinates'] ?? null;

                if (! is_array($coordinates) || count($coordinates) < 2) {
                    return null;
                }

                return [
                    'latitude' => (float) $coordinates[1],
                    'longitude' => (float) $coordinates[0],
                    'label' => (string) ($feature['properties']['label'] ?? $query),
                    'raw' => $feature,
                ];
            } catch (Throwable $e) {
                report($e);

                return null;
            }
        };

        if (! $useCache) {
            return $resolver();
        }

        return Cache::remember(
            'directory-search:geocode:' . md5($query),
            now()->addDays(30),
            $resolver
        );
    }

    public function geocodeAddressParts(array $parts, bool $useCache = true): ?array
    {
        $query = collect($parts)
            ->map(fn ($part) => trim((string) $part))
            ->filter()
            ->implode(', ');

        return $this->geocodeQuery($query, $useCache);
    }
}
