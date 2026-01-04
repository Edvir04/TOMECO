<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\PublicRegistrationController;

/*
|--------------------------------------------------------------------------
| Public Routes (Main Entry Points)
|--------------------------------------------------------------------------
|
| These routes adapt based on APP_PORTAL_TYPE environment variable.
| Set APP_PORTAL_TYPE=admin for admin server, APP_PORTAL_TYPE=violator for violator server
|
*/

// Determine portal type
$portalType = env('APP_PORTAL_TYPE', 'admin');

if ($portalType === 'violator') {
    // Violator Server Instance - Redirect root to violator portal
    Route::get('/', function () {
        return redirect()->route('violator.portal');
    })->name('welcome');
    
    // Redirect any legacy routes to violator portal
    Route::get('/violator-portal', function () {
        return redirect()->route('violator.portal');
    });
    
} else {
    // Admin Server Instance - Show welcome page (auto-logout if already logged in)
    Route::get('/', function () {
        // Automatically logout if user is logged in when visiting welcome page
        if (Auth::guard('superadmin')->check() || Auth::guard('admin')->check()) {
            foreach (['superadmin','admin'] as $guard) {
                if (Auth::guard($guard)->check()) {
                    Auth::guard($guard)->logout();
                }
            }
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }
        return view('welcome');
    })->name('welcome');
    
    // Public Registration (for creating admin accounts - consider restricting in production)
    Route::get('/open-register', [PublicRegistrationController::class, 'form'])->name('open.register');
    Route::post('/open-register', [PublicRegistrationController::class, 'store'])->name('open.register.store');
    
    // Legacy route redirects for backward compatibility
    Route::get('/login', function () {
        return redirect()->route('admin.login');
    })->name('login');
    
    Route::get('/dashboard', function () {
        return redirect()->route('admin.dashboard');
    })->name('layout.dashboard');
}

// Common redirect
Route::get('/home', function () use ($portalType) {
    if ($portalType === 'violator') {
        return redirect()->route('violator.portal');
    }
    return redirect()->route('welcome');
})->name('home');
