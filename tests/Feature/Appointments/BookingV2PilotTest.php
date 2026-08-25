<?php

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\BookingLink;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use App\Models\InformationRequest;
use App\Mail\AppointmentCreatedPatientMail;
use App\Mail\AppointmentReminderClientMail;
use App\Services\AppointmentAvailabilityService;
use App\Services\AppointmentLifecycleService;
use App\Models\Meeting;
use App\Models\PracticeLocation;
use App\Models\SpecialAvailability;
use App\Services\BookingLocationService;
use App\Services\BookingAppointmentCreationService;
use App\Support\BookingV2Access;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

uses(RefreshDatabase::class);

function bookingV2User(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'minimum_notice_hours' => 0,
        'buffer_time_between_appointments' => 0,
        'booking_schedule_mode' => 'fixed',
        'booking_slot_interval_minutes' => 30,
    ], $attributes));
}

function bookingV2Product(User $user, array $attributes = []): Product
{
    return Product::create(array_merge([
        'user_id' => $user->id,
        'name' => 'Séance pilote',
        'price' => 70,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
    ], $attributes));
}

function bookingV2Date(): Carbon
{
    return now()->addWeeks(2)->startOfWeek()->setTime(9, 0)->startOfMinute();
}

function bookingV2Availability(User $user, Carbon $date, string $end = '13:00:00'): Availability
{
    return Availability::create([
        'user_id' => $user->id,
        'day_of_week' => $date->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => $end,
        'applies_to_all' => true,
    ]);
}

function enableBookingV2For(User $user): void
{
    config()->set('appointments.booking_v2.enabled', true);
    config()->set('appointments.booking_v2.allowed_user_ids', [$user->id]);
}

function bookingV2Template(User $user, Product $product): Appointment
{
    $appointment = new Appointment([
        'user_id' => $user->id,
        'product_id' => $product->id,
        'duration' => $product->duration,
        'type' => 'visio',
    ]);
    $appointment->id = 0;
    $appointment->setRelation('user', $user);
    $appointment->setRelation('product', $product);

    return $appointment;
}

test('booking v2 requires both the switch and an explicit allowlist entry', function () {
    $allowed = bookingV2User();
    $other = bookingV2User();

    config()->set('appointments.booking_v2.enabled', false);
    config()->set('appointments.booking_v2.allowed_user_ids', [$allowed->id]);
    expect(app(BookingV2Access::class)->enabledFor($allowed))->toBeFalse();

    config()->set('appointments.booking_v2.enabled', true);
    config()->set('appointments.booking_v2.allowed_user_ids', []);
    expect(app(BookingV2Access::class)->enabledFor($allowed))->toBeFalse();

    config()->set('appointments.booking_v2.allowed_user_ids', [$allowed->id]);
    expect(app(BookingV2Access::class)->enabledFor($allowed))->toBeTrue()
        ->and(app(BookingV2Access::class)->enabledFor($other))->toBeFalse();
});

test('a non allowlisted practitioner keeps the exact legacy fifteen minute grid', function () {
    $user = bookingV2User(['booking_slot_interval_minutes' => 60]);
    $product = bookingV2Product($user);
    $date = bookingV2Date();
    bookingV2Availability($user, $date, '11:00:00');

    config()->set('appointments.booking_v2.enabled', true);
    config()->set('appointments.booking_v2.allowed_user_ids', []);

    $starts = collect(app(AppointmentAvailabilityService::class)->slotsForDate(
        bookingV2Template($user, $product),
        $date
    ))->pluck('start')->all();

    expect($starts)->toBe(['09:00', '09:15', '09:30', '09:45', '10:00']);
});

