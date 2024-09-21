<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Models\ClientProfile;
use App\Policies\ClientProfilePolicy;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
		  \Illuminate\Support\Facades\Gate::policy(ClientProfile::class, ClientProfilePolicy::class);
        // Listen for login event and update login count and last login time
        Event::listen(Login::class, function ($event) {
            $user = $event->user;

            // Update login count and last login timestamp
            $user->login_count = $user->login_count + 1;
            $user->last_login_at = Carbon::now();
            $user->save();
        });
    }
}
