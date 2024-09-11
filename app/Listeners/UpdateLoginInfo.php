<?php

namespace App\Listeners;

use App\Events\UserLogin;
use Illuminate\Support\Facades\DB;

class UpdateLoginInfo
{
    /**
     * Handle the event.
     *
     * @param  \App\Events\UserLogin  $event
     * @return void
     */
    public function handle(UserLogin $event)
    {
        // Update the login count and last login timestamp
        $user = $event->user;

        $user->login_count = $user->login_count + 1;
        $user->last_login_at = now();

        // Save the updated user info
        $user->save();
    }
}
