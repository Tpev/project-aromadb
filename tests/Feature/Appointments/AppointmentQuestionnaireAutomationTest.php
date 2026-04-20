<?php

use App\Mail\QuestionnaireSentMail;
use App\Models\Appointment;
use App\Models\ClientProfile;
use App\Models\Product;
use App\Models\Questionnaire;
use App\Models\Response;
use App\Models\User;
use App\Services\AppointmentQuestionnaireAutomationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function createTherapistForQuestionnaireAutomation(): User
{
    return User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
}

function createQuestionnaireForTherapist(User $therapist, string $title = 'Pré-séance'): Questionnaire
{
    return Questionnaire::create([
        'user_id' => $therapist->id,
        'title' => $title,
        'description' => 'Questionnaire automatique',
    ]);
}

function createClientForTherapist(User $therapist, array $overrides = []): ClientProfile
{
    return ClientProfile::create(array_merge([
        'user_id' => $therapist->id,
        'first_name' => 'Sophie',
        'last_name' => 'Client',
        'email' => 'client-' . uniqid() . '@example.test',
    ], $overrides));
}

function createConfiguredProduct(User $therapist, Questionnaire $questionnaire, string $frequency = Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY): Product
{
    return Product::create([
        'user_id' => $therapist->id,
        'name' => 'Bilan naturo',
        'description' => 'Première séance',
        'price' => 60,
        'tax_rate' => 0,
        'duration' => 60,
        'can_be_booked_online' => true,
        'collect_payment' => false,
        'visio' => false,
        'adomicile' => true,
        'en_entreprise' => false,
        'dans_le_cabinet' => false,
        'max_per_day' => null,
        'display_order' => 0,
        'requires_emargement' => false,
        'visible_in_portal' => true,
        'price_visible_in_portal' => true,
        'booking_questionnaire_enabled' => true,
        'booking_questionnaire_id' => $questionnaire->id,
        'booking_questionnaire_frequency' => $frequency,
    ]);
}

test('therapist can save questionnaire automation settings on a product', function () {
    $therapist = createTherapistForQuestionnaireAutomation();
    $questionnaire = createQuestionnaireForTherapist($therapist);

    $this->actingAs($therapist)
        ->post(route('products.store'), [
            'name' => 'Consultation découverte',
            'description' => 'Séance test',
            'price' => 45,
            'tax_rate' => 0,
            'duration' => 60,
            'mode' => 'adomicile',
            'max_per_day' => '',
            'can_be_booked_online' => 1,
            'collect_payment' => 0,
            'display_order' => 0,
            'requires_emargement' => 0,
            'visible_in_portal' => 1,
            'price_visible_in_portal' => 1,
            'direct_booking_enabled' => 0,
            'booking_questionnaire_enabled' => 1,
            'booking_questionnaire_id' => $questionnaire->id,
            'booking_questionnaire_frequency' => 'first_time_only',
        ])
        ->assertRedirect();

    $product = Product::query()->latest('id')->first();

    expect($product)->not->toBeNull();
    expect($product->booking_questionnaire_enabled)->toBeTrue();
    expect($product->booking_questionnaire_id)->toBe($questionnaire->id);
    expect($product->booking_questionnaire_frequency)->toBe('first_time_only');
});

