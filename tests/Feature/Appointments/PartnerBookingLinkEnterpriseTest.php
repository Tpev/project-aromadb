<?php

use App\Models\BookingLink;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('partner booking link exposes entreprise mode in the booking catalog', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'slug' => 'therapist-link-test',
        'visible_annuarire_admin_set' => true,
    ]);

    $product = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Gameplay 1',
        'description' => 'Session en entreprise',
        'price' => 90,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => false,
        'en_entreprise' => true,
        'dans_le_cabinet' => false,
    ]);

    $bookingLink = BookingLink::create([
        'user_id' => $therapist->id,
        'token' => 'partner-enterprise-token',
        'name' => 'Lien partenaire test',
        'allowed_product_ids' => [$product->id],
        'is_enabled' => true,
        'uses_count' => 0,
    ]);

    $response = $this->get(route('bookingLinks.create', ['token' => $bookingLink->token]));

    $response->assertOk();
    $response->assertSee('"en_entreprise":true', false);
});

test('normal and partner bookings require an email address and phone number', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'accept_online_appointments' => true,
    ]);

    $product = Product::create([
        'user_id' => $therapist->id,
        'name' => 'Consultation test',
        'description' => 'Consultation au cabinet',
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

    $bookingLink = BookingLink::create([
        'user_id' => $therapist->id,
        'token' => 'required-contact-token',
        'name' => 'Lien avec coordonnées obligatoires',
        'allowed_product_ids' => [$product->id],
        'is_enabled' => true,
        'uses_count' => 0,
    ]);

    $bookingData = [
        'therapist_id' => $therapist->id,
        'first_name' => 'Camille',
        'last_name' => 'Martin',
        'appointment_date' => now()->addWeek()->format('Y-m-d'),
        'appointment_time' => '10:00',
        'product_id' => $product->id,
        'type' => 'visio',
    ];

    $this->post(route('appointments.storePatient'), $bookingData)
        ->assertSessionHasErrors(['email', 'phone']);

    $this->post(route('bookingLinks.store', $bookingLink->token), $bookingData)
        ->assertSessionHasErrors(['email', 'phone']);

    $partnerForm = $this->get(route('bookingLinks.create', $bookingLink->token));

    expect($partnerForm->getContent())
        ->toMatch('/<input[^>]+name="email"[^>]+required>/')
        ->toMatch('/<input[^>]+name="phone"[^>]+required>/');
});