test('non allowlisted public endpoints keep accepting services handled by the legacy form', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user, ['can_be_booked_online' => false]);
    $date = bookingV2Date();
    bookingV2Availability($user, $date, '11:00:00');

    config()->set('appointments.booking_v2.enabled', true);
    config()->set('appointments.booking_v2.allowed_user_ids', []);

    $payload = [
        'therapist_id' => $user->id,
        'product_id' => $product->id,
        'date' => $date->toDateString(),
        'mode' => 'visio',
    ];

    $this->post(route('appointments.available-slots-patient'), $payload)
        ->assertOk()
        ->assertJsonCount(5, 'slots');

    $this->get(route('mobile.appointments.slots', $payload))
        ->assertOk()
        ->assertJsonCount(5, 'slots');

    $datePayload = array_merge($payload, ['days' => 21]);
    unset($datePayload['date']);

    $this->post(route('appointments.available-dates-concrete-patient'), $datePayload)
        ->assertOk();
    $this->get(route('mobile.appointments.concrete_dates', $datePayload))
        ->assertOk();
});

test('fixed booking grids support every configured interval', function (int $interval, array $expected) {
    $user = bookingV2User(['booking_slot_interval_minutes' => $interval]);
    $product = bookingV2Product($user);
    $date = bookingV2Date();
    bookingV2Availability($user, $date, '11:00:00');
    enableBookingV2For($user);

    $starts = collect(app(AppointmentAvailabilityService::class)->slotsForDate(
        bookingV2Template($user, $product),
        $date
    ))->pluck('start')->all();

    expect($starts)->toBe($expected);
})->with([
    '15 minutes' => [15, ['09:00', '09:15', '09:30', '09:45', '10:00']],
    '30 minutes' => [30, ['09:00', '09:30', '10:00']],
    '45 minutes' => [45, ['09:00', '09:45']],
    '60 minutes' => [60, ['09:00', '10:00']],
]);

test('optimized scheduling adapts to each selected service duration and buffer', function () {
    $user = bookingV2User(['booking_schedule_mode' => 'optimized']);
    $short = bookingV2Product($user, ['duration' => 60, 'buffer_time_after_minutes' => 15]);
    $long = bookingV2Product($user, ['name' => 'Séance longue', 'duration' => 90, 'buffer_time_after_minutes' => 15]);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);

    $shortStarts = collect(app(AppointmentAvailabilityService::class)->slotsForDate(bookingV2Template($user, $short), $date))->pluck('start')->all();
    $longStarts = collect(app(AppointmentAvailabilityService::class)->slotsForDate(bookingV2Template($user, $long), $date))->pluck('start')->all();

    expect($shortStarts)->toBe(['09:00', '10:15', '11:30'])
        ->and($longStarts)->toBe(['09:00', '10:45']);
});

test('optimized scheduling proposes useful starts immediately before an existing protected appointment', function () {
    $user = bookingV2User(['booking_schedule_mode' => 'optimized']);
    $candidateProduct = bookingV2Product($user, ['buffer_time_after_minutes' => 15]);
    $existingProduct = bookingV2Product($user, ['name' => 'Séance suivante']);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Nora',
        'last_name' => 'Test',
        'email' => 'nora-boundary@example.test',
    ]);
    Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $existingProduct->id,
        'appointment_date' => $date->copy()->setTime(11, 0),
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ]);

    $starts = collect(app(AppointmentAvailabilityService::class)->slotsForDate(
        bookingV2Template($user, $candidateProduct),
        $date
    ))->pluck('start');

    expect($starts)->toContain('09:45');
});

test('the ninety day v2 date scan preserves slot results with a bounded query count', function () {
    $user = bookingV2User(['booking_schedule_mode' => 'optimized']);
    $product = bookingV2Product($user, [
        'duration' => 60,
        'preparation_time_minutes' => 10,
        'buffer_time_after_minutes' => 15,
    ]);
    $firstDate = Carbon::today();

    foreach (range(0, 6) as $dayOfWeek) {
        Availability::create([
            'user_id' => $user->id,
            'day_of_week' => $dayOfWeek,
            'start_time' => '09:00:00',
            'end_time' => '18:00:00',
            'applies_to_all' => true,
        ]);
    }
    enableBookingV2For($user);
    $template = bookingV2Template($user, $product);
    $service = app(AppointmentAvailabilityService::class);

    $expectedDates = collect(range(0, 13))
        ->map(fn (int $offset): Carbon => $firstDate->copy()->addDays($offset))
        ->filter(fn (Carbon $date): bool => $service->slotsForDate($template, $date) !== [])
        ->map(fn (Carbon $date): string => $date->toDateString())
        ->values()
        ->all();

    DB::flushQueryLog();
    DB::enableQueryLog();
    $result = $service->availableDates($template, $firstDate, 90);
    $queryCount = count(DB::getQueryLog());
    DB::disableQueryLog();

    expect(array_slice($result['dates'], 0, count($expectedDates)))->toBe($expectedDates)
        ->and($result['next']['date'] ?? null)->toBe($expectedDates[0] ?? null)
        ->and($queryCount)->toBeLessThan(20);
});

