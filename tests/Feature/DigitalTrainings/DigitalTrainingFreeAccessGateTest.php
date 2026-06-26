<?php

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingEnrollment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

test('therapist can enable open free access on training creation', function () {
    $therapist = makeTherapistForFreeAccessGate();

    $this->actingAs($therapist)
        ->post(route('digital-trainings.store'), [
            'title' => 'Ressource libre',
            'description' => 'Contenu ouvert',
            'is_free' => 1,
            'free_access_is_open' => 1,
            'free_access_requires_identity' => 1,
            'access_type' => 'public',
            'status' => 'draft',
        ])
        ->assertRedirect();

    $training = DigitalTraining::query()->latest('id')->first();

    expect($training)->not->toBeNull()
        ->and($training->is_free)->toBeTrue()
        ->and($training->free_access_is_open)->toBeTrue()
        ->and($training->free_access_requires_identity)->toBeFalse();
});

test('public landing page shows the free access gate when enabled', function () {
    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $this->get(route('digital-trainings.public.show', $training))
        ->assertOk()
        ->assertSee('communications par email')
        ->assertSee('Nom')
        ->assertSee('Email')
        ->assertSee($therapist->name);
});

test('public landing page shows open free access without the identity form when enabled', function () {
    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist, [
        'free_access_requires_identity' => false,
        'free_access_is_open' => true,
    ]);

    $this->get(route('digital-trainings.public.show', $training))
        ->assertOk()
        ->assertSee('Accès libre gratuit')
        ->assertSee('Accéder librement')
        ->assertDontSee('communications par email')
        ->assertDontSee('prenom.nom@email.com');
});

test('visitor can unlock a free gated training and store email communication consent', function () {
    Mail::fake();

    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $response = $this->post(route('digital-trainings.public.free-access.store', $training), [
        'first_name' => 'Sophie',
        'last_name' => 'Martin',
        'email' => 'sophie@example.test',
        'email_communication_consent' => 1,
    ]);

    $enrollment = DigitalTrainingEnrollment::query()->latest('id')->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->digital_training_id)->toBe($training->id)
        ->and($enrollment->participant_name)->toBe('Sophie Martin')
        ->and($enrollment->participant_email)->toBe('sophie@example.test')
        ->and($enrollment->source)->toBe(DigitalTrainingEnrollment::SOURCE_FREE_GATE)
        ->and($enrollment->email_communication_consent)->toBeTrue()
        ->and($enrollment->email_communication_consent_at)->not->toBeNull();

    $response->assertRedirect(route('digital-trainings.access.show', $enrollment->access_token));
    $response->assertCookie($training->freeAccessCookieName());
    Mail::assertNothingSent();
});

test('visitor can unlock a free gated training without giving email communication consent', function () {
    Mail::fake();

    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $this->post(route('digital-trainings.public.free-access.store', $training), [
        'first_name' => 'Lucie',
        'last_name' => 'Bernard',
        'email' => 'lucie@example.test',
    ])
        ->assertRedirect()
        ->assertCookie($training->freeAccessCookieName());

    $enrollment = DigitalTrainingEnrollment::query()->latest('id')->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->email_communication_consent)->toBeFalse()
        ->and($enrollment->email_communication_consent_at)->toBeNull();
});

test('visitor reuses an existing gated free access enrollment when submitting the same email', function () {
    Mail::fake();

    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $existing = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Sophie Martin',
        'participant_email' => 'sophie@example.test',
        'access_token' => (string) Str::uuid(),
        'token_expires_at' => now()->addMonths(6),
        'source' => DigitalTrainingEnrollment::SOURCE_FREE_GATE,
        'email_communication_consent' => false,
    ]);

    $response = $this->post(route('digital-trainings.public.free-access.store', $training), [
        'first_name' => 'Sophie',
        'last_name' => 'Martin',
        'email' => 'SOPHIE@example.test',
        'email_communication_consent' => 1,
    ]);

    expect(DigitalTrainingEnrollment::count())->toBe(1);

    $existing->refresh();

    expect($existing->email_communication_consent)->toBeTrue()
        ->and($existing->email_communication_consent_at)->not->toBeNull();

    $response
        ->assertRedirect(route('digital-trainings.access.show', $existing->access_token))
        ->assertCookie($training->freeAccessCookieName());

    Mail::assertNothingSent();
});

test('public landing page lets a returning gated visitor continue from the access cookie', function () {
    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist);

    $enrollment = DigitalTrainingEnrollment::create([
        'digital_training_id' => $training->id,
        'participant_name' => 'Sophie Martin',
        'participant_email' => 'sophie@example.test',
        'access_token' => (string) Str::uuid(),
        'token_expires_at' => now()->addMonths(6),
        'source' => DigitalTrainingEnrollment::SOURCE_FREE_GATE,
    ]);

    $cookieName = $training->freeAccessCookieName();

    $this->withCookie($cookieName, $enrollment->access_token)
        ->get(route('digital-trainings.public.show', $training))
        ->assertOk()
        ->assertSee('Accès déjà disponible')
        ->assertSee('Continuer ma formation')
        ->assertSee(route('digital-trainings.access.show', $enrollment->access_token));
});

test('visitor can open an open free training without identity or login', function () {
    Mail::fake();

    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist, [
        'free_access_requires_identity' => false,
        'free_access_is_open' => true,
    ]);

    $response = $this->post(route('digital-trainings.public.open-access.store', $training));

    $enrollment = DigitalTrainingEnrollment::query()->latest('id')->first();

    expect($enrollment)->not->toBeNull()
        ->and($enrollment->digital_training_id)->toBe($training->id)
        ->and($enrollment->participant_name)->toBeNull()
        ->and($enrollment->participant_email)->toBeNull()
        ->and($enrollment->source)->toBe(DigitalTrainingEnrollment::SOURCE_OPEN_FREE_ACCESS)
        ->and($enrollment->email_communication_consent)->toBeFalse()
        ->and($enrollment->email_communication_consent_at)->toBeNull();

    $response->assertRedirect(route('digital-trainings.access.show', $enrollment->access_token));
    $response->assertCookie($training->freeAccessCookieName());
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

test('open free access route is not available when open access is disabled', function () {
    $therapist = makeTherapistForFreeAccessGate();
    $training = makePublishedFreeTraining($therapist, [
        'free_access_requires_identity' => false,
        'free_access_is_open' => false,
    ]);

    $this->post(route('digital-trainings.public.open-access.store', $training))
        ->assertNotFound();

    expect(DigitalTrainingEnrollment::count())->toBe(0);
});

