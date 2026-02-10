<?php

namespace App\Policies;

use App\Models\TherapistArticle;
use App\Models\User;

class TherapistArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return (bool) ($user->is_therapist ?? true);
    }

    public function view(User $user, TherapistArticle $article): bool
    {
        return $article->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return (bool) ($user->is_therapist ?? true);
    }

    public function update(User $user, TherapistArticle $article): bool
    {
        return $article->user_id === $user->id;
    }

    public function delete(User $user, TherapistArticle $article): bool
    {
        return $article->user_id === $user->id;
    }
}
