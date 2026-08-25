<?php

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('duplicating a Stripe-backed prestation does not copy unique Stripe identifiers', function () {
    $practitioner = User::factory()->create([
        'is_therapist' => true,
    ]);

    config()->set('appointments.booking_v2.enabled', true);
    config()->set('appointments.booking_v2.allowed_user_ids', [$practitioner->id]);

    $source = Product::create([
        'user_id' => $practitioner->id,
        'name' => 'Prestation Stripe',
        'description' => 'Prestation source',
        'price' => 75,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => true,
        'visio' => true,
        'adomicile' => false,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'stripe_product_id' => 'prod_source_unique',
        'stripe_price_id' => 'price_source_unique',
        'preparation_time_minutes' => 10,
        'buffer_time_after_minutes' => 15,
        'confirmation_email_note' => 'Message de confirmation',
        'reminder_email_note' => 'Message de rappel',
    ]);

    $response = $this->actingAs($practitioner)->post(
        route('products.storeDuplicate', $source),
        [
            'name' => 'Prestation Stripe copiée',
            'description' => $source->description,
            'price' => $source->price,
            'tax_rate' => $source->tax_rate,
            'duration' => $source->duration,
            'mode' => 'visio',
            'can_be_booked_online' => 1,
            'collect_payment' => 1,
            'requires_emargement' => 0,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'direct_booking_enabled' => 0,
            'preparation_time_minutes' => 10,
            'buffer_time_after_minutes' => 15,
            'confirmation_email_note' => 'Message de confirmation',
            'reminder_email_note' => 'Message de rappel',
        ]
    );

    $duplicate = Product::query()
        ->where('user_id', $practitioner->id)
        ->whereKeyNot($source->id)
        ->sole();

    $response->assertRedirect(route('products.show', $duplicate));

    expect($duplicate->stripe_product_id)->toBeNull()
        ->and($duplicate->stripe_price_id)->toBeNull()
        ->and($duplicate->collect_payment)->toBeTrue()
        ->and($duplicate->preparation_time_minutes)->toBe(10)
        ->and($duplicate->buffer_time_after_minutes)->toBe(15)
        ->and($duplicate->confirmation_email_note)->toBe('Message de confirmation')
        ->and($duplicate->reminder_email_note)->toBe('Message de rappel');
});
