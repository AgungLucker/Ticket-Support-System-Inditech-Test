<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('access-supervisor', fn (User $user): bool => $user->isSupervisor());
        Gate::define('access-agent', fn (User $user): bool => $user->isAgent());
        Gate::define('access-customer', fn (User $user): bool => $user->isCustomer());
        Gate::define('export-ticket-reports', fn (User $user): bool => $user->isAdmin() || $user->isSupervisor());
    }
}
