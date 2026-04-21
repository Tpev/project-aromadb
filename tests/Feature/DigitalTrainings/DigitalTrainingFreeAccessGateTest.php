<?php

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function makeTherapistForFreeAccessGate(): User
{
    return User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
}

function makePublishedFreeTraining(User $therapist, array $overrides = []): DigitalTraining
{
    return DigitalTraining::create(array_merge([
        'user_id' => $therapist->id,
        'title' => 'Formation gratuite',
        'slug' => 'formation-gratuite-' . uniqid(),
        'description' => 'Contenu gratuit',
        'is_free' => true,
        'free_access_requires_identity' => true,
        'status' => 'published',
        'access_type' => 'public',
    ], $overrides));
}

test('therapist can enable gated free access on training creation', function () {
    $therapist = makeTherapistForFreeAccessGate();

    $this->actingAs($therapist)
        ->post(route('digital-trainings.store'), [
            'title' => 'Guide gratuit',
            'description' => 'Contenu free',
            'is_free' => 1,
            'free_access_requires_identity' => 1,
            'access_type' => 'public',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $training = DigitalTraining::query()->latest('id')->first();

    expect($training)->not->toBeNull()
        ->and($training->is_free)->toBeTrue()
        ->and($training->free_access_requires_identity)->toBeTrue();
});

test('public landing page shows the free access gate when enabled', function () {
    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $this->get(route('digital-trainings.public.show', $training))
        ->assertOk()
        ->assertSee('Accéder gratuitement')
        ->assertSee('Prénom')
        ->assertSee('Nom')
        ->assertSee('Email');
});

test('visitor can unlock a free gated training and gets redirected to the player', function () {
    Mail::fake();

    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $response = $this->post(route('digital-trainings.public.free-access.store', $training), [
        'first_name' => 'Sophie',
        'last_name' => 'Martin',
        'email' => 'sophie@example.test',
    ]);

    $enrollment = DigitalTrainingEnrollment::query()->latest('id')->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->digital_training_id)->toBe($training->id)
        ->and($enrollment->participant_name)->toBe('Sophie Martin')
        ->and($enrollment->participant_email)->toBe('sophie@example.test')
        ->and($enrollment->source)->toBe(DigitalTrainingEnrollment::SOURCE_FREE_GATE);

    $response->assertRedirect(route('digital-trainings.access.show', $enrollment->access_token));
    Mail::assertNothingSent();
});

test('free gated access route is not available when the gate is disabled', function () {
    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist, [
        'free_access_requires_identity' => false,
    ]);

    $this->post(route('digital-trainings.public.free-access.store', $training), [
        'first_name' => 'Sophie',
        'last_name' => 'Martin',
        'email' => 'sophie@example.test',
    ])->assertNotFound();

    expect(DigitalTrainingEnrollment::count())->toBe(0);
});
