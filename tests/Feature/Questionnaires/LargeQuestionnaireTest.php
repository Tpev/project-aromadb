<?php

use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\User;

function questionnaireUser(): User
{
    return User::factory()->create([
        'is_therapist' => true,
        'license_status' => 'active',
    ]);
}

test('desktop compact payload stores a large questionnaire and long question text', function () {
    $user = questionnaireUser();
    $longText = trim('Contexte detaille : '.str_repeat('une information importante, ', 40));
    $questions = collect(range(1, 120))->map(fn (int $number) => [
        'text' => $number === 73 ? $longText : "Question numero {$number}",
        'type' => $number % 5 === 0 ? 'multiple_choice' : 'text',
        'options' => $number % 5 === 0 ? 'Oui, Non, A preciser' : '',
    ])->all();

    $this->actingAs($user)
        ->post(route('questionnaires.store'), [
            'title' => 'Questionnaire complet',
            'description' => 'Questionnaire de production avec de nombreuses questions.',
            'questions_payload' => json_encode($questions, JSON_THROW_ON_ERROR),
        ])
        ->assertRedirect(route('questionnaires.index'))
        ->assertSessionHasNoErrors();

    $questionnaire = Questionnaire::where('user_id', $user->id)
        ->where('title', 'Questionnaire complet')
        ->firstOrFail();

    expect($questionnaire->questions()->count())->toBe(120)
        ->and($questionnaire->questions()->where('text', $longText)->exists())->toBeTrue();
});

test('compact payload safely updates adds and removes questionnaire questions', function () {
    $user = questionnaireUser();
    $questionnaire = Questionnaire::create([
        'user_id' => $user->id,
        'title' => 'Bilan initial',
        'description' => null,
    ]);
    $kept = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Question a conserver',
        'type' => 'multiple_choice',
    ]);
    $kept->forceFill(['options' => 'Oui, Non'])->save();
    $removed = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Question a supprimer',
        'type' => 'text',
    ]);
    $updatedText = trim(str_repeat('Texte de question modifie et detaille. ', 20));

    $this->actingAs($user)
        ->put(route('questionnaires.update', $questionnaire), [
            'title' => 'Bilan actualise',
            'description' => 'Description actualisee',
            'questions_payload' => json_encode([
                [
                    'id' => $kept->id,
                    'text' => $updatedText,
                    'type' => 'text',
                    'options' => 'Cette ancienne option doit etre effacee',
                ],
                [
                    'text' => 'Nouvelle question',
                    'type' => 'multiple_choice',
                    'options' => 'Option A, Option B',
                ],
            ], JSON_THROW_ON_ERROR),
        ])
        ->assertRedirect(route('questionnaires.index'))
        ->assertSessionHasNoErrors();

    expect($questionnaire->fresh()->title)->toBe('Bilan actualise')
        ->and($questionnaire->questions()->count())->toBe(2)
        ->and($kept->fresh()->text)->toBe($updatedText)
        ->and($kept->fresh()->options)->toBeNull()
        ->and(Question::find($removed->id))->toBeNull()
        ->and($questionnaire->questions()->where('text', 'Nouvelle question')->value('options'))->toBe('Option A, Option B');
});

test('legacy questionnaire fields remain supported', function () {
    $user = questionnaireUser();

    $this->actingAs($user)
        ->post(route('questionnaires.store'), [
            'title' => 'Ancien formulaire',
            'description' => null,
            'questions' => [
                ['text' => 'Question texte', 'type' => 'text'],
                [
                    'text' => 'Question avec choix',
                    'type' => 'multiple_choice',
                    'options' => 'Premier, Deuxieme',
                ],
            ],
        ])
        ->assertRedirect(route('questionnaires.index'))
        ->assertSessionHasNoErrors();

    $questionnaire = Questionnaire::where('user_id', $user->id)
        ->where('title', 'Ancien formulaire')
        ->firstOrFail();

    expect($questionnaire->questions()->count())->toBe(2)
        ->and($questionnaire->questions()->where('type', 'multiple_choice')->value('options'))
        ->toBe('Premier, Deuxieme');
});

test('questionnaire size limit fails validation without partial writes', function () {
    $user = questionnaireUser();
    $questions = collect(range(1, 301))->map(fn (int $number) => [
        'text' => "Question {$number}",
        'type' => 'text',
        'options' => '',
    ])->all();

    $this->from(route('questionnaires.create'))
        ->actingAs($user)
        ->post(route('questionnaires.store'), [
            'title' => 'Trop grand',
            'questions_payload' => json_encode($questions, JSON_THROW_ON_ERROR),
        ])
        ->assertRedirect(route('questionnaires.create'))
        ->assertSessionHasErrors('questions');

    expect(Questionnaire::where('user_id', $user->id)->where('title', 'Trop grand')->exists())->toBeFalse();
});

test('questionnaire update rejects foreign question ids and rolls back all changes', function () {
    $user = questionnaireUser();
    $otherUser = questionnaireUser();
    $questionnaire = Questionnaire::create([
        'user_id' => $user->id,
        'title' => 'Titre original',
        'description' => null,
    ]);
    $ownQuestion = Question::create([
        'questionnaire_id' => $questionnaire->id,
        'text' => 'Question originale',
        'type' => 'text',
    ]);
    $otherQuestionnaire = Questionnaire::create([
        'user_id' => $otherUser->id,
        'title' => 'Autre questionnaire',
        'description' => null,
    ]);
    $foreignQuestion = Question::create([
        'questionnaire_id' => $otherQuestionnaire->id,
        'text' => 'Question privee',
        'type' => 'text',
    ]);

    $this->actingAs($user)
        ->put(route('questionnaires.update', $questionnaire), [
            'title' => 'Titre qui ne doit pas etre enregistre',
            'questions_payload' => json_encode([[
                'id' => $foreignQuestion->id,
                'text' => 'Tentative',
                'type' => 'text',
                'options' => '',
            ]], JSON_THROW_ON_ERROR),
        ])
        ->assertForbidden();

    expect($questionnaire->fresh()->title)->toBe('Titre original')
        ->and($ownQuestion->fresh()->text)->toBe('Question originale')
        ->and($foreignQuestion->fresh()->text)->toBe('Question privee');
});

test('mobile compact payload uses the same large questionnaire path', function () {
    $user = questionnaireUser();
    $longText = trim(str_repeat('Question mobile longue. ', 30));

    $this->actingAs($user)
        ->post('/mobile/questionnaires', [
            'title' => 'Bilan mobile long',
            'description' => null,
            'questions_payload' => json_encode([
                ['text' => $longText, 'type' => 'text', 'options' => ''],
                ['text' => 'Votre preference ?', 'type' => 'multiple_choice', 'options' => 'A, B'],
            ], JSON_THROW_ON_ERROR),
        ])
        ->assertSessionHasNoErrors();

    $questionnaire = Questionnaire::where('user_id', $user->id)
        ->where('title', 'Bilan mobile long')
        ->firstOrFail();

    expect($questionnaire->questions()->count())->toBe(2)
        ->and($questionnaire->questions()->where('text', $longText)->exists())->toBeTrue();
});
