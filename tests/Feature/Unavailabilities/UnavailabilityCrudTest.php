<?php

use App\Models\Unavailability;
use App\Models\User;
use Carbon\Carbon;

beforeEach(function () {
    // Keep middleware stack intact; send valid CSRF tokens in write requests.
});

test('authenticated user can create an unavailability', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->post(route('unavailabilities.store'), [
            '_token' => 'csrf-token',
            'start_date' => '2026-04-20',
            'start_time' => '09:00',
            'end_date' => '2026-04-20',
            'end_time' => '11:00',
            'reason' => 'Conges',
        ]);

    $response->assertRedirect(route('unavailabilities.index'));

    $this->assertDatabaseHas('unavailabilities', [
        'user_id' => $user->id,
        'reason' => 'Conges',
    ]);
});

test('index page shows edit action for unavailability rows', function () {
    $user = User::factory()->create();

    $unavailability = Unavailability::create([
        'user_id' => $user->id,
        'start_date' => Carbon::parse('2026-05-01 08:00'),
        'end_date' => Carbon::parse('2026-05-01 10:00'),
        'reason' => 'Formation',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('unavailabilities.index'));

    $response->assertOk();
    $response->assertSee(route('unavailabilities.edit', $unavailability->id), false);
    $response->assertSee(route('unavailabilities.destroy-many'), false);
    $response->assertSee('name="unavailability_ids[]"', false);
});

test('owner can view edit page and update an unavailability', function () {
    $user = User::factory()->create();

    $unavailability = Unavailability::create([
        'user_id' => $user->id,
        'start_date' => Carbon::parse('2026-04-21 10:00'),
        'end_date' => Carbon::parse('2026-04-21 12:00'),
        'reason' => 'Ancienne raison',
    ]);

    $editResponse = $this
        ->actingAs($user)
        ->get(route('unavailabilities.edit', $unavailability->id));

    $editResponse->assertOk();
    $editResponse->assertSee('Modifier');

    $updateResponse = $this
        ->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->put(route('unavailabilities.update', $unavailability->id), [
            '_token' => 'csrf-token',
            'start_date' => '2026-04-22',
            'start_time' => '13:00',
            'end_date' => '2026-04-22',
            'end_time' => '14:30',
            'reason' => 'Raison mise a jour',
        ]);

    $updateResponse->assertRedirect(route('unavailabilities.index'));

    $this->assertDatabaseHas('unavailabilities', [
        'id' => $unavailability->id,
        'user_id' => $user->id,
        'reason' => 'Raison mise a jour',
        'start_date' => '2026-04-22 13:00:00',
        'end_date' => '2026-04-22 14:30:00',
    ]);
});

test('update rejects end datetime before or equal start datetime', function () {
    $user = User::factory()->create();

    $unavailability = Unavailability::create([
        'user_id' => $user->id,
        'start_date' => Carbon::parse('2026-04-23 09:00'),
        'end_date' => Carbon::parse('2026-04-23 10:00'),
        'reason' => 'Test',
    ]);

    $response = $this
        ->actingAs($user)
        ->from(route('unavailabilities.edit', $unavailability->id))
        ->withSession(['_token' => 'csrf-token'])
        ->put(route('unavailabilities.update', $unavailability->id), [
            '_token' => 'csrf-token',
            'start_date' => '2026-04-23',
            'start_time' => '11:00',
            'end_date' => '2026-04-23',
            'end_time' => '11:00',
            'reason' => 'Invalide',
        ]);

    $response->assertSessionHasErrors('end_time');

    $this->assertDatabaseHas('unavailabilities', [
        'id' => $unavailability->id,
        'reason' => 'Test',
    ]);
});

test('non owner cannot edit, update or delete another users unavailability', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();

    $unavailability = Unavailability::create([
        'user_id' => $owner->id,
        'start_date' => Carbon::parse('2026-04-24 10:00'),
        'end_date' => Carbon::parse('2026-04-24 12:00'),
        'reason' => 'Owner reason',
    ]);

    $this->actingAs($other)
        ->get(route('unavailabilities.edit', $unavailability->id))
        ->assertRedirect(route('unavailabilities.index'));

    $this->actingAs($other)
        ->withSession(['_token' => 'csrf-token'])
        ->put(route('unavailabilities.update', $unavailability->id), [
            '_token' => 'csrf-token',
            'start_date' => '2026-04-25',
            'start_time' => '08:00',
            'end_date' => '2026-04-25',
            'end_time' => '09:00',
            'reason' => 'Should not update',
        ])
        ->assertRedirect(route('unavailabilities.index'));

    $this->assertDatabaseHas('unavailabilities', [
        'id' => $unavailability->id,
        'reason' => 'Owner reason',
    ]);

    $this->actingAs($other)
        ->withSession(['_token' => 'csrf-token'])
        ->delete(route('unavailabilities.destroy', $unavailability->id), [
            '_token' => 'csrf-token',
        ])
        ->assertRedirect(route('unavailabilities.index'));

    $this->assertDatabaseHas('unavailabilities', [
        'id' => $unavailability->id,
        'user_id' => $owner->id,
    ]);
});

