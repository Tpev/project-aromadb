<?php

use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\PackProduct;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('therapist can create a quote line from a pack selection payload', function () {
    $therapist = User::factory()->create(['is_therapist' => true]);

    $client = ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Pack',
        'email' => 'quote-pack-' . uniqid() . '@example.test',
    ]);

    $pack = PackProduct::create([
        'user_id' => $therapist->id,
        'name' => 'Pack confort',
        'description' => 'Pack de test',
        'price' => 120.00,
        'tax_rate' => 20.00,
        'is_active' => true,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
    ]);

    $response = $this->actingAs($therapist)->post(route('invoices.storeQuote'), [
        'client_profile_id' => $client->id,
        'quote_date' => now()->toDateString(),
        'valid_until' => now()->addDays(15)->toDateString(),
        'items' => [[
            'type' => 'custom',
            'product_id' => null,
            'inventory_item_id' => null,
            'label' => 'Pack : ' . $pack->name,
            'description' => '',
            'quantity' => 1,
            'unit_price' => $pack->price,
            'tax_rate' => $pack->tax_rate,
        ]],
    ]);

    $response->assertSessionHasNoErrors();

    $quote = Invoice::query()->where('type', 'quote')->firstOrFail();

    $response->assertRedirect(route('invoices.showQuote', $quote));

    expect($quote->items)->toHaveCount(1);
    expect($quote->items->first()->label)->toBe('Pack : Pack confort');
    expect((float) $quote->total_amount)->toBe(120.0);
    expect((float) $quote->total_tax_amount)->toBe(24.0);
    expect((float) $quote->total_amount_with_tax)->toBe(144.0);

    $this->actingAs($therapist)
        ->get(route('invoices.showQuote', $quote))
        ->assertOk()
        ->assertSee('Pack : Pack confort');
});
