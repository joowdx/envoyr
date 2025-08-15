<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordReset
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && is_null(Auth::user()->password_reset_at)) {
            // Don't redirect if on password reset routes
            if (! $request->routeIs('password.reset.force')) {
                return redirect()->route('password.reset.force');
            }
        }

        return $next($request);
    }

    public function needsPasswordReset(): bool
    {
        return Auth::check() && is_null(Auth::user()->password_reset_at);
    }
}
