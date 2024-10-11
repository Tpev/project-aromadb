<?php

namespace App\Policies;

use App\Models\Questionnaire;
use App\Models\User;

class QuestionnairePolicy
{
    /**
     * Determine whether the user can view the questionnaire.
     */
    public function view(User $user, Questionnaire $questionnaire)
    {
        \Log::info('Authorizing view for questionnaire', [
            'user_id' => $user->id,
            'questionnaire_user_id' => $questionnaire->user_id,
        ]);

        // Check if the authenticated user is the owner of the questionnaire
        return $user->id === $questionnaire->user_id;
    }

    /**
     * Determine whether the user can update the questionnaire.
     */
    public function update(User $user, Questionnaire $questionnaire)
    {
        \Log::info('Authorizing update for questionnaire', [
            'user_id' => $user->id,
            'questionnaire_user_id' => $questionnaire->user_id,
        ]);

        // Check if the authenticated user is the owner of the questionnaire
        return $user->id === $questionnaire->user_id;
    }

    /**
     * Determine whether the user can delete the questionnaire.
     */
    public function delete(User $user, Questionnaire $questionnaire)
    {
        \Log::info('Authorizing delete for questionnaire', [
            'user_id' => $user->id,
            'questionnaire_user_id' => $questionnaire->user_id,
        ]);

        // Check if the authenticated user is the owner of the questionnaire
        return $user->id === $questionnaire->user_id;
    }
}
