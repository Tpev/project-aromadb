<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceActivityLog;
use App\Models\User;

class InvoiceActivityService
{
    public function record(
        Invoice $invoice,
        string $event,
        string $message,
        ?User $actor = null,
        array $metadata = []
    ): InvoiceActivityLog {
        return $invoice->activityLogs()->create([
            'user_id' => $actor?->id,
            'event' => $event,
            'message' => $message,
            'metadata' => $metadata ?: null,
        ]);
    }
}
