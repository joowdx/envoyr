<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserNotDeactivated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->deactivated_at) {
            Auth::logout();
            return redirect()->route('filament..auth.login')
                ->withErrors(['account' => 'Your account has been deactivated. Contact your administrator.']);
        }

        return $next($request);
    }
}
