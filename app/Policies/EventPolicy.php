<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any events.
     */
    public function viewAny(User $user)
    {
        // Allow any authenticated user to view their own events
        return true;
    }

    /**
     * Determine whether the user can view the event.
     */
    public function view(User $user, Event $event)
    {
        // Allow if the user owns the event
        return $user->id === $event->user_id;
    }

    /**
     * Determine whether the user can create events.
     */
    public function create(User $user)
    {
        // Allow any authenticated user to create an event
        return true;
    }

    /**
     * Determine whether the user can update the event.
     */
    public function update(User $user, Event $event)
    {
        // Allow if the user owns the event
        return $user->id === $event->user_id;
    }

    /**
     * Determine whether the user can delete the event.
     */
    public function delete(User $user, Event $event)
    {
        // Allow if the user owns the event
        return $user->id === $event->user_id;
    }

    // Include other methods like restore and forceDelete if needed
}
