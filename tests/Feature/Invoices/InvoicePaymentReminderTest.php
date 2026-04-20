<?php

use App\Mail\InvoicePaymentReminderMail;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createInvoiceForReminderTest(array $invoiceOverrides = [], array $clientOverrides = []): Invoice
{
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create(array_merge([
        'user_id' => $therapist->id,
        'first_name' => 'Delphine',
        'last_name' => 'Client',
        'email' => 'delphine-' . uniqid() . '@example.test',
    ], $clientOverrides));

    $invoice = Invoice::create(array_merge([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(10)->toDateString(),
        'invoice_number' => 2201,
        'status' => 'En attente',
        'type' => 'invoice',
        'sent_at' => now()->subDays(2),
        'total_amount' => 100.00,
        'total_tax_amount' => 0.00,
        'total_amount_with_tax' => 100.00,
    ], $invoiceOverrides));

    $invoice->items()->create([
        'type' => 'custom',
        'label' => 'Consultation',
        'description' => 'Consultation aromatique',
        'quantity' => 1,
        'unit_price' => 100.00,
        'tax_rate' => 0.00,
        'tax_amount' => 0.00,
        'total_price' => 100.00,
        'total_price_with_tax' => 100.00,
    ]);

    return $invoice;
}

test('invoice show page displays the payment reminder button once 24 hours have passed', function () {
    $invoice = createInvoiceForReminderTest();

    $this->actingAs($invoice->user)
        ->get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertSeeText('Relancer le paiement');
});

test('invoice show page displays when the reminder will become available if 24 hours have not passed yet', function () {
    $invoice = createInvoiceForReminderTest([
        'sent_at' => now()->subHours(6),
    ]);

    $this->actingAs($invoice->user)
        ->get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertDontSeeText('Relancer le paiement')
        ->assertSeeText('Relance disponible');
});

test('sending a payment reminder queues the reminder email and tracks it on the invoice', function () {
    Mail::fake();

    $invoice = createInvoiceForReminderTest([
        'payment_link' => 'https://stripe.example.test/invoice-reminder',
    ]);

    $this->actingAs($invoice->user)
        ->from(route('invoices.show', $invoice))
        ->post(route('invoices.sendPaymentReminder', $invoice))
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('success', 'Relance de paiement envoyée par email avec succès.');

    $invoice->refresh();

    expect($invoice->payment_reminder_count)->toBe(1);
    expect($invoice->last_payment_reminder_sent_at)->not->toBeNull();

    Mail::assertQueued(InvoicePaymentReminderMail::class, function (InvoicePaymentReminderMail $mail) use ($invoice) {
        return $mail->hasTo($invoice->clientProfile->email)
            && $mail->invoice->is($invoice);
    });
});

test('sending a payment reminder is blocked before 24 hours have passed since the first email', function () {
    Mail::fake();

    $invoice = createInvoiceForReminderTest([
        'sent_at' => now()->subHours(12),
    ]);

    $this->actingAs($invoice->user)
        ->from(route('invoices.show', $invoice))
        ->post(route('invoices.sendPaymentReminder', $invoice))
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error');

    expect($invoice->fresh()->payment_reminder_count)->toBe(0);
    expect($invoice->fresh()->last_payment_reminder_sent_at)->toBeNull();

    Mail::assertNothingQueued();
});

test('sending a payment reminder is blocked when the invoice is already fully paid', function () {
    Mail::fake();

    $invoice = createInvoiceForReminderTest();

    $invoice->receipts()->create([
        'user_id' => $invoice->user_id,
        'invoice_number' => (string) $invoice->invoice_number,
        'encaissement_date' => now()->toDateString(),
        'client_name' => $invoice->clientProfile->first_name . ' ' . $invoice->clientProfile->last_name,
        'nature' => 'Facture',
        'amount_ht' => 100.00,
        'amount_ttc' => 100.00,
        'payment_method' => 'card',
        'direction' => 'credit',
        'source' => 'payment',
    ]);

    $this->actingAs($invoice->user)
        ->from(route('invoices.show', $invoice))
        ->post(route('invoices.sendPaymentReminder', $invoice))
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('error', "La facture est déjà réglée, aucune relance n'a été envoyée.");

    Mail::assertNothingQueued();
});
