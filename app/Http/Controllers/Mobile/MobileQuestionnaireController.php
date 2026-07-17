<?php

namespace App\Http\Controllers\Mobile;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\Questionnaire;
use App\Services\QuestionnairePayloadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request, QuestionnairePayloadService $payloadService)
    {
        $validated = $payloadService->validate($request);

        $questionnaire = DB::transaction(function () use ($validated, $payloadService) {
            $questionnaire = Questionnaire::create([
                'user_id' => Auth::id(),
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ]);

            $payloadService->syncQuestions($questionnaire, $validated['questions']);

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

    public function update(
        Request $request,
        Questionnaire $questionnaire,
        QuestionnairePayloadService $payloadService
    )
    {
        $this->authorizeOwner($questionnaire);

        $validated = $payloadService->validate($request);

        DB::transaction(function () use ($questionnaire, $validated, $payloadService) {
            $questionnaire->update([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
            ]);

            $payloadService->syncQuestions($questionnaire, $validated['questions']);
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

    protected function authorizeOwner(Questionnaire $questionnaire): void
    {
        abort_unless((int) $questionnaire->user_id === (int) Auth::id(), 403);
    }
}
