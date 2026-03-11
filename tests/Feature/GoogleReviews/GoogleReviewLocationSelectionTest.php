<?php

use App\Models\GoogleBusinessAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;

test('google reviews page shows a location selector when multiple establishments are available', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    GoogleBusinessAccount::create([
        'user_id' => $therapist->id,
        'account_id' => 'acc_123',
        'account_display_name' => 'Compte Test',
        'location_id' => 'loc_1',
        'location_title' => 'Cabinet Principal',
        'access_token' => 'token_ok',
        'access_token_expires_at' => now()->addHour(),
    ]);

    Http::fake([
        'https://mybusinessaccountmanagement.googleapis.com/*' => Http::response([
            'accounts' => [
                ['name' => 'accounts/acc_123', 'accountName' => 'Cabinet Paris'],
                ['name' => 'accounts/acc_456', 'accountName' => 'Cabinet Lyon'],
            ],
        ], 200),
        'https://mybusinessbusinessinformation.googleapis.com/v1/accounts/acc_123/locations*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_1', 'title' => 'Cabinet Principal'],
            ],
        ], 200),
        'https://mybusinessbusinessinformation.googleapis.com/v1/accounts/acc_456/locations*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_9', 'title' => 'Cabinet Bellecour'],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($therapist)->get(route('pro.google-reviews.index'));

    $response->assertOk();
    $response->assertSee('google_location_id', false);
    $response->assertSee('name="location_selection"', false);
    $response->assertSee('Cabinet Principal (Cabinet Paris)');
    $response->assertSee('Cabinet Bellecour (Cabinet Lyon)');
});

test('sync rejects a location id that is not in the fetched Google establishments list', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $account = GoogleBusinessAccount::create([
        'user_id' => $therapist->id,
        'account_id' => 'acc_123',
        'account_display_name' => 'Compte Test',
        'location_id' => 'loc_1',
        'location_title' => 'Cabinet Principal',
        'access_token' => 'token_ok',
        'access_token_expires_at' => now()->addHour(),
    ]);

    Http::fake([
        'https://mybusinessaccountmanagement.googleapis.com/*' => Http::response([
            'accounts' => [
                ['name' => 'accounts/acc_123', 'accountName' => 'Compte Test'],
            ],
        ], 200),
        'https://mybusinessbusinessinformation.googleapis.com/*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_1', 'title' => 'Cabinet Principal'],
                ['name' => 'locations/loc_2', 'title' => 'Cabinet Secondaire'],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.google-reviews.sync'), [
        'location_selection' => 'acc_123|loc_unknown',
    ]);

    $response->assertRedirect(route('pro.google-reviews.index'));
    $response->assertSessionHas('error');

    $account->refresh();
    expect($account->location_id)->toBe('loc_1');
    expect($account->location_title)->toBe('Cabinet Principal');
});

test('sync rejects explicit location selection when establishments list cannot be verified', function () {
    $therapist = User::factory()->create([
        'is_therapist' => true,
    ]);

    $account = GoogleBusinessAccount::create([
        'user_id' => $therapist->id,
        'account_id' => 'acc_123',
        'account_display_name' => 'Compte Test',
        'location_id' => 'loc_1',
        'location_title' => 'Cabinet Principal',
        'access_token' => 'token_ok',
        'access_token_expires_at' => now()->addHour(),
    ]);

    Http::fake([
        'https://mybusinessaccountmanagement.googleapis.com/*' => Http::response([], 500),
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.google-reviews.sync'), [
        'location_selection' => 'acc_123|loc_2',
    ]);

    $response->assertRedirect(route('pro.google-reviews.index'));
    $response->assertSessionHas('error');

    $account->refresh();
    expect($account->location_id)->toBe('loc_1');
});
