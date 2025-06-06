<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Affiliate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip the check if we're already on the RequiredOffice page
        if ($request->routeIs('filament.auth.auth.user-affiliation.prompt')) {
            return $next($request);
        }

        $user = $request->user();

        if (! $user?->hasOffice() || ! $user?->hasSection()) {
            return redirect()->route('filament.auth.auth.user-affiliation.prompt');
        }

        return $next($request);
    }
}
