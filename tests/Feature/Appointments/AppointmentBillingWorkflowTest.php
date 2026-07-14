<?php

use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\SessionNote;
use App\Models\User;
use App\Services\ReceiptRecordingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function billingWorkflowContext(string $email = 'appointment-billing@example.test'): array
{
    $user = User::factory()->create([
        'email' => $email,
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Serge',
        'last_name' => 'Workflow',
        'email' => 'serge-'.uniqid().'@example.test',
    ]);
    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Séance de suivi',
        'description' => 'Accompagnement individuel',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'dans_le_cabinet' => true,
    ]);
    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => now()->subDay(),
        'status' => 'Complété',
        'duration' => 60,
        'type' => 'cabinet',
    ]);

    return compact('user', 'client', 'product', 'appointment');
}

function billingWorkflowInvoice(array $context, array $attributes = []): Invoice
{
    static $number = 22000;

    return Invoice::create(array_merge([
        'user_id' => $context['user']->id,
        'client_profile_id' => $context['client']->id,
        'appointment_id' => $context['appointment']->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => ++$number,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 80,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 80,
    ], $attributes));
}

function billingWorkflowPayload(array $context, array $overrides = []): array
{
    return array_merge([
        'client_profile_id' => $context['client']->id,
        'appointment_id' => $context['appointment']->id,
        'invoice_date' => now()->toDateString(),
        'items' => [[
            'type' => 'product',
            'product_id' => $context['product']->id,
            'description' => '',
            'quantity' => 1,
            'unit_price' => 80,
            'tax_rate' => 0,
        ]],
    ], $overrides);
}

test('creating an invoice from an already invoiced appointment redirects to the existing invoice', function () {
    $context = billingWorkflowContext();
    $existing = billingWorkflowInvoice($context);

    $this->actingAs($context['user'])
        ->post(route('invoices.store'), billingWorkflowPayload($context))
        ->assertRedirect(route('invoices.show', $existing))
        ->assertSessionHas('error');

    expect(Invoice::where('appointment_id', $context['appointment']->id)->count())->toBe(1);
});

test('a deliberate second invoice requires and records a reason', function () {
    $context = billingWorkflowContext('appointment-additional@example.test');
    billingWorkflowInvoice($context);

    $this->actingAs($context['user'])
        ->from(route('appointments.show', $context['appointment']))
        ->post(route('invoices.store'), billingWorkflowPayload($context, [
            'allow_additional_invoice' => 1,
        ]))
        ->assertSessionHasErrors('additional_invoice_reason');

    $response = $this->post(route('invoices.store'), billingWorkflowPayload($context, [
        'allow_additional_invoice' => 1,
        'additional_invoice_reason' => 'Deux prestations distinctes le même jour',
    ]));

    $created = Invoice::where('appointment_id', $context['appointment']->id)->latest('id')->firstOrFail();
    $response->assertRedirect(route('invoices.show', $created));

    expect(Invoice::where('appointment_id', $context['appointment']->id)->count())->toBe(2)
        ->and($created->activityLogs()->where('event', 'created')->firstOrFail()
            ->metadata['additional_invoice_reason'])->toBe('Deux prestations distinctes le même jour');
});

test('an existing invoice can be associated only to an owned appointment for the same client', function () {
    $context = billingWorkflowContext('appointment-associate@example.test');
    $invoice = billingWorkflowInvoice($context, ['appointment_id' => null]);

    $this->actingAs($context['user'])
        ->from(route('appointments.show', $context['appointment']))
        ->post(route('appointments.invoices.associate', $context['appointment']), [
            'invoice_id' => $invoice->id,
        ])
        ->assertRedirect(route('appointments.show', $context['appointment']))
        ->assertSessionHas('success');

    expect($invoice->fresh()->appointment_id)->toBe($context['appointment']->id);

    $otherClient = ClientProfile::create([
        'user_id' => $context['user']->id,
        'first_name' => 'Autre',
        'last_name' => 'Client',
        'email' => 'other-client@example.test',
    ]);
    $otherInvoice = Invoice::create([
        'user_id' => $context['user']->id,
        'client_profile_id' => $otherClient->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => 22999,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 30,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 30,
    ]);

    $this->post(route('appointments.invoices.associate', $context['appointment']), [
        'invoice_id' => $otherInvoice->id,
    ])->assertSessionHas('error');

    expect($otherInvoice->fresh()->appointment_id)->toBeNull();
});

