<?php

namespace App\Http\Controllers;

use App\Models\DigitalTrainingBlockComment;
use App\Models\DigitalTrainingEnrollment;
use App\Models\TrainingBlock;
use App\Notifications\DigitalTrainingCommentPosted;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PublicTrainingCommentController extends Controller
{
    public function store(Request $request, string $token, TrainingBlock $block)
    {
        $enrollment = DigitalTrainingEnrollment::where('access_token', $token)
            ->with(['training.user', 'clientProfile'])
            ->first();

        if (!$enrollment || ($enrollment->token_expires_at && $enrollment->token_expires_at->isPast())) {
            return $this->errorResponse($request, 'Lien d’accès invalide ou expiré.', 403);
        }

        $training = $enrollment->training;
        if (!$training || !$block->module || $block->module->digital_training_id !== $training->id) {
            return $this->errorResponse($request, 'Contenu introuvable pour cette formation.', 404);
        }

        if (!$block->commentsEnabled()) {
            return $this->errorResponse($request, 'Les commentaires sont désactivés pour ce contenu.', 403);
        }

        $data = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $comment = DigitalTrainingBlockComment::create([
            'digital_training_id' => $training->id,
            'training_module_id' => $block->training_module_id,
            'training_block_id' => $block->id,
            'digital_training_enrollment_id' => $enrollment->id,
            'client_profile_id' => $enrollment->client_profile_id,
            'participant_name_snapshot' => $enrollment->participant_name,
            'participant_email_snapshot' => $enrollment->participant_email,
            'comment' => trim($data['comment']),
            'created_by_role' => 'participant',
            'is_visible' => true,
        ]);

        try {
            $training->user?->notify(new DigitalTrainingCommentPosted($comment->load(['training', 'block'])));
        } catch (\Throwable $e) {
            Log::error('Failed to send digital training comment notification: ' . $e->getMessage());
        }

        if ($request->expectsJson()) {
            $displayName = trim((string) ($comment->participant_name_snapshot ?? ''));
            $displayName = $displayName !== '' ? Str::of($displayName)->before(' ')->trim()->value() : null;

            if (!$displayName && filled($comment->participant_email_snapshot)) {
                $displayName = Str::of((string) $comment->participant_email_snapshot)->before('@')->trim()->value();
            }

            return new JsonResponse([
                'message' => 'Commentaire envoyé.',
                'comment' => [
                    'id' => $comment->id,
                    'comment' => $comment->comment,
                    'participant_name' => $displayName ?: 'Participant',
                    'author_role' => 'participant',
                    'author_role_label' => 'Participant',
                    'created_at_label' => $comment->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                ],
            ]);
        }

        return redirect()
            ->route('digital-trainings.access.show', ['token' => $token, 'block' => $block->id])
            ->with('success', 'Votre commentaire a bien été envoyé.');
    }

    protected function errorResponse(Request $request, string $message, int $status)
    {
        if ($request->expectsJson()) {
            return new JsonResponse(['message' => $message], $status);
        }

        abort($status, $message);
    }
}
