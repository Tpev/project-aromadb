<?php

use App\Models\ClientProfile;
use App\Models\Questionnaire;
use App\Models\Response as QuestionnaireResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

function questionnaireContextTherapist(string $email): User
{
    return User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
        'email' => $email,
    ]);
}

function questionnaireContextClient(User $therapist, string $firstName, string $email): ClientProfile
{
    return ClientProfile::create([
        'user_id' => $therapist->id,
        'first_name' => $firstName,
        'last_name' => 'Questionnaire',
        'email' => $email,
    ]);
}

function questionnaireContextTemplate(User $therapist): Questionnaire
{
    return Questionnaire::create([
        'user_id' => $therapist->id,
        'title' => 'Bilan de suivi',
        'description' => 'Questionnaire de test',
    ]);
}

test('questionnaire send screen locks the client selected from a client profile', function () {
    $therapist = questionnaireContextTherapist('questionnaire-context@example.test');
    $selectedClient = questionnaireContextClient($therapist, 'Alice', 'alice-context@example.test');
    $otherClient = questionnaireContextClient($therapist, 'Bruno', 'bruno-context@example.test');
    questionnaireContextTemplate($therapist);

    $this->actingAs($therapist)
        ->get(route('questionnaires.send.show', ['client_profile_id' => $selectedClient->id]))
        ->assertOk()
        ->assertSee('Alice Questionnaire')
        ->assertSee('Le destinataire est verrouillé')
        ->assertSee('name="client_profile_id" value="'.$selectedClient->id.'"', false)
        ->assertDontSee('Bruno Questionnaire')
        ->assertDontSee('id="client_profile_id"', false)
        ->assertSee(route('client_profiles.show', [
            'clientProfile' => $selectedClient->id,
            'tab' => 'Questionnaires',
        ]), false);

    expect($otherClient->exists)->toBeTrue();
});

test('global questionnaire send screen still allows choosing among owned clients', function () {
    $therapist = questionnaireContextTherapist('questionnaire-global@example.test');
    questionnaireContextClient($therapist, 'Alice', 'alice-global@example.test');
    questionnaireContextClient($therapist, 'Bruno', 'bruno-global@example.test');
    questionnaireContextTemplate($therapist);

    $this->actingAs($therapist)
        ->get(route('questionnaires.send.show'))
        ->assertOk()
        ->assertSee('id="client_profile_id"', false)
        ->assertSee('Alice Questionnaire')
        ->assertSee('Bruno Questionnaire')
        ->assertDontSee('Le destinataire est verrouillé');
});

test('questionnaire client context cannot target another practitioners client', function () {
    $therapist = questionnaireContextTherapist('questionnaire-owner@example.test');
    $otherTherapist = questionnaireContextTherapist('questionnaire-other@example.test');
    $foreignClient = questionnaireContextClient($otherTherapist, 'ClientEtranger', 'foreign-context@example.test');
    $questionnaire = questionnaireContextTemplate($therapist);

    $this->actingAs($therapist)
        ->get(route('questionnaires.send.show', ['client_profile_id' => $foreignClient->id]))
        ->assertNotFound();

    $this->post(route('questionnaires.send', $questionnaire), [
        'client_profile_id' => $foreignClient->id,
        'questionnaire_id' => $questionnaire->id,
        'action' => 'fill_now',
    ])->assertNotFound();
});

test('questionnaire response returns to the questionnaires tab of its client profile', function () {
    $therapist = questionnaireContextTherapist('questionnaire-return@example.test');
    $client = questionnaireContextClient($therapist, 'Alice', 'alice-return@example.test');
    $questionnaire = questionnaireContextTemplate($therapist);
    $response = QuestionnaireResponse::create([
        'questionnaire_id' => $questionnaire->id,
        'client_profile_id' => $client->id,
        'token' => 'questionnaire-return-token',
        'answers' => json_encode([]),
        'is_completed' => true,
    ]);

    $this->actingAs($therapist)
        ->get(route('questionnaires.responses.show', $response))
        ->assertOk()
        ->assertSee('Retour à la fiche client')
        ->assertSee(route('client_profiles.show', [
            'clientProfile' => $client->id,
            'tab' => 'Questionnaires',
        ]), false);

    $this->get(route('client_profiles.show', [
        'clientProfile' => $client->id,
        'tab' => 'Questionnaires',
    ]))
        ->assertOk()
        ->assertSee("x-data=\"{ tab: 'Questionnaires' }\"", false);
});

test('emailed questionnaire returns to the originating client questionnaires tab', function () {
    Mail::fake();
    $therapist = questionnaireContextTherapist('questionnaire-send@example.test');
    $client = questionnaireContextClient($therapist, 'Alice', 'alice-send@example.test');
    $questionnaire = questionnaireContextTemplate($therapist);

    $this->actingAs($therapist)
        ->post(route('questionnaires.send', $questionnaire), [
            'client_profile_id' => $client->id,
            'questionnaire_id' => $questionnaire->id,
            'action' => 'send_email',
        ])
        ->assertRedirect(route('client_profiles.show', [
            'clientProfile' => $client->id,
            'tab' => 'Questionnaires',
        ]));

    expect(QuestionnaireResponse::where('client_profile_id', $client->id)->exists())->toBeTrue();
});
