<?php

namespace App\Services\SuperPdp;

use App\Models\SuperPdpConnection;
use App\Models\SuperPdpReceivedInvoice;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SuperPdpReceivedInvoiceSyncService
{
    public function __construct(private readonly SuperPdpApiClient $client)
    {
    }

    public function sync(SuperPdpConnection $connection): int
    {
        if (! $connection->isConnected() || ! $connection->receiving_invoices_enabled) {
            return 0;
        }

        $payload = $this->client->listIncomingInvoices($connection);
        $count = 0;

        foreach (data_get($payload, 'data', []) as $invoice) {
            $this->upsertInvoice($connection, $invoice);
            $count++;
        }

        $connection->forceFill([
            'last_synced_at' => now(),
            'last_error' => null,
        ])->save();

        return $count;
    }

    private function upsertInvoice(SuperPdpConnection $connection, array $invoice): SuperPdpReceivedInvoice
    {
        $enInvoice = data_get($invoice, 'en_invoice', []);
        $latestEvent = $this->latestEvent(collect(data_get($invoice, 'events', [])));
        $totalWithVat = data_get($enInvoice, 'totals.total_with_vat');

        return SuperPdpReceivedInvoice::updateOrCreate([
            'connection_id' => $connection->id,
            'super_pdp_invoice_id' => (int) data_get($invoice, 'id'),
        ], [
            'user_id' => $connection->user_id,
            'super_pdp_company_id' => data_get($invoice, 'company_id'),
            'direction' => data_get($invoice, 'direction', 'in'),
            'external_id' => data_get($invoice, 'external_id'),
            'invoice_number' => data_get($enInvoice, 'number'),
            'invoice_date' => $this->parseDate(data_get($enInvoice, 'issue_date')),
            'seller_name' => data_get($enInvoice, 'seller.name'),
            'buyer_name' => data_get($enInvoice, 'buyer.name'),
            'currency_code' => data_get($enInvoice, 'currency_code'),
            'total_with_vat' => is_numeric($totalWithVat) ? $totalWithVat : null,
            'latest_event_code' => data_get($latestEvent, 'status_code'),
            'latest_event_text' => data_get($latestEvent, 'status_text'),
            'latest_event_at' => $this->parseDateTime(data_get($latestEvent, 'created_at')),
            'raw_payload' => $invoice,
            'last_synced_at' => now(),
        ]);
    }

    private function latestEvent(Collection $events): ?array
    {
        return $events
            ->sortByDesc(fn (array $event) => (string) data_get($event, 'created_at', ''))
            ->first();
    }

    private function parseDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value)->toDateString();
    }

    private function parseDateTime(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        return Carbon::parse($value);
    }
}
