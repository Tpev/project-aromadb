<?php

namespace App\Services;

use GuzzleHttp\Client;

class IpInfoService
{
    protected $client;
    protected $token;

    public function __construct()
    {
        $this->client = new Client();
        $this->token = env('IPINFO_TOKEN'); // Store your token in .env
    }

    /**
     * Get the country by IP address
     */
    public function getCountryByIp($ip)
    {
        try {
            $response = $this->client->get("http://ipinfo.io/{$ip}/json", [
                'headers' => [
                    'Authorization' => "Bearer {$this->token}"
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            return $data['country'] ?? null;
        } catch (\Exception $e) {
            return null; // Handle errors or fallback to default behavior
        }
    }
}
