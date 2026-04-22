<?php

use App\Mail\CommunityInviteMail;
use App\Models\ClientProfile;
use App\Models\CommunityChannel;
use App\Models\CommunityGroup;
use App\Models\CommunityMember;
use App\Models\CommunityMessage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

function makePractitioner(): User
{
    return User::factory()->create([
        'is_therapist' => true,
        'company_name' => 'Cabinet Demo',
    ]);
}

function makeClientForPractitioner(User $practitioner, array $overrides = []): ClientProfile
{
    return ClientProfile::create(array_merge([
        'user_id' => $practitioner->id,
        'first_name' => 'Alice',
        'last_name' => 'Martin',
        'email' => 'alice-' . uniqid() . '@example.test',
        'password' => 'password',
    ], $overrides));
}

test('praticien can create a community with default channels', function () {
    $practitioner = makePractitioner();

    $this->actingAs($practitioner)
        ->post(route('communities.store'), [
            'name' => 'Programme printemps',
            'description' => 'Un espace prive pour suivre le programme.',
        ])
        ->assertRedirect();

    $community = CommunityGroup::first();

    expect($community)->not->toBeNull()
        ->and($community->name)->toBe('Programme printemps')
        ->and($community->channels()->count())->toBe(2)
        ->and($community->channels()->pluck('name')->all())->toBe(['General', 'Annonces']);
});

test('praticien can invite a client and invited client can accept community access', function () {
    $practitioner = makePractitioner();
    $client = makeClientForPractitioner($practitioner);
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Groupe souffle',
    ]);
    $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);
    Mail::fake();

    $this->actingAs($practitioner)
        ->post(route('communities.members.store', $community), [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect(route('communities.manage', $community));

    $member = CommunityMember::first();

    expect($member)->not->toBeNull()
        ->and($member->status)->toBe(CommunityMember::STATUS_INVITED)
        ->and($member->invitation_email_sent_at)->not->toBeNull();

    Mail::assertQueued(CommunityInviteMail::class, function (CommunityInviteMail $mail) use ($client) {
        return $mail->client->is($client)
            && $mail->requiresAccountSetup === false
            && $mail->joinUrl === route('client.communities.index');
    });

    $this->actingAs($client, 'client')
        ->get(route('client.communities.index'))
        ->assertOk()
        ->assertSee('Groupe souffle');

    $this->actingAs($client, 'client')
        ->post(route('client.communities.accept', $community))
        ->assertRedirect(route('client.communities.show', $community));

    $member->refresh();

    expect($member->status)->toBe(CommunityMember::STATUS_ACTIVE)
        ->and($member->joined_at)->not->toBeNull();
});

test('praticien can resend an invitation email', function () {
    $practitioner = makePractitioner();
    $client = makeClientForPractitioner($practitioner);
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Relances',
    ]);

    Mail::fake();

    $this->actingAs($practitioner)
        ->post(route('communities.members.store', $community), [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect(route('communities.manage', $community));

    $member = CommunityMember::first();

    $this->travel(5)->minutes();

    $this->actingAs($practitioner)
        ->post(route('communities.members.resend', [$community, $member]))
        ->assertRedirect(route('communities.manage', $community));

    $member->refresh();

    expect($member->status)->toBe(CommunityMember::STATUS_INVITED)
        ->and($member->invitation_email_sent_at)->not->toBeNull();

    Mail::assertQueued(CommunityInviteMail::class, 2);
});

test('client cannot post in annonces but praticien can', function () {
    $practitioner = makePractitioner();
    $client = makeClientForPractitioner($practitioner);
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Communaute annonces',
    ]);
    $announcements = $community->channels()->create([
        'name' => 'Annonces',
        'channel_type' => CommunityChannel::TYPE_ANNOUNCEMENTS,
        'position' => 1,
    ]);

    CommunityMember::create([
        'community_group_id' => $community->id,
        'client_profile_id' => $client->id,
        'status' => CommunityMember::STATUS_ACTIVE,
        'invited_at' => now(),
        'joined_at' => now(),
    ]);

    $this->actingAs($client, 'client')
        ->from(route('client.communities.show', $community))
        ->post(route('client.communities.messages.store', $community), [
            'community_channel_id' => $announcements->id,
            'content' => 'Je peux poster ?',
        ])
        ->assertRedirect(route('client.communities.show', $community));

    expect(CommunityMessage::count())->toBe(0);

    $this->actingAs($practitioner)
        ->post(route('communities.messages.store', $community), [
            'community_channel_id' => $announcements->id,
            'content' => 'Bienvenue dans les annonces.',
        ])
        ->assertRedirect(route('communities.show', ['community' => $community->id, 'channel' => $announcements->id]));

    expect(CommunityMessage::count())->toBe(1)
        ->and(CommunityMessage::first()->sender_type)->toBe(CommunityMessage::SENDER_PRACTITIONER);
});