test('owner can delete unavailability', function () {
    $user = User::factory()->create();

    $unavailability = Unavailability::create([
        'user_id' => $user->id,
        'start_date' => Carbon::parse('2026-04-26 15:00'),
        'end_date' => Carbon::parse('2026-04-26 16:00'),
        'reason' => 'Suppression',
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->delete(route('unavailabilities.destroy', $unavailability->id), [
            '_token' => 'csrf-token',
        ]);

    $response->assertRedirect(route('unavailabilities.index'));

    $this->assertDatabaseMissing('unavailabilities', [
        'id' => $unavailability->id,
    ]);
});

test('owner can delete several selected unavailabilities at once', function () {
    $user = User::factory()->create();
    $selected = collect([
        ['start' => '2026-04-27 09:00', 'end' => '2026-04-27 10:00', 'reason' => 'Congé 1'],
        ['start' => '2026-04-28 09:00', 'end' => '2026-04-28 10:00', 'reason' => 'Congé 2'],
    ])->map(fn (array $data) => Unavailability::create([
        'user_id' => $user->id,
        'start_date' => Carbon::parse($data['start']),
        'end_date' => Carbon::parse($data['end']),
        'reason' => $data['reason'],
    ]));
    $unselected = Unavailability::create([
        'user_id' => $user->id,
        'start_date' => Carbon::parse('2026-04-29 09:00'),
        'end_date' => Carbon::parse('2026-04-29 10:00'),
        'reason' => 'À conserver',
    ]);

    $response = $this
        ->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->delete(route('unavailabilities.destroy-many'), [
            '_token' => 'csrf-token',
            'unavailability_ids' => $selected->pluck('id')->all(),
        ]);

    $response->assertRedirect(route('unavailabilities.index'));
    $response->assertSessionHas('success', '2 indisponibilités supprimées avec succès.');

    foreach ($selected as $unavailability) {
        $this->assertDatabaseMissing('unavailabilities', ['id' => $unavailability->id]);
    }
    $this->assertDatabaseHas('unavailabilities', ['id' => $unselected->id]);
});

test('bulk deletion never deletes another users unavailability', function () {
    $owner = User::factory()->create();
    $other = User::factory()->create();
    $owned = Unavailability::create([
        'user_id' => $owner->id,
        'start_date' => Carbon::parse('2026-04-30 09:00'),
        'end_date' => Carbon::parse('2026-04-30 10:00'),
        'reason' => 'Owned',
    ]);
    $foreign = Unavailability::create([
        'user_id' => $other->id,
        'start_date' => Carbon::parse('2026-04-30 11:00'),
        'end_date' => Carbon::parse('2026-04-30 12:00'),
        'reason' => 'Foreign',
    ]);

    $this->actingAs($owner)
        ->withSession(['_token' => 'csrf-token'])
        ->delete(route('unavailabilities.destroy-many'), [
            '_token' => 'csrf-token',
            'unavailability_ids' => [$owned->id, $foreign->id],
        ])
        ->assertRedirect(route('unavailabilities.index'));

    $this->assertDatabaseMissing('unavailabilities', ['id' => $owned->id]);
    $this->assertDatabaseHas('unavailabilities', [
        'id' => $foreign->id,
        'user_id' => $other->id,
    ]);
});

test('bulk deletion requires at least one selected unavailability', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->withSession(['_token' => 'csrf-token'])
        ->from(route('unavailabilities.index'))
        ->delete(route('unavailabilities.destroy-many'), [
            '_token' => 'csrf-token',
        ])
        ->assertRedirect(route('unavailabilities.index'))
        ->assertSessionHasErrors('unavailability_ids');
});
