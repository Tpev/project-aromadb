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

        $comments = DigitalTrainingBlockComment::with(['module', 'block', 'enrollment'])
            ->where('digital_training_id', $digitalTraining->id)
            ->where('is_visible', true)
            ->latest()
            ->paginate(30);

        return view('digital-trainings.comments.index', [
            'training' => $digitalTraining,
            'comments' => $comments,
        ]);
    }
}
