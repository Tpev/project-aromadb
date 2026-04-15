<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingBlockComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TherapistTrainingCommentReplyController extends Controller
{
    public function store(Request $request, DigitalTraining $digitalTraining, DigitalTrainingBlockComment $comment)
    {
        if ($digitalTraining->user_id !== Auth::id()) {
            abort(403);
        }

        if ($comment->digital_training_id !== $digitalTraining->id) {
            abort(404);
        }

        $parentComment = $comment->parent_comment_id ? ($comment->parent ?: $comment) : $comment;

        $data = $request->validate([
            'comment' => 'required|string|max:2000',
        ]);

        $therapist = Auth::user();

        DigitalTrainingBlockComment::create([
            'digital_training_id' => $digitalTraining->id,
            'training_module_id' => $parentComment->training_module_id,
            'training_block_id' => $parentComment->training_block_id,
            'digital_training_enrollment_id' => $parentComment->digital_training_enrollment_id,
            'client_profile_id' => $parentComment->client_profile_id,
            'parent_comment_id' => $parentComment->id,
            'participant_name_snapshot' => $therapist->name ?: 'Votre thérapeute',
            'participant_email_snapshot' => $therapist->email,
            'comment' => trim($data['comment']),
            'created_by_role' => 'therapist',
            'is_visible' => true,
        ]);

        return redirect()
            ->route('digital-trainings.comments.index', $digitalTraining)
            ->with('success', 'Votre réponse a bien été envoyée.');
    }
}
