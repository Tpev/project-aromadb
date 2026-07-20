<?php

namespace App\Console\Commands;

use App\Services\AccountDataExportService;
use Illuminate\Console\Command;

class PurgeAccountDataExports extends Command
{
    protected $signature = 'account:exports:purge
        {--days=7 : Supprime uniquement les archives generees plus anciennes que ce delai}';

    protected $description = 'Supprime les anciennes archives privees d export de comptes.';

    public function handle(AccountDataExportService $exporter): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = $exporter->purgeExpiredExports($days);

        $this->info($deleted.' archive(s) d export supprimee(s).');

        return self::SUCCESS;
    }
}
