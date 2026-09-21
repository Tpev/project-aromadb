<?php

use App\Mail\AppointmentCancelledByClient;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\BookingLink;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

beforeEach(function () {
    Mail::fake();
    Queue::fake();
    config()->set('appointments.booking_v2.enabled', false);
    $this->practitioner = User::factory()->create([
        'is_therapist' => true, 'license_status' => 'active', 'accept_online_appointments' => true,
        'minimum_notice_hours' => 0, 'buffer_time_between_appointments' => 0,
        'slug' => 'booking-preferences',
    ]);
    $this->service = Product::create([
        'user_id' => $this->practitioner->id, 'name' => 'Consultation', 'price' => 60,
        'duration' => 60, 'tax_rate' => 0, 'can_be_booked_online' => true,
        'collect_payment' => false, 'visio' => true, 'dans_le_cabinet' => false,
        'adomicile' => false, 'en_entreprise' => false,
    ]);
    $this->client = ClientProfile::create([
        'user_id' => $this->practitioner->id, 'first_name' => 'Camille', 'last_name' => 'Test',
        'email' => 'client@example.test',
    ]);
    $this->date = now()->addWeek()->startOfDay();
    Availability::create([
        'user_id' => $this->practitioner->id, 'day_of_week' => $this->date->dayOfWeekIso - 1,
        'start_time' => '09:00:00', 'end_time' => '18:00:00', 'applies_to_all' => true,
    ]);
    $this->link = BookingLink::create([
        'user_id' => $this->practitioner->id, 'token' => 'phone-setting-partner',
        'name' => 'Partenaire', 'allowed_product_ids' => [$this->service->id], 'is_enabled' => true,
    ]);
    $this->payload = [
        'therapist_id' => $this->practitioner->id, 'product_id' => $this->service->id,
        'first_name' => 'Camille', 'last_name' => 'Test', 'email' => $this->client->email,
        'appointment_date' => $this->date->toDateString(), 'appointment_time' => '10:00', 'type' => 'visio',
    ];
});

function preferencesBookingUrl(string $channel, BookingLink $link): string
{
    return match ($channel) {
        'partner' => route('bookingLinks.store', $link->token),
        'mobile' => route('mobile.appointments.store'),
        default => route('appointments.storePatient'),
    };
}

test('phone is optional by default in every client booking channel', function (string $channel) {
    expect($this->practitioner->fresh()->booking_phone_required)->toBeFalse();
    $this->post(preferencesBookingUrl($channel, $this->link), $this->payload)
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('appointments', 1);
})->with(['public', 'partner', 'mobile']);

test('therapists can enable and disable the phone requirement in either settings form', function (string $channel) {
    $this->actingAs($this->practitioner);
    $route = $channel === 'mobile' ? 'mobile.profile.update' : 'profile.updateCompanyInfo';
    foreach ([1, 0] as $required) {
        $this->put(route($route), [
            'name' => $this->practitioner->name, 'email' => $this->practitioner->email,
            'booking_phone_required' => $required,
        ])->assertRedirect()->assertSessionHasNoErrors();
        expect($this->practitioner->fresh()->booking_phone_required)->toBe((bool) $required);
    }
})->with(['desktop', 'mobile']);

test('enabled phone requirement rejects empty input in every client booking channel', function (string $channel) {
    $this->practitioner->update(['booking_phone_required' => true]);
    $this->post(preferencesBookingUrl($channel, $this->link), $this->payload + ['phone' => '   '])
        ->assertSessionHasErrors('phone');
    $this->assertDatabaseCount('appointments', 0);
})->with(['public', 'partner', 'mobile']);

test('international phone input is accepted when required', function (string $channel) {
    $this->practitioner->update(['booking_phone_required' => true]);
    $this->post(preferencesBookingUrl($channel, $this->link), $this->payload + ['phone' => '+33 6 12 34 56 78'])
        ->assertRedirect()->assertSessionHasNoErrors();
    $this->assertDatabaseCount('appointments', 1);
})->with(['public', 'partner', 'mobile']);

test('malformed therapist ids return validation errors instead of server errors', function (string $channel) {
    $payload = array_replace($this->payload, ['therapist_id' => [$this->practitioner->id]]);
    $this->post(preferencesBookingUrl($channel, $this->link), $payload)->assertSessionHasErrors('therapist_id');
    $this->assertDatabaseCount('appointments', 0);
})->with(['public', 'mobile']);

test('public booking guidance and phone requirement render on desktop mobile and partner forms', function () {
    foreach ([false, true] as $required) {
        $this->practitioner->update(['booking_phone_required' => $required]);
        foreach ([route('appointments.createPatient', $this->practitioner), '/mobile/therapeute/'.$this->practitioner->slug.'/prendre-rdv', route('bookingLinks.create', $this->link->token)] as $url) {
            $response = $this->get($url)->assertOk()->assertSee('pour voir les créneaux disponibles.');
            $document = new DOMDocument;
            @$document->loadHTML($response->getContent());
            $phone = (new DOMXPath($document))->query('//input[@name="phone"]')->item(0);
            expect($phone->hasAttribute('required'))->toBe($required);
        }
    }
});

test('client cancellation requires a nonblank reason from token and portal', function (string $channel, string $reason) {
    $appointment = Appointment::create([
        'user_id' => $this->practitioner->id, 'client_profile_id' => $this->client->id,
        'product_id' => $this->service->id, 'appointment_date' => $this->date->copy()->setTime(10, 0),
        'duration' => 60, 'type' => 'visio', 'status' => 'confirmed',
    ]);
    if ($channel === 'portal') {
        $this->actingAs($this->client, 'client');
    }
    $url = $channel === 'portal' ? route('client.appointments.cancel', $appointment) : route('appointment.confirmation.cancel', $appointment->token);
    $this->post($url, ['cancellation_reason' => $reason])->assertSessionHasErrors('cancellation_reason');
    expect($appointment->fresh()->isCancelled())->toBeFalse();
    Mail::assertNothingQueued();
})->with(['token', 'portal'])->with(['', '   ', str_repeat('a', 501)]);

test('a cancellation reason is saved and visible to the practitioner in the email and appointment', function (string $channel) {
    $appointment = Appointment::create([
        'user_id' => $this->practitioner->id, 'client_profile_id' => $this->client->id,
        'product_id' => $this->service->id, 'appointment_date' => $this->date->copy()->setTime(10, 0),
        'duration' => 60, 'type' => 'visio', 'status' => 'confirmed',
    ]);
    if ($channel === 'portal') {
        $this->actingAs($this->client, 'client');
    }
    $url = $channel === 'portal' ? route('client.appointments.cancel', $appointment) : route('appointment.confirmation.cancel', $appointment->token);
    $this->post($url, ['cancellation_reason' => '  Empêchement professionnel  '])
        ->assertRedirect()->assertSessionHasNoErrors();
    expect($appointment->fresh()->cancellation_reason)->toBe('Empêchement professionnel');
    Mail::assertQueued(AppointmentCancelledByClient::class, fn ($mail) => str_contains($mail->render(), 'Empêchement professionnel'));
    $this->actingAs($this->practitioner)->get(route('appointments.show', $appointment))->assertOk()->assertSee('Empêchement professionnel');
    $this->get(route('mobile.appointments.show', $appointment))->assertOk()->assertSee('Empêchement professionnel');
})->with(['token', 'portal']);
