<?php

namespace App\Services;

use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use JsonException;

class QuestionnairePayloadService
{
    private const MAX_QUESTIONS = 300;

    private const MAX_PAYLOAD_BYTES = 2_000_000;

    public function validate(Request $request): array
    {
        $this->mergeCompactPayload($request);

        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:15000'],
            'questions' => ['required', 'array', 'min:1', 'max:'.self::MAX_QUESTIONS],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.text' => ['required', 'string', 'max:10000'],
            'questions.*.type' => ['required', 'string', 'in:text,multiple_choice'],
            'questions.*.options' => ['nullable', 'string', 'max:15000'],
        ], [
            'questions.max' => 'Un questionnaire peut contenir jusqu’à '.self::MAX_QUESTIONS.' questions.',
            'questions.*.text.max' => 'Une question ne peut pas dépasser 10 000 caractères.',
        ]);

        $validator->after(function ($validator) use ($request): void {
            foreach ($request->input('questions', []) as $index => $question) {
                if (! is_array($question)) {
                    continue;
                }

                if (($question['type'] ?? null) !== 'multiple_choice') {
                    continue;
                }

                if (trim((string) ($question['options'] ?? '')) === '') {
                    $validator->errors()->add(
                        "questions.$index.options",
                        'Les options sont obligatoires pour une question à choix multiple.'
                    );
                }
            }
        });

        $validated = $validator->validate();
        $validated['description'] = filled($validated['description'] ?? null)
            ? trim((string) $validated['description'])
            : null;
        $validated['questions'] = array_values(array_map(function (array $question): array {
            return [
                'id' => ! empty($question['id']) ? (int) $question['id'] : null,
                'text' => trim($question['text']),
                'type' => $question['type'],
                'options' => $question['type'] === 'multiple_choice'
                    ? trim((string) ($question['options'] ?? ''))
                    : null,
            ];
        }, $validated['questions']));

        return $validated;
    }

    public function syncQuestions(Questionnaire $questionnaire, array $questionRows): void
    {
        $existing = $questionnaire->questions()->get()->keyBy('id');
        $keptQuestionIds = [];

        foreach ($questionRows as $row) {
            $question = null;

            if (! empty($row['id'])) {
                $question = $existing->get((int) $row['id']);
                abort_unless($question, 403);
            }

            $question ??= new Question;
            $question->questionnaire_id = $questionnaire->id;
            $question->text = $row['text'];
            $question->type = $row['type'];
            $question->options = $row['options'];
            $question->save();

            $keptQuestionIds[] = $question->id;
        }

        $questionnaire->questions()
            ->whereNotIn('id', $keptQuestionIds)
            ->delete();
    }

    private function mergeCompactPayload(Request $request): void
    {
        if (! $request->exists('questions_payload')) {
            return;
        }

        $payload = (string) $request->input('questions_payload', '');
        if ($payload === '' || strlen($payload) > self::MAX_PAYLOAD_BYTES) {
            throw ValidationException::withMessages([
                'questions' => 'Le questionnaire envoyé est vide ou trop volumineux.',
            ]);
        }

        try {
            $questions = json_decode($payload, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            throw ValidationException::withMessages([
                'questions' => 'Le questionnaire n’a pas pu être lu. Rechargez la page puis réessayez.',
            ]);
        }

        if (! is_array($questions)) {
            throw ValidationException::withMessages([
                'questions' => 'Le format du questionnaire est invalide.',
            ]);
        }

        $request->merge(['questions' => $questions]);
    }
}
