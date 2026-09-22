<?php

namespace App\Services;

use App\Models\Receipt;

class ReceiptReportingService
{
    public function monthly(int $userId, int $year): array
    {
        $data = array_fill(1, 12, [
            'total' => 0.0,
            'service' => 0.0,
            'goods' => 0.0,
            'other' => 0.0,
        ]);

        $receipts = Receipt::withAccountingDate()
            ->where('user_id', $userId)
            ->whereYear('accounting_date', $year)
            ->get(['accounting_date', 'nature', 'direction', 'amount_ttc']);

        foreach ($receipts as $receipt) {
            $month = (int) $receipt->accounting_date->month;
            $nature = in_array($receipt->nature, ['service', 'goods'], true) ? $receipt->nature : 'other';
            $data[$month]['total'] += $receipt->signed_amount_ttc;
            $data[$month][$nature] += $receipt->signed_amount_ttc;
        }

        return array_map(fn ($month) => array_map(fn ($amount) => round($amount, 2), $month), $data);
    }
}
