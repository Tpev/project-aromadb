<?php

use App\Mail\QuestionnaireCompletedMail;
use App\Models\ClientProfile;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Models\Response as QuestionnaireResponse;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

function multipleAnswersFixture(?bool $allowMultiple = false): array
{
    $therapist = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $client = ClientProfile::create([
        'user_id' => $therapist->id, 'first_name' => 'Alice', 'last_name' => 'Questionnaire',
        'email' => 'questionnaire-client@example.test',
    ]);
    $questionnaire = Questionnaire::create(['user_id' => $therapist->id, 'title' => 'Bilan']);
    $question = new Question;
    $question->forceFill([
        'questionnaire_id' => $questionnaire->id, 'text' => 'Vos préférences ?',
        'type' => 'multiple_choice', 'options' => 'Option A, Option B, 0',
        'allow_multiple' => $allowMultiple,
    ])->save();
    $response = QuestionnaireResponse::create([
        'questionnaire_id' => $questionnaire->id, 'client_profile_id' => $client->id,
        'token' => Str::random(32), 'answers' => [],
    ]);

    return [$therapist, $questionnaire, $question, $response];
}

function multipleAnswersInputs(string $html, string $name): DOMNodeList
{
    $dom = new DOMDocument;
    @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);

    return (new DOMXPath($dom))->query('//input[@type="checkbox" and @name="'.$name.'"]');
}

test('the optional setting is saved per question on desktop and mobile', function ($route, $flag) {
    $therapist = User::factory()->create(['is_therapist' => true, 'license_status' => 'active']);
    $row = ['text' => 'Vos choix ?', 'type' => 'multiple_choice', 'options' => 'A, B'];
    if ($flag !== 'missing') {
        $row['allow_multiple'] = $flag;
    }
    $this->actingAs($therapist)->post(route($route), [
        'title' => 'Réglages par question',
        'questions_payload' => json_encode([$row, ['text' => 'Autre question', 'type' => 'text', 'allow_multiple' => true]]),
    ])->assertSessionHasNoErrors()->assertRedirect();

    $questions = Questionnaire::where('user_id', $therapist->id)->firstOrFail()->questions;
    expect($questions[0]->allowsMultipleAnswers())->toBe($flag === true)
        ->and($questions[1]->allowsMultipleAnswers())->toBeFalse()
        ->and($questions[1]->allow_multiple)->toBeFalse();
})->with(['questionnaires.store', 'mobile.questionnaires.store'])->with([true, false, null, 'missing']);

test('existing editors can enable disable and preserve the optional setting', function ($route) {
    [$therapist, $questionnaire, $question] = multipleAnswersFixture(null);
    $row = ['id' => $question->id, 'text' => $question->text, 'type' => 'multiple_choice', 'options' => $question->options];
    foreach ([true, 'missing', false, true, null] as $flag) {
        $updated = $row;
        if ($flag !== 'missing') {
            $updated['allow_multiple'] = $flag;
        }
        $this->actingAs($therapist)->put(route($route, $questionnaire), [
            'title' => $questionnaire->title, 'questions' => [$updated],
        ])->assertSessionHasNoErrors()->assertRedirect();
        expect($question->fresh()->allowsMultipleAnswers())->toBe(in_array($flag, [true, 'missing'], true));
    }
    $this->put(route($route, $questionnaire), [
        'title' => $questionnaire->title,
        'questions' => [array_replace($row, ['type' => 'text', 'allow_multiple' => true])],
    ])->assertSessionHasNoErrors();
    expect($question->fresh()->allow_multiple)->toBeFalse();
})->with(['questionnaires.update', 'mobile.questionnaires.update']);

test('desktop and mobile editors show checked state without crashing on null', function ($route, $flag) {
    [$therapist, $questionnaire] = multipleAnswersFixture($flag);
    $html = $this->actingAs($therapist)->get(route($route, $questionnaire))
        ->assertOk()->assertSee('Autoriser plusieurs réponses')->getContent();
    $inputs = multipleAnswersInputs($html, 'questions[0][allow_multiple]');
    expect($inputs->length)->toBe(1)
        ->and($inputs->item(0)->hasAttribute('checked'))->toBe($flag === true);
})->with(['questionnaires.edit', 'mobile.questionnaires.edit'])->with([true, false, null]);

test('existing null and false settings keep the client single choice form and submission', function ($flag) {
    [$therapist, $questionnaire, $question, $response] = multipleAnswersFixture($flag);
    $this->get(route('questionnaires.fill', $response->token))->assertOk()
        ->assertSee('<select name="answers['.$question->id.']"', false)
        ->assertDontSee('name="answers['.$question->id.'][]"', false);
    $this->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => 'Option A'],
    ])->assertSessionHasNoErrors()->assertRedirect(route('thank_you'));
    expect($response->fresh()->decodedAnswers())->toBe([$question->id => 'Option A']);
})->with([false, null]);

