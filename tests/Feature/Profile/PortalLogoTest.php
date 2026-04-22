<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('therapist can upload a portal logo from company info', function () {
    Storage::fake('public');

    $user = User::factory()->create([
        'is_therapist' => true,
    ]);

    $response = $this->actingAs($user)->put(route('profile.updateCompanyInfo'), [
        'portal_logo' => UploadedFile::fake()->image('portal-logo.png', 1200, 400),
        'portal_logo_crop' => json_encode([
            'x' => 80,
            'y' => 20,
            'width' => 900,
            'height' => 260,
            'image_width' => 1200,
            'image_height' => 400,
        ]),
    ]);

    $response->assertRedirect(route('profile.editCompanyInfo'));

    $user->refresh();

    expect($user->portal_logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($user->portal_logo_path);
});

test('therapist can remove an existing portal logo', function () {
    Storage::fake('public');

    $path = 'portal_logos/42/portal-logo.webp';
    $user = User::factory()->create([
        'is_therapist' => true,
        'portal_logo_path' => $path,
    ]);

    Storage::disk('public')->put($user->portal_logo_path, 'fake-image');

    $response = $this->actingAs($user)->put(route('profile.updateCompanyInfo'), [
        'remove_portal_logo' => '1',
    ]);

    $response->assertRedirect(route('profile.editCompanyInfo'));

    $user->refresh();

    expect($user->portal_logo_path)->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
