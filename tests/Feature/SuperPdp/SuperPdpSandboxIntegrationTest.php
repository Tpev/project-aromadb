<?php

use App\Models\SuperPdpConnection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

function superPdpTestConfig(): void
{
    config([
        'services.super_pdp.environment' => 'sandbox',
        'services.super_pdp.base_url' => 'https://api.superpdp.tech',
        'services.super_pdp.authorize_url' => 'https://api.superpdp.tech/oauth2/authorize',
        'services.super_pdp.token_url' => 'https://api.superpdp.tech/oauth2/token',
        'services.super_pdp.revoke_url' => 'https://api.superpdp.tech/oauth2/revoke',
        'services.super_pdp.client_id' => 'sandbox-client-id',
        'services.super_pdp.client_secret' => 'sandbox-client-secret',
        'services.super_pdp.redirect_uri' => 'https://aromamade.test/super-pdp/oauth/callback',
        'services.super_pdp.allowed_emails' => ['john.satch00@gmail.com'],
    ]);
}

function superPdpTestUser(array $overrides = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'email' => 'john.satch00@gmail.com',
        'company_name' => 'AromaMade Test',
    ], $overrides));
}

test('super pdp card and routes are hidden for non allowed users', function () {
    superPdpTestConfig();

    $user = superPdpTestUser([
        'email' => 'not-allowed@example.test',
    ]);

    $this->actingAs($user)
        ->get(route('profile.editCompanyInfo'))
        ->assertOk()
        ->assertDontSee('SUPER PDP');

    $this->actingAs($user)
        ->post(route('super-pdp.connect'))
        ->assertNotFound();
});

test('allowed sandbox user can see the super pdp onboarding card', function () {
    superPdpTestConfig();

    $user = superPdpTestUser();

    $this->actingAs($user)
        ->get(route('profile.editCompanyInfo'))
        ->assertOk()
        ->assertSee('Facturation électronique avec SUPER PDP')
        ->assertSee('Démarrer l’onboarding SUPER PDP');
});

test('starting onboarding redirects to the super pdp authorization endpoint', function () {
    superPdpTestConfig();

    $user = superPdpTestUser();

    $response = $this->actingAs($user)
        ->post(route('super-pdp.connect'), [
            'receive_in_app' => '1',
        ]);

    $response->assertRedirect();

    $location = $response->headers->get('Location');
    parse_str(parse_url($location, PHP_URL_QUERY), $query);

    expect(str_starts_with($location, 'https://api.superpdp.tech/oauth2/authorize?'))->toBeTrue();
    expect($query['response_type'])->toBe('code');
    expect($query['client_id'])->toBe('sandbox-client-id');
    expect($query['redirect_uri'])->toBe('https://aromamade.test/super-pdp/oauth/callback');
    expect($query['login_hint'])->toBe('john.satch00@gmail.com');
    expect($query['superpdp_send_and_receive'])->toBe('receive');
    expect($query['superpdp_only_future'])->toBe('true');
    expect($query['state'])->not->toBeEmpty();

    $connection = SuperPdpConnection::first();

    expect($connection->status)->toBe(SuperPdpConnection::STATUS_AUTHORIZATION_STARTED);
    expect($connection->receiving_invoices_enabled)->toBeTrue();
});

test('oauth callback stores encrypted tokens and company metadata', function () {
    superPdpTestConfig();

    Http::fake([
        'https://api.superpdp.tech/oauth2/token' => Http::response([
            'access_token' => 'access-token',
            'refresh_token' => 'refresh-token',
            'expires_in' => 1800,
            'token_type' => 'Bearer',
        ]),
        'https://api.superpdp.tech/v1.beta/companies/me' => Http::response([
            'id' => 42,
            'env' => 'sandbox',
            'formal_name' => 'Burger Queen',
            'number' => '000000001',
            'number_scheme' => 'sandbox',
        ]),
    ]);

    $user = superPdpTestUser();

    $this->actingAs($user)
        ->withSession([
            'super_pdp.oauth_state' => 'state-ok',
            'super_pdp.receive_in_app' => true,
        ])
        ->get(route('super-pdp.oauth.callback', [
            'code' => 'authorization-code',
            'state' => 'state-ok',
        ]))
        ->assertRedirect(route('profile.editCompanyInfo'))
        ->assertSessionHas('success');

    $connection = SuperPdpConnection::first();

    expect($connection->status)->toBe(SuperPdpConnection::STATUS_CONNECTED);
    expect($connection->access_token)->toBe('access-token');
    expect($connection->refresh_token)->toBe('refresh-token');
    expect($connection->getRawOriginal('access_token'))->not->toBe('access-token');
    expect($connection->super_pdp_company_id)->toBe(42);
    expect($connection->super_pdp_company_name)->toBe('Burger Queen');
    expect($connection->receiving_invoices_enabled)->toBeTrue();
});

test('received invoice inbox can sync incoming sandbox invoices', function () {
    superPdpTestConfig();

    $user = superPdpTestUser();
    $connection = SuperPdpConnection::create([
        'user_id' => $user->id,
        'environment' => 'sandbox',
        'status' => SuperPdpConnection::STATUS_CONNECTED,
        'receiving_invoices_enabled' => true,
        'access_token' => 'valid-access-token',
        'refresh_token' => 'valid-refresh-token',
        'token_expires_at' => now()->addHour(),
        'super_pdp_company_id' => 42,
        'super_pdp_company_name' => 'Burger Queen',
    ]);

    Http::fake([
        'https://api.superpdp.tech/v1.beta/invoices*' => Http::response([
            'count' => 1,
            'has_after' => false,
            'has_before' => false,
            'data' => [[
                'id' => 777,
                'company_id' => 42,
                'direction' => 'in',
                'created_at' => '2026-06-26T08:00:00Z',
                'en_invoice' => [
                    'number' => 'INV-2026-001',
                    'issue_date' => '2026-06-26',
                    'currency_code' => 'EUR',
                    'seller' => ['name' => 'Tricatel'],
                    'buyer' => ['name' => 'Burger Queen'],
                    'totals' => ['total_with_vat' => '120.50'],
                ],
                'events' => [[
                    'id' => 10,
                    'invoice_id' => 777,
                    'status_code' => 'fr:200',
                    'status_text' => 'Déposée',
                    'created_at' => '2026-06-26T08:05:00Z',
                ]],
            ]],
        ]),
    ]);

    $this->actingAs($user)
        ->get(route('super-pdp.received-invoices.index', ['sync' => 1]))
        ->assertRedirect(route('super-pdp.received-invoices.index'))
        ->assertSessionHas('success', '1 facture(s) reçue(s) synchronisée(s).');

    $this->assertDatabaseHas('super_pdp_received_invoices', [
        'connection_id' => $connection->id,
        'user_id' => $user->id,
        'super_pdp_invoice_id' => 777,
        'invoice_number' => 'INV-2026-001',
        'seller_name' => 'Tricatel',
        'latest_event_text' => 'Déposée',
    ]);

    $this->actingAs($user)
        ->get(route('super-pdp.received-invoices.index'))
        ->assertOk()
        ->assertSee('INV-2026-001')
        ->assertSee('Tricatel');
});
