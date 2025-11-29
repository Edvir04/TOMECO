<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Superadmin;
use App\Models\Admin;
use App\Models\TomecoEnforcer;

class SettingsController extends Controller
{
    public function index()
    {
        $user = null;
        $role = null;

        // Check which guard is authenticated
        if (Auth::guard('superadmin')->check()) {
            $user = Auth::guard('superadmin')->user();
            $role = 'superadmin';
        } elseif (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $role = 'admin';
        } elseif (Auth::guard('enforcer')->check()) {
            $user = Auth::guard('enforcer')->user();
            $role = 'enforcer';
        }

        if (!$user) {
            return redirect()->route('admin.login')->withErrors(['message' => 'Please login to access settings.']);
        }

        // Convert profile picture path to URL if needed
        if ($user->profile_picture && !filter_var($user->profile_picture, FILTER_VALIDATE_URL) && !str_starts_with($user->profile_picture, '/')) {
            $user->profile_picture = Storage::url($user->profile_picture);
        }

        return view('layout.settings', compact('user', 'role'));
    }

    public function getPersonalInfo()
    {
        $user = null;
        $role = null;

        // Check which guard is authenticated
        if (Auth::guard('superadmin')->check()) {
            $user = Auth::guard('superadmin')->user();
            $role = 'superadmin';
            // Fetch fresh data from Superadmin model
            $user = Superadmin::findOrFail($user->id);
        } elseif (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $role = 'admin';
            // Fetch fresh data from Admin model
            $user = Admin::findOrFail($user->id);
        } elseif (Auth::guard('enforcer')->check()) {
            $user = Auth::guard('enforcer')->user();
            $role = 'enforcer';
            // Fetch fresh data from TomecoEnforcer model
            $user = TomecoEnforcer::findOrFail($user->id);
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'role' => $role,
            'fullname' => $user->fullname,
            'username' => $user->username,
            'id_number' => $user->id_number,
            'gender' => $user->gender,
            'dob' => $user->dob ? $user->dob->toDateString() : null,
            'contact_number' => $user->contact_number,
            'address' => $user->address,
            'profile_picture' => $user->profile_picture ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : (str_starts_with($user->profile_picture, '/') ? $user->profile_picture : Storage::url($user->profile_picture))) : null,
            'created_at' => $user->created_at ? $user->created_at->toDateTimeString() : null,
        ]);
    }

    public function update(Request $request)
    {
        $user = null;
        $role = null;

        // Check which guard is authenticated
        if (Auth::guard('superadmin')->check()) {
            $user = Auth::guard('superadmin')->user();
            $role = 'superadmin';
            $user = Superadmin::findOrFail($user->id);
        } elseif (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            $role = 'admin';
            $user = Admin::findOrFail($user->id);
        } elseif (Auth::guard('enforcer')->check()) {
            $user = Auth::guard('enforcer')->user();
            $role = 'enforcer';
            $user = TomecoEnforcer::findOrFail($user->id);
        }

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $request->validate([
            'fullname' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:255'],
            'gender' => ['required', 'in:male,female,other'],
            'dob' => ['required', 'date'],
            'contact_number' => ['required', 'string', 'max:64'],
            'address' => ['required', 'string', 'max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
        ]);

        $user->fullname = $request->fullname;
        $user->username = $request->username;
        $user->id_number = $request->id_number;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->contact_number = $request->contact_number;
        $user->address = $request->address;

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            // Delete old image if exists
            if ($user->profile_picture && Storage::disk('public')->exists($user->profile_picture)) {
                Storage::disk('public')->delete($user->profile_picture);
            }
            
            // Store new image
            $imagePath = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $imagePath;
        }

        $user->save();

        // Get the full URL for the profile picture
        $profilePictureUrl = $user->profile_picture ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : (str_starts_with($user->profile_picture, '/') ? $user->profile_picture : Storage::url($user->profile_picture))) : null;

        return response()->json([
            'success' => true,
            'message' => 'Personal information updated successfully',
            'user' => [
                'id' => $user->id,
                'role' => $role,
                'fullname' => $user->fullname,
                'username' => $user->username,
                'id_number' => $user->id_number,
                'gender' => $user->gender,
                'dob' => $user->dob ? $user->dob->toDateString() : null,
                'contact_number' => $user->contact_number,
                'address' => $user->address,
                'profile_picture' => $profilePictureUrl,
            ]
        ]);
    }
}