test('enabled questions save every selected answer and render safe client details and email', function ($authenticated) {
    Mail::fake();
    [$therapist, $questionnaire, $question, $response] = multipleAnswersFixture(true);
    $question->forceFill(['options' => 'Option A, <script>alert(1)</script>, 0'])->save();
    $textQuestion = Question::create(['questionnaire_id' => $questionnaire->id, 'text' => 'Commentaire', 'type' => 'text']);
    $html = $this->get(route('questionnaires.fill', $response->token))->assertOk()->getContent();
    expect(multipleAnswersInputs($html, 'answers['.$question->id.'][]')->length)->toBe(3);
    if ($authenticated) {
        $this->actingAs($therapist);
    }
    $selected = ['Option A', '<script>alert(1)</script>', '0'];
    $this->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => $selected, $textQuestion->id => 'Réponse libre'],
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect($response->fresh()->decodedAnswers())->toBe([$question->id => $selected, $textQuestion->id => 'Réponse libre']);
    expect($response->fresh()->is_completed)->toBeTrue();

    // Historical answers remain readable even if the therapist later disables the setting.
    $question->update(['allow_multiple' => false]);
    $this->actingAs($therapist)->get(route('questionnaires.responses.show', $response))->assertOk()
        ->assertSee('Option A')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
        ->assertDontSee('<script>alert(1)</script>', false)->assertSee('Réponse libre');
    $mail = (new QuestionnaireCompletedMail($response->fresh()))->render();
    expect($mail)->toContain('Option A', '&lt;script&gt;alert(1)&lt;/script&gt;', 'Réponse libre');
    if ($authenticated) {
        Mail::assertQueued(QuestionnaireCompletedMail::class, 1);
    } else {
        Mail::assertNothingQueued();
    }
})->with([true, false]);

test('a single answer from a previously opened form is still accepted after enabling multiple answers', function () {
    [, , $question, $response] = multipleAnswersFixture(true);
    $this->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => 'Option B'],
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect($response->fresh()->decodedAnswers())->toBe([$question->id => ['Option B']]);
});

test('invalid multiple answers do not overwrite a response', function ($selection) {
    Mail::fake();
    [$therapist, , $question, $response] = multipleAnswersFixture(true);
    $this->actingAs($therapist)->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => $selection],
    ])->assertSessionHasErrors();
    expect($response->fresh()->is_completed)->toBeFalse()
        ->and($response->fresh()->decodedAnswers())->toBe([]);
    Mail::assertNothingQueued();
})->with([
    'empty' => [[]], 'null' => [null], 'unknown choice' => [['Unknown']],
    'duplicate' => [['Option A', 'Option A']], 'nested' => [[['Option A']]], 'number' => [12],
]);

test('single choice questions reject multiple submitted values', function ($flag) {
    [, , $question, $response] = multipleAnswersFixture($flag);
    $this->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => ['Option A', 'Option B']],
    ])->assertSessionHasErrors('answers.'.$question->id);
    expect($response->fresh()->is_completed)->toBeFalse();
})->with([false, null]);

test('missing answers and foreign question ids are rejected', function () {
    [, $questionnaire, $question, $response] = multipleAnswersFixture(true);
    $text = Question::create(['questionnaire_id' => $questionnaire->id, 'text' => 'Texte', 'type' => 'text']);
    $this->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$text->id => 'Autre réponse'],
    ])->assertSessionHasErrors('answers.'.$question->id);
    $this->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => ['Option A'], $text->id => 'Texte', 999999 => 'Foreign'],
    ])->assertSessionHasErrors('answers');
    expect($response->fresh()->decodedAnswers())->toBe([]);
});

test('selected answers survive validation errors on another question', function () {
    [, $questionnaire, $question, $response] = multipleAnswersFixture(true);
    $text = Question::create(['questionnaire_id' => $questionnaire->id, 'text' => 'Texte', 'type' => 'text']);
    $url = route('questionnaires.fill', $response->token);
    $this->from($url)->post(route('questionnaires.storeResponses', $response->token), [
        'answers' => [$question->id => ['Option A', 'Option B']],
    ])->assertRedirect($url)->assertSessionHasErrors('answers.'.$text->id);
    $html = $this->get($url)->assertOk()->getContent();
    $inputs = multipleAnswersInputs($html, 'answers['.$question->id.'][]');
    expect($inputs->item(0)->hasAttribute('checked'))->toBeTrue()
        ->and($inputs->item(1)->hasAttribute('checked'))->toBeTrue()
        ->and($inputs->item(2)->hasAttribute('checked'))->toBeFalse();
});

test('historical response formats including null still render in details and emails', function ($format) {
    [$therapist, , $question, $response] = multipleAnswersFixture(null);
    $values = [$question->id => 'Ancienne réponse'];
    $response->answers = match ($format) {
        'legacy' => json_encode($values), 'array' => $values,
        'empty legacy' => '[]', 'empty array' => [], 'null' => null,
    };
    // The original column is NOT NULL; a JSON null is a supported legacy value.
    if ($format === 'null') {
        DB::table('responses')->where('id', $response->id)->update(['answers' => 'null']);
    } else {
        $response->save();
    }
    $page = $this->actingAs($therapist)->get(route('questionnaires.responses.show', $response))->assertOk();
    $mail = (new QuestionnaireCompletedMail($response->fresh()))->render();
    if (in_array($format, ['legacy', 'array'], true)) {
        $page->assertSee('Ancienne réponse');
        expect($mail)->toContain('Ancienne réponse');
    } else {
        expect($response->fresh()->decodedAnswers())->toBe([]);
    }
})->with(['legacy', 'array', 'empty legacy', 'empty array', 'null']);

test('the additive migration preserves existing questions and answers and defaults to single choice', function () {
    [, , $question, $response] = multipleAnswersFixture(null);
    $response->answers = json_encode([$question->id => 'Option B']);
    $response->save();
    $migration = require database_path('migrations/2026_09_21_120000_add_allow_multiple_to_questions_table.php');
    $migration->down();
    try {
        $before = DB::table('questions')->where('id', $question->id)->first();
        $answerBefore = DB::table('responses')->where('id', $response->id)->value('answers');
    } finally {
        $migration->up();
    }
    expect($question->fresh()->text)->toBe($before->text)
        ->and($question->fresh()->options)->toBe($before->options)
        ->and($question->fresh()->allowsMultipleAnswers())->toBeFalse()
        ->and(DB::table('responses')->where('id', $response->id)->value('answers'))->toBe($answerBefore);
});
