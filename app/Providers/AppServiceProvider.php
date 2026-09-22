<?php

namespace App\Providers;

use Illuminate\Auth\Events\Login;
use App\Listeners\UpdateLastLogin;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use App\View\Composers\SidebarComposer;
use App\View\Composers\TopbarNotificationComposer;
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
        View::composer('components.topbar', TopbarNotificationComposer::class);
        Gate::policy(User::class, UserPolicy::class);
        Event::listen(Login::class, UpdateLastLogin::class);

        $this->registerAbilityGates();
    }

    protected function registerAbilityGates(): void
    {
        Gate::before(function (User $user, string $ability, array $arguments = []) {
            if (! $user->isAdmin()) {
                return null;
            }

            $isDeletingSelf = $ability === 'delete' && ($arguments[0] ?? null) instanceof User && $arguments[0]->is($user);

            if ($isDeletingSelf) {
                return null;
            }

            return true;
        });

        foreach (config('abilities') as $ability => $allowedRoles) {
            Gate::define($ability, fn (User $user) => in_array($user->role, $allowedRoles, true));
        }
    }
}
