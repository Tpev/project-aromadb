<?php

use App\Http\Controllers\AppointmentController;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function serviceDateContext(): array
{
    $user = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Date',
        'last_name' => 'Service',
        'email' => 'date-service-'.uniqid().'@example.test',
    ]);
    $description = trim(str_repeat('Description catalogue detaillee. ', 20));
    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Bilan initial',
        'description' => $description,
        'price' => 90,
        'tax_rate' => 0,
        'duration' => 60,
        'dans_le_cabinet' => true,
    ]);

    return compact('user', 'client', 'product', 'description');
}

function serviceDatePayload(array $context, array $itemOverrides = []): array
{
    return [
        'client_profile_id' => $context['client']->id,
        'invoice_date' => now()->toDateString(),
        'items' => [array_merge([
            'type' => 'product',
            'product_id' => $context['product']->id,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 90,
            'tax_rate' => 0,
            'service_date' => '2026-07-14',
        ], $itemOverrides)],
    ];
}

test('invoice stores a full catalog description snapshot and a service date', function () {
    $context = serviceDateContext();

    $response = $this->actingAs($context['user'])->post(route('invoices.store'), serviceDatePayload($context));

    $invoice = Invoice::where('user_id', $context['user']->id)->latest('id')->firstOrFail();
    $item = $invoice->items()->firstOrFail();

    $response->assertRedirect(route('invoices.show', $invoice));
    expect($item->description_snapshot)->toBe($context['description'])
        ->and(mb_strlen($item->description))->toBe(255)
        ->and($item->billing_description)->toBe($context['description'])
        ->and($item->service_date->format('Y-m-d'))->toBe('2026-07-14');
});

test('manual description overrides the catalog snapshot', function () {
    $context = serviceDateContext();
    $payload = serviceDatePayload($context, ['description' => 'Libellé choisi par le praticien']);

    $this->actingAs($context['user'])->post(route('invoices.store'), $payload);

    $item = Invoice::where('user_id', $context['user']->id)->latest('id')->firstOrFail()->items()->firstOrFail();
    expect($item->billing_description)->toBe('Libellé choisi par le praticien');
});

test('single service date and service period are mutually exclusive', function () {
    $context = serviceDateContext();
    $payload = serviceDatePayload($context, [
        'service_period_start' => '2026-07-01',
        'service_period_end' => '2026-07-31',
    ]);

    $this->actingAs($context['user'])
        ->from(route('invoices.create'))
        ->post(route('invoices.store'), $payload)
        ->assertRedirect(route('invoices.create'))
        ->assertSessionHasErrors('items.0.service_date');

    expect(Invoice::where('user_id', $context['user']->id)->count())->toBe(0);
});

test('appointment invoice entry point validates ownership and prefills service date', function () {
    $context = serviceDateContext();
    $appointment = Appointment::create([
        'user_id' => $context['user']->id,
        'client_profile_id' => $context['client']->id,
        'product_id' => $context['product']->id,
        'appointment_date' => '2026-07-20 10:00:00',
        'duration' => 60,
        'status' => 'Confirmé',
        'type' => 'cabinet',
    ]);

    $this->actingAs($context['user'])
        ->get(route('invoices.create', ['appointment_id' => $appointment->id]))
        ->assertOk()
        ->assertSee('name="appointment_id" value="'.$appointment->id.'"', false)
        ->assertSee('2026-07-20');

    $otherUser = User::factory()->create(['license_status' => 'active']);
    $this->actingAs($otherUser)
        ->get(route('invoices.create', ['appointment_id' => $appointment->id]))
        ->assertNotFound();
});

test('automatic appointment invoice snapshots its service date and is idempotent', function () {
    $context = serviceDateContext();
    $appointment = Appointment::create([
        'user_id' => $context['user']->id,
        'client_profile_id' => $context['client']->id,
        'product_id' => $context['product']->id,
        'appointment_date' => '2026-07-22 14:30:00',
        'duration' => 60,
        'status' => 'Payée',
        'type' => 'cabinet',
    ]);

    $controller = app(AppointmentController::class);
    $method = new ReflectionMethod($controller, 'createInvoiceFromAppointment');
    $invoice = $method->invoke($controller, $appointment);
    $second = $method->invoke($controller, $appointment);
    $item = $invoice->items()->firstOrFail();

    expect($second->id)->toBe($invoice->id)
        ->and(Invoice::where('appointment_id', $appointment->id)->count())->toBe(1)
        ->and($item->service_date->format('Y-m-d'))->toBe('2026-07-22')
        ->and($item->billing_description)->toBe($context['description']);
});