test('client message in discussion notifies the praticien', function () {
    $practitioner = makePractitioner();
    $client = makeClientForPractitioner($practitioner, [
        'first_name' => 'Nina',
        'last_name' => 'Durand',
    ]);
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Groupe questions',
    ]);
    $discussion = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    CommunityMember::create([
        'community_group_id' => $community->id,
        'client_profile_id' => $client->id,
        'status' => CommunityMember::STATUS_ACTIVE,
        'invited_at' => now(),
        'joined_at' => now(),
    ]);

    $this->actingAs($client, 'client')
        ->post(route('client.communities.messages.store', $community), [
            'community_channel_id' => $discussion->id,
            'content' => 'Merci pour les conseils de cette semaine.',
        ])
        ->assertRedirect(route('client.communities.show', ['community' => $community->id, 'channel' => $discussion->id]));

    $practitioner->refresh();

    expect(CommunityMessage::count())->toBe(1)
        ->and($practitioner->notifications()->count())->toBe(1)
        ->and($practitioner->notifications()->first()->data['community_group_id'])->toBe($community->id);
});

test('inviting a client without active espace client sends a setup-based community invite email', function () {
    $practitioner = makePractitioner();
    $client = makeClientForPractitioner($practitioner, [
        'password' => null,
    ]);
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Groupe bienvenue',
    ]);

    Mail::fake();

    $this->actingAs($practitioner)
        ->post(route('communities.members.store', $community), [
            'client_profile_id' => $client->id,
        ])
        ->assertRedirect(route('communities.manage', $community));

    Mail::assertQueued(CommunityInviteMail::class, function (CommunityInviteMail $mail) use ($client) {
        return $mail->client->is($client)
            && $mail->requiresAccountSetup === true
            && str_contains($mail->joinUrl, '/client/setup/')
            && str_contains($mail->joinUrl, 'redirect=');
    });

    $client->refresh();

    expect($client->password_setup_token_hash)->not->toBeNull()
        ->and($client->password_setup_expires_at)->not->toBeNull();
});

test('praticien can share attachment and pin message in a channel', function () {
    Storage::fake('public');

    $practitioner = makePractitioner();
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Ressources',
    ]);
    $discussion = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    $this->actingAs($practitioner)
        ->post(route('communities.messages.store', $community), [
            'community_channel_id' => $discussion->id,
            'content' => 'Voici le document de bienvenue.',
            'attachments' => [
                UploadedFile::fake()->create('guide.pdf', 120, 'application/pdf'),
            ],
        ])
        ->assertRedirect(route('communities.show', ['community' => $community->id, 'channel' => $discussion->id]));

    $message = CommunityMessage::with('attachments')->first();

    expect($message)->not->toBeNull()
        ->and($message->attachments)->toHaveCount(1);

    Storage::disk('public')->assertExists($message->attachments->first()->file_path);

    $this->actingAs($practitioner)
        ->post(route('communities.messages.pin', [$community, $message]))
        ->assertRedirect(route('communities.show', ['community' => $community->id, 'channel' => $discussion->id]));

    $discussion->refresh();

    expect($discussion->pinned_community_message_id)->toBe($message->id);

    $this->actingAs($practitioner)
        ->get(route('communities.attachments.download', $message->attachments->first()))
        ->assertOk();
});

test('client can download a shared attachment from an active community', function () {
    Storage::fake('public');

    $practitioner = makePractitioner();
    $client = makeClientForPractitioner($practitioner);
    $community = CommunityGroup::create([
        'user_id' => $practitioner->id,
        'name' => 'Supports',
    ]);
    $discussion = $community->channels()->create([
        'name' => 'General',
        'channel_type' => CommunityChannel::TYPE_DISCUSSION,
        'position' => 1,
    ]);

    CommunityMember::create([
        'community_group_id' => $community->id,
        'client_profile_id' => $client->id,
        'status' => CommunityMember::STATUS_ACTIVE,
        'invited_at' => now(),
        'joined_at' => now(),
    ]);

    $this->actingAs($practitioner)
        ->post(route('communities.messages.store', $community), [
            'community_channel_id' => $discussion->id,
            'content' => 'Support de séance',
            'attachments' => [
                UploadedFile::fake()->create('support.pdf', 50, 'application/pdf'),
            ],
        ])
        ->assertRedirect();

    $attachment = CommunityMessage::first()->attachments()->first();

    $this->actingAs($client, 'client')
        ->get(route('client.communities.attachments.download', $attachment))
        ->assertOk();
});
