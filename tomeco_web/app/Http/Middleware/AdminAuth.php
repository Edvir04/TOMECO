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
        // Only allow superadmin and admin to access the admin portal
        // Enforcers should use the mobile app
        $isAuthenticated = Auth::guard('superadmin')->check() 
                        || Auth::guard('admin')->check();

        if (!$isAuthenticated) {
            // If an enforcer is somehow logged in, logout them
            if (Auth::guard('enforcer')->check()) {
                Auth::guard('enforcer')->logout();
                return redirect()->route('admin.login')
                    ->withErrors(['message' => 'Enforcers can only access the mobile app. Please use the mobile application.']);
            }
            
            // Check if session expired or was invalidated (e.g., server restart)
            if ($request->session()->has('_token')) {
                // Session exists but auth failed - likely expired
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            
            return redirect()->route('admin.login')
                ->withErrors(['message' => 'Please login to access the admin portal.']);
        }

        // Add cache control headers to prevent back button issues
        $response = $next($request);
        
        // Prevent caching of protected pages
        if (!$response->headers->has('Cache-Control')) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        
        return $response;
    }
}

