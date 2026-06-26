<?php

namespace App\Console\Commands;

use App\Models\SuperPdpConnection;
use App\Services\SuperPdp\SuperPdpReceivedInvoiceSyncService;
use App\Support\SuperPdpFeature;
use Illuminate\Console\Command;

class SyncSuperPdpReceivedInvoices extends Command
{
    protected $signature = 'super-pdp:sync-received-invoices {--user= : User id or email} {--dry-run}';

    protected $description = 'Synchronize incoming invoices from SUPER PDP sandbox for enabled test users.';

    public function handle(SuperPdpReceivedInvoiceSyncService $syncService): int
    {
        $query = SuperPdpConnection::query()
            ->with('user')
            ->where('environment', 'sandbox')
            ->where('status', SuperPdpConnection::STATUS_CONNECTED)
            ->where('receiving_invoices_enabled', true);

        if ($userFilter = $this->option('user')) {
            $query->whereHas('user', function ($query) use ($userFilter) {
                $query->where('id', $userFilter)
                    ->orWhere('email', $userFilter);
            });
        }

        $checked = 0;
        $synced = 0;
        $skipped = 0;
        $errors = 0;

        foreach ($query->cursor() as $connection) {
            $checked++;

            if (! SuperPdpFeature::enabledFor($connection->user)) {
                $skipped++;
                $this->line("Connection {$connection->id}: skipped, feature gate disabled.");
                continue;
            }

            if ($this->option('dry-run')) {
                $skipped++;
                $this->line("Connection {$connection->id}: would sync incoming invoices.");
                continue;
            }

            try {
                $count = $syncService->sync($connection);
                $synced += $count;
                $this->line("Connection {$connection->id}: synced {$count} invoice(s).");
            } catch (\Throwable $e) {
                $errors++;
                $this->error("Connection {$connection->id}: {$e->getMessage()}");
            }
        }

        $this->table(['Checked', 'Synced invoices', 'Skipped', 'Errors'], [[
            $checked,
            $synced,
            $skipped,
            $errors,
        ]]);

        return $errors > 0 ? self::FAILURE : self::SUCCESS;
    }
}
