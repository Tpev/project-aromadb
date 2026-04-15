<?php

namespace App\Http\Controllers;

use App\Models\DigitalTrainingBlockComment;
use App\Models\DigitalTrainingEnrollment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
}
