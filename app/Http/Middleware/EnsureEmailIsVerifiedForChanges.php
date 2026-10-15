<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedForChanges
{
    /**
     * Routes that can be accessed even before the user's email is verified.
     */
    private const ALLOWED_ROUTES = [
        'profile.edit',
        'profile.update',
        'profile.avatar',
        'password.update',
        'verification.send',
        'logout',
        'notification-preferences.update',
        'notifications.read',
        'notifications.read-all',
        'chatbot.messages.store',
        'chatbot.messages.destroy',
        'profile.deletion-request',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        if ($routeName && in_array($routeName, self::ALLOWED_ROUTES, true)) {
            return $next($request);
        }

        $isChange = ! $request->isMethodSafe();
        $isForm = $routeName && preg_match('/\.(create|edit)$/', $routeName);

        if (! $isChange && ! $isForm) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            abort(403, 'Verify your email address first. You cannot make changes until your email is verified.');
        }

        return redirect()->route('profile.edit')->with('status', 'verification-required');
    }
}
