<?php

use App\Models\Appointment;
use App\Models\Availability;
use App\Models\BookingLink;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use App\Services\BookingAppointmentCreationService;
use App\Services\ClientBookingBlockService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();
    Notification::fake();
    Queue::fake();
    config()->set('appointments.booking_v2.enabled', false);
    $this->practitioner = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'license_product' => 'new_pro_mensuelle',
        'accept_online_appointments' => true, 'minimum_notice_hours' => 0,
        'buffer_time_between_appointments' => 0, 'slug' => 'booking-block-test',
    ]);
    $this->service = Product::create([
        'user_id' => $this->practitioner->id, 'name' => 'Consultation', 'price' => 60,
        'duration' => 60, 'tax_rate' => 0, 'can_be_booked_online' => true,
        'collect_payment' => false, 'visio' => true, 'dans_le_cabinet' => false,
        'adomicile' => false, 'en_entreprise' => false,
    ]);
    $this->client = ClientProfile::create([
        'user_id' => $this->practitioner->id, 'first_name' => 'Jane', 'last_name' => 'Doe',
        'email' => 'jane@example.test',
    ]);
    $this->date = now()->addWeek()->startOfDay();
    Availability::create([
        'user_id' => $this->practitioner->id, 'day_of_week' => $this->date->dayOfWeekIso - 1,
        'start_time' => '09:00:00', 'end_time' => '18:00:00', 'applies_to_all' => true,
    ]);
    $this->link = BookingLink::create([
        'user_id' => $this->practitioner->id, 'token' => 'booking-block-partner',
        'name' => 'Partenaire', 'allowed_product_ids' => [$this->service->id], 'is_enabled' => true,
    ]);
    $this->payload = [
        'therapist_id' => $this->practitioner->id, 'product_id' => $this->service->id,
        'first_name' => 'Jane', 'last_name' => 'Doe', 'email' => $this->client->email,
        'appointment_date' => $this->date->toDateString(), 'appointment_time' => '10:00', 'type' => 'visio',
    ];
    $this->blocks = app(ClientBookingBlockService::class);
});

function blockedBookingUrl(string $channel, BookingLink $link): string
{
    return match ($channel) {
        'partner' => route('bookingLinks.store', $link->token),
        'mobile' => route('mobile.appointments.store'),
        default => route('appointments.storePatient'),
    };
}

test('therapists can block and unblock from either client profile without modifying existing records', function (string $channel) {
    $route = $channel === 'mobile' ? 'mobile.clients.booking-block' : 'client_profiles.booking-block';
    $profileRoute = $channel === 'mobile' ? 'mobile.clients.show' : 'client_profiles.show';
    $appointment = Appointment::create([
        'user_id' => $this->practitioner->id, 'client_profile_id' => $this->client->id,
        'product_id' => $this->service->id, 'appointment_date' => $this->date->copy()->setTime(10, 0),
        'duration' => 60, 'type' => 'visio', 'status' => 'confirmed',
    ]);
    $clientBefore = $this->client->fresh()->getAttributes();
    $appointmentBefore = $appointment->fresh()->getAttributes();

    $this->actingAs($this->practitioner)->get(route($profileRoute, $this->client))
        ->assertOk()->assertSee('Bloquer ce client')->assertViewHas('bookingEmailBlocked', false);
    foreach ([1, 1, 0, 0] as $blocked) {
        $this->put(route($route, $this->client), ['blocked' => $blocked])
            ->assertRedirect(route($profileRoute, $this->client))->assertSessionHasNoErrors();
        $this->assertDatabaseCount('client_booking_blocks', $blocked);
        $this->get(route($profileRoute, $this->client))->assertOk()
            ->assertViewHas('bookingEmailBlocked', (bool) $blocked)
            ->assertSee($blocked ? 'Débloquer ce client' : 'Bloquer ce client');
    }
    expect($this->client->fresh()->getAttributes())->toBe($clientBefore)
        ->and($appointment->fresh()->getAttributes())->toBe($appointmentBefore);
    Mail::assertNothingSent();
    Mail::assertNothingQueued();
    Notification::assertNothingSent();
})->with(['desktop', 'mobile']);

test('only the owning therapist can change a block and request email cannot change its target', function (string $channel) {
    $route = $channel === 'mobile' ? 'mobile.clients.booking-block' : 'client_profiles.booking-block';
    $url = route($route, $this->client);
    $this->put($url, ['blocked' => 1])->assertRedirect();
    $other = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $this->actingAs($other)->put($url, ['blocked' => 1])->assertForbidden();
    $this->assertDatabaseCount('client_booking_blocks', 0);

    $this->actingAs($this->practitioner)->put($url, [
        'blocked' => 1, 'email' => 'someone-else@example.test', 'user_id' => $other->id,
    ])->assertSessionHasNoErrors();
    $this->assertDatabaseHas('client_booking_blocks', ['user_id' => $this->practitioner->id, 'email' => $this->client->email]);
    $this->actingAs($other)->put($url, ['blocked' => 0])->assertForbidden();
    $this->assertDatabaseCount('client_booking_blocks', 1);
})->with(['desktop', 'mobile']);

test('missing or invalid email and malformed block requests cannot create a block', function (?string $email) {
    $this->client->update(['email' => $email]);
    $this->actingAs($this->practitioner)->put(route('client_profiles.booking-block', $this->client), ['blocked' => 1])
        ->assertSessionHasErrors('booking_block');
    $this->put(route('client_profiles.booking-block', $this->client), ['blocked' => [1]])
        ->assertSessionHasErrors('blocked');
    $this->assertDatabaseCount('client_booking_blocks', 0);
})->with([null, '', '   ', 'invalid-address']);

