<?php

namespace App\Policies;

use App\Models\SessionNote;
use App\Models\User;

class SessionNotePolicy
{
    /**
     * Determine whether the user can view the session note.
     */
    public function view(User $user, SessionNote $sessionNote)
    {
        return $user->id === $sessionNote->user_id;
    }

    /**
     * Determine whether the user can create a session note.
     */
    public function create(User $user)
    {
        return $user->is_therapist; // Assuming therapists can create session notes.
    }

    /**
     * Determine whether the user can update the session note.
     */
    public function update(User $user, SessionNote $sessionNote)
    {
        return $user->id === $sessionNote->user_id;
    }

    /**
     * Determine whether the user can delete the session note.
     */
    public function delete(User $user, SessionNote $sessionNote)
    {
        return $user->id === $sessionNote->user_id;
    }
}
