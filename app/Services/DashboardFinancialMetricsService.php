<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Receipt;
use Carbon\CarbonInterface;

class DashboardFinancialMetricsService
{
    public function forUser(int $userId, ?CarbonInterface $now = null): array
    {
        $now = $now ? $now->copy() : now();
        $monthStart = $now->copy()->startOfMonth();
        $nextMonthStart = $monthStart->copy()->addMonth();
        $yearStart = $now->copy()->startOfYear();
        $nextYearStart = $yearStart->copy()->addYear();

        $monthReceipts = Receipt::where('user_id', $userId)
            ->where('encaissement_date', '>=', $monthStart->toDateString())
            ->where('encaissement_date', '<', $nextMonthStart->toDateString())
            ->get(['direction', 'amount_ttc']);

        $netReceived = $monthReceipts->sum(fn (Receipt $receipt) => $receipt->signed_amount_ttc);
        $correctionsAndRefunds = $monthReceipts
            ->where('direction', 'debit')
            ->sum(fn (Receipt $receipt) => (float) $receipt->amount_ttc);

        $billedThisMonth = (float) Invoice::where('user_id', $userId)
            ->where('type', 'invoice')
            ->where('invoice_date', '>=', $monthStart->toDateString())
            ->where('invoice_date', '<', $nextMonthStart->toDateString())
            ->sum('total_amount_with_tax');

        $invoices = Invoice::where('user_id', $userId)
            ->where('type', 'invoice')
            ->withSum(['receipts as received_credits' => fn ($query) => $query->where('direction', 'credit')], 'amount_ttc')
            ->withSum(['receipts as received_debits' => fn ($query) => $query->where('direction', 'debit')], 'amount_ttc')
            ->get(['id', 'status', 'total_amount_with_tax']);

        $outstanding = $invoices->sum(function (Invoice $invoice) {
            $received = (float) ($invoice->received_credits ?? 0) - (float) ($invoice->received_debits ?? 0);

            return max(0, (float) $invoice->total_amount_with_tax - $received);
        });

        $legacyPaidWithoutReceiptCount = $invoices->filter(function (Invoice $invoice) {
            $received = (float) ($invoice->received_credits ?? 0) - (float) ($invoice->received_debits ?? 0);

            return in_array($invoice->status, ["Pay\u{00E9}e", 'Payee'], true) && $received <= 0.001;
        })->count();

        $monthlyNetReceived = array_fill(1, 12, 0.0);
        Receipt::where('user_id', $userId)
            ->where('encaissement_date', '>=', $yearStart->toDateString())
            ->where('encaissement_date', '<', $nextYearStart->toDateString())
            ->get(['encaissement_date', 'direction', 'amount_ttc'])
            ->each(function (Receipt $receipt) use (&$monthlyNetReceived) {
                $monthlyNetReceived[(int) $receipt->encaissement_date->month] += $receipt->signed_amount_ttc;
            });

        $monthlyBilled = array_fill(1, 12, 0.0);
        Invoice::where('user_id', $userId)
            ->where('type', 'invoice')
            ->where('invoice_date', '>=', $yearStart->toDateString())
            ->where('invoice_date', '<', $nextYearStart->toDateString())
            ->get(['invoice_date', 'total_amount_with_tax'])
            ->each(function (Invoice $invoice) use (&$monthlyBilled) {
                $monthlyBilled[(int) $invoice->invoice_date->month] += (float) $invoice->total_amount_with_tax;
            });

        return [
            'net_received_this_month' => round((float) $netReceived, 2),
            'billed_this_month' => round($billedThisMonth, 2),
            'outstanding' => round((float) $outstanding, 2),
            'corrections_and_refunds' => round((float) $correctionsAndRefunds, 2),
            'legacy_paid_without_receipt_count' => $legacyPaidWithoutReceiptCount,
            'monthly_net_received' => array_map(fn ($value) => round((float) $value, 2), $monthlyNetReceived),
            'monthly_billed' => array_map(fn ($value) => round((float) $value, 2), $monthlyBilled),
        ];
    }
}
