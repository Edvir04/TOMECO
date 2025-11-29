<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via any admin guard
        $isAuthenticated = Auth::guard('superadmin')->check() 
                        || Auth::guard('admin')->check() 
                        || Auth::guard('enforcer')->check();

        if (!$isAuthenticated) {
            return redirect()->route('admin.login')
                ->withErrors(['message' => 'Please login to access the admin portal.']);
        }

        return $next($request);
    }
}

