<?php

use App\Mail\AppointmentCreatedTherapistMail;
use App\Models\Appointment;
use App\Models\Availability;
use App\Models\BookingLink;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

function bookingNotesProduct(User $therapist): Product
{
    return Product::create([
        'user_id' => $therapist->id,
        'name' => 'Séance découverte',
        'description' => 'Séance de test',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
    ]);
}

function bookingNotesLink(User $therapist, Product $product, string $token): BookingLink
{
    return BookingLink::create([
        'user_id' => $therapist->id,
        'token' => $token,
        'name' => 'Lien partenaire',
        'allowed_product_ids' => [$product->id],
        'is_enabled' => true,
        'uses_count' => 0,
    ]);
}

test('legacy practitioners use the existing booking notes placeholder in both public forms', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'booking_notes_placeholder' => null,
    ]);
    $product = bookingNotesProduct($therapist);
    $bookingLink = bookingNotesLink($therapist, $product, 'booking-notes-default');

    foreach ([
        route('appointments.createPatient', $therapist),
        route('bookingLinks.create', $bookingLink->token),
    ] as $url) {
        $this->get($url)
            ->assertOk()
            ->assertSee('name="notes"', false)
            ->assertSee('placeholder="'.User::DEFAULT_BOOKING_NOTES_PLACEHOLDER.'"', false)
            ->assertSee('Informations complémentaires (facultatif)');
    }
});

test('each practitioner booking form uses only their customized placeholder', function () {
    $firstTherapist = User::factory()->create([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'booking_notes_placeholder' => 'Quel est votre objectif pour cette séance ?',
    ]);
    $firstProduct = bookingNotesProduct($firstTherapist);
    $firstLink = bookingNotesLink($firstTherapist, $firstProduct, 'booking-notes-first');

    $secondTherapist = User::factory()->create([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'booking_notes_placeholder' => 'Avez-vous une information pratique à partager ?',
    ]);
    bookingNotesProduct($secondTherapist);

    $this->get(route('appointments.createPatient', $firstTherapist))
        ->assertOk()
        ->assertSee('placeholder="Quel est votre objectif pour cette séance ?"', false)
        ->assertDontSee('Avez-vous une information pratique à partager ?');

    $this->get(route('bookingLinks.create', $firstLink->token))
        ->assertOk()
        ->assertSee('placeholder="Quel est votre objectif pour cette séance ?"', false)
        ->assertDontSee('Avez-vous une information pratique à partager ?');
});

test('practitioners can customize or restore the default booking notes placeholder', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'booking_notes_placeholder' => null,
    ]);

    $this->actingAs($therapist)
        ->put(route('profile.updateCompanyInfo'), [
            'booking_notes_placeholder' => '  Que souhaitez-vous préparer pendant cette séance ?  ',
        ])
        ->assertRedirect(route('profile.editCompanyInfo'));

    expect($therapist->fresh()->booking_notes_placeholder)
        ->toBe('Que souhaitez-vous préparer pendant cette séance ?')
        ->and($therapist->fresh()->resolvedBookingNotesPlaceholder())
        ->toBe('Que souhaitez-vous préparer pendant cette séance ?');

    $this->actingAs($therapist)
        ->put(route('profile.updateCompanyInfo'), [
            'booking_notes_placeholder' => '   ',
        ])
        ->assertRedirect(route('profile.editCompanyInfo'));

    expect($therapist->fresh()->booking_notes_placeholder)->toBeNull()
        ->and($therapist->fresh()->resolvedBookingNotesPlaceholder())
        ->toBe(User::DEFAULT_BOOKING_NOTES_PLACEHOLDER);
});

test('company information tabs render with a valid alpine state', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $this->actingAs($therapist)
        ->get(route('profile.editCompanyInfo'))
        ->assertOk()
        ->assertSee('x-data="{ activeTab:', false)
        ->assertDontSee("x-data='{ activeTab:", false);
});

