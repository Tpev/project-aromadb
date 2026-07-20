<?php

use App\Models\ClientProfile;
use App\Models\User;
use App\Services\AccountDataExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

function accountExportZipEntries(string $path): array
{
    $zip = new \ZipArchive;
    expect($zip->open($path))->toBeTrue();

    $entries = [];
    for ($index = 0; $index < $zip->numFiles; $index++) {
        $name = $zip->getNameIndex($index);
        $entries[$name] = $zip->getFromIndex($index);
    }

    $zip->close();

    return $entries;
}

test('account export includes owned records and files while excluding every other tenant', function () {
    $owner = User::factory()->create([
        'name' => 'Praticienne Exportee',
        'email' => 'export-owner@example.test',
        'is_therapist' => true,
        'google_access_token' => 'OWNER-OAUTH-SECRET',
    ]);
    $other = User::factory()->create([
        'name' => 'OTHER-TENANT-USER',
        'email' => 'other-tenant@example.test',
        'is_therapist' => true,
    ]);

    $ownedClient = ClientProfile::create([
        'user_id' => $owner->id,
        'first_name' => 'Cliente',
        'last_name' => 'Autorisee',
        'email' => 'owned-client@example.test',
        'password' => 'OWNED-CLIENT-PASSWORD-SECRET',
    ]);
    $otherClient = ClientProfile::create([
        'user_id' => $other->id,
        'first_name' => 'OTHER-TENANT-CLIENT',
        'last_name' => 'Interdite',
        'email' => 'other-client@example.test',
    ]);

    $now = now();
    $ownedAppointmentId = DB::table('appointments')->insertGetId([
        'user_id' => $owner->id,
        'client_profile_id' => $ownedClient->id,
        'token' => Str::random(64),
        'appointment_date' => '2026-07-20 10:00:00',
        'status' => 'scheduled',
        'notes' => 'OWNED-APPOINTMENT',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('appointments')->insert([
        'user_id' => $other->id,
        'client_profile_id' => $otherClient->id,
        'token' => Str::random(64),
        'appointment_date' => '2026-07-20 11:00:00',
        'status' => 'scheduled',
        'notes' => 'OTHER-TENANT-APPOINTMENT',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    // Deliberate ownership mismatch: user_id alone must never be enough.
    DB::table('appointments')->insert([
        'user_id' => $owner->id,
        'client_profile_id' => $otherClient->id,
        'token' => Str::random(64),
        'appointment_date' => '2026-07-20 12:00:00',
        'status' => 'scheduled',
        'notes' => 'CROSS-TENANT-CORRUPT-ROW',
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    Storage::disk('public')->put('client-files/owned.txt', 'OWNED-FILE-CONTENT');
    Storage::disk('public')->put('client-files/other.txt', 'OTHER-TENANT-FILE-CONTENT');
    DB::table('client_files')->insert([
        [
            'client_profile_id' => $ownedClient->id,
            'file_path' => 'client-files/owned.txt',
            'original_name' => 'owned.txt',
            'mime_type' => 'text/plain',
            'size' => 18,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'client_profile_id' => $otherClient->id,
            'file_path' => 'client-files/other.txt',
            'original_name' => 'other.txt',
            'mime_type' => 'text/plain',
            'size' => 25,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    DB::table('meetings')->insert([
        'name' => 'OWNED-MEETING',
        'start_time' => '2026-07-20 10:00:00',
        'duration' => 30,
        'client_profile_id' => $ownedClient->id,
        'appointment_id' => $ownedAppointmentId,
        'room_token' => Str::random(32),
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('meetings')->insert([
        'name' => 'ORPHAN-MEETING-MUST-NOT-LEAK',
        'start_time' => '2026-07-20 13:00:00',
        'duration' => 30,
        'client_profile_id' => null,
        'appointment_id' => null,
        'room_token' => Str::random(32),
        'created_at' => $now,
        'updated_at' => $now,
    ]);

    DB::table('offer_journey_pipeline_stages')->insert([
        ['user_id' => $owner->id, 'name' => 'OWNED-PIPELINE-STAGE', 'slug' => 'owned-stage', 'created_at' => $now, 'updated_at' => $now],
        ['user_id' => $other->id, 'name' => 'OTHER-TENANT-PIPELINE-STAGE', 'slug' => 'other-stage', 'created_at' => $now, 'updated_at' => $now],
    ]);
    $ownedJourneyId = DB::table('offer_journeys')->insertGetId([
        'user_id' => $owner->id,
        'name' => 'OWNED-JOURNEY',
        'slug' => 'owned-journey',
        'objective' => 'lead_magnet',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $otherJourneyId = DB::table('offer_journeys')->insertGetId([
        'user_id' => $other->id,
        'name' => 'OTHER-TENANT-JOURNEY',
        'slug' => 'other-journey',
        'objective' => 'lead_magnet',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $ownedContactId = DB::table('offer_journey_contacts')->insertGetId([
        'user_id' => $owner->id,
        'email' => 'owned-lead@example.test',
        'email_normalized' => 'owned-lead@example.test',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    $otherContactId = DB::table('offer_journey_contacts')->insertGetId([
        'user_id' => $other->id,
        'email' => 'other-lead@example.test',
        'email_normalized' => 'other-lead@example.test',
        'created_at' => $now,
        'updated_at' => $now,
    ]);
    DB::table('offer_journey_form_answers')->insert([
        [
            'offer_journey_contact_id' => $ownedContactId,
            'offer_journey_id' => $ownedJourneyId,
            'field_name' => 'objectif',
            'field_label' => 'Objectif',
            'field_type' => 'text',
            'purpose' => 'OWNED-FORM-ANSWER',
            'answered_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'offer_journey_contact_id' => $otherContactId,
            'offer_journey_id' => $otherJourneyId,
            'field_name' => 'objectif',
            'field_label' => 'Objectif',
            'field_type' => 'text',
            'purpose' => 'OTHER-TENANT-FORM-ANSWER',
            'answered_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ],
        [
            'offer_journey_contact_id' => $otherContactId,
            'offer_journey_id' => $ownedJourneyId,
            'field_name' => 'objectif',
            'field_label' => 'Objectif',
            'field_type' => 'text',
            'purpose' => 'CROSS-TENANT-FORM-ANSWER',
            'answered_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ],
    ]);

    $result = app(AccountDataExportService::class)->export($owner, 50);
    $entries = accountExportZipEntries($result->absolutePath);
    $archiveText = implode("\n", array_values($entries));

    expect($entries)->toHaveKeys([
        'README.txt',
        'manifest.json',
        'compte/profil.csv',
        'clients/fiches-clients.csv',
        'clients/rendez-vous.csv',
        'documents/fichiers-clients/'.$ownedClient->id.'/1-file_path-owned.txt',
    ]);

    expect($archiveText)
        ->toContain('Praticienne Exportee')
        ->toContain('Cliente')
        ->toContain('OWNED-APPOINTMENT')
        ->toContain('OWNED-MEETING')
        ->toContain('OWNED-FILE-CONTENT')
        ->toContain('OWNED-PIPELINE-STAGE')
        ->toContain('OWNED-FORM-ANSWER')
        ->not->toContain('OTHER-TENANT-USER')
        ->not->toContain('OTHER-TENANT-CLIENT')
        ->not->toContain('OTHER-TENANT-APPOINTMENT')
        ->not->toContain('CROSS-TENANT-CORRUPT-ROW')
        ->not->toContain('ORPHAN-MEETING-MUST-NOT-LEAK')
        ->not->toContain('OTHER-TENANT-FILE-CONTENT')
        ->not->toContain('OTHER-TENANT-PIPELINE-STAGE')
        ->not->toContain('OTHER-TENANT-FORM-ANSWER')
        ->not->toContain('CROSS-TENANT-FORM-ANSWER')
        ->not->toContain('OWNER-OAUTH-SECRET')
        ->not->toContain('OWNED-CLIENT-PASSWORD-SECRET');

    $manifest = json_decode($entries['manifest.json'], true, flags: JSON_THROW_ON_ERROR);
    expect($manifest['account']['user_id'])->toBe($owner->id)
        ->and($manifest['dataset_counts']['Fiches clients'])->toBe(1)
        ->and($manifest['dataset_counts']['Rendez-vous'])->toBe(1)
        ->and($manifest['exported_files'])->toBe(1);
});

test('dry run creates no archive and a real export requires exact email confirmation', function () {
    $user = User::factory()->create([
        'email' => 'confirmation@example.test',
        'is_therapist' => true,
    ]);

    $this->artisan('account:export', [
        'user' => $user->id,
        '--dry-run' => true,
    ])->assertSuccessful();

    expect(Storage::disk('local')->allFiles('private/account-exports'))->toBeEmpty();

    $this->artisan('account:export', [
        'user' => $user->id,
        '--confirm-email' => 'wrong@example.test',
    ])->assertFailed();

    expect(Storage::disk('local')->allFiles('private/account-exports'))->toBeEmpty();

    $this->artisan('account:export', [
        'user' => $user->id,
        '--confirm-email' => $user->email,
    ])->assertSuccessful();

    expect(Storage::disk('local')->allFiles('private/account-exports'))->toHaveCount(1);
});

test('scheduled cleanup deletes only expired generated zip archives', function () {
    Storage::disk('local')->put('private/account-exports/expired.zip', 'expired');
    Storage::disk('local')->put('private/account-exports/recent.zip', 'recent');
    Storage::disk('local')->put('private/account-exports/keep.txt', 'not an archive');

    touch(
        Storage::disk('local')->path('private/account-exports/expired.zip'),
        now()->subDays(8)->timestamp,
    );

    $deleted = app(AccountDataExportService::class)->purgeExpiredExports(7);

    expect($deleted)->toBe(1);
    Storage::disk('local')->assertMissing('private/account-exports/expired.zip');
    Storage::disk('local')->assertExists('private/account-exports/recent.zip');
    Storage::disk('local')->assertExists('private/account-exports/keep.txt');
});