test('reassigning an invoice from another appointment requires explicit confirmation', function () {
    $context = billingWorkflowContext('appointment-reassign@example.test');
    $otherAppointment = Appointment::create([
        'user_id' => $context['user']->id,
        'client_profile_id' => $context['client']->id,
        'product_id' => $context['product']->id,
        'appointment_date' => now()->subDays(2),
        'status' => 'Complété',
        'duration' => 60,
        'type' => 'cabinet',
    ]);
    $invoice = billingWorkflowInvoice($context, ['appointment_id' => $otherAppointment->id]);

    $this->actingAs($context['user'])
        ->post(route('appointments.invoices.associate', $context['appointment']), [
            'invoice_id' => $invoice->id,
        ])
        ->assertSessionHas('error');

    expect($invoice->fresh()->appointment_id)->toBe($otherAppointment->id);

    $this->post(route('appointments.invoices.associate', $context['appointment']), [
        'invoice_id' => $invoice->id,
        'confirm_reassign' => 1,
    ])->assertSessionHas('success');

    expect($invoice->fresh()->appointment_id)->toBe($context['appointment']->id);
});

test('appointment session note and billing statuses remain independent', function () {
    $context = billingWorkflowContext('appointment-statuses@example.test');

    expect($context['appointment']->fresh()->session_tracking_label)->toBe('Terminée')
        ->and($context['appointment']->fresh()->note_tracking_label)->toBe('Note à rédiger')
        ->and($context['appointment']->fresh()->billing_tracking_label)->toBe('À facturer');

    SessionNote::create([
        'user_id' => $context['user']->id,
        'client_profile_id' => $context['client']->id,
        'appointment_id' => $context['appointment']->id,
        'note' => 'Note liée.',
    ]);
    $invoice = billingWorkflowInvoice($context);

    expect($context['appointment']->fresh()->status)->toBe('Complété')
        ->and($context['appointment']->fresh()->note_tracking_label)->toBe('Note créée')
        ->and($context['appointment']->fresh()->billing_tracking_label)->toBe('En attente de règlement');

    app(ReceiptRecordingService::class)->recordInvoicePayment($invoice, 30, now()->toDateString(), 'cash');
    expect($context['appointment']->fresh()->billing_tracking_label)->toBe('Partiellement réglée');

    app(ReceiptRecordingService::class)->recordInvoicePayment($invoice, 50, now()->toDateString(), 'cash');
    expect($context['appointment']->fresh()->billing_tracking_label)->toBe('Réglée')
        ->and($context['appointment']->fresh()->status)->toBe('Complété');
});

test('legacy appointments with multiple invoices are visible on desktop and mobile', function () {
    $context = billingWorkflowContext('appointment-legacy-multiple@example.test');
    $first = billingWorkflowInvoice($context);
    $second = billingWorkflowInvoice($context);

    expect($context['appointment']->fresh()->billing_tracking_label)->toBe('Plusieurs factures');

    $this->actingAs($context['user'])
        ->get(route('appointments.show', $context['appointment']))
        ->assertOk()
        ->assertSee('Plusieurs factures')
        ->assertSee(route('invoices.show', $first), false)
        ->assertSee(route('invoices.show', $second), false);

    $this->get(route('mobile.appointments.show', $context['appointment']))
        ->assertOk()
        ->assertSee('Plusieurs factures')
        ->assertSee(route('mobile.invoices.show', $first), false)
        ->assertSee(route('mobile.invoices.show', $second), false);
});
