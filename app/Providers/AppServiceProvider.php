<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use App\Models\ClientProfile;
use App\Policies\ClientProfilePolicy;
use Carbon\Carbon;
use App\Services\IpInfoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
             $this->app->bind(IpInfoService::class, function ($app) {
            return new IpInfoService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
		 Carbon::setLocale('fr');
		  \Illuminate\Support\Facades\Gate::policy(ClientProfile::class, ClientProfilePolicy::class);
        // Listen for login event and update login count and last login time
        Event::listen(Login::class, function ($event) {
            $user = $event->user;

            // Update login count and last login timestamp
            $user->login_count = $user->login_count + 1;
            $user->last_login_at = Carbon::now();
            $user->save();
        });
		
		Route::middleware('web')->group(function () {
        app('router')->matched(function ($event) {
            if (
                Auth::check() &&
                Auth::user()->license_status === 'inactive' &&
                !Request::is('license-tiers/pricing') &&
                !Request::is('logout') &&
                !Request::is('sanctum/*')
            ) {
                redirect('/license-tiers/pricing')->send();
                exit; // important to halt further route handling
            }
        });
    });
    }
}
