<?php

namespace App\Services\SuperPdp;

use App\Models\SuperPdpConnection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class SuperPdpApiClient
{
    public function __construct(private readonly SuperPdpOAuthService $oauthService)
    {
    }

    public function currentCompany(SuperPdpConnection $connection): array
    {
        return $this->authorized($connection)
            ->get($this->url('/v1.beta/companies/me'))
            ->throw()
            ->json();
    }

    public function listIncomingInvoices(SuperPdpConnection $connection, int $limit = 50): array
    {
        return $this->authorized($connection)
            ->get($this->url('/v1.beta/invoices'), [
                'direction' => 'in',
                'order' => 'desc',
                'limit' => $limit,
                'expand[]' => ['en_invoice', 'events'],
            ])
            ->throw()
            ->json();
    }

    public function invoiceDocument(SuperPdpConnection $connection, int $invoiceId, string $format = 'factur-x'): Response
    {
        return $this->authorized($connection)
            ->get($this->url('/v1.beta/invoices/' . $invoiceId), [
                'format' => $format,
            ])
            ->throw();
    }

    private function authorized(SuperPdpConnection $connection): \Illuminate\Http\Client\PendingRequest
    {
        $token = $this->accessToken($connection);

        return Http::withToken($token)
            ->acceptJson()
            ->timeout(30);
    }

    private function accessToken(SuperPdpConnection $connection): string
    {
        if ($connection->token_expires_at && $connection->token_expires_at->gt(now()->addMinutes(2)) && filled($connection->access_token)) {
            return $connection->access_token;
        }

        $connection = $this->oauthService->refreshAccessToken($connection);

        if (blank($connection->access_token)) {
            throw new RuntimeException('Unable to refresh SUPER PDP access token.');
        }

        return $connection->access_token;
    }

    private function url(string $path): string
    {
        return rtrim((string) config('services.super_pdp.base_url'), '/') . '/' . ltrim($path, '/');
    }
}