test('v2 keeps the consultation mode selected for a multi mode service and rejects an unsupported mode', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user, [
        'visio' => true,
        'adomicile' => true,
    ]);
    enableBookingV2For($user);

    $template = bookingV2Template($user, $product);
    $template->type = 'domicile';
    expect(app(\App\Services\BookingSchedulingPolicy::class)->resolvedMode($template, $user))->toBe('domicile');

    $template->type = 'cabinet';
    expect(app(AppointmentAvailabilityService::class)->isAvailable($template, bookingV2Date()))->toBeFalse();
});

test('single cabinet selection uses only locations configured for the selected service', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user, [
        'visio' => false,
        'dans_le_cabinet' => true,
    ]);
    $configured = PracticeLocation::create([
        'user_id' => $user->id,
        'label' => 'Cabinet Centre',
        'address_line1' => '1 rue Centrale',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
    ]);
    PracticeLocation::create([
        'user_id' => $user->id,
        'label' => 'Cabinet Nord',
        'address_line1' => '2 rue du Nord',
        'postal_code' => '75018',
        'city' => 'Paris',
        'country' => 'FR',
    ]);
    Availability::create([
        'user_id' => $user->id,
        'day_of_week' => bookingV2Date()->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '13:00:00',
        'applies_to_all' => true,
        'practice_location_id' => $configured->id,
    ]);
    enableBookingV2For($user);

    $map = app(BookingLocationService::class)->compatibleLocationsByProduct($user, collect([$product]));

    expect($map[$product->id])->toHaveCount(1)
        ->and($map[$product->id][0]['id'])->toBe($configured->id)
        ->and($map[$product->id][0]['label'])->toBe('Cabinet Centre');
});

test('pilot location backfill is scoped dry run safe and covers weekly and special availability', function () {
    $pilot = bookingV2User();
    $other = bookingV2User();
    $primary = PracticeLocation::create([
        'user_id' => $pilot->id,
        'label' => 'Cabinet principal',
        'address_line1' => '1 rue du Test',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
        'is_primary' => true,
    ]);
    PracticeLocation::create([
        'user_id' => $other->id,
        'label' => 'Autre cabinet',
        'address_line1' => '2 rue du Test',
        'postal_code' => '69001',
        'city' => 'Lyon',
        'country' => 'FR',
        'is_primary' => true,
    ]);
    $weekly = bookingV2Availability($pilot, bookingV2Date());
    $special = SpecialAvailability::create([
        'user_id' => $pilot->id,
        'date' => bookingV2Date()->toDateString(),
        'start_time' => '14:00:00',
        'end_time' => '17:00:00',
        'applies_to_all' => true,
    ]);
    $otherWeekly = bookingV2Availability($other, bookingV2Date());

    $this->artisan('app:backfill-availability-locations', [
        '--user-id' => [$pilot->id],
        '--dry-run' => true,
    ])->assertSuccessful();

    expect($weekly->fresh()->practice_location_id)->toBeNull()
        ->and($special->fresh()->practice_location_id)->toBeNull();

    $this->artisan('app:backfill-availability-locations', [
        '--user-id' => [$pilot->id],
    ])->assertSuccessful();

    expect($weekly->fresh()->practice_location_id)->toBe($primary->id)
        ->and($special->fresh()->practice_location_id)->toBe($primary->id)
        ->and($otherWeekly->fresh()->practice_location_id)->toBeNull();
});

