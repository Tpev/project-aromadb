<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function calendarTherapist(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'license_status' => 'active',
    ], $attributes));
}

function calendarProduct(User $therapist, array $attributes = []): Product
{
    return Product::create(array_merge([
        'user_id' => $therapist->id,
        'name' => 'Seance agenda',
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

function calendarClient(User $therapist, string $firstName, string $lastName, string $email): ClientProfile
{
    return ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
    ]);
}

function calendarAppointment(User $therapist, ClientProfile $client, Product $product, array $attributes = []): Appointment
{
    return Appointment::create(array_merge([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addDay(),
        'status' => 'Confirmé',
        'duration' => 60,
        'type' => 'cabinet',
    ], $attributes));
}

test('cancelled appointments are excluded from calendar events on appointments index', function () {
    $therapist = calendarTherapist();
    $product = calendarProduct($therapist);

    $visibleClient = calendarClient($therapist, 'Alice', 'Visible', 'alice@example.test');
    $hiddenClient = calendarClient($therapist, 'Bob', 'Cancelled', 'bob@example.test');

    $visibleAppointment = calendarAppointment($therapist, $visibleClient, $product, [
        'status' => 'Confirmé',
        'appointment_date' => now()->addDays(2),
    ]);

    $cancelledAppointment = calendarAppointment($therapist, $hiddenClient, $product, [
        'status' => 'cancelled',
        'appointment_date' => now()->addDays(3),
    ]);

    $response = $this->actingAs($therapist)->get(route('appointments.index'));

    $response->assertOk();
    $response->assertViewHas('events', function (array $events) use ($visibleAppointment, $cancelledAppointment) {
        $eventUrls = collect($events)->pluck('url')->filter()->values();

        return $eventUrls->contains(route('appointments.show', $visibleAppointment))
            && ! $eventUrls->contains(route('appointments.show', $cancelledAppointment));
    });
});

test('cancelled appointments are visually muted in appointments index lists', function () {
    $therapist = calendarTherapist(['email' => 'calendar-muted@example.test']);
    $product = calendarProduct($therapist);
    $client = calendarClient($therapist, 'Camille', 'Muted', 'camille@example.test');

    calendarAppointment($therapist, $client, $product, [
        'status' => 'cancelled',
        'appointment_date' => now()->addDay(),
    ]);

    $response = $this->actingAs($therapist)->get(route('appointments.index'));

    $response->assertOk()
        ->assertSee('am-row-cancelled', false)
        ->assertSee('bg-secondary-subtle text-secondary', false);
});
