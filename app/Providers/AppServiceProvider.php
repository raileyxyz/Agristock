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
        Gate::before(fn (User $user) => $user->isAdmin() ? true : null);

        foreach (config('abilities') as $ability => $allowedRoles) {
            Gate::define($ability, fn (User $user) => in_array($user->role, $allowedRoles, true));
        }
    }

    protected function registerNotificationListeners(): void
    {
        Event::listen(\App\Events\StockReceived::class, \App\Listeners\SendStockReceivedNotification::class);
        Event::listen(\App\Events\StockOutRecorded::class, \App\Listeners\SendStockOutNotification::class);
        Event::listen(\App\Events\StockTransferCompleted::class, \App\Listeners\SendStockTransferNotification::class);
        Event::listen(\App\Events\StockAdjusted::class, \App\Listeners\SendStockAdjustmentNotification::class);
        Event::listen(\App\Events\NewUserAdded::class, \App\Listeners\SendNewUserAddedNotification::class);
        Event::listen(\App\Events\UserAccountArchived::class, \App\Listeners\SendUserArchivedNotification::class);
        Event::listen(\App\Events\UserRoleChanged::class, \App\Listeners\SendUserRoleChangedNotification::class);
    }
}
