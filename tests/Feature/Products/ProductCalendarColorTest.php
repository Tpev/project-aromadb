<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\Unavailability;
use App\Models\User;
use App\Support\ProductCalendarColor;

function colorProductPayload(array $overrides = []): array
{
    return array_replace([
        'name' => 'Séance couleurs', 'description' => 'Prestation de test',
        'price' => 70, 'tax_rate' => 0, 'duration' => 60, 'mode' => 'dans_le_cabinet',
        'can_be_booked_online' => 0, 'collect_payment' => 0, 'requires_emargement' => 0,
        'visible_in_portal' => 1, 'price_visible_in_portal' => 1,
    ], $overrides);
}

function colorTherapist(): User
{
    return User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
}

function coloredProduct(User $user, ?string $color = '#2563EB'): Product
{
    return Product::create([
        'user_id' => $user->id, 'name' => 'Prestation '.$color,
        'calendar_color' => $color, 'price' => 70, 'tax_rate' => 0, 'duration' => 60,
    ]);
}

function colorAppointment(User $user, ?Product $product, array $attributes = []): Appointment
{
    $client = ClientProfile::create([
        'user_id' => $user->id, 'first_name' => 'Alice', 'last_name' => 'Couleur', 'email' => 'alice@example.test',
    ]);

    return Appointment::create(array_merge([
        'user_id' => $user->id, 'client_profile_id' => $client->id, 'product_id' => $product?->id,
        'appointment_date' => now()->addDay(), 'duration' => 60, 'status' => 'confirmed', 'type' => 'cabinet',
    ], $attributes));
}

test('both editors save normalize change and clear service colors while old forms preserve them', function (string $routes) {
    $user = colorTherapist();
    $this->actingAs($user)->get(route($routes.'.create'))->assertOk()->assertSee('Couleur dans l’agenda');
    $this->post(route($routes.'.store'), colorProductPayload(['calendar_color' => '#7c3aed']))
        ->assertSessionHasNoErrors()->assertRedirect();
    $product = Product::sole();
    expect($product->calendar_color)->toBe('#7C3AED');

    $this->get(route($routes.'.edit', $product))->assertOk()->assertSee('#7C3AED');
    $this->put(route($routes.'.update', $product), colorProductPayload(['name' => 'Ancien formulaire']))->assertSessionHasNoErrors();
    expect($product->fresh()->calendar_color)->toBe('#7C3AED');
    $this->put(route($routes.'.update', $product), colorProductPayload(['calendar_color' => '#fde68a']))->assertSessionHasNoErrors();
    expect($product->fresh()->calendar_color)->toBe('#FDE68A');
    $this->put(route($routes.'.update', $product), colorProductPayload(['calendar_color' => '']))->assertSessionHasNoErrors();
    expect($product->fresh()->calendar_color)->toBeNull();
})->with(['products', 'mobile.products']);

test('legacy create submissions need no color', function (string $routes) {
    $this->actingAs(colorTherapist())->post(route($routes.'.store'), colorProductPayload())->assertSessionHasNoErrors();
    expect(Product::sole()->calendar_color)->toBeNull();
})->with(['products', 'mobile.products']);

test('invalid color input cannot be stored and validation pages still render', function (string $routes, mixed $color) {
    $user = colorTherapist();
    $product = coloredProduct($user);
    $this->actingAs($user)->from(route($routes.'.edit', $product))
        ->put(route($routes.'.update', $product), colorProductPayload(['calendar_color' => $color]))
        ->assertSessionHasErrors('calendar_color');
    expect($product->fresh()->calendar_color)->toBe('#2563EB');
    $this->get(route($routes.'.edit', $product))->assertOk()->assertSee('Couleur dans l’agenda');
})->with(['products', 'mobile.products'])->with([
    'CSS injection' => ['#123456;background:url(https://example.test)'],
    'HTML injection' => ['"><script>alert(1)</script>'],
    'short hex' => ['#ABC'],
    'missing hash' => ['2563EB'],
    'array' => [['#2563EB']],
    'number' => [123456],
]);

test('therapists cannot edit or recolor another therapists service', function (string $routes) {
    $owner = colorTherapist();
    $product = coloredProduct($owner);
    $this->actingAs(colorTherapist())->get(route($routes.'.edit', $product))->assertForbidden();
    $this->put(route($routes.'.update', $product), colorProductPayload(['calendar_color' => '#000000']))->assertForbidden();
    expect($product->fresh()->calendar_color)->toBe('#2563EB');
})->with(['products', 'mobile.products']);

test('duplicate forms can retain replace or clear a service color without modifying the original', function (array $changes, ?string $expected) {
    $user = colorTherapist();
    $product = coloredProduct($user);
    $this->actingAs($user)->get(route('products.duplicate', $product))->assertOk()->assertSee('#2563EB');
    $this->post(route('products.storeDuplicate', $product), colorProductPayload($changes))->assertSessionHasNoErrors();
    expect(Product::whereKeyNot($product->id)->sole()->calendar_color)->toBe($expected)
        ->and($product->fresh()->calendar_color)->toBe('#2563EB');
})->with([[[], '#2563EB'], [['calendar_color' => '#0f766e'], '#0F766E'], [['calendar_color' => ''], null]]);