test('booking a configured product sends questionnaire only on the first booking when configured that way', function () {
    Mail::fake();

    $therapist = createTherapistForQuestionnaireAutomation();
    $client = createClientForTherapist($therapist);
    $questionnaire = createQuestionnaireForTherapist($therapist);
    $product = createConfiguredProduct($therapist, $questionnaire, Product::BOOKING_QUESTIONNAIRE_FIRST_TIME_ONLY);

    $payload = [
        'client_profile_id' => $client->id,
        'appointment_date' => now()->addDays(2)->format('Y-m-d'),
        'appointment_time' => '09:00',
        'status' => 'Confirmé',
        'notes' => 'Premier rendez-vous',
        'product_id' => $product->id,
        'force_availability_override' => 1,
    ];

    $this->actingAs($therapist)
        ->post(route('appointments.store'), $payload)
        ->assertRedirect(route('appointments.index'));

    Mail::assertQueued(QuestionnaireSentMail::class, 1);

    $response = Response::query()->where('source', AppointmentQuestionnaireAutomationService::SOURCE)->first();
    expect($response)->not->toBeNull();
    expect($response->questionnaire_id)->toBe($questionnaire->id);
    expect($response->client_profile_id)->toBe($client->id);
    expect($response->appointment_id)->not->toBeNull();

    $this->actingAs($therapist)
        ->post(route('appointments.store'), array_merge($payload, [
            'appointment_date' => now()->addDays(3)->format('Y-m-d'),
            'appointment_time' => '10:30',
            'notes' => 'Deuxième rendez-vous',
        ]))
        ->assertRedirect(route('appointments.index'));

    Mail::assertQueued(QuestionnaireSentMail::class, 1);
    expect(Response::query()->where('source', AppointmentQuestionnaireAutomationService::SOURCE)->count())->toBe(1);
});

test('booking a configured product can send questionnaire on every booking', function () {
    Mail::fake();

    $therapist = createTherapistForQuestionnaireAutomation();
    $client = createClientForTherapist($therapist);
    $questionnaire = createQuestionnaireForTherapist($therapist, 'Suivi');
    $product = createConfiguredProduct($therapist, $questionnaire, Product::BOOKING_QUESTIONNAIRE_EVERY_BOOKING);

    $this->actingAs($therapist)
        ->post(route('appointments.store'), [
            'client_profile_id' => $client->id,
            'appointment_date' => now()->addDays(4)->format('Y-m-d'),
            'appointment_time' => '11:00',
            'status' => 'Confirmé',
            'notes' => 'Rendez-vous 1',
            'product_id' => $product->id,
            'force_availability_override' => 1,
        ])
        ->assertRedirect(route('appointments.index'));

    $this->actingAs($therapist)
        ->post(route('appointments.store'), [
            'client_profile_id' => $client->id,
            'appointment_date' => now()->addDays(5)->format('Y-m-d'),
            'appointment_time' => '11:30',
            'status' => 'Confirmé',
            'notes' => 'Rendez-vous 2',
            'product_id' => $product->id,
            'force_availability_override' => 1,
        ])
        ->assertRedirect(route('appointments.index'));

    Mail::assertQueued(QuestionnaireSentMail::class, 2);
    expect(Response::query()->where('source', AppointmentQuestionnaireAutomationService::SOURCE)->count())->toBe(2);
});

test('automation service does not create duplicate questionnaire sends for the same appointment', function () {
    Mail::fake();

    $therapist = createTherapistForQuestionnaireAutomation();
    $client = createClientForTherapist($therapist);
    $questionnaire = createQuestionnaireForTherapist($therapist);
    $product = createConfiguredProduct($therapist, $questionnaire, Product::BOOKING_QUESTIONNAIRE_EVERY_BOOKING);

    $appointment = Appointment::create([
        'client_profile_id' => $client->id,
        'user_id' => $therapist->id,
        'appointment_date' => now()->addDays(7)->setTime(9, 0),
        'status' => 'Confirmé',
        'notes' => null,
        'type' => 'domicile',
        'duration' => 60,
        'product_id' => $product->id,
    ]);

    $service = app(AppointmentQuestionnaireAutomationService::class);

    $service->dispatchForConfirmedAppointment($appointment);
    $service->dispatchForConfirmedAppointment($appointment);

    Mail::assertQueued(QuestionnaireSentMail::class, 1);
    expect(Response::query()->where('appointment_id', $appointment->id)->where('source', AppointmentQuestionnaireAutomationService::SOURCE)->count())->toBe(1);
});

test('questionnaire mail renders with the client first name', function () {
    $mail = new QuestionnaireSentMail(
        'Camille Martin',
        'Questionnaire prÃ©-sÃ©ance',
        'https://example.test/questionnaires/token-123',
        'Sophie'
    );

    $rendered = $mail->render();

    expect($rendered)->toContain('Sophie');
    expect($rendered)->toContain('Questionnaire prÃ©-sÃ©ance');
});