test('v2 profile only advertises booking when the service has a usable destination', function () {
    $user = bookingV2User([
        'slug' => 'cabinet-pilote-destination',
        'company_name' => 'Cabinet Destination',
    ]);
    $product = bookingV2Product($user, [
        'name' => 'Séance au cabinet',
        'description' => 'Une prestation au cabinet.',
        'visible_in_portal' => true,
        'visio' => false,
        'dans_le_cabinet' => true,
    ]);
    PracticeLocation::create([
        'user_id' => $user->id,
        'label' => 'Cabinet principal',
        'address_line1' => '1 rue du Test',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
        'is_primary' => true,
    ]);
    bookingV2Availability($user, bookingV2Date());
    enableBookingV2For($user);

    $bookingUrl = route('appointments.createPatient', [
        'therapist' => $user->id,
        'product_id' => $product->id,
    ]);

    $this->get(route('therapist.show', $user->slug))
        ->assertOk()
        ->assertDontSee($bookingUrl, false);

    $this->artisan('app:backfill-availability-locations', [
        '--user-id' => [$user->id],
    ])->assertSuccessful();

    $this->get(route('therapist.show', $user->slug))
        ->assertOk()
        ->assertSee($bookingUrl, false);
});

test('asymmetric preparation and post appointment buffers protect the gap', function () {
    $user = bookingV2User();
    $firstProduct = bookingV2Product($user, ['buffer_time_after_minutes' => 30]);
    $nextProduct = bookingV2Product($user, ['name' => 'Deuxième séance', 'preparation_time_minutes' => 10]);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);

    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Camille',
        'last_name' => 'Test',
        'email' => 'camille-v2@example.test',
    ]);
    Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $firstProduct->id,
        'appointment_date' => $date,
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ]);

    $candidate = bookingV2Template($user, $nextProduct);
    expect(app(AppointmentAvailabilityService::class)->isAvailable($candidate, $date->copy()->setTime(10, 15)))->toBeFalse()
        ->and(app(AppointmentAvailabilityService::class)->isAvailable($candidate, $date->copy()->setTime(10, 30)))->toBeTrue();
});

test('the final booking service rejects a slot taken after the page availability check', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Lou',
        'last_name' => 'Test',
        'email' => 'lou-final-check@example.test',
    ]);
    $attributes = [
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => $date,
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ];

    app(BookingAppointmentCreationService::class)->create(
        $attributes,
        $user,
        $product,
        $date,
        'visio',
        null,
    );

    expect(fn () => app(BookingAppointmentCreationService::class)->create(
        $attributes,
        $user,
        $product,
        $date,
        'visio',
        null,
    ))->toThrow(ValidationException::class)
        ->and(Appointment::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('v2 appointment snapshots remain protective after the pilot switch is disabled', function () {
    $user = bookingV2User(['buffer_time_between_appointments' => 0]);
    $product = bookingV2Product($user, [
        'buffer_time_after_minutes' => 60,
        'confirmation_email_note' => 'Merci de préparer votre document.',
        'reminder_email_note' => 'Pensez à arriver cinq minutes avant.',
    ]);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Noa',
        'last_name' => 'Test',
        'email' => 'noa-v2@example.test',
    ]);

    $existing = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => $date,
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ]);

    expect($existing->buffer_time_after_minutes)->toBe(60)
        ->and($existing->confirmation_email_note)->toBe('Merci de préparer votre document.')
        ->and($existing->reminder_email_note)->toBe('Pensez à arriver cinq minutes avant.');

    config()->set('appointments.booking_v2.enabled', false);
    $candidate = bookingV2Template($user, $product);

    expect(app(AppointmentAvailabilityService::class)->isAvailable($candidate, $date->copy()->setTime(10, 30)))->toBeFalse()
        ->and(app(AppointmentAvailabilityService::class)->isAvailable($candidate, $date->copy()->setTime(11, 0)))->toBeTrue();
});

test('the public slot endpoint rejects a product belonging to another practitioner', function () {
    $user = bookingV2User();
    $other = bookingV2User();
    $foreignProduct = bookingV2Product($other);
    enableBookingV2For($user);

    $this->post(route('appointments.available-slots-patient'), [
        'therapist_id' => $user->id,
        'product_id' => $foreignProduct->id,
        'date' => bookingV2Date()->toDateString(),
        'mode' => 'visio',
    ])->assertStatus(422)
        ->assertJsonPath('slots', []);
});

