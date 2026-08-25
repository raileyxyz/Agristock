<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use App\Listeners\UpdateLastLogin;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use App\View\Composers\SidebarComposer;
use Illuminate\Support\ServiceProvider;
use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;

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
        View::composer('components.sidebar', SidebarComposer::class);
        Gate::policy(User::class, UserPolicy::class);
        Event::listen(Login::class, UpdateLastLogin::class);

        $this->registerAbilityGates();
    }

    protected function registerAbilityGates(): void
    {
        Gate::before(fn (User $user) => $user->isAdmin() ? true : null);

        foreach (config('abilities') as $ability => $allowedRoles) {
            Gate::define($ability, fn (User $user) => in_array($user->role, $allowedRoles, true));
        }
    }
}
