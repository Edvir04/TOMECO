<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Superadmin;
use App\Models\Admin;
use App\Models\TomecoEnforcer;

class AccountsController extends Controller
{
    public function index(Request $request)
    {
        // Get current user's role
        $currentRole = $this->getCurrentUserRole();
        
        // Get search and filter parameters
        $search = $request->get('search', '');
        $roleFilter = $request->get('role', '');
        
        $supers = Superadmin::select(
            'id','fullname','username','id_number','gender','dob','contact_number','address','profile_picture','created_at'
        )->get()->map(fn($u) => $this->row($u, 'superadmin'));

        $admins = Admin::select(
            'id','fullname','username','id_number','gender','dob','contact_number','address','profile_picture','created_at'
        )->get()->map(fn($u) => $this->row($u, 'admin'));

        $enforcers = TomecoEnforcer::select(
            'id','fullname','username','id_number','gender','dob','contact_number','address','profile_picture','created_at'
        )->get()->map(fn($u) => $this->row($u, 'enforcer'));

        $allAccounts = $supers->concat($admins)->concat($enforcers);

        // Apply role filter
        if (!empty($roleFilter)) {
            $allAccounts = $allAccounts->filter(function($account) use ($roleFilter) {
                return $account['role'] === $roleFilter;
            });
        }

        // Apply search filter
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $allAccounts = $allAccounts->filter(function($account) use ($searchLower) {
                return str_contains(strtolower($account['fullname'] ?? ''), $searchLower) ||
                       str_contains(strtolower($account['username'] ?? ''), $searchLower) ||
                       str_contains(strtolower($account['id_number'] ?? ''), $searchLower) ||
                       str_contains(strtolower($account['contact_number'] ?? ''), $searchLower) ||
                       str_contains(strtolower($account['address'] ?? ''), $searchLower);
            });
        }

        $allAccounts = $allAccounts->sortByDesc('created_at')->values();

        // Paginate manually because data is merged from multiple models
        $perPage = 10;
        $page = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = $allAccounts->forPage($page, $perPage)->values();

        $accounts = new LengthAwarePaginator(
            $currentItems,
            $allAccounts->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('layout.accounts', compact('accounts', 'currentRole', 'search', 'roleFilter'));
    }
    
    private function getCurrentUserRole()
    {
        if (Auth::guard('superadmin')->check()) {
            return 'superadmin';
        } elseif (Auth::guard('admin')->check()) {
            return 'admin';
        } elseif (Auth::guard('enforcer')->check()) {
            return 'enforcer';
        }
        return null;
    }

    public function store(Request $request)
    {
        // Get current user's role
        $currentRole = $this->getCurrentUserRole();
        
        // Determine allowed roles based on current user
        $allowedRoles = [];
        if ($currentRole === 'superadmin') {
            $allowedRoles = ['superadmin', 'admin', 'enforcer'];
        } elseif ($currentRole === 'admin') {
            $allowedRoles = ['enforcer'];
        } else {
            return back()->withErrors(['role' => 'You do not have permission to create accounts.'])->withInput();
        }
        
        $request->validate([
            'role'            => ['required', 'in:' . implode(',', $allowedRoles)],
            'fullname'        => ['required','string','max:255'],
            'username'        => ['required','string','max:255'],
            'id_number'       => ['required','string','max:255'],
            'password'        => ['required','string','min:8'],
            'gender'          => ['required','in:male,female,other'],
            'dob'             => ['required','date'],
            'contact_number'  => ['required','string','max:64'],
            'address'         => ['required','string','max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
        ]);

        // Additional check to prevent unauthorized role creation
        if (!in_array($request->role, $allowedRoles)) {
            return back()->withErrors(['role' => 'You do not have permission to create this account type.'])->withInput();
        }

        $data = $request->only([
            'fullname','username','id_number','gender','dob','contact_number','address'
        ]);
        $data['password'] = Hash::make($request->password);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $imagePath = $request->file('profile_picture')->store('profile-pictures', 'public');
            $data['profile_picture'] = $imagePath;
        }

        $model = match ($request->role) {
            'superadmin' => Superadmin::create($data),
            'admin'      => Admin::create($data),
            'enforcer'   => TomecoEnforcer::create($data),
        };

        return back()->with('status', 'Account created: '.$model->fullname.' ('.$request->role.')');
    }

    public function update(Request $request, string $role, int $id)
    {
        $currentRole = $this->getCurrentUserRole();
        // Admin can only update enforcers; superadmin can update all
        if ($currentRole === 'admin' && $role !== 'enforcer') {
            return back()->withErrors(['role' => 'You do not have permission to edit this account.']);
        }

        $request->validate([
            'fullname'        => ['required','string','max:255'],
            'username'        => ['required','string','max:255'],
            'id_number'       => ['required','string','max:255'],
            'password'        => ['nullable','string','min:8'],
            'gender'          => ['required','in:male,female,other'],
            'dob'             => ['required','date'],
            'contact_number'  => ['required','string','max:64'],
            'address'         => ['required','string','max:255'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'], // 5MB max
        ]);

        $user = $this->find($role, $id);

        $user->fullname        = $request->fullname;
        $user->username        = $request->username;
        $user->id_number       = $request->id_number;
        $user->gender          = $request->gender;
        $user->dob             = $request->dob;
        $user->contact_number  = $request->contact_number;
        $user->address         = $request->address;

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

        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Account updated: '.$user->fullname.' ('.$role.')');
    }

    public function destroy(string $role, int $id)
    {
        $currentRole = $this->getCurrentUserRole();
        // Admins can only delete enforcer accounts; superadmin can delete all
        if ($currentRole === 'admin' && $role !== 'enforcer') {
            return back()->withErrors(['message' => 'You do not have permission to delete this account.']);
        }

        $user = $this->find($role, $id);
        $user->delete();

        return back()->with('success', 'Account deleted ('.$role.').');
    }

    private function find(string $role, int $id)
    {
        return match ($role) {
            'superadmin' => Superadmin::findOrFail($id),
            'admin'      => Admin::findOrFail($id),
            'enforcer'   => TomecoEnforcer::findOrFail($id),
            default      => abort(404),
        };
    }

    private function row($m, string $role): array
    {
        return [
            'id'             => $m->id,
            'role'           => $role,
            'fullname'       => $m->fullname,
            'username'       => $m->username,
            'id_number'      => $m->id_number,
            'gender'         => $m->gender,
            'dob'            => optional($m->dob)->toDateString(),
            'contact_number' => $m->contact_number,
            'address'        => $m->address,
            'profile_picture'=> $m->profile_picture,
            'created_at'     => $m->created_at,
        ];
    }

    public function show(string $role, int $id)
    {
        $user = $this->find($role, $id);
        
        // Get the full URL for the profile picture
        $profilePictureUrl = $user->profile_picture ? (filter_var($user->profile_picture, FILTER_VALIDATE_URL) ? $user->profile_picture : (str_starts_with($user->profile_picture, '/') ? $user->profile_picture : Storage::url($user->profile_picture))) : null;
        
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
            'profile_picture' => $profilePictureUrl,
            'created_at' => $user->created_at,
        ]);
    }

    public function edit(string $role, int $id)
    {
        // Find the user based on the role and ID
        $user = $this->find($role, $id);

        // Pass the user data to the edit view
        return view('layout.edit_account', compact('user', 'role'));
    }

}
