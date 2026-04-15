<?php

namespace App\Http\Controllers;

use App\Models\DigitalTraining;
use App\Models\DigitalTrainingBlockComment;
use Illuminate\Support\Facades\Auth;

class DigitalTrainingCommentController extends Controller
{
    public function index(DigitalTraining $digitalTraining)
    {
        if ($digitalTraining->user_id !== Auth::id()) {
            abort(403);
        }

        $comments = DigitalTrainingBlockComment::with([
                'module',
                'block',
                'enrollment',
                'replies' => function ($query) {
                    $query->where('is_visible', true)->orderBy('created_at');
                },
            ])
            ->where('digital_training_id', $digitalTraining->id)
            ->where('is_visible', true)
            ->whereNull('parent_comment_id')
            ->latest()
            ->paginate(30);

        return view('digital-trainings.comments.index', [
            'training' => $digitalTraining,
            'comments' => $comments,
        ]);
    }
}
