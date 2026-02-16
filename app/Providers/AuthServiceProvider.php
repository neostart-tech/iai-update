<?php

namespace App\Providers;

use App\Models\Reclamation;
use App\Models\User;
use App\Policies\ReclamationPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Reclamation::class => ReclamationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('manage-gallery', function (User $user) {
            // Autoriser uniquement certains rôles (ex: 13,14 = administrateurs)
            return $user->hasRoles(13, 14);
        });
    }
}
