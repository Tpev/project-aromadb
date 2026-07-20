<?php

use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
    Storage::fake('public');
});

test('the therapist admin page shows the data export action', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $therapist = User::factory()->create([
        'name' => 'Praticienne test',
        'is_therapist' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.therapists.show', $therapist))
        ->assertOk()
        ->assertSee('Télécharger l’export ZIP')
        ->assertSee(route('admin.therapists.exportData', $therapist), false);
});

test('an admin can download the selected therapist data export', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $therapist = User::factory()->create([
        'name' => 'Praticienne exportée',
        'email' => 'praticienne@example.test',
        'is_therapist' => true,
    ]);
    $otherTherapist = User::factory()->create(['is_therapist' => true]);

    ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => 'Cliente autorisée',
        'last_name' => 'Export',
        'email' => 'cliente-export@example.test',
    ]);
    ClientProfile::create([
        'user_id' => $otherTherapist->id,
        'first_name' => 'AUTRE COMPTE INTERDIT',
        'last_name' => 'Isolation',
        'email' => 'autre-compte@example.test',
    ]);

    $response = $this->actingAs($admin)->post(
        route('admin.therapists.exportData', $therapist),
    );

    $response->assertOk()
        ->assertHeader('content-type', 'application/zip')
        ->assertHeader('x-content-type-options', 'nosniff');

    $disposition = $response->headers->get('content-disposition');
    expect($disposition)
        ->toContain('attachment;')
        ->toContain('export-olithea-compte-'.$therapist->id.'-');

    $archivePath = $response->baseResponse->getFile()->getPathname();
    $zip = new ZipArchive;
    expect($zip->open($archivePath))->toBeTrue();

    $clientCsv = $zip->getFromName('clients/fiches-clients.csv');
    $zip->close();

    expect($clientCsv)
        ->toContain('Cliente autorisée')
        ->not->toContain('AUTRE COMPTE INTERDIT');

    @unlink($archivePath);
});

test('a non admin cannot download a therapist data export', function () {
    $user = User::factory()->create(['is_admin' => false]);
    $therapist = User::factory()->create(['is_therapist' => true]);

    $this->actingAs($user)
        ->post(route('admin.therapists.exportData', $therapist))
        ->assertForbidden();

    expect(Storage::disk('local')->allFiles('private/account-exports'))->toBeEmpty();
});

test('the admin export endpoint refuses a non therapist account', function () {
    $admin = User::factory()->create(['is_admin' => true]);
    $customer = User::factory()->create(['is_therapist' => false]);

    $this->actingAs($admin)
        ->post(route('admin.therapists.exportData', $customer))
        ->assertNotFound();

    expect(Storage::disk('local')->allFiles('private/account-exports'))->toBeEmpty();
});
