<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

         // 🔓 MVP RULE: Admin can do everything
        Gate::before(function ($user, $ability) {
            return $user->role === 'admin' ? true : null;
        });

         // ✅ Attendance permissions for teachers
        Gate::define('view attendances', function ($user) {
            return $user->role === 'teacher';
        });

        Gate::define('take attendances', function ($user) {
            return $user->role === 'teacher';
        });
    }
}