test('a stale public post cannot book a v2 service that is no longer available online', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user, ['can_be_booked_online' => false]);
    enableBookingV2For($user);

    $this->post(route('appointments.storePatient'), [
        'therapist_id' => $user->id,
        'product_id' => $product->id,
        'first_name' => 'Camille',
        'last_name' => 'Test',
        'email' => 'camille-stale@example.test',
        'phone' => '0612345678',
        'appointment_date' => bookingV2Date()->toDateString(),
        'appointment_time' => bookingV2Date()->format('H:i'),
        'type' => 'visio',
    ])->assertSessionHasErrors('product_id');

    expect(Appointment::query()->where('user_id', $user->id)->exists())->toBeFalse();
});

test('product scheduling and email settings are saved only for an allowlisted practitioner', function () {
    $allowed = bookingV2User();
    enableBookingV2For($allowed);

    $payload = [
        'name' => 'Bilan',
        'description' => 'Premier bilan',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'mode' => 'visio',
        'max_per_day' => null,
        'can_be_booked_online' => 1,
        'collect_payment' => 0,
        'requires_emargement' => 0,
        'visible_in_portal' => 1,
        'price_visible_in_portal' => 1,
        'preparation_time_minutes' => 10,
        'buffer_time_after_minutes' => 20,
        'booking_notes_placeholder' => 'Quel est votre objectif pour ce bilan ?',
        'confirmation_email_note' => 'Préparez votre questionnaire.',
        'reminder_email_note' => 'Merci d’arriver à l’heure.',
    ];

    $this->actingAs($allowed)->post(route('products.store'), $payload)->assertRedirect();
    $stored = Product::query()->where('user_id', $allowed->id)->where('name', 'Bilan')->firstOrFail();
    expect($stored->preparation_time_minutes)->toBe(10)
        ->and($stored->buffer_time_after_minutes)->toBe(20)
        ->and($stored->booking_notes_placeholder)->toBe('Quel est votre objectif pour ce bilan ?')
        ->and($stored->confirmation_email_note)->toBe('Préparez votre questionnaire.');

    $legacy = bookingV2User();
    config()->set('appointments.booking_v2.allowed_user_ids', [$allowed->id]);
    $this->actingAs($legacy)->post(route('products.store'), array_merge($payload, ['name' => 'Bilan legacy']))->assertRedirect();
    $legacyProduct = Product::query()->where('user_id', $legacy->id)->where('name', 'Bilan legacy')->firstOrFail();
    expect($legacyProduct->preparation_time_minutes)->toBeNull()
        ->and($legacyProduct->buffer_time_after_minutes)->toBeNull()
        ->and($legacyProduct->booking_notes_placeholder)->toBeNull()
        ->and($legacyProduct->confirmation_email_note)->toBeNull();
});

test('a disabled information request is rejected before storage or email work', function () {
    $user = bookingV2User([
        'slug' => 'cabinet-pilote-info',
        'information_requests_enabled' => false,
    ]);
    enableBookingV2For($user);

    $this->post(route('therapist.sendInformationRequest', $user->slug), [
        'first_name' => 'Marie',
        'last_name' => 'Test',
        'email' => 'marie@example.test',
        'message' => 'Bonjour',
    ])->assertRedirect()
        ->assertSessionHas('error', 'Ce praticien n’accepte pas de demande d’information pour le moment.');

    expect(InformationRequest::query()->where('therapist_id', $user->id)->exists())->toBeFalse();

    $this->get(route('therapist.show', $user->slug))
        ->assertOk()
        ->assertSee('Ce praticien n’accepte pas de demande d’information pour le moment.')
        ->assertDontSee(route('therapist.sendInformationRequest', $user->slug), false);

    $this->get(route('mobile.therapists.show', $user->slug))
        ->assertOk()
        ->assertDontSee(route('mobile.therapists.information', $user->slug), false);
});

