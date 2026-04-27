<?php

namespace App\Http\Controllers;

use App\Models\DigitalTrainingBlockComment;
use App\Models\DigitalTrainingEnrollment;
use App\Models\TrainingBlock;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicTrainingAccessController extends Controller
{
    /**
     * Display the training player for a given access token.
     */
    public function show(string $token)
    {
        $enrollment = DigitalTrainingEnrollment::where('access_token', $token)
            ->with(['training.modules.blocks'])
            ->first();

        if (!$enrollment) {
            return view('digital-trainings.access-invalid', [
                'reason' => 'invalid',
            ]);
        }

        // Check expiration
        if ($enrollment->token_expires_at && $enrollment->token_expires_at->isPast()) {
            return view('digital-trainings.access-invalid', [
                'reason' => 'expired',
            ]);
        }

        $training = $enrollment->training;

        if (!$training) {
            return view('digital-trainings.access-invalid', [
                'reason' => 'no_training',
            ]);
        }

        // Update tracking
        $now = now();
        if (!$enrollment->first_accessed_at) {
            $enrollment->first_accessed_at = $now;
        }
        $enrollment->last_accessed_at = $now;
        $enrollment->save();

        // Sort modules and blocks by display_order if not already done in relationships
        $blockIds = $training->modules
            ->flatMap(fn ($module) => $module->blocks->pluck('id'))
            ->values();

        $commentsByBlock = DigitalTrainingBlockComment::query()
            ->where('digital_training_id', $training->id)
            ->whereIn('training_block_id', $blockIds)
            ->where('is_visible', true)
            ->with(['replies' => function ($query) {
                $query->where('is_visible', true)->orderBy('created_at');
            }])
            ->orderBy('created_at')
            ->get()
            ->map(fn ($comment) => $this->decorateCommentDisplay($comment, $training))
            ->whereNull('parent_comment_id')
            ->groupBy('training_block_id');

        $modules = $training->modules->sortBy('display_order')->values()->map(function ($module) {
            $module->sorted_blocks = $module->blocks->sortBy('display_order')->values();
            return $module;
        });

        return view('digital-trainings.player', [
            'training'   => $training,
            'enrollment' => $enrollment,
            'modules'    => $modules,
            'commentsByBlock' => $commentsByBlock,
            'selectedBlockId' => (int) request()->query('block', 0),
        ]);
    }

    public function markBlockViewed(Request $request, string $token, TrainingBlock $block): JsonResponse
    {
        $enrollment = DigitalTrainingEnrollment::where('access_token', $token)
            ->with(['training.modules.blocks'])
            ->first();

        if (! $enrollment || ($enrollment->token_expires_at && $enrollment->token_expires_at->isPast())) {
            return new JsonResponse(['message' => 'Lien d’accès invalide ou expiré.'], 403);
        }

        $training = $enrollment->training;
        if (! $training || ! $block->module || $block->module->digital_training_id !== $training->id) {
            return new JsonResponse(['message' => 'Contenu introuvable pour cette formation.'], 404);
        }

        $viewedBlockIds = collect($enrollment->viewed_block_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if (! $viewedBlockIds->contains((int) $block->id)) {
            $viewedBlockIds->push((int) $block->id);
        }

        $totalBlocks = $training->modules
            ->flatMap(fn ($module) => $module->blocks->pluck('id'))
            ->filter()
            ->count();

        $progress = $totalBlocks > 0
            ? (int) round(min(100, ($viewedBlockIds->unique()->count() / $totalBlocks) * 100))
            : 0;

        $enrollment->viewed_block_ids = $viewedBlockIds->unique()->values()->all();
        $enrollment->progress_percent = $progress;

        if ($progress >= 100) {
            $enrollment->completed_at ??= now();
        }

        $enrollment->last_accessed_at = now();
        $enrollment->save();

        return new JsonResponse([
            'progress_percent' => $enrollment->progress_percent,
            'completed' => (bool) $enrollment->completed_at,
        ]);
    }

    public function downloadBlockFile(string $token, TrainingBlock $block): StreamedResponse
    {
        $enrollment = $this->resolveAccessibleEnrollment($token);

        abort_unless($block->module && (int) $block->module->digital_training_id === (int) $enrollment->digital_training_id, 404);
        abort_unless(filled($block->file_path), 404);
        abort_unless(Storage::disk('public')->exists($block->file_path), 404);

        $extension = strtolower(pathinfo((string) $block->file_path, PATHINFO_EXTENSION));
        $safeTitle = Str::slug($block->title ?: $enrollment->training?->title ?: 'contenu');
        $fallbackBase = match ((string) $block->type) {
            'video_url' => 'video-formation',
            'audio' => 'audio-formation',
            'pdf' => 'document-formation',
            default => 'fichier-formation',
        };

        $fileName = ($safeTitle !== '' ? $safeTitle : $fallbackBase) . ($extension !== '' ? '.' . $extension : '');

        return Storage::disk('public')->download($block->file_path, $fileName);
    }

    /**
     * Mark the training as completed for this token.
     */
    public function markCompleted(string $token, Request $request)
    {
        $enrollment = DigitalTrainingEnrollment::where('access_token', $token)->first();

        if (!$enrollment) {
            return view('digital-trainings.access-invalid', [
                'reason' => 'invalid',
            ]);
        }

        if ($enrollment->token_expires_at && $enrollment->token_expires_at->isPast()) {
            return view('digital-trainings.access-invalid', [
                'reason' => 'expired',
            ]);
        }

        $enrollment->progress_percent = 100;
        if (!$enrollment->completed_at) {
            $enrollment->completed_at = now();
        }
        $enrollment->save();

        return redirect()
            ->route('digital-trainings.access.show', $enrollment->access_token)
            ->with('success', 'Bravo ! Votre formation est marquée comme terminée.');
    }

    protected function decorateCommentDisplay(DigitalTrainingBlockComment $comment, $training): DigitalTrainingBlockComment
    {
        $name = trim((string) ($comment->participant_name_snapshot ?? ''));
        $firstName = $name !== '' ? Str::of($name)->before(' ')->trim()->value() : null;

        if (! $firstName && filled($comment->participant_email_snapshot)) {
            $firstName = Str::of((string) $comment->participant_email_snapshot)->before('@')->trim()->value();
        }

        if ($comment->created_by_role === 'therapist') {
            $fallback = trim((string) optional($training->user)->name);
            $firstName = $firstName ?: ($fallback !== '' ? Str::of($fallback)->before(' ')->trim()->value() : null);
            $comment->setAttribute('participant_first_name', $firstName ?: 'Votre thérapeute');
            $comment->setAttribute('author_role_label', 'Thérapeute');
        } else {
            $comment->setAttribute('participant_first_name', $firstName ?: 'Participant');
            $comment->setAttribute('author_role_label', 'Participant');
        }

        $comment->setRelation('replies', $comment->replies->map(
            fn ($reply) => $this->decorateCommentDisplay($reply, $training)
        )->values());

        return $comment;
    }

    protected function resolveAccessibleEnrollment(string $token): DigitalTrainingEnrollment
    {
        $enrollment = DigitalTrainingEnrollment::where('access_token', $token)
            ->with('training')
            ->first();

        abort_unless($enrollment, 404);
        abort_unless(!($enrollment->token_expires_at && $enrollment->token_expires_at->isPast()), 403);

        return $enrollment;
    }
}
