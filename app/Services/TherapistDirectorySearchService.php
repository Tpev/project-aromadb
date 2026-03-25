<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TherapistDirectorySearchService
{
    public function __construct(
        private readonly FrenchAddressGeocodingService $geocodingService
    ) {
    }

    public function search(array $filters): Collection
    {
        $name = trim((string) ($filters['name'] ?? ''));
        $specialty = trim((string) ($filters['specialty'] ?? ''));
        $location = trim((string) ($filters['location'] ?? ''));
        $radiusKm = (float) ($filters['radius_km'] ?? config('directory_search.default_radius_km', 10));

        $base = $this->baseQuery();

        $specialtyContext = $this->buildSpecialtyContext($specialty);
        $locationContext = $this->buildLocationContext($location, $radiusKm);

        $this->applySpecialtyPrefilter($base, $specialtyContext);
        $this->applyLocationPrefilter($base, $locationContext);

        if ($name === '') {
            return $this->applyFinalFilters($base->get(), $specialtyContext, $locationContext)->values();
        }

        $prefilter = (clone $base)
            ->where(function (Builder $query) use ($name) {
                $query->where('name', 'like', '%' . $name . '%')
                    ->orWhere('company_name', 'like', '%' . $name . '%')
                    ->orWhereRaw('SOUNDEX(name) = SOUNDEX(?)', [$name])
                    ->orWhereRaw('SOUNDEX(company_name) = SOUNDEX(?)', [$name]);
            })
            ->limit(250)
            ->get();

        if ($prefilter->isEmpty()) {
            $prefilter = (clone $base)->limit(500)->get();
        }

        $prefilter = $this->applyFinalFilters($prefilter, $specialtyContext, $locationContext);
        $results = $this->scoreByName($prefilter, $name);

        if ($results->isNotEmpty()) {
            return $results->values();
        }

        $fallbackPool = $this->applyFinalFilters((clone $base)->limit(500)->get(), $specialtyContext, $locationContext);

        return $this->scoreByName($fallbackPool, $name, true)->values();
    }

    public function normalize(string $value): string
    {
        $value = trim(mb_strtolower($value, 'UTF-8'));
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        if ($ascii !== false && $ascii !== null) {
            $value = $ascii;
        }

        $value = preg_replace('/[^a-z0-9 ]+/', ' ', $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return trim($value);
    }

    private function baseQuery(): Builder
    {
        return User::query()
            ->with('practiceLocations')
            ->where('is_therapist', true)
            ->whereNotNull('slug')
            ->where('slug', '!=', '')
            ->where('visible_annuarire_admin_set', true);
    }

    private function buildSpecialtyContext(string $specialty): ?array
    {
        if ($specialty === '') {
            return null;
        }

        $inputRaw = mb_strtolower($specialty, 'UTF-8');
        $inputNormalized = $this->normalize($specialty);
        $rawTerms = [$inputRaw];
        $normalizedTerms = [$inputNormalized];

        foreach (config('directory_search.specialty_aliases', []) as $canonical => $aliases) {
            $rawGroup = collect([$canonical, ...$aliases])
                ->map(fn ($term) => trim(mb_strtolower((string) $term, 'UTF-8')))
                ->filter()
                ->unique()
                ->values();

            $normalizedGroup = $rawGroup
                ->map(fn ($term) => $this->normalize($term))
                ->filter()
                ->unique()
                ->values();

            $matchesGroup = $normalizedGroup->contains(fn ($term) => $term === $inputNormalized)
                || $normalizedGroup->contains(fn ($term) => Str::contains($term, $inputNormalized))
                || Str::contains($inputNormalized, $this->normalize($canonical));

            if (! $matchesGroup) {
                continue;
            }

            $rawTerms = array_merge($rawTerms, $rawGroup->all());
            $normalizedTerms = array_merge($normalizedTerms, $normalizedGroup->all());
        }

        return [
            'raw' => collect($rawTerms)->unique()->values()->all(),
            'normalized' => collect($normalizedTerms)->unique()->values()->all(),
        ];
    }

    private function buildLocationContext(string $location, float $radiusKm): ?array
    {
        if ($location === '') {
            return null;
        }

        $geocoded = $this->geocodingService->geocodeQuery($location);

        return [
            'raw' => $location,
            'normalized' => $this->normalize($location),
            'radius_km' => $radiusKm > 0 ? $radiusKm : (float) config('directory_search.default_radius_km', 10),
            'geocoded' => $geocoded,
        ];
    }

    private function applySpecialtyPrefilter(Builder $query, ?array $specialtyContext): void
    {
        if ($specialtyContext === null) {
            return;
        }

        $terms = $specialtyContext['raw'];

        $query->where(function (Builder $specialtyQuery) use ($terms) {
            foreach ($terms as $term) {
                $like = '%' . $term . '%';

                $specialtyQuery->orWhereRaw('LOWER(CAST(services AS CHAR)) LIKE ?', [$like])
                    ->orWhereRaw("LOWER(COALESCE(profile_description, '')) LIKE ?", [$like])
                    ->orWhereRaw("LOWER(COALESCE(about, '')) LIKE ?", [$like]);
            }
        });
    }

    private function applyLocationPrefilter(Builder $query, ?array $locationContext): void
    {
        if ($locationContext === null) {
            return;
        }

        $like = '%' . mb_strtolower($locationContext['raw'], 'UTF-8') . '%';
        $geocoded = $locationContext['geocoded'];

        if (! is_array($geocoded)) {
            return;
        }

        $bounds = $this->boundingBox(
            (float) $geocoded['latitude'],
            (float) $geocoded['longitude'],
            (float) $locationContext['radius_km']
        );

        $query->where(function (Builder $locationQuery) use ($like, $bounds) {
            $this->applyUserLocationTextQuery($locationQuery, $like);

            $locationQuery->orWhere(function (Builder $coordinateQuery) use ($bounds) {
                $coordinateQuery->whereNotNull('latitude_setByAdmin')
                    ->whereNotNull('longitude_setByAdmin')
                    ->whereBetween('latitude_setByAdmin', [$bounds['min_lat'], $bounds['max_lat']])
                    ->whereBetween('longitude_setByAdmin', [$bounds['min_lng'], $bounds['max_lng']]);
            });

            $locationQuery->orWhereHas('practiceLocations', function (Builder $locationRelation) use ($like, $bounds) {
                $this->applyPracticeLocationTextQuery($locationRelation, $like);

                $locationRelation->orWhere(function (Builder $coordinateQuery) use ($bounds) {
                    $coordinateQuery->whereNotNull('latitude')
                        ->whereNotNull('longitude')
                        ->whereBetween('latitude', [$bounds['min_lat'], $bounds['max_lat']])
                        ->whereBetween('longitude', [$bounds['min_lng'], $bounds['max_lng']]);
                });
            });
        });
    }

    private function applyFinalFilters(Collection $therapists, ?array $specialtyContext, ?array $locationContext): Collection
    {
        if ($specialtyContext !== null) {
            $therapists = $therapists->filter(fn (User $therapist) => $this->matchesSpecialty($therapist, $specialtyContext));
        }

        if ($locationContext !== null) {
            $therapists = $therapists->filter(fn (User $therapist) => $this->matchesLocation($therapist, $locationContext));
        }

        return $therapists->values();
    }

    private function matchesSpecialty(User $therapist, array $specialtyContext): bool
    {
        $terms = $specialtyContext['normalized'];

        $services = $therapist->services;

        if (is_string($services)) {
            $decoded = json_decode($services, true);
            $services = is_array($decoded) ? $decoded : [$services];
        }

        if (! is_array($services)) {
            $services = [];
        }

        $haystack = collect($services)
            ->map(fn ($service) => $this->normalize((string) $service))
            ->filter()
            ->implode(' ');

        $profileText = $this->normalize(collect([
            $therapist->profile_description,
            $therapist->about,
        ])->filter()->implode(' '));

        foreach ($terms as $term) {
            if ($term === '') {
                continue;
            }

            if (Str::contains($haystack, $term) || Str::contains($profileText, $term)) {
                return true;
            }
        }

        return false;
    }

    private function matchesLocation(User $therapist, array $locationContext): bool
    {
        if ($this->matchesLocationText($therapist, $locationContext['normalized'])) {
            return true;
        }

        $geocoded = $locationContext['geocoded'];

        if (! is_array($geocoded)) {
            return false;
        }

        $distances = [];

        if ($therapist->latitude_setByAdmin !== null && $therapist->longitude_setByAdmin !== null) {
            $distances[] = $this->distanceKm(
                (float) $geocoded['latitude'],
                (float) $geocoded['longitude'],
                (float) $therapist->latitude_setByAdmin,
                (float) $therapist->longitude_setByAdmin
            );
        }

        foreach ($therapist->practiceLocations as $location) {
            if ($location->latitude === null || $location->longitude === null) {
                continue;
            }

            $distances[] = $this->distanceKm(
                (float) $geocoded['latitude'],
                (float) $geocoded['longitude'],
                (float) $location->latitude,
                (float) $location->longitude
            );
        }

        if (empty($distances)) {
            return false;
        }

        $therapist->directory_distance_km = min($distances);

        return $therapist->directory_distance_km <= (float) $locationContext['radius_km'];
    }

    private function matchesLocationText(User $therapist, string $normalizedLocation): bool
    {
        if ($normalizedLocation === '') {
            return true;
        }

        $chunks = collect([
            $therapist->city_setByAdmin,
            $therapist->state_setByAdmin,
            $therapist->postal_code_setByAdmin,
            $therapist->street_address_setByAdmin,
            $therapist->address_line2_setByAdmin,
            $therapist->country_setByAdmin,
        ]);

        foreach ($therapist->practiceLocations as $location) {
            $chunks = $chunks->merge([
                $location->label,
                $location->address_line1,
                $location->address_line2,
                $location->postal_code,
                $location->city,
                $location->country,
            ]);
        }

        $haystack = $this->normalize($chunks->filter()->implode(' '));

        return $haystack !== '' && Str::contains($haystack, $normalizedLocation);
    }

    private function scoreByName(Collection $therapists, string $name, bool $fallbackMode = false): Collection
    {
        $normalizedTerm = $this->normalize($name);

        $scored = $therapists->map(function (User $therapist) use ($normalizedTerm) {
            $fullName = $this->normalize((string) ($therapist->name ?? ''));
            $companyName = $this->normalize((string) ($therapist->company_name ?? ''));

            $distances = [];

            if ($fullName !== '') {
                $distances[] = $this->lev($fullName, $normalizedTerm);
            }

            if ($companyName !== '') {
                $distances[] = $this->lev($companyName, $normalizedTerm);
            }

            foreach (preg_split('/\s+/', $fullName) ?: [] as $token) {
                if ($token !== '') {
                    $distances[] = $this->lev($token, $normalizedTerm);
                }
            }

            foreach (preg_split('/\s+/', $companyName) ?: [] as $token) {
                if ($token !== '') {
                    $distances[] = $this->lev($token, $normalizedTerm);
                }
            }

            if (empty($distances)) {
                $distances[] = 999;
            }

            $score = min($distances);

            if ($fullName !== '') {
                if (Str::startsWith($fullName, $normalizedTerm)) {
                    $score -= 2;
                }

                if (Str::contains($fullName, $normalizedTerm)) {
                    $score -= 1;
                }
            }

            if ($companyName !== '') {
                if (Str::startsWith($companyName, $normalizedTerm)) {
                    $score -= 2;
                }

                if (Str::contains($companyName, $normalizedTerm)) {
                    $score -= 1;
                }
            }

            $therapist->am_fuzzy_score = $score;

            return $therapist;
        });

        $threshold = max(1, (int) floor(max(1, mb_strlen($normalizedTerm)) * 0.4));

        $results = $scored
            ->filter(fn (User $therapist) => $therapist->am_fuzzy_score <= $threshold)
            ->sortBy('am_fuzzy_score')
            ->values();

        if ($results->isNotEmpty() || ! $fallbackMode) {
            return $results;
        }

        return $scored->sortBy('am_fuzzy_score')->take(20)->values();
    }

    private function applyUserLocationTextQuery(Builder $query, string $like): void
    {
        $query->whereRaw("LOWER(COALESCE(city_setByAdmin, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(state_setByAdmin, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(postal_code_setByAdmin, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(street_address_setByAdmin, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(address_line2_setByAdmin, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(country_setByAdmin, '')) LIKE ?", [$like]);
    }

    private function applyPracticeLocationTextQuery(Builder $query, string $like): void
    {
        $query->whereRaw("LOWER(COALESCE(city, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(postal_code, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(address_line1, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(address_line2, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(country, '')) LIKE ?", [$like])
            ->orWhereRaw("LOWER(COALESCE(label, '')) LIKE ?", [$like]);
    }

    private function boundingBox(float $latitude, float $longitude, float $radiusKm): array
    {
        $latDelta = $radiusKm / 111.32;
        $cosLatitude = cos(deg2rad($latitude));
        $lngDelta = $radiusKm / (111.32 * max(0.1, abs($cosLatitude)));

        return [
            'min_lat' => $latitude - $latDelta,
            'max_lat' => $latitude + $latDelta,
            'min_lng' => $longitude - $lngDelta,
            'max_lng' => $longitude + $lngDelta,
        ];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadiusKm = 6371;
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);

        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        return 2 * $earthRadiusKm * asin(min(1, sqrt($a)));
    }

    private function lev(string $left, string $right): int
    {
        if ($left === '' || $right === '') {
            return max(strlen($left), strlen($right));
        }

        return levenshtein($left, $right);
    }
}
