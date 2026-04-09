<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAccessExpiration
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // If access is expired and it's not the expired page or logout route
            if ($user->access_upto && $user->access_upto->isPast()) {
                if (!$request->routeIs('access.expired') && !$request->routeIs('logout')) {
                    return redirect()->route('access.expired');
                }
            }
        }

        return $next($request);
    }
}
