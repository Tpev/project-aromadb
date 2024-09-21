<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ClientProfile;

class ClientProfilePolicy
{
    /**
     * Determine whether the user can view the client profile.
     */
public function view(User $user, ClientProfile $clientProfile)
{
    \Log::info('Authorizing view', ['user_id' => $user->id, 'client_profile_user_id' => $clientProfile->user_id]);
    return $user->id === $clientProfile->user_id;
}


    /**
     * Determine whether the user can update the client profile.
     */
    public function update(User $user, ClientProfile $clientProfile)
    {
        return $user->id === $clientProfile->user_id;
    }

    /**
     * Determine whether the user can delete the client profile.
     */
    public function delete(User $user, ClientProfile $clientProfile)
    {
        return $user->id === $clientProfile->user_id;
    }
	
	public function viewAny(User $user)
{
    // Only allow users who are therapists
    return $user->is_therapist;
}

}
