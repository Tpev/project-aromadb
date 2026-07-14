<?php

use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\User;
use App\Services\ReceiptRecordingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function receiptServiceInvoice(float $total = 100): Invoice
{
    static $number = 8000;
    $user = User::factory()->create();
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Client',
        'last_name' => 'Receipt',
        'email' => 'receipt-'.uniqid().'@example.test',
    ]);

    return Invoice::create([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => ++$number,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => $total,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => $total,
    ]);
}

test('provider reference makes receipt creation idempotent', function () {
    $invoice = receiptServiceInvoice();
    $service = app(ReceiptRecordingService::class);

    $first = $service->recordInvoicePayment($invoice, 100, now()->toDateString(), 'card', 'payment', null, 'stripe', 'pi_same');
    $second = $service->recordInvoicePayment($invoice, 100, now()->toDateString(), 'card', 'payment', null, 'stripe', 'pi_same');

    expect($second->id)->toBe($first->id)
        ->and(Receipt::where('invoice_id', $invoice->id)->count())->toBe(1)
        ->and($invoice->fresh()->status)->toBe("Pay\u{00E9}e");
});

test('manual partial receipts remain additive and update invoice status', function () {
    $invoice = receiptServiceInvoice();
    $service = app(ReceiptRecordingService::class);

    $service->recordInvoicePayment($invoice, 25, now()->toDateString(), 'cash');
    $service->recordInvoicePayment($invoice, 20, now()->toDateString(), 'check');

    expect(Receipt::where('invoice_id', $invoice->id)->count())->toBe(2)
        ->and($invoice->fresh()->status)->toBe("Partiellement pay\u{00E9}e")
        ->and((float) $invoice->fresh()->solde_restant)->toBe(55.0);
});

test('a provider reference cannot be reused for another invoice', function () {
    $firstInvoice = receiptServiceInvoice();
    $secondInvoice = Invoice::create([
        'user_id' => $firstInvoice->user_id,
        'client_profile_id' => $firstInvoice->client_profile_id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => 8999,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => 100,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => 100,
    ]);
    $service = app(ReceiptRecordingService::class);

    $service->recordInvoicePayment($firstInvoice, 100, now()->toDateString(), 'card', 'payment', null, 'stripe', 'pi_owned');

    expect(fn () => $service->recordInvoicePayment(
        $secondInvoice,
        100,
        now()->toDateString(),
        'card',
        'payment',
        null,
        'stripe',
        'pi_owned'
    ))->toThrow(LogicException::class);
});

test('a counter entry reopens the invoice balance without deleting the original receipt', function () {
    $invoice = receiptServiceInvoice();
    $receipt = app(ReceiptRecordingService::class)->recordInvoicePayment(
        $invoice,
        100,
        now()->toDateString(),
        'card',
        'payment',
        null,
        'stripe',
        'pi_refunded'
    );

    $this->actingAs($invoice->user)
        ->post(route('receipts.reverse', $receipt), [
            'encaissement_date' => now()->toDateString(),
            'amount_ttc' => 100,
            'note' => 'Remboursement intégral',
        ])
        ->assertRedirect();

    expect(Receipt::where('invoice_id', $invoice->id)->count())->toBe(2)
        ->and(Receipt::where('invoice_id', $invoice->id)->where('direction', 'debit')->count())->toBe(1)
        ->and($invoice->fresh()->status)->toBe('En attente')
        ->and((float) $invoice->fresh()->solde_restant)->toBe(100.0);
});
