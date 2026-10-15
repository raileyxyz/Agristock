<?php

namespace App\Http\Middleware;

use Closure;
use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->status !== Status::ACTIVE) {
            $deleted = Auth::user()->status === Status::DELETED;

            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'email' => $deleted ? 'Your account has been deleted.' : 'Your account has been archived.',
            ]);
        }

        return $next($request);
    }
}