test('the private booking form explains a rejected slot and restores client input', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Déjà',
        'last_name' => 'Réservé',
        'email' => 'existing-private@example.test',
    ]);
    Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => $date,
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ]);
    $bookingLink = BookingLink::create([
        'user_id' => $user->id,
        'token' => 'booking-v2-private-errors',
        'name' => 'Lien pilote',
        'allowed_product_ids' => [$product->id],
        'is_enabled' => true,
        'uses_count' => 0,
    ]);

    $this->from(route('bookingLinks.create', $bookingLink->token))
        ->followingRedirects()
        ->post(route('bookingLinks.store', $bookingLink->token), [
        'therapist_id' => $user->id,
        'product_id' => $product->id,
        'type' => 'visio',
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice-restored@example.test',
        'phone' => '0612345678',
        'appointment_date' => $date->toDateString(),
        'appointment_time' => $date->format('H:i'),
    ])->assertOk()
        ->assertSee('La réservation n’a pas pu être enregistrée.')
        ->assertSee('Le créneau horaire est indisponible ou entre en conflit avec un autre rendez-vous.')
        ->assertSee('alice-restored@example.test')
        ->assertSee('booking-v2-private-errors');

    expect(Appointment::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('the booking v2 migration can be safely rerun after a partial deployment', function () {
    $migration = require database_path('migrations/2026_08_20_090000_add_booking_v2_pilot_fields.php');
    $migration->up();

    expect(Schema::hasColumns('users', [
        'booking_schedule_mode',
        'booking_slot_interval_minutes',
        'information_requests_enabled',
    ]))->toBeTrue()
        ->and(Schema::hasColumns('products', [
            'preparation_time_minutes',
            'buffer_time_after_minutes',
            'booking_notes_placeholder',
            'confirmation_email_note',
            'reminder_email_note',
        ]))->toBeTrue()
        ->and(Schema::hasColumns('appointments', [
            'preparation_time_minutes',
            'buffer_time_after_minutes',
            'confirmation_email_note',
            'reminder_email_note',
        ]))->toBeTrue();
});

test('allowlisted portal service cards link to the ordinary preselected booking flow on desktop and mobile', function () {
    $user = bookingV2User([
        'slug' => 'cabinet-pilote-reservation-directe',
        'company_name' => 'Cabinet Pilote',
    ]);
    $product = bookingV2Product($user, [
        'name' => 'Séance découverte',
        'description' => 'Faire le point sur vos besoins.',
        'visible_in_portal' => true,
    ]);
    enableBookingV2For($user);

    $desktopBookingUrl = route('appointments.createPatient', [
        'therapist' => $user->id,
        'product_id' => $product->id,
    ]);
    $mobileBookingUrl = route('mobile.appointments.create_from_therapist', [
        'slug' => $user->slug,
        'product_id' => $product->id,
    ]);

    $this->get(route('therapist.show', $user->slug))
        ->assertOk()
        ->assertSee('Voir les créneaux')
        ->assertSee($desktopBookingUrl, false);

    $this->get(route('mobile.therapists.show', $user->slug))
        ->assertOk()
        ->assertSee('Voir les créneaux')
        ->assertSee($mobileBookingUrl, false);

    $this->get($desktopBookingUrl)
        ->assertOk()
        ->assertSee('Séance découverte');

    $this->get($mobileBookingUrl)
        ->assertOk()
        ->assertSee('Séance découverte');
});

test('direct booking buttons distinguish same-name prestation variants on desktop and mobile', function () {
    $user = bookingV2User([
        'slug' => 'cabinet-variantes-reservation',
        'company_name' => 'Cabinet Variantes',
    ]);
    bookingV2Product($user, [
        'name' => 'Accompagnement individuel',
        'duration' => 60,
        'price' => 70,
        'visio' => true,
        'dans_le_cabinet' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);
    bookingV2Product($user, [
        'name' => 'Accompagnement individuel',
        'duration' => 90,
        'price' => 95,
        'visio' => false,
        'adomicile' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);
    enableBookingV2For($user);

    foreach ([
        route('therapist.show', $user->slug),
        route('mobile.therapists.show', $user->slug),
    ] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('Visio · 60 min · 70,00 €')
            ->assertSee('À domicile · 90 min · 95,00 €');
    }
});

test('snapshotted product notes appear only in the appropriate escaped client email', function () {
    $user = bookingV2User(['company_name' => 'Cabinet Pilote']);
    $product = bookingV2Product($user, [
        'confirmation_email_note' => 'Confirmation personnalisée <script>alert(1)</script>',
        'reminder_email_note' => 'Rappel personnalisé',
    ]);
    enableBookingV2For($user);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Lina',
        'last_name' => 'Test',
        'email' => 'lina-v2@example.test',
    ]);
    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => bookingV2Date(),
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ]);

    $confirmation = (new AppointmentCreatedPatientMail($appointment))->render();
    $reminder = (new AppointmentReminderClientMail($appointment))->render();

    expect($confirmation)->toContain('Confirmation personnalisée')
        ->and($confirmation)->not->toContain('<script>alert(1)</script>')
        ->and($confirmation)->not->toContain('Rappel personnalisé')
        ->and($reminder)->toContain('Rappel personnalisé')
        ->and($reminder)->not->toContain('Confirmation personnalisée');
});

