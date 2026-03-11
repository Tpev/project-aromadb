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
        'https://mybusinessbusinessinformation.googleapis.com/*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_1', 'title' => 'Cabinet Principal'],
                ['name' => 'locations/loc_2', 'title' => 'Cabinet Secondaire'],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($therapist)->get(route('pro.google-reviews.index'));

    $response->assertOk();
    $response->assertSee('google_location_id', false);
    $response->assertSee('name="location_id"', false);
    $response->assertSee('Cabinet Principal');
    $response->assertSee('Cabinet Secondaire');
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
        'https://mybusinessbusinessinformation.googleapis.com/*' => Http::response([
            'locations' => [
                ['name' => 'locations/loc_1', 'title' => 'Cabinet Principal'],
                ['name' => 'locations/loc_2', 'title' => 'Cabinet Secondaire'],
            ],
        ], 200),
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.google-reviews.sync'), [
        'location_id' => 'loc_unknown',
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
        'https://mybusinessbusinessinformation.googleapis.com/*' => Http::response([], 500),
    ]);

    $response = $this->actingAs($therapist)->post(route('pro.google-reviews.sync'), [
        'location_id' => 'loc_2',
    ]);

    $response->assertRedirect(route('pro.google-reviews.index'));
    $response->assertSessionHas('error');

    $account->refresh();
    expect($account->location_id)->toBe('loc_1');
});
