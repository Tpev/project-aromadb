<?php

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function sortingTherapist(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'license_status' => 'active',
    ], $attributes));
}

function sortingClient(User $therapist, array $attributes): ClientProfile
{
    return ClientProfile::create(array_merge([
        'user_id' => $therapist->id,
        'first_name' => 'Client',
        'last_name' => 'Test',
    ], $attributes));
}

test('client list sorting is deterministic and isolated to the authenticated practitioner', function () {
    $therapist = sortingTherapist(['email' => 'client-sort@example.test']);
    $otherTherapist = sortingTherapist(['email' => 'other-client-sort@example.test']);

    sortingClient($therapist, [
        'first_name' => 'Zoé',
        'last_name' => 'Alpha',
        'email' => 'zulu@example.test',
        'phone' => '0300000000',
    ]);
    sortingClient($therapist, [
        'first_name' => 'Yann',
        'last_name' => 'Bravo',
        'email' => 'alpha@example.test',
        'phone' => '0200000000',
    ]);
    sortingClient($therapist, [
        'first_name' => 'Ana',
        'last_name' => 'Charlie',
        'email' => 'mike@example.test',
        'phone' => '0100000000',
    ]);
    sortingClient($therapist, [
        'first_name' => 'Nina',
        'last_name' => 'NoEmail',
        'email' => null,
        'phone' => null,
    ]);
    sortingClient($otherTherapist, [
        'first_name' => 'Intrus',
        'last_name' => 'Externe',
        'email' => 'foreign@example.test',
        'phone' => '0000000000',
    ]);

    $this->actingAs($therapist)
        ->get(route('client_profiles.index'))
        ->assertOk()
        ->assertSeeInOrder(['Zoé Alpha', 'Yann Bravo', 'Ana Charlie'])
        ->assertDontSee('Intrus Externe')
        ->assertViewHas('clientSort', 'name')
        ->assertViewHas('clientSortDirection', 'asc');

    $this->get(route('client_profiles.index', ['sort' => 'name', 'direction' => 'desc']))
        ->assertOk()
        ->assertSeeInOrder(['Ana Charlie', 'Yann Bravo', 'Zoé Alpha']);

    $this->get(route('client_profiles.index', ['sort' => 'email', 'direction' => 'asc']))
        ->assertOk()
        ->assertSeeInOrder(['alpha@example.test', 'mike@example.test', 'zulu@example.test', 'Nina NoEmail']);

    $this->get(route('client_profiles.index', ['sort' => 'phone', 'direction' => 'desc']))
        ->assertOk()
        ->assertSeeInOrder(['0300000000', '0200000000', '0100000000', 'Nina NoEmail']);
});

test('invalid client sort parameters fall back safely and mobile exposes sorting controls', function () {
    $therapist = sortingTherapist(['email' => 'client-sort-fallback@example.test']);

    sortingClient($therapist, [
        'first_name' => 'Béatrice',
        'last_name' => 'Zulu',
        'email' => 'beatrice@example.test',
    ]);
    sortingClient($therapist, [
        'first_name' => 'Alex',
        'last_name' => 'Alpha',
        'email' => 'alex@example.test',
    ]);

    $this->actingAs($therapist)
        ->get(route('client_profiles.index', ['sort' => 'not-a-column', 'direction' => 'sideways']))
        ->assertOk()
        ->assertSeeInOrder(['Alex Alpha', 'Béatrice Zulu'])
        ->assertViewHas('clientSort', 'name')
        ->assertViewHas('clientSortDirection', 'asc');

    $this->get(route('mobile.clients.index', ['sort' => 'email', 'direction' => 'desc']))
        ->assertOk()
        ->assertViewIs('mobile.clients.index')
        ->assertSeeInOrder(['beatrice@example.test', 'alex@example.test'])
        ->assertSee('Trier les clients par')
        ->assertViewHas('clientSort', 'email')
        ->assertViewHas('clientSortDirection', 'desc');
});