test('rescheduling a legacy paid appointment under v2 preserves identity payment and visio then snapshots current rules', function () {
    $user = bookingV2User(['booking_schedule_mode' => 'fixed', 'booking_slot_interval_minutes' => 30]);
    $product = bookingV2Product($user, [
        'buffer_time_after_minutes' => 20,
        'preparation_time_minutes' => 10,
    ]);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Ana',
        'last_name' => 'Test',
        'email' => 'ana-v2@example.test',
    ]);

    config()->set('appointments.booking_v2.enabled', false);
    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => $date,
        'duration' => 60,
        'status' => Appointment::STATUS_PAID,
        'stripe_session_id' => 'cs_test_preserved',
        'type' => 'visio',
    ]);
    $meeting = Meeting::create([
        'name' => 'Visio pilote',
        'start_time' => $date,
        'duration' => 60,
        'participant_email' => $client->email,
        'client_profile_id' => $client->id,
        'room_token' => 'room-token-preserved',
        'appointment_id' => $appointment->id,
    ]);
    $originalToken = $appointment->token;

    enableBookingV2For($user);
    $result = app(AppointmentLifecycleService::class)->reschedule(
        $appointment,
        $date->copy()->setTime(11, 0),
        'practitioner',
        $user->id,
        false
    );
    $fresh = $result['appointment']->fresh();

    expect($fresh->id)->toBe($appointment->id)
        ->and($fresh->token)->toBe($originalToken)
        ->and($fresh->stripe_session_id)->toBe('cs_test_preserved')
        ->and($fresh->canonicalStatus())->toBe(Appointment::STATUS_PAID)
        ->and($fresh->preparation_time_minutes)->toBe(10)
        ->and($fresh->buffer_time_after_minutes)->toBe(20)
        ->and($meeting->fresh()->room_token)->toBe('room-token-preserved')
        ->and(Meeting::query()->where('appointment_id', $appointment->id)->count())->toBe(1);
});

test('cancelling a v2 appointment releases its duration and protected buffers', function () {
    $user = bookingV2User();
    $product = bookingV2Product($user, ['buffer_time_after_minutes' => 30]);
    $date = bookingV2Date();
    bookingV2Availability($user, $date);
    enableBookingV2For($user);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Eli',
        'last_name' => 'Test',
        'email' => 'eli-v2@example.test',
    ]);
    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => $date,
        'duration' => 60,
        'status' => Appointment::STATUS_CONFIRMED,
        'type' => 'visio',
    ]);
    $candidate = bookingV2Template($user, $product);

    expect(app(AppointmentAvailabilityService::class)->isAvailable($candidate, $date->copy()->setTime(10, 0)))->toBeFalse();

    app(AppointmentLifecycleService::class)->cancel(
        $appointment,
        'practitioner',
        $user->id,
        null,
        false
    );

    expect($appointment->fresh()->isCancelled())->toBeTrue()
        ->and(app(AppointmentAvailabilityService::class)->isAvailable($candidate, $date->copy()->setTime(10, 0)))->toBeTrue();
});
