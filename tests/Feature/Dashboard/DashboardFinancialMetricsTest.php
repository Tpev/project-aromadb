<?php

use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\User;
use App\Services\DashboardFinancialMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function financialInvoice(User $user, ClientProfile $client, float $total, array $overrides = []): Invoice
{
    static $number = 7000;

    return Invoice::create(array_merge([
        'user_id' => $user->id,
        'client_profile_id' => $client->id,
        'invoice_date' => now()->toDateString(),
        'invoice_number' => ++$number,
        'status' => 'En attente',
        'type' => 'invoice',
        'total_amount' => $total,
        'total_tax_amount' => 0,
        'total_amount_with_tax' => $total,
    ], $overrides));
}

function financialReceipt(Invoice $invoice, float $amount, string $direction = 'credit', array $overrides = []): Receipt
{
    return Receipt::create(array_merge([
        'user_id' => $invoice->user_id,
        'invoice_id' => $invoice->id,
        'invoice_number' => (string) $invoice->invoice_number,
        'encaissement_date' => now()->toDateString(),
        'client_name' => 'Client KPI',
        'nature' => 'service',
        'amount_ht' => $amount,
        'amount_ttc' => $amount,
        'payment_method' => 'card',
        'direction' => $direction,
        'source' => $direction === 'credit' ? 'payment' : 'refund',
    ], $overrides));
}

test('dashboard financial metrics separate billed amounts receipts and corrections', function () {
    $user = User::factory()->create();
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Client',
        'last_name' => 'KPI',
        'email' => 'kpi@example.test',
    ]);

    $partiallyPaid = financialInvoice($user, $client, 120);
    financialReceipt($partiallyPaid, 100);
    financialReceipt($partiallyPaid, 20, 'debit');

    financialInvoice($user, $client, 50);
    financialInvoice($user, $client, 30, ['status' => "Pay\u{00E9}e"]);
    financialInvoice($user, $client, 999, ['type' => 'quote']);
    financialInvoice($user, $client, 75, ['invoice_date' => now()->subMonth()->toDateString()]);

    $metrics = app(DashboardFinancialMetricsService::class)->forUser($user->id);

    expect($metrics['net_received_this_month'])->toBe(80.0)
        ->and($metrics['billed_this_month'])->toBe(200.0)
        ->and($metrics['corrections_and_refunds'])->toBe(20.0)
        ->and($metrics['outstanding'])->toBe(195.0)
        ->and($metrics['legacy_paid_without_receipt_count'])->toBe(1)
        ->and($metrics['monthly_net_received'][now()->month])->toBe(80.0)
        ->and($metrics['monthly_billed'][now()->month])->toBe(200.0);
});

test('dashboard net receipts can be negative after reimbursements', function () {
    $user = User::factory()->create();
    $client = ClientProfile::create([
        'user_id' => $user->id,
        'first_name' => 'Client',
        'last_name' => 'Refund',
        'email' => 'refund@example.test',
    ]);
    $invoice = financialInvoice($user, $client, 100);
    financialReceipt($invoice, 20);
    financialReceipt($invoice, 50, 'debit');

    $metrics = app(DashboardFinancialMetricsService::class)->forUser($user->id);

    expect($metrics['net_received_this_month'])->toBe(-30.0)
        ->and($metrics['corrections_and_refunds'])->toBe(50.0);
});
