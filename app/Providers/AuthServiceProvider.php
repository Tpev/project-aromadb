<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\ClientProfile;
use App\Policies\DocumentPolicy;
use App\Policies\ClientProfilePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Document::class      => DocumentPolicy::class,
        ClientProfile::class => ClientProfilePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
