<?php

namespace App\Services\SuperPdp;

use App\Models\SuperPdpConnection;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class SuperPdpOAuthService
{
    private const SESSION_STATE = 'super_pdp.oauth_state';
    private const SESSION_RECEIVE_IN_APP = 'super_pdp.receive_in_app';

    public function isConfigured(): bool
    {
        return filled(config('services.super_pdp.client_id'))
            && filled(config('services.super_pdp.client_secret'));
    }

    public function redirectUri(): string
    {
        return config('services.super_pdp.redirect_uri') ?: route('super-pdp.oauth.callback');
    }

    public function authorizationUrl(User $user, bool $receiveInApp): string
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('SUPER PDP sandbox OAuth credentials are missing.');
        }

        $state = Str::random(48);

        session([
            self::SESSION_STATE => $state,
            self::SESSION_RECEIVE_IN_APP => $receiveInApp,
        ]);

        $query = [
            'response_type' => 'code',
            'client_id' => config('services.super_pdp.client_id'),
            'redirect_uri' => $this->redirectUri(),
            'state' => $state,
            'login_hint' => $user->email,
            'superpdp_send_and_receive' => $receiveInApp ? 'receive' : 'send',
            'superpdp_only_future' => 'true',
        ];

        return rtrim((string) config('services.super_pdp.authorize_url'), '?') . '?' . http_build_query($query);
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function exchangeAuthorizationCode(User $user, string $code, string $state): SuperPdpConnection
    {
        if (! hash_equals((string) session(self::SESSION_STATE), $state)) {
            throw new RuntimeException('Invalid SUPER PDP OAuth state.');
        }

        $receiveInApp = (bool) session(self::SESSION_RECEIVE_IN_APP, false);

        $tokenPayload = $this->tokenRequest([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri(),
        ]);

        $connection = $this->connectionFor($user);
        $this->fillTokenData($connection, $tokenPayload);

        $connection->fill([
            'status' => SuperPdpConnection::STATUS_CONNECTED,
            'receiving_invoices_enabled' => $receiveInApp,
            'last_error' => null,
            'revoked_at' => null,
            'connected_at' => now(),
        ]);

        $company = app(SuperPdpApiClient::class)->currentCompany($connection);

        $connection->fill([
            'super_pdp_company_id' => data_get($company, 'id'),
            'super_pdp_company_name' => data_get($company, 'formal_name'),
            'super_pdp_company_number' => data_get($company, 'number'),
            'super_pdp_company_number_scheme' => data_get($company, 'number_scheme'),
            'metadata' => [
                'company' => $company,
                'oauth_connected_at' => now()->toIso8601String(),
            ],
        ])->save();

        session()->forget([self::SESSION_STATE, self::SESSION_RECEIVE_IN_APP]);

        return $connection;
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function refreshAccessToken(SuperPdpConnection $connection): SuperPdpConnection
    {
        if (blank($connection->refresh_token)) {
            throw new RuntimeException('Missing SUPER PDP refresh token.');
        }

        $tokenPayload = $this->tokenRequest([
            'grant_type' => 'refresh_token',
            'refresh_token' => $connection->refresh_token,
        ]);

        $this->fillTokenData($connection, $tokenPayload);
        $connection->last_error = null;
        $connection->save();

        return $connection->refresh();
    }

    public function markAuthorizationStarted(User $user, bool $receiveInApp): SuperPdpConnection
    {
        $connection = $this->connectionFor($user);

        $connection->fill([
            'status' => SuperPdpConnection::STATUS_AUTHORIZATION_STARTED,
            'receiving_invoices_enabled' => $receiveInApp,
            'last_error' => null,
            'revoked_at' => null,
        ])->save();

        return $connection;
    }

    public function connectionFor(User $user): SuperPdpConnection
    {
        return SuperPdpConnection::firstOrCreate([
            'user_id' => $user->id,
            'environment' => 'sandbox',
        ], [
            'status' => SuperPdpConnection::STATUS_NOT_STARTED,
        ]);
    }

    public function markError(User $user, string $message): SuperPdpConnection
    {
        $connection = $this->connectionFor($user);

        $connection->fill([
            'status' => SuperPdpConnection::STATUS_ERROR,
            'last_error' => Str::limit($message, 2000, ''),
        ])->save();

        return $connection;
    }

    public function disconnect(SuperPdpConnection $connection): void
    {
        $refreshToken = $connection->refresh_token;

        if ($refreshToken && $this->isConfigured()) {
            try {
                Http::asForm()
                    ->timeout(15)
                    ->post((string) config('services.super_pdp.revoke_url'), [
                        'token' => $refreshToken,
                        'client_id' => config('services.super_pdp.client_id'),
                        'client_secret' => config('services.super_pdp.client_secret'),
                    ]);
            } catch (\Throwable) {
                // Local disconnect should still succeed even if the remote revoke endpoint is unavailable.
            }
        }

        $connection->fill([
            'status' => SuperPdpConnection::STATUS_REVOKED,
            'access_token' => null,
            'refresh_token' => null,
            'token_expires_at' => null,
            'token_type' => null,
            'last_error' => null,
            'revoked_at' => now(),
        ])->save();
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function tokenRequest(array $payload): array
    {
        $response = Http::asForm()
            ->acceptJson()
            ->timeout(30)
            ->post((string) config('services.super_pdp.token_url'), array_merge($payload, [
                'client_id' => config('services.super_pdp.client_id'),
                'client_secret' => config('services.super_pdp.client_secret'),
            ]));

        $response->throw();

        return $response->json();
    }

    private function fillTokenData(SuperPdpConnection $connection, array $tokenPayload): void
    {
        $connection->fill([
            'access_token' => data_get($tokenPayload, 'access_token'),
            'refresh_token' => data_get($tokenPayload, 'refresh_token', $connection->refresh_token),
            'token_type' => data_get($tokenPayload, 'token_type', 'Bearer'),
            'scope' => data_get($tokenPayload, 'scope'),
            'token_expires_at' => now()->addSeconds((int) data_get($tokenPayload, 'expires_in', 1800)),
        ]);
    }
}
