<?php

use App\Models\Receipt;
use App\Models\User;
use App\Services\DashboardFinancialMetricsService;
use App\Services\ReceiptReportingService;
use Carbon\Carbon;

function reversalReportingUser(): User
{
    return User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'license_product' => 'new_pro_mensuelle',
    ]);
}

function reversalReportingReceipt(User $user, array $attributes = []): Receipt
{
    return Receipt::create(array_merge([
        'user_id' => $user->id,
        'encaissement_date' => '2026-09-02',
        'invoice_number' => '187',
        'client_name' => 'Client correction',
        'nature' => 'service',
        'amount_ht' => 35,
        'amount_ttc' => 35,
        'payment_method' => 'transfer',
        'direction' => 'credit',
        'source' => 'payment',
    ], $attributes));
}

test('a legacy August reversal cancels the September payment across reports and exports', function () {
    $user = reversalReportingUser();
    $original = reversalReportingReceipt($user);
    $correction = reversalReportingReceipt($user, [
        'encaissement_date' => '2026-08-31',
        'direction' => 'debit',
        'source' => 'correction',
        'is_reversal' => true,
        'reversal_of_id' => $original->id,
    ]);
    $replacement = reversalReportingReceipt($user, [
        'encaissement_date' => '2026-08-31',
        'invoice_number' => '188',
    ]);
    reversalReportingReceipt(reversalReportingUser(), ['amount_ttc' => 999]);

    $this->actingAs($user);

    foreach (['receipts.caMonthly', 'mobile.receipts.monthly'] as $route) {
        $this->get(route($route, ['year' => 2026]))
            ->assertOk()
            ->assertViewHas('data', fn ($data) => $data[8]['total'] === 35.0
                && $data[8]['service'] === 35.0 && $data[9]['total'] === 0.0);
    }

    foreach (['receipts.index', 'mobile.receipts.index'] as $route) {
        $this->get(route($route, ['from' => '2026-08-01', 'to' => '2026-08-31']))
            ->assertOk()
            ->assertViewHas('total', fn ($total) => (float) $total === 35.0)
            ->assertViewHas('receipts', fn ($rows) => $rows->pluck('id')->all() === [$replacement->id]);

        $this->get(route($route, ['from' => '2026-09-01', 'to' => '2026-09-30']))
            ->assertOk()
            ->assertViewHas('total', fn ($total) => (float) $total === 0.0)
            ->assertViewHas('receipts', fn ($rows) => $rows->total() === 2
                && $rows->firstWhere('id', $correction->id)->accounting_date->toDateString() === '2026-09-02')
            ->assertSee('Date saisie : 31/08/2026');
    }

    $csv = $this->get(route('receipts.export', ['from' => '2026-09-01', 'to' => '2026-09-30']))
        ->assertOk()->streamedContent();
    $lines = array_map(fn ($line) => str_getcsv($line, ';'), explode("\n", trim($csv)));
    expect($lines)->toHaveCount(3)
        ->and($lines[1][0])->toBe('02/09/2026')
        ->and($lines[2][0])->toBe('02/09/2026')
        ->and($lines[2][7])->toBe('Debit')
        ->and($lines[2][10])->toBe('31/08/2026');

    $dashboard = app(DashboardFinancialMetricsService::class);
    $august = $dashboard->forUser($user->id, Carbon::parse('2026-08-31'));
    $september = $dashboard->forUser($user->id, Carbon::parse('2026-09-22'));
    expect($august['net_received_this_month'])->toBe(35.0)
        ->and($august['corrections_and_refunds'])->toBe(0.0)
        ->and($september['net_received_this_month'])->toBe(0.0)
        ->and($september['corrections_and_refunds'])->toBe(35.0)
        ->and($september['monthly_net_received'][8])->toBe(35.0)
        ->and($september['monthly_net_received'][9])->toBe(0.0)
        ->and($original->fresh()->encaissement_date->toDateString())->toBe('2026-09-02')
        ->and($correction->fresh()->encaissement_date->toDateString())->toBe('2026-08-31');
});

test('partial corrections cross year boundaries while refunds keep their actual date', function () {
    $user = reversalReportingUser();
    $original = reversalReportingReceipt($user, [
        'encaissement_date' => '2025-12-31',
        'amount_ttc' => 100,
    ]);
    reversalReportingReceipt($user, [
        'encaissement_date' => '2026-01-10',
        'amount_ttc' => 40,
        'direction' => 'debit',
        'source' => 'correction',
        'is_reversal' => true,
        'reversal_of_id' => $original->id,
    ]);
    $refundedPayment = reversalReportingReceipt($user, [
        'encaissement_date' => '2025-12-31',
        'amount_ttc' => 20,
    ]);
    reversalReportingReceipt($user, [
        'encaissement_date' => '2026-01-15',
        'amount_ttc' => 10,
        'direction' => 'debit',
        'source' => 'refund',
        'is_reversal' => true,
        'reversal_of_id' => $refundedPayment->id,
    ]);

    $reports = app(ReceiptReportingService::class);
    $dashboard = app(DashboardFinancialMetricsService::class);
    expect($reports->monthly($user->id, 2025)[12]['total'])->toBe(80.0)
        ->and($reports->monthly($user->id, 2026)[1]['total'])->toBe(-10.0)
        ->and($dashboard->forUser($user->id, Carbon::parse('2025-12-31'))['net_received_this_month'])->toBe(80.0)
        ->and($dashboard->forUser($user->id, Carbon::parse('2026-01-31'))['net_received_this_month'])->toBe(-10.0);
});

test('reversing a payment uses its original date and prevents repeated cancellation', function (string $route, array $dateInput) {
    $user = reversalReportingUser();
    $original = reversalReportingReceipt($user, ['amount_ttc' => 120, 'amount_ht' => 100]);

    $this->actingAs($user)->post(route($route, $original), array_merge($dateInput, [
        'amount_ttc' => 60,
        'note' => 'Correction partielle',
    ]))->assertRedirect()->assertSessionHas('success');

    $reversal = $original->reversals()->sole();
    expect($reversal->encaissement_date->toDateString())->toBe('2026-09-02')
        ->and($reversal->direction)->toBe('debit')
        ->and($reversal->source)->toBe('correction')
        ->and((float) $reversal->amount_ttc)->toBe(60.0)
        ->and((float) $reversal->amount_ht)->toBe(50.0)
        ->and($reversal->is_reversal)->toBeTrue();

    $this->post(route($route, $original))->assertRedirect()->assertSessionHas('error');
    $this->post(route($route, $reversal))->assertRedirect()->assertSessionHas('error');
    expect($original->reversals()->count())->toBe(1)
        ->and(app(ReceiptReportingService::class)->monthly($user->id, 2026)[9]['total'])->toBe(60.0);
})->with(['receipts.reverse', 'mobile.receipts.reverse'])
    ->with(['submitted date' => [['encaissement_date' => '2026-08-31']], 'no date' => [[]]]);

test('an invalid cross-owner reversal link does not borrow the other owners date', function () {
    $original = reversalReportingReceipt(reversalReportingUser());
    $otherUser = reversalReportingUser();
    reversalReportingReceipt($otherUser, [
        'encaissement_date' => '2026-08-31',
        'direction' => 'debit',
        'source' => 'correction',
        'is_reversal' => true,
        'reversal_of_id' => $original->id,
    ]);

    $data = app(ReceiptReportingService::class)->monthly($otherUser->id, 2026);
    expect($data[8]['total'])->toBe(-35.0)->and($data[9]['total'])->toBe(0.0);
});
