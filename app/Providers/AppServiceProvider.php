<?php

namespace App\Providers;

use App\Enums\Role;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

public function boot(): void
{
    // Admin y Privilegio tienen acceso a todo.
    Gate::before(function ($user, string $ability) {
        if (in_array($user->role, [
            Role::ADMIN,
            Role::PRIVILEGED,
        ], true)) {
            return true;
        }

        return null;
    });

        // Panel de trabajo: todos los roles autenticados.
        Gate::define('view-work-panel', fn ($user) =>
            $user->role === Role::SUPERVISOR
        );

        // Supervisor, Admin y Privilegio.
        Gate::define('view-products', fn ($user) =>
            $user->role === Role::SUPERVISOR
        );

        Gate::define('view-categories', fn ($user) =>
            $user->role === Role::SUPERVISOR
        );

        Gate::define('view-customers', fn ($user) =>
            $user->role === Role::SUPERVISOR
        );

        Gate::define('view-orders', fn ($user) =>
            $user->role === Role::SUPERVISOR
        );

        Gate::define('view-reports', fn ($user) =>
            $user->role === Role::SUPERVISOR
        );

        // Solo Admin y Privilegio, mediante Gate::before.
        Gate::define('manage-quotations', fn ($user) => false);

        Gate::define('manage-production', fn ($user) => false);

        Gate::define('manage-users', fn ($user) => false);
    }
}
