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
