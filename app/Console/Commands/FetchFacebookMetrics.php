<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\FacebookMetric;

class FetchFacebookMetrics extends Command
{
    protected $signature = 'facebook:fetch-metrics';
    protected $description = 'Fetch Facebook page metrics and store them in the database';

    public function handle()
    {
        // Define the URL (ideally, store the access token and page ID in .env)
        $url = 'https://graph.facebook.com/v21.0/110148498636200';
        $accessToken = 'EAAYZAaPk9UPIBO4FNGZAqxlQPZAozEnyvDptADyjfLc39fuTnm3gLg5kA1VKhKBj4ZAgCd67q8wnXecaSm5ZAvOol9sdepXJ90QbGiebFGLXrnxT05cQzuJBrLs2zXazR3L9wPzfV6rN2rqbdWMCNAQ2bAa5uZBzrbJDWN1cMONYgXsQjNGgEAZB5sJSt6uuyeck3MaSIIa'; // Store this in your .env file
        $fields = 'fan_count,followers_count';

        // Make the GET request
        $response = Http::get($url, [
            'fields' => $fields,
            'access_token' => $accessToken,
        ]);

        if ($response->successful()) {
            $data = $response->json();

            // Save the data to the database
            FacebookMetric::create([
                'fan_count' => $data['fan_count'] ?? null,
                'followers_count' => $data['followers_count'] ?? null,
                'page_id' => $data['id'] ?? null,
            ]);

            $this->info('Facebook metrics fetched and stored successfully.');
        } else {
            $this->error('Failed to fetch Facebook metrics.');
            // Optionally, log the error details
            \Log::error('Failed to fetch Facebook metrics.', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
        }
    }
}

