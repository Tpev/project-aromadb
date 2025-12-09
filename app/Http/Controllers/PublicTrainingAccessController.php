<?php

namespace App\Http\Controllers;

use App\Models\DigitalTrainingEnrollment;
use Illuminate\Http\Request;

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
        $modules = $training->modules->sortBy('display_order')->values()->map(function ($module) {
            $module->sorted_blocks = $module->blocks->sortBy('display_order')->values();
            return $module;
        });

        return view('digital-trainings.player', [
            'training'   => $training,
            'enrollment' => $enrollment,
            'modules'    => $modules,
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
}