test('appointment information is visible to the practitioner on desktop mobile and email', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);
    $product = bookingNotesProduct($therapist);
    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'email' => 'camille-booking-notes@example.test',
        'phone' => '0612345678',
        'notes' => 'Note permanente du dossier client',
    ]);
    $appointment = Appointment::create([
        'user_id' => $therapist->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->addWeek()->setTime(10, 0),
        'duration' => 60,
        'type' => 'visio',
        'status' => Appointment::STATUS_CONFIRMED,
        'notes' => "Je souhaite parler de mon objectif.\nMerci.",
    ]);

    $this->actingAs($therapist)
        ->get(route('appointments.show', $appointment))
        ->assertOk()
        ->assertSee('Informations complémentaires au rendez-vous')
        ->assertSee('Je souhaite parler de mon objectif.');

    $this->actingAs($therapist)
        ->get(route('mobile.appointments.show', $appointment))
        ->assertOk()
        ->assertSee('Informations complémentaires au rendez-vous')
        ->assertSee('Je souhaite parler de mon objectif.');

    $email = (new AppointmentCreatedTherapistMail($appointment))->render();

    expect($email)
        ->toContain('Informations complémentaires')
        ->toContain('Je souhaite parler de mon objectif.')
        ->not->toContain('Note permanente du dossier client');
});

test('normal and partner booking messages stay attached to their appointment', function () {
    Mail::fake();

    $therapist = User::factory()->create([
        'is_therapist' => true,
        'accept_online_appointments' => true,
        'minimum_notice_hours' => 0,
        'buffer_time_between_appointments' => 0,
    ]);
    $product = bookingNotesProduct($therapist);
    $bookingLink = bookingNotesLink($therapist, $product, 'booking-notes-storage');
    $date = now()->addDays(8)->setTime(10, 0)->startOfMinute();

    Availability::create([
        'user_id' => $therapist->id,
        'day_of_week' => $date->dayOfWeekIso - 1,
        'start_time' => '09:00:00',
        'end_time' => '14:00:00',
        'applies_to_all' => true,
    ]);

    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'email' => 'camille-booking-storage@example.test',
        'phone' => '0612345678',
        'notes' => 'Note permanente à préserver',
    ]);

    $commonPayload = [
        'therapist_id' => $therapist->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'email' => $client->email,
        'phone' => $client->phone,
        'appointment_date' => $date->toDateString(),
        'product_id' => $product->id,
        'type' => 'visio',
    ];

    $normalResponse = $this->post(route('appointments.storePatient'), array_merge($commonPayload, [
        'appointment_time' => '10:00',
        'notes' => 'Message depuis la réservation publique',
    ]));
    $normalResponse->assertSessionHasNoErrors();

    $partnerResponse = $this->post(route('bookingLinks.store', $bookingLink->token), array_merge($commonPayload, [
        'first_name' => 'Alex',
        'last_name' => 'Durand',
        'email' => 'alex-partner-booking@example.test',
        'phone' => '0698765432',
        'appointment_time' => '12:00',
        'notes' => 'Message depuis le lien partenaire',
    ]));
    $partnerResponse->assertSessionHasNoErrors();

    $partnerClient = ClientProfile::where('user_id', $therapist->id)
        ->where('email', 'alex-partner-booking@example.test')
        ->firstOrFail();

    $this->assertDatabaseHas('appointments', [
        'user_id' => $therapist->id,
        'client_profile_id' => $client->id,
        'notes' => 'Message depuis la réservation publique',
    ]);
    $this->assertDatabaseHas('appointments', [
        'user_id' => $therapist->id,
        'client_profile_id' => $partnerClient->id,
        'booking_link_id' => $bookingLink->id,
        'notes' => 'Message depuis le lien partenaire',
    ]);

    expect($client->fresh()->notes)->toBe('Note permanente à préserver')
        ->and($partnerClient->notes)->toBeNull();
});
