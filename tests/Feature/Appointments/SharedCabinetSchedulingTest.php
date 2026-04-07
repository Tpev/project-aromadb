<?php

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\ClientProfile;
use App\Models\PracticeLocation;
use App\Models\PracticeLocationMember;
use App\Models\Product;
use App\Models\User;
use App\Services\CabinetAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('features.shared_cabinets_v1', true);
});

function sharedSchedulingTherapist(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'minimum_notice_hours' => 0,
        'buffer_time_between_appointments' => 0,
    ], $attributes));
}

function sharedSchedulingLocation(User $owner, array $attributes = []): PracticeLocation
{
    $location = PracticeLocation::create(array_merge([
        'user_id' => $owner->id,
        'label' => 'Cabinet partagé agenda',
        'address_line1' => '10 avenue du Test',
        'postal_code' => '69000',
        'city' => 'Lyon',
        'country' => 'FR',
        'is_shared' => true,
        'shared_enabled_at' => now(),
    ], $attributes));

    app(CabinetAccessService::class)->ensureOwnerMembership($location);

    return $location;
}

function sharedSchedulingProduct(User $therapist, array $attributes = []): Product
{
    return Product::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Séance test',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'en_visio' => false,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => true,
    ], $attributes));
}

function sharedSchedulingClient(User $therapist, string $email): ClientProfile
{
    return ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Test',
        'email' => $email,
    ]);
}

function addSharedMember(PracticeLocation $location, User $owner, User $member): void
{
    PracticeLocationMember::create([
        'practice_location_id' => $location->id,
        'user_id' => $member->id,
        'role' => 'member',
        'accepted_at' => now(),
        'added_by_user_id' => $owner->id,
    ]);
}

test('shared cabinet booking hides an already occupied slot on the public web endpoint', function () {
    $owner = sharedSchedulingTherapist(['email' => 'owner-slots@example.test']);
    $member = sharedSchedulingTherapist(['email' => 'member-slots@example.test']);
    $location = sharedSchedulingLocation($owner);
    addSharedMember($location, $owner, $member);

    $memberProduct = sharedSchedulingProduct($member);
    $ownerProduct = sharedSchedulingProduct($owner, ['name' => 'Séance propriétaire']);

    Availability::create([
        'user_id' => $member->id,
        'practice_location_id' => $location->id,
        'day_of_week' => 0,
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'applies_to_all' => true,
    ]);

    $ownerClient = sharedSchedulingClient($owner, 'owner-client@example.test');

    Appointment::create([
        'client_profile_id' => $ownerClient->id,
        'user_id' => $owner->id,
        'product_id' => $ownerProduct->id,
        'practice_location_id' => $location->id,
        'appointment_date' => '2026-04-06 09:00:00',
        'status' => 'confirmed',
        'duration' => 60,
        'type' => 'cabinet',
    ]);

    $response = $this->post(route('appointments.available-slots-patient'), [
        'therapist_id' => $member->id,
        'product_id' => $memberProduct->id,
        'date' => '2026-04-06',
        'mode' => 'cabinet',
        'location_id' => $location->id,
    ]);

    $response->assertOk();

    $starts = collect($response->json('slots'))->pluck('start')->all();

    expect($starts)->not->toContain('09:00');
    expect($starts)->toContain('10:00');
});

test('shared cabinet conflict does not block another cabinet or a non cabinet mode', function () {
    $owner = sharedSchedulingTherapist(['email' => 'owner-other@example.test']);
    $member = sharedSchedulingTherapist(['email' => 'member-other@example.test']);
    $sharedLocation = sharedSchedulingLocation($owner, ['label' => 'Cabinet partagé']);
    $otherLocation = sharedSchedulingLocation($member, [
        'label' => 'Autre cabinet',
        'is_shared' => false,
        'shared_enabled_at' => null,
    ]);

    addSharedMember($sharedLocation, $owner, $member);

    $cabinetProduct = sharedSchedulingProduct($member);
    $visioProduct = sharedSchedulingProduct($member, [
        'name' => 'Séance visio',
        'visio' => true,
        'dans_le_cabinet' => false,
    ]);
    $ownerProduct = sharedSchedulingProduct($owner, ['name' => 'Séance propriétaire bis']);

    Availability::create([
        'user_id' => $member->id,
        'practice_location_id' => $sharedLocation->id,
        'day_of_week' => 0,
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'applies_to_all' => true,
    ]);

    Availability::create([
        'user_id' => $member->id,
        'practice_location_id' => $otherLocation->id,
        'day_of_week' => 0,
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'applies_to_all' => true,
    ]);

    $ownerClient = sharedSchedulingClient($owner, 'owner-client-other@example.test');

    Appointment::create([
        'client_profile_id' => $ownerClient->id,
        'user_id' => $owner->id,
        'product_id' => $ownerProduct->id,
        'practice_location_id' => $sharedLocation->id,
        'appointment_date' => '2026-04-06 09:00:00',
        'status' => 'confirmed',
        'duration' => 60,
        'type' => 'cabinet',
    ]);

    $otherCabinetResponse = $this->post(route('appointments.available-slots-patient'), [
        'therapist_id' => $member->id,
        'product_id' => $cabinetProduct->id,
        'date' => '2026-04-06',
        'mode' => 'cabinet',
        'location_id' => $otherLocation->id,
    ]);

    $otherCabinetResponse->assertOk();
    expect(collect($otherCabinetResponse->json('slots'))->pluck('start')->all())->toContain('09:00');

    $visioResponse = $this->post(route('appointments.available-slots-patient'), [
        'therapist_id' => $member->id,
        'product_id' => $visioProduct->id,
        'date' => '2026-04-06',
        'mode' => 'visio',
    ]);

    $visioResponse->assertOk();
    expect(collect($visioResponse->json('slots'))->pluck('start')->all())->toContain('09:00');
});

test('mobile slots endpoint applies the same shared cabinet constraint', function () {
    $owner = sharedSchedulingTherapist(['email' => 'owner-mobile@example.test', 'slug' => 'owner-mobile']);
    $member = sharedSchedulingTherapist(['email' => 'member-mobile@example.test', 'slug' => 'member-mobile']);
    $location = sharedSchedulingLocation($owner);
    addSharedMember($location, $owner, $member);

    $memberProduct = sharedSchedulingProduct($member);
    $ownerProduct = sharedSchedulingProduct($owner, ['name' => 'Séance mobile propriétaire']);

    Availability::create([
        'user_id' => $member->id,
        'practice_location_id' => $location->id,
        'day_of_week' => 0,
        'start_time' => '09:00:00',
        'end_time' => '12:00:00',
        'applies_to_all' => true,
    ]);

    $ownerClient = sharedSchedulingClient($owner, 'owner-mobile-client@example.test');

    Appointment::create([
        'client_profile_id' => $ownerClient->id,
        'user_id' => $owner->id,
        'product_id' => $ownerProduct->id,
        'practice_location_id' => $location->id,
        'appointment_date' => '2026-04-06 09:00:00',
        'status' => 'confirmed',
        'duration' => 60,
        'type' => 'cabinet',
    ]);

    $response = $this->get(route('mobile.appointments.slots', [
        'therapist_id' => $member->id,
        'product_id' => $memberProduct->id,
        'date' => '2026-04-06',
        'mode' => 'cabinet',
        'location_id' => $location->id,
    ]));

    $response->assertOk();

    $starts = collect($response->json('slots'))->pluck('start')->all();

    expect($starts)->not->toContain('09:00');
    expect($starts)->toContain('10:00');
});
