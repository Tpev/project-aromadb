<?php

use App\Mail\InvoicePaymentLinkMail;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\User;
use App\Services\StripePaymentLinkFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

afterEach(function () {
    \Mockery::close();
});

function createInvoiceForPaymentLinkEmailTest(array $invoiceOverrides = [], array $clientOverrides = []): Invoice
{
    $therapist = User::factory()->create([
        'is_therapist' => true,
        'stripe_account_id' => 'acct_test_invoice',
        'license_status' => 'active',
    ]);

    $client = ClientProfile::create(array_merge([
        'user_id' => $therapist->id,
        'first_name' => 'Jane',
        'last_name' => 'Client',
        'email' => 'jane-' . uniqid() . '@example.test',
    ], $clientOverrides));

    $invoice = Invoice::create(array_merge([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'invoice_date' => now()->toDateString(),
        'due_date' => now()->addDays(10)->toDateString(),
        'invoice_number' => 1201,
        'status' => 'En attente',
        'type' => 'invoice',
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

test('invoice show page makes it clear creating a payment link also sends an email', function () {
    $invoice = createInvoiceForPaymentLinkEmailTest();

    $this->actingAs($invoice->user)
        ->get(route('invoices.show', $invoice))
        ->assertOk()
        ->assertSeeText('Créer et envoyer le lien de paiement')
        ->assertSeeText("Cette action crée le lien Stripe puis l'envoie automatiquement par email");
});

test('creating a payment link queues the payment link email to the billing recipient', function () {
    Mail::fake();

    $invoice = createInvoiceForPaymentLinkEmailTest();

    $stripePaymentLinkFactory = \Mockery::mock(StripePaymentLinkFactory::class);
    $stripePaymentLinkFactory
        ->shouldReceive('createInvoicePaymentLink')
        ->once()
        ->withArgs(function (Invoice $passedInvoice, int $amountCents, string $label, string $redirectUrl, string $stripeAccountId) use ($invoice) {
            return $passedInvoice->is($invoice)
                && $amountCents === 10000
                && $label === 'Facture n°1201'
                && str_contains($redirectUrl, '/invoices/' . $invoice->id)
                && $stripeAccountId === 'acct_test_invoice';
        })
        ->andReturn('https://stripe.example.test/payment-link');

    $this->app->instance(StripePaymentLinkFactory::class, $stripePaymentLinkFactory);

    $this->actingAs($invoice->user)
        ->from(route('invoices.show', $invoice))
        ->post(route('invoices.createPaymentLink', $invoice))
        ->assertRedirect(route('invoices.show', $invoice))
        ->assertSessionHas('success', 'Lien de paiement Stripe créé et envoyé par email avec succès.');

    expect($invoice->fresh()->payment_link)->toBe('https://stripe.example.test/payment-link');

    Mail::assertQueued(InvoicePaymentLinkMail::class, function (InvoicePaymentLinkMail $mail) use ($invoice) {
        return $mail->hasTo($invoice->clientProfile->email)
            && $mail->recipientName === 'Jane Client';
    });
});
