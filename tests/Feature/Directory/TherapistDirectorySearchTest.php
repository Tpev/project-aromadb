<?php

use App\Models\PracticeLocation;
use App\Models\User;
use App\Services\TherapistDirectorySearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

test('directory search matches specialty aliases like naturopathe and naturopathie', function () {
    $matchingTherapist = User::factory()->create([
        'name' => 'Therapeute Naturo',
        'email' => 'naturo@example.test',
        'is_therapist' => true,
        'slug' => 'therapeute-naturo',
        'visible_annuarire_admin_set' => true,
        'services' => json_encode(['Naturopathie']),
    ]);

    $nonMatchingTherapist = User::factory()->create([
        'name' => 'Therapeute Massage',
        'email' => 'massage@example.test',
        'is_therapist' => true,
        'slug' => 'therapeute-massage',
        'visible_annuarire_admin_set' => true,
        'services' => json_encode(['Massage bien-être']),
    ]);

    $results = app(TherapistDirectorySearchService::class)->search([
        'specialty' => 'naturopathe',
    ]);

    expect($results->pluck('id')->all())
        ->toContain($matchingTherapist->id)
        ->not->toContain($nonMatchingTherapist->id);
});

test('directory search matches nearby practice locations within the default 10km radius', function () {
    Http::fake([
        'https://data.geopf.fr/geocodage/search*' => Http::response([
            'features' => [[
                'geometry' => [
                    'coordinates' => [7.6825, 48.5577],
                ],
                'properties' => [
                    'label' => 'Lingolsheim',
                ],
            ]],
        ], 200),
    ]);

    $nearTherapist = User::factory()->create([
        'name' => 'Cabinet Strasbourg',
        'email' => 'strasbourg@example.test',
        'is_therapist' => true,
        'slug' => 'cabinet-strasbourg',
        'visible_annuarire_admin_set' => true,
    ]);

    PracticeLocation::create([
        'user_id' => $nearTherapist->id,
        'label' => 'Cabinet Strasbourg Centre',
        'address_line1' => '1 rue de Strasbourg',
        'postal_code' => '67000',
        'city' => 'Strasbourg',
        'country' => 'FR',
        'latitude' => 48.5734,
        'longitude' => 7.7521,
        'is_primary' => true,
    ]);

    $farTherapist = User::factory()->create([
        'name' => 'Cabinet Paris',
        'email' => 'paris@example.test',
        'is_therapist' => true,
        'slug' => 'cabinet-paris',
        'visible_annuarire_admin_set' => true,
    ]);

    PracticeLocation::create([
        'user_id' => $farTherapist->id,
        'label' => 'Cabinet Paris',
        'address_line1' => '10 avenue de Paris',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
        'latitude' => 48.8566,
        'longitude' => 2.3522,
        'is_primary' => true,
    ]);

    $results = app(TherapistDirectorySearchService::class)->search([
        'location' => 'Lingolsheim',
    ]);

    expect($results->pluck('id')->all())
        ->toContain($nearTherapist->id)
        ->not->toContain($farTherapist->id);
});

test('directory geocoding command initializes missing practice location and therapist coordinates', function () {
    Http::fake([
        'https://data.geopf.fr/geocodage/search*' => Http::response([
            'features' => [[
                'geometry' => [
                    'coordinates' => [7.7521, 48.5734],
                ],
                'properties' => [
                    'label' => 'Adresse test',
                ],
            ]],
        ], 200),
    ]);

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-test',
        'visible_annuarire_admin_set' => true,
        'street_address_setByAdmin' => '5 rue des Lilas',
        'postal_code_setByAdmin' => '67000',
        'city_setByAdmin' => 'Strasbourg',
        'country_setByAdmin' => 'FR',
        'latitude_setByAdmin' => null,
        'longitude_setByAdmin' => null,
    ]);

    $location = PracticeLocation::create([
        'user_id' => $therapist->id,
        'label' => 'Cabinet test',
        'address_line1' => '5 rue des Lilas',
        'postal_code' => '67000',
        'city' => 'Strasbourg',
        'country' => 'FR',
        'latitude' => null,
        'longitude' => null,
        'is_primary' => true,
    ]);

    $this->artisan('directory:initialize-geocoding')
        ->assertExitCode(0);

    $location->refresh();
    $therapist->refresh();

    expect($location->latitude)->toBe(48.5734)
        ->and($location->longitude)->toBe(7.7521)
        ->and((float) $therapist->latitude_setByAdmin)->toBe(48.5734)
        ->and((float) $therapist->longitude_setByAdmin)->toBe(7.7521);
});
