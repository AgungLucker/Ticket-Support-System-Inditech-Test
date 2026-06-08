<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        Gate::define('access-admin', fn (User $user): bool => $user->isAdmin());
        Gate::define('access-supervisor', fn (User $user): bool => $user->isSupervisor());
        Gate::define('access-agent', fn (User $user): bool => $user->isAgent());
        Gate::define('access-customer', fn (User $user): bool => $user->isCustomer());
        Gate::define('export-ticket-reports', fn (User $user): bool => $user->isAdmin() || $user->isSupervisor());
    }
}
