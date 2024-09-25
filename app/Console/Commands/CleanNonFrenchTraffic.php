<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PageViewLog;
use App\Services\IpInfoService; // Assuming you're using this service for geolocation
use DB;

class CleanNonFrenchTraffic extends Command
{
    protected $signature = 'traffic:clean-non-france';
    protected $description = 'Remove all page view logs that are not from France';

    protected $ipInfoService;

    public function __construct(IpInfoService $ipInfoService)
    {
        parent::__construct();
        $this->ipInfoService = $ipInfoService;
    }

    public function handle()
    {
        $this->info('Starting to clean non-France traffic from PageViewLogs...');

        // Process entries in batches to avoid memory exhaustion
        PageViewLog::whereNull('country')->chunk(100, function ($logs) {
            foreach ($logs as $log) {
                // Get the country from the IP address
                $country = $this->ipInfoService->getCountryByIp($log->ip_address);

                // If the country is not France ('FR'), delete the entry
                if ($country !== 'FR') {
                    $this->info("Deleting entry with IP {$log->ip_address} (Country: {$country})");
                    $log->delete();
                } else {
                    // Optionally, update the country for France entries
                    $log->update(['country' => 'FR']);
                }
            }
        });

        $this->info('Finished cleaning non-France traffic from PageViewLogs.');
    }
}