test('service colors apply to past and future calendar events and both appointment lists', function (string $route) {
    $user = colorTherapist();
    $product = coloredProduct($user);
    $past = colorAppointment($user, $product, ['appointment_date' => now()->subDay(), 'status' => 'completed']);
    $future = colorAppointment($user, $product);
    $snapshots = [$past->fresh()->getAttributes(), $future->fresh()->getAttributes()];

    $response = $this->actingAs($user)->get(route($route))->assertOk()->assertSee('data-service-color="#2563EB"', false);
    $response->assertViewHas('events', fn ($events) => count($events) === 2 && collect($events)->every(fn ($event) => $event['color'] === '#2563EB' && $event['textColor'] === '#FFFFFF' && $event['display'] === 'block'));

    $this->put(route('products.update', $product), colorProductPayload(['calendar_color' => '#FDE68A']))->assertSessionHasNoErrors();
    $this->get(route($route))->assertOk()->assertSee('data-service-color="#FDE68A"', false)
        ->assertViewHas('events', fn ($events) => collect($events)->every(fn ($event) => $event['color'] === '#FDE68A' && $event['textColor'] === '#000000'));
    expect($past->fresh()->getAttributes())->toBe($snapshots[0])->and($future->fresh()->getAttributes())->toBe($snapshots[1]);
})->with(['appointments.index', 'mobile.appointments.index']);

test('uncolored and missing services retain legacy colors and cancelled external and unavailable slots retain their treatment', function (string $route) {
    $user = colorTherapist();
    $uncolored = coloredProduct($user, null);
    $colored = coloredProduct($user);
    $past = colorAppointment($user, $uncolored, ['appointment_date' => now()->subDay()]);
    $future = colorAppointment($user, $uncolored);
    $missing = colorAppointment($user, null);
    $cancelled = colorAppointment($user, $colored, ['status' => 'cancelled']);
    colorAppointment($user, $colored, ['external' => true, 'notes' => 'Google occupé']);
    Unavailability::create(['user_id' => $user->id, 'reason' => 'Pause', 'start_date' => now()->addDays(2), 'end_date' => now()->addDays(2)->addHour()]);

    $this->actingAs($user)->get(route($route))->assertOk()->assertDontSee('data-service-color=', false)
        ->assertViewHas('events', function ($events) use ($past, $future, $missing, $cancelled) {
            $byUrl = collect($events)->keyBy('url');

            return count($events) === 5
                && $byUrl[route('appointments.show', $past)]['color'] === '#854f38'
                && $byUrl[route('appointments.show', $past)]['textColor'] === '#ffffff'
                && $byUrl[route('appointments.show', $future)]['color'] === '#647a0b'
                && $byUrl[route('appointments.show', $future)]['textColor'] === '#636363'
                && $byUrl[route('appointments.show', $missing)]['color'] === '#647a0b'
                && ! $byUrl->has(route('appointments.show', $cancelled))
                && collect($events)->firstWhere('title', 'Google occupé')['color'] === '#999999'
                && collect($events)->firstWhere('title', 'Pause')['color'] === '#808080';
        });
    $this->get(route($route, ['appointment_status' => 'cancelled']))->assertOk()->assertDontSee('data-service-color=', false);
})->with(['appointments.index', 'mobile.appointments.index']);

test('corrupt or foreign service colors safely fall back without exposing them', function () {
    $user = colorTherapist();
    $invalid = coloredProduct($user, 'invalid');
    $foreign = coloredProduct(colorTherapist(), '#B91C1C');
    colorAppointment($user, $invalid);
    colorAppointment($user, $foreign);

    $this->actingAs($user)->get(route('appointments.index'))->assertOk()->assertDontSee('data-service-color=', false)
        ->assertViewHas('events', fn ($events) => collect($events)->every(fn ($event) => $event['color'] === '#647a0b'));
});

test('new color migration preserves old services and appointment records', function () {
    $user = colorTherapist();
    $product = coloredProduct($user, null);
    $appointment = colorAppointment($user, $product);
    $migration = require database_path('migrations/2026_11_03_110000_add_calendar_color_to_products.php');
    $migration->down();
    $oldProduct = $product->fresh()->getAttributes();
    $oldAppointment = $appointment->fresh()->getAttributes();
    $migration->up();

    expect($product->fresh()->getAttributes())->toMatchArray($oldProduct)
        ->and($product->fresh()->calendar_color)->toBeNull()
        ->and($appointment->fresh()->getAttributes())->toBe($oldAppointment);
    $this->actingAs($user)->get(route('appointments.index'))->assertOk();
});

test('calendar text remains readable on dark light and middle tone colors', function () {
    foreach (['#000000', '#FFFFFF', '#777777', '#808000', '#FF0000', '#00FF00', '#0000FF', ...array_keys(ProductCalendarColor::PALETTE)] as $color) {
        $text = ProductCalendarColor::textColor($color);
        $rgb = sscanf($color, '#%02x%02x%02x');
        $linear = array_map(fn ($channel) => $channel / 255 <= 0.04045 ? $channel / 255 / 12.92 : (($channel / 255 + 0.055) / 1.055) ** 2.4, $rgb);
        $brightness = $linear[0] * 0.2126 + $linear[1] * 0.7152 + $linear[2] * 0.0722;
        $contrast = $text === '#FFFFFF' ? 1.05 / ($brightness + 0.05) : ($brightness + 0.05) / 0.05;
        expect($contrast)->toBeGreaterThanOrEqual(4.5);
    }
});
