<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        // Redirect to dashboard if already logged in
        if (Auth::guard('superadmin')->check() || Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        
        // Prevent caching of login page to avoid back button issues
        $response = response()->view('auth.login');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => ['required','string'],
            'password' => ['required','string'],
        ]);

        $creds = ['username' => $request->username, 'password' => $request->password];

        // Only allow superadmin and admin to login to the web admin portal
        // Enforcers should use the mobile app
        foreach (['superadmin','admin'] as $guard) {
            if (Auth::guard($guard)->attempt($creds, $request->boolean('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended(route('admin.dashboard'));
            }
        }

        // Check if credentials match an enforcer (to show specific error message)
        if (Auth::guard('enforcer')->attempt($creds, false)) {
            Auth::guard('enforcer')->logout(); // Logout immediately since we don't allow web login
            return back()->withErrors(['username' => 'Enforcers can only access the mobile app. Please use the mobile application to login.'])->onlyInput('username');
        }

        return back()->withErrors(['username' => 'Invalid credentials.'])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        // Only logout superadmin and admin (enforcers shouldn't be logged in via web)
        foreach (['superadmin','admin'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // If it's a GET request (auto-logout), return JSON or empty response
        if ($request->isMethod('get')) {
            return response()->json(['success' => true]);
        }
        
        return redirect()->route('admin.login');
    }
}
