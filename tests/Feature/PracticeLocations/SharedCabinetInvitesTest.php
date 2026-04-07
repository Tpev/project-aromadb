<?php

use App\Mail\PracticeLocationInviteMail;
use App\Models\PracticeLocation;
use App\Models\PracticeLocationInvite;
use App\Models\PracticeLocationMember;
use App\Models\User;
use App\Services\CabinetAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('features.shared_cabinets_v1', true);
});

function sharedCabinetTherapist(array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'is_therapist' => true,
        'accept_online_appointments' => true,
    ], $attributes));
}

function sharedCabinetLocation(User $owner, array $attributes = []): PracticeLocation
{
    $location = PracticeLocation::create(array_merge([
        'user_id' => $owner->id,
        'label' => 'Cabinet partagé test',
        'address_line1' => '1 rue des Fleurs',
        'postal_code' => '75001',
        'city' => 'Paris',
        'country' => 'FR',
        'is_primary' => true,
        'is_shared' => true,
        'shared_enabled_at' => now(),
    ], $attributes));

    app(CabinetAccessService::class)->ensureOwnerMembership($location);

    return $location;
}

test('owner can invite an existing therapist who can accept and then see the shared cabinet', function () {
    Mail::fake();

    $owner = sharedCabinetTherapist(['email' => 'owner@example.test']);
    $member = sharedCabinetTherapist(['email' => 'member@example.test']);
    $location = sharedCabinetLocation($owner);

    $this->actingAs($owner)
        ->post(route('practice-locations.invites.store', $location), [
            'email' => $member->email,
        ])
        ->assertRedirect(route('practice-locations.edit', $location));

    $invite = PracticeLocationInvite::query()->first();

    expect($invite)->not->toBeNull();
    expect($invite->status)->toBe(PracticeLocationInvite::STATUS_PENDING);

    Mail::assertQueued(PracticeLocationInviteMail::class, function (PracticeLocationInviteMail $mail) use ($invite) {
        return $mail->invite->is($invite);
    });

    $this->actingAs($member)
        ->post(route('practice-locations.invites.accept', $invite->token))
        ->assertRedirect(route('practice-locations.index'));

    $this->assertDatabaseHas('practice_location_members', [
        'practice_location_id' => $location->id,
        'user_id' => $member->id,
        'role' => 'member',
    ]);

    $this->actingAs($member)
        ->get(route('practice-locations.index'))
        ->assertOk()
        ->assertSee($location->label);
});

test('invited therapist can decline and the cabinet remains inaccessible', function () {
    $owner = sharedCabinetTherapist(['email' => 'owner-decline@example.test']);
    $member = sharedCabinetTherapist(['email' => 'member-decline@example.test']);
    $location = sharedCabinetLocation($owner);

    $this->actingAs($owner)
        ->post(route('practice-locations.invites.store', $location), [
            'email' => $member->email,
        ]);

    $invite = PracticeLocationInvite::query()->firstOrFail();

    $this->actingAs($member)
        ->post(route('practice-locations.invites.decline', $invite->token))
        ->assertRedirect(route('practice-locations.index'));

    $invite->refresh();

    expect($invite->status)->toBe(PracticeLocationInvite::STATUS_DECLINED);

    $this->assertDatabaseMissing('practice_location_members', [
        'practice_location_id' => $location->id,
        'user_id' => $member->id,
    ]);

    $this->actingAs($member)
        ->get(route('practice-locations.index'))
        ->assertOk()
        ->assertDontSee($location->label);
});

test('owner can cancel a pending invitation', function () {
    $owner = sharedCabinetTherapist(['email' => 'owner-cancel@example.test']);
    $member = sharedCabinetTherapist(['email' => 'member-cancel@example.test']);
    $location = sharedCabinetLocation($owner);

    $this->actingAs($owner)
        ->post(route('practice-locations.invites.store', $location), [
            'email' => $member->email,
        ]);

    $invite = PracticeLocationInvite::query()->firstOrFail();

    $this->actingAs($owner)
        ->post(route('practice-locations.invites.cancel', $invite))
        ->assertRedirect(route('practice-locations.edit', $location));

    $invite->refresh();

    expect($invite->status)->toBe(PracticeLocationInvite::STATUS_CANCELLED);
});

test('accepted member cannot access owner only cabinet settings', function () {
    $owner = sharedCabinetTherapist(['email' => 'owner-perms@example.test']);
    $member = sharedCabinetTherapist(['email' => 'member-perms@example.test']);
    $location = sharedCabinetLocation($owner);

    PracticeLocationMember::create([
        'practice_location_id' => $location->id,
        'user_id' => $member->id,
        'role' => 'member',
        'accepted_at' => now(),
        'added_by_user_id' => $owner->id,
    ]);

    $this->actingAs($member)
        ->get(route('practice-locations.edit', $location))
        ->assertForbidden();
});
