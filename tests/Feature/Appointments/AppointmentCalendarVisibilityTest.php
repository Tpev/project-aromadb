<?php

use App\Models\Appointment;
use App\Models\Availability;
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

test('cancelled appointments are hidden by default and remain accessible in the cancelled history', function () {
    $therapist = calendarTherapist(['email' => 'calendar-muted@example.test']);
    $product = calendarProduct($therapist);
    $activeClient = calendarClient($therapist, 'Alice', 'Active', 'alice-active@example.test');
    $futureCancelledClient = calendarClient($therapist, 'Camille', 'FutureCancelled', 'camille@example.test');
    $pastCancelledClient = calendarClient($therapist, 'Pascal', 'PastCancelled', 'pascal@example.test');

    calendarAppointment($therapist, $activeClient, $product, [
        'status' => 'Confirmé',
        'appointment_date' => now()->addDays(2),
    ]);

    calendarAppointment($therapist, $futureCancelledClient, $product, [
        'status' => 'cancelled',
        'appointment_date' => now()->addDay(),
    ]);

    calendarAppointment($therapist, $pastCancelledClient, $product, [
        'status' => 'Annulé',
        'appointment_date' => now()->subDay(),
    ]);

    $response = $this->actingAs($therapist)->get(route('appointments.index'));

    $response->assertOk()
        ->assertSee('Alice Active')
        ->assertDontSee('Camille FutureCancelled')
        ->assertDontSee('Pascal PastCancelled')
        ->assertViewHas('appointmentStatusFilter', 'active')
        ->assertViewHas('appointmentStatusCounts', [
            'active' => 1,
            'cancelled' => 2,
            'all' => 3,
        ]);

    $cancelledResponse = $this->get(route('appointments.index', ['appointment_status' => 'cancelled']));

    $cancelledResponse->assertOk()
        ->assertSee('FutureCancelled')
        ->assertSee('PastCancelled')
        ->assertSee('am-row-cancelled', false)
        ->assertSee('bg-secondary-subtle text-secondary', false)
        ->assertViewHas('appointmentStatusFilter', 'cancelled')
        ->assertViewHas('appointments', fn ($appointments) => $appointments->count() === 2 && $appointments->every(fn (Appointment $appointment) => $appointment->isCancelled()))
        ->assertViewHas('rendezVousAVenir', fn ($appointments) => $appointments->count() === 1 && $appointments->first()->isCancelled())
        ->assertViewHas('rendezVousPasses', fn ($appointments) => $appointments->count() === 1 && $appointments->first()->isCancelled());

    $this->get(route('mobile.appointments.index', ['appointment_status' => 'cancelled']))
        ->assertOk()
        ->assertViewIs('mobile.appointments.index')
        ->assertSee('FutureCancelled')
        ->assertSee('PastCancelled')
        ->assertViewHas('appointments', fn ($appointments) => $appointments->count() === 2 && $appointments->every(fn (Appointment $appointment) => $appointment->isCancelled()));
});

test('client names open client profiles while appointment rows keep their appointment destination', function () {
    $therapist = calendarTherapist(['email' => 'calendar-client-link@example.test']);
    $product = calendarProduct($therapist);
    $client = calendarClient($therapist, 'Alice', 'Navigation', 'alice-navigation@example.test');
    $appointment = calendarAppointment($therapist, $client, $product, [
        'appointment_date' => now()->addDays(2),
    ]);

    $this->actingAs($therapist)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee(route('client_profiles.show', $client), false)
        ->assertSee('class="am-client-link', false)
        ->assertSee('data-url="'.route('appointments.show', $appointment).'"', false)
        ->assertSee('Cliquez sur le nom pour ouvrir la fiche client');
});

test('Google events are shown in the calendar by default and can be hidden without entering appointment lists', function () {
    $therapist = calendarTherapist(['email' => 'calendar-google-filter@example.test']);
    $product = calendarProduct($therapist);
    $client = calendarClient($therapist, 'Interne', 'Olithea', 'internal@example.test');

    calendarAppointment($therapist, $client, $product, [
        'appointment_date' => now()->addDays(2),
    ]);

    $external = Appointment::create([
        'user_id' => $therapist->id,
        'client_profile_id' => null,
        'product_id' => null,
        'appointment_date' => now()->addDays(3),
        'duration' => 90,
        'status' => 'busy',
        'type' => 'external',
        'external' => true,
        'google_event_id' => 'google-busy-1',
        'notes' => 'Sport personnel',
    ]);

    $this->actingAs($therapist)
        ->get(route('appointments.index'))
        ->assertOk()
        ->assertSee('Afficher les événements Google')
        ->assertViewHas('showGoogleEvents', true)
        ->assertViewHas('events', fn (array $events) => collect($events)->pluck('title')->contains('Sport personnel'))
        ->assertViewHas('appointments', fn ($appointments) => $appointments->every(fn (Appointment $appointment) => ! $appointment->external));

    $this->get(route('appointments.index', ['calendar_source' => 'olithea']))
        ->assertOk()
        ->assertViewHas('showGoogleEvents', false)
        ->assertViewHas('events', fn (array $events) => ! collect($events)->pluck('title')->contains('Sport personnel'));

    $this->get(route('appointments.index'))
        ->assertViewHas('showGoogleEvents', false);

    expect($external->fresh())->not->toBeNull()
        ->and($external->fresh()->external)->toBeTrue();

    $this->flushSession();

    $this->actingAs($therapist)
        ->get(route('mobile.appointments.index'))
        ->assertOk()
        ->assertViewIs('mobile.appointments.index')
        ->assertSee('Événements Google')
        ->assertViewHas('showGoogleEvents', true)
        ->assertViewHas('events', fn (array $events) => collect($events)->pluck('title')->contains('Sport personnel'));

    $this->get(route('mobile.appointments.index', ['calendar_source' => 'olithea']))
        ->assertOk()
        ->assertViewHas('showGoogleEvents', false)
        ->assertViewHas('events', fn (array $events) => ! collect($events)->pluck('title')->contains('Sport personnel'));
});

test('a hidden Google event still blocks its public booking slot', function () {
    $therapist = calendarTherapist(['email' => 'calendar-google-blocking@example.test']);
    $product = calendarProduct($therapist, [
        'name' => 'Séance visio Google',
        'visio' => true,
        'en_visio' => true,
        'dans_le_cabinet' => false,
    ]);
    $bookingDate = now()->addDays(10)->startOfDay();

    Availability::create([
        'user_id' => $therapist->id,
        'day_of_week' => $bookingDate->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '11:00:00',
        'applies_to_all' => true,
    ]);

    Appointment::create([
        'user_id' => $therapist->id,
        'appointment_date' => $bookingDate->copy()->setTime(9, 0),
        'duration' => 60,
        'status' => 'busy',
        'type' => 'external',
        'external' => true,
        'google_event_id' => 'google-public-block-1',
        'notes' => 'Sport personnel',
    ]);

    $this->actingAs($therapist)
        ->get(route('appointments.index', ['calendar_source' => 'olithea']))
        ->assertViewHas('events', fn (array $events) => ! collect($events)->pluck('title')->contains('Sport personnel'));

    $response = $this->post(route('appointments.available-slots-patient'), [
        'therapist_id' => $therapist->id,
        'product_id' => $product->id,
        'date' => $bookingDate->toDateString(),
        'mode' => 'visio',
    ]);

    $response->assertOk();
    $starts = collect($response->json('slots'))->pluck('start');

    expect($starts)->not->toContain('09:00')
        ->and($starts)->toContain('10:00');
});
