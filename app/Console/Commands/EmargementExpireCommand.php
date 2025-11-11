<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\EmargementService;

class EmargementExpireCommand extends Command
{
    protected $signature = 'emargement:expire';
    protected $description = 'Expire pending emargements older than 14 days';

    public function handle(EmargementService $service): int
    {
        $count = $service->expireOverdue();
        $this->info("Expired {$count} emargements.");
        return self::SUCCESS;
    }
}
