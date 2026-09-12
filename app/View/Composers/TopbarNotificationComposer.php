<?php

namespace App\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TopbarNotificationComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        $notifications = $user
            ? $user->notifications()->latest()->limit(8)->get()
            : collect();

        $view->with('topbarNotifications', $notifications);
        $view->with('unreadNotificationCount', $user ? $user->unreadNotifications()->count() : 0);
    }
}
