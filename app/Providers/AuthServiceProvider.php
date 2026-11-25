<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // RBAC Gates
        Gate::define('admin', fn($user) => $user->role === 'admin');
        Gate::define('hrmu-staff', fn($user) => in_array($user->role, ['admin', 'hrmu_staff']));
        Gate::define('routing-clerk', fn($user) => in_array($user->role, ['admin', 'hrmu_staff', 'routing_clerk']));
        Gate::define('employee', fn($user) => true);
    }
}
