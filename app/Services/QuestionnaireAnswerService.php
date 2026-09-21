<?php

namespace App\Services;

use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuestionnaireAnswerService
{
    public function validate(Request $request, Questionnaire $questionnaire): array
    {
        $questions = $questionnaire->questions;
        $rules = ['answers' => ['required', $questions->isEmpty() ? 'array' : 'array:'.implode(',', $questions->modelKeys())]];

        foreach ($questions as $question) {
            $key = 'answers.'.$question->id;
            if ($question->allowsMultipleAnswers()) {
                // A previously opened single-choice form can still submit one answer.
                if (is_string($request->input($key))) {
                    $request->merge(['answers' => array_replace($request->input('answers', []), [
                        $question->id => [$request->input($key)],
                    ])]);
                }
                $rules[$key] = ['required', 'array', 'min:1', 'max:'.count($question->choiceOptions())];
                $rules[$key.'.*'] = ['required', 'string', 'distinct:strict', Rule::in($question->choiceOptions())];
            } else {
                $rules[$key] = ['required', 'string'];
                if ($question->type === 'multiple_choice') {
                    $rules[$key][] = Rule::in($question->choiceOptions());
                }
            }
        }

        $validated = $request->validate($rules, [
            'answers.required' => 'Veuillez répondre au questionnaire.',
            'answers.array' => 'Les réponses ne correspondent pas à ce questionnaire.',
            'answers.*.required' => 'Veuillez répondre à cette question.',
            'answers.*.array' => 'Veuillez sélectionner les réponses proposées.',
            'answers.*.string' => 'Veuillez fournir une seule réponse à cette question.',
            'answers.*.in' => 'Veuillez sélectionner une réponse proposée.',
            'answers.*.*.in' => 'Veuillez sélectionner uniquement les réponses proposées.',
            'answers.*.*.distinct' => 'Une même réponse ne peut être sélectionnée plusieurs fois.',
        ]);

        return array_map(fn ($answer) => is_array($answer) ? array_values($answer) : $answer, $validated['answers']);
    }
}
