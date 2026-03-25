<?php

namespace App\Console\Commands;

use App\Models\PracticeLocation;
use App\Models\User;
use App\Services\FrenchAddressGeocodingService;
use Illuminate\Console\Command;

class InitializeDirectoryGeocoding extends Command
{
    protected $signature = 'directory:initialize-geocoding {--force : Regeocode even records that already have coordinates} {--limit=0 : Limit the number of records processed per dataset}';

    protected $description = 'Initialise les coordonnees geographiques des cabinets et des profils praticiens existants pour la recherche annuaire.';

    public function handle(FrenchAddressGeocodingService $geocodingService): int
    {
        $force = (bool) $this->option('force');
        $limit = max(0, (int) $this->option('limit'));

        $this->info('Initialisation des coordonnees de l annuaire...');

        $locationCount = $this->processPracticeLocations($geocodingService, $force, $limit);
        $userCount = $this->processTherapistProfiles($geocodingService, $force, $limit);

        $this->newLine();
        $this->info("Termine. Cabinets mis a jour : {$locationCount}. Profils mis a jour : {$userCount}.");
        $this->line('Commande a reutiliser : php artisan directory:initialize-geocoding');

        return self::SUCCESS;
    }

    private function processPracticeLocations(FrenchAddressGeocodingService $geocodingService, bool $force, int $limit): int
    {
        $query = PracticeLocation::query()->orderBy('id');

        if (! $force) {
            $query->where(function ($builder) {
                $builder->whereNull('latitude')
                    ->orWhereNull('longitude');
            });
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        $updated = 0;

        foreach ($query->get() as $location) {
            $coordinates = $geocodingService->geocodeAddressParts([
                $location->address_line1,
                $location->address_line2,
                trim(($location->postal_code ?? '') . ' ' . ($location->city ?? '')),
                $location->country,
            ], false);

            if (! is_array($coordinates)) {
                $this->warn("Cabinet #{$location->id} non geocode : {$location->full_address}");
                continue;
            }

            $location->forceFill([
                'latitude' => $coordinates['latitude'],
                'longitude' => $coordinates['longitude'],
            ])->save();

            $updated++;
        }

        $this->line("Cabinets traites : {$updated}");

        return $updated;
    }

    private function processTherapistProfiles(FrenchAddressGeocodingService $geocodingService, bool $force, int $limit): int
    {
        $query = User::query()
            ->where('is_therapist', true)
            ->orderBy('id');

        if (! $force) {
            $query->where(function ($builder) {
                $builder->whereNull('latitude_setByAdmin')
                    ->orWhereNull('longitude_setByAdmin');
            });
        }

        $query->where(function ($builder) {
            $builder->whereNotNull('street_address_setByAdmin')
                ->orWhereNotNull('city_setByAdmin')
                ->orWhereNotNull('postal_code_setByAdmin')
                ->orWhereNotNull('state_setByAdmin');
        });

        if ($limit > 0) {
            $query->limit($limit);
        }

        $updated = 0;

        foreach ($query->get() as $user) {
            $coordinates = $geocodingService->geocodeAddressParts([
                $user->street_address_setByAdmin,
                $user->address_line2_setByAdmin,
                trim(($user->postal_code_setByAdmin ?? '') . ' ' . ($user->city_setByAdmin ?? '')),
                $user->state_setByAdmin,
                $user->country_setByAdmin,
            ], false);

            if (! is_array($coordinates)) {
                $this->warn("Profil praticien #{$user->id} non geocode.");
                continue;
            }

            $user->forceFill([
                'latitude_setByAdmin' => $coordinates['latitude'],
                'longitude_setByAdmin' => $coordinates['longitude'],
            ])->save();

            $updated++;
        }

        $this->line("Profils praticiens traites : {$updated}");

        return $updated;
    }
}
