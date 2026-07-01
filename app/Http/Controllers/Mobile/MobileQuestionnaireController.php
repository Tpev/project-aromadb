<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Questionnaire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MobileQuestionnaireController extends Controller
{
    public function index()
    {
        $questionnaires = Questionnaire::query()
            ->withCount('questions')
            ->where('user_id', Auth::id())
            ->latest('updated_at')
            ->latest('id')
            ->get();

        return view('mobile.questionnaires.index', compact('questionnaires'));
    }

    public function create()
    {
        return view('mobile.questionnaires.form', [
            'questionnaire' => new Questionnaire(),
            'title' => 'Nouveau questionnaire',
            'action' => route('mobile.questionnaires.store'),
            'method' => 'POST',
            'submitLabel' => 'Creer',
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedPayload($request);

        $questionnaire = DB::transaction(function () use ($validated) {
            $questionnaire = Questionnaire::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->syncQuestions($questionnaire, $validated['questions']);

            return $questionnaire;
        });

        return redirect()
            ->route('mobile.questionnaires.show', $questionnaire)
            ->with('success', 'Questionnaire cree.');
    }

    public function show(Questionnaire $questionnaire)
    {
        $this->authorizeOwner($questionnaire);

        $questionnaire->load('questions')->loadCount('questions');

        return view('mobile.questionnaires.show', compact('questionnaire'));
    }

    public function edit(Questionnaire $questionnaire)
    {
        $this->authorizeOwner($questionnaire);

        $questionnaire->load('questions');

        return view('mobile.questionnaires.form', [
            'questionnaire' => $questionnaire,
            'title' => 'Modifier le questionnaire',
            'action' => route('mobile.questionnaires.update', $questionnaire),
            'method' => 'PUT',
            'submitLabel' => 'Enregistrer',
        ]);
    }

    public function update(Request $request, Questionnaire $questionnaire)
    {
        $this->authorizeOwner($questionnaire);

        $validated = $this->validatedPayload($request);

        DB::transaction(function () use ($questionnaire, $validated) {
            $questionnaire->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ]);

            $this->syncQuestions($questionnaire, $validated['questions']);
        });

        return redirect()
            ->route('mobile.questionnaires.show', $questionnaire)
            ->with('success', 'Questionnaire mis a jour.');
    }

    public function destroy(Questionnaire $questionnaire)
    {
        $this->authorizeOwner($questionnaire);

        $questionnaire->delete();

        return redirect()
            ->route('mobile.questionnaires.index')
            ->with('success', 'Questionnaire supprime.');
    }

    public function destroyQuestion(Questionnaire $questionnaire, Question $question)
    {
        $this->authorizeOwner($questionnaire);

        abort_unless((int) $question->questionnaire_id === (int) $questionnaire->id, 404);

        $question->delete();

        return redirect()
            ->route('mobile.questionnaires.show', $questionnaire)
            ->with('success', 'Question supprimee.');
    }

    protected function validatedPayload(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'questions' => ['required', 'array', 'min:1'],
            'questions.*.id' => ['nullable', 'integer'],
            'questions.*.text' => ['required', 'string', 'max:255'],
            'questions.*.type' => ['required', 'string', 'in:text,multiple_choice'],
            'questions.*.options' => ['nullable', 'string', 'max:1000'],
        ]);

        foreach ($validated['questions'] as $index => $question) {
            if ($question['type'] === 'multiple_choice' && trim((string) ($question['options'] ?? '')) === '') {
                throw ValidationException::withMessages([
                    "questions.{$index}.options" => 'Les options sont requises pour une question a choix multiple.',
                ]);
            }
        }

        return $validated;
    }

    protected function syncQuestions(Questionnaire $questionnaire, array $questionRows): void
    {
        $keptQuestionIds = [];

        foreach ($questionRows as $row) {
            $question = null;

            if (! empty($row['id'])) {
                $question = $questionnaire->questions()
                    ->whereKey((int) $row['id'])
                    ->first();

                abort_unless($question, 403);
            }

            $question ??= new Question([
                'questionnaire_id' => $questionnaire->id,
            ]);

            $question->questionnaire_id = $questionnaire->id;
            $question->text = trim($row['text']);
            $question->type = $row['type'];
            $question->options = $row['type'] === 'multiple_choice'
                ? trim((string) ($row['options'] ?? ''))
                : null;
            $question->save();

            $keptQuestionIds[] = $question->id;
        }

        $questionnaire->questions()
            ->whereNotIn('id', $keptQuestionIds)
            ->delete();
    }

    protected function authorizeOwner(Questionnaire $questionnaire): void
    {
        abort_unless((int) $questionnaire->user_id === (int) Auth::id(), 403);
    }
}