test('profiles without email still render without an actionable block button', function (string $route) {
    $this->client->update(['email' => null]);
    $this->actingAs($this->practitioner)->get(route($route, $this->client))->assertOk()
        ->assertSee('Ajoutez une adresse email')->assertDontSee('Bloquer ce client');
})->with(['client_profiles.show', 'mobile.clients.show']);

test('duplicate client profiles share the same email block and can unblock it', function () {
    $this->client->update(['email' => '  JANE@Example.Test  ']);
    $duplicate = ClientProfile::create([
        'user_id' => $this->practitioner->id, 'first_name' => 'Autre prénom', 'last_name' => 'Doublon',
        'email' => 'jane@example.test',
    ]);
    $this->actingAs($this->practitioner)->put(route('client_profiles.booking-block', $this->client), ['blocked' => 1])
        ->assertSessionHasNoErrors();
    $this->assertDatabaseHas('client_booking_blocks', ['user_id' => $this->practitioner->id, 'email' => 'jane@example.test']);
    $this->get(route('client_profiles.show', $duplicate))->assertOk()->assertViewHas('bookingEmailBlocked', true);
    $this->put(route('mobile.clients.booking-block', $duplicate), ['blocked' => 0])->assertSessionHasNoErrors();
    $this->assertDatabaseCount('client_booking_blocks', 0);
});

test('all public booking submissions refuse blocked emails before any appointment or payment work', function (string $channel, bool $v2) {
    config()->set('appointments.booking_v2.enabled', $v2);
    $this->service->update(['collect_payment' => true]);
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, true);
    $this->mock(BookingAppointmentCreationService::class)->shouldNotReceive('create');
    $before = $this->client->fresh()->getAttributes();
    $payload = array_replace($this->payload, [
        'email' => '  JANE@EXAMPLE.TEST  ', 'first_name' => 'Different', 'last_name' => 'Name',
        'gift_voucher_code' => 'invalid-voucher-must-not-be-processed',
    ]);
    $this->post(blockedBookingUrl($channel, $this->link), $payload)
        ->assertRedirect()->assertSessionHasErrors(['booking' => ClientBookingBlockService::REFUSAL_MESSAGE]);
    foreach (['appointments', 'meetings', 'invoices'] as $table) {
        $this->assertDatabaseCount($table, 0);
    }
    $this->assertDatabaseCount('client_profiles', 1);
    expect($this->client->fresh()->getAttributes())->toBe($before)
        ->and($this->link->fresh()->uses_count)->toBe(0);
    Mail::assertNothingSent();
    Mail::assertNothingQueued();
    Notification::assertNothingSent();
    Queue::assertNothingPushed();
})->with(['public', 'partner', 'mobile'])->with([false, true]);

test('booking forms display only the neutral refusal message after submission', function (string $channel) {
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, true);
    $form = match ($channel) {
        'partner' => route('bookingLinks.create', $this->link->token),
        'mobile' => '/mobile/therapeute/'.$this->practitioner->slug.'/prendre-rdv',
        default => route('appointments.createPatient', $this->practitioner),
    };
    $this->get($form)->assertOk()->assertDontSee(ClientBookingBlockService::REFUSAL_MESSAGE);
    $this->from($form)->post(blockedBookingUrl($channel, $this->link), $this->payload)->assertRedirect($form);
    $this->get($form)->assertOk()->assertSee(ClientBookingBlockService::REFUSAL_MESSAGE)
        ->assertDontSee('Réservations bloquées')->assertDontSee('blacklist');
})->with(['public', 'partner', 'mobile']);

test('the same email can book with another therapist and unblocking restores booking', function (string $channel) {
    $other = User::factory()->create(['is_therapist' => true]);
    $this->blocks->setBlocked($other->id, $this->client->email, true);
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, true);
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, false);
    $this->post(blockedBookingUrl($channel, $this->link), $this->payload)
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseHas('appointments', ['user_id' => $this->practitioner->id, 'client_profile_id' => $this->client->id]);
    expect($this->blocks->isBlocked($other->id, $this->client->email))->toBeTrue();
})->with(['public', 'partner', 'mobile']);

test('other emails and legacy mobile bookings without email are unaffected', function (?string $email) {
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, true);
    $this->post(route('mobile.appointments.store'), array_replace($this->payload, ['email' => $email]))
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('appointments', 1);
})->with([null, 'jane+other@example.test']);

test('a partner token uses its own therapist when submitted therapist id is forged', function () {
    $other = User::factory()->create(['is_therapist' => true]);
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, true);
    $this->post(route('bookingLinks.store', $this->link->token), array_replace($this->payload, ['therapist_id' => $other->id]))
        ->assertSessionHasErrors(['booking' => ClientBookingBlockService::REFUSAL_MESSAGE]);
    $this->assertDatabaseCount('appointments', 0);
});

test('therapists can still create an appointment manually for a blocked email', function (string $route) {
    $this->blocks->setBlocked($this->practitioner->id, $this->client->email, true);
    $this->actingAs($this->practitioner)->post(route($route), $this->payload + [
        'client_profile_id' => $this->client->id, 'status' => 'confirmed',
    ])->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('appointments', 1);
    expect(DB::table('client_booking_blocks')->count())->toBe(1);
})->with(['appointments.store', 'mobile.appointments.store_practitioner']);
