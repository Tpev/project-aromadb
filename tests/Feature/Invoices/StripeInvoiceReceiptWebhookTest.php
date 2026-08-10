<?php

use App\Http\Controllers\StripeController;
use App\Http\Controllers\StripeWebhookController;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\Receipt;
use App\Models\User;
use App\Notifications\InvoicePaid;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

test('duplicate Stripe invoice checkout callbacks create one receipt', function () {
    Notification::fake();

    $user = User::factory()->create();
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Stripe',
        'last_name' => 'Client',
        'email' => 'stripe-client@example.test',
    ]);
    $invoice = Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => 9100,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 100,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 100,
    ]);

    $session = (object) [
        'id' => 'cs_invoice_same',
        'payment_status' => 'paid',
        'amount_total' => 10000,
        'payment_intent' => (object) ['id' => 'pi_invoice_same'],
        'payment_link' => null,
        'metadata' => (object) ['invoice_id' => (string) $invoice->id],
    ];

    $controller = app(StripeWebhookController::class);
    $method = new ReflectionMethod($controller, 'handleCheckoutSessionCompleted');
    $method->invoke($controller, $session, null);
    $method->invoke($controller, $session, null);

    expect(Receipt::where('invoice_id', $invoice->id)->count())->toBe(1)
        ->and(Receipt::first()->provider)->toBe('stripe')
        ->and(Receipt::first()->provider_reference)->toBe('pi_invoice_same')
        ->and($invoice->fresh()->status)->toBe("Pay\u{00E9}e");

    Notification::assertSentToTimes($user, InvoicePaid::class, 1);
});

test('appointment Stripe webhook finalizes invoice receipt and service date without browser return', function () {
    $user = User::factory()->create([
        'stripe_account_id' => 'acct_appointment_test',
    ]);
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Appointment',
        'last_name' => 'Stripe',
        'email' => 'appointment-stripe@example.test',
    ]);
    $product = Product::create([
        'user_id' => $user->id,
        'name' => 'Séance Stripe',
        'description' => 'Description de la séance',
        'price' => 80,
        'tax_rate' => 0,
        'duration' => 60,
        'dans_le_cabinet' => true,
    ]);
    $appointment = Appointment::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'product_id' => $product->id,
        'appointment_date' => '2026-07-25 11:00:00',
        'duration' => 60,
        'status' => 'pending',
        'type' => 'cabinet',
    ]);

    $session = (object) [
        'id' => 'cs_appointment_same',
        'payment_status' => 'paid',
        'amount_total' => 8000,
        'payment_intent' => 'pi_appointment_same',
        'metadata' => (object) ['appointment_id' => (string) $appointment->id],
    ];

    $controller = app(StripeController::class);
    $method = new ReflectionMethod($controller, 'handleCheckoutSessionCompleted');
    $method->invoke($controller, $session, 'acct_appointment_test');
    $method->invoke($controller, $session, 'acct_appointment_test');

    $invoice = Invoice::where('appointment_id', $appointment->id)->firstOrFail();

    expect(Invoice::where('appointment_id', $appointment->id)->count())->toBe(1)
        ->and(Receipt::where('invoice_id', $invoice->id)->count())->toBe(1)
        ->and($invoice->items()->firstOrFail()->service_date->format('Y-m-d'))->toBe('2026-07-25')
        ->and($appointment->fresh()->status)->toBe(Appointment::STATUS_PAID);
});
