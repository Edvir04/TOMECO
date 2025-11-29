<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Superadmin;
use App\Models\Admin;
use App\Models\TomecoEnforcer;

class PublicRegistrationController extends Controller
{
    public function form()
    {
        // Simple blade form below
        return view('auth.open-register');
    }

    public function store(Request $request)
    {
        $request->validate([
            'role'            => ['required','in:superadmin,admin,enforcer'],
            'fullname'        => ['required','string','max:255'],
            'username'        => ['required','string','max:255'],
            'password'        => ['required','string','min:8'],
            'gender'          => ['required','in:male,female,other'],
            'dob'             => ['required','date'],
            'contact_number'  => ['required','string','max:64'],
            'address'         => ['required','string','max:255'],
            'profile_picture' => ['nullable','string','max:255'],
        ]);

        $data = $request->only([
            'fullname','username','gender','dob','contact_number','address','profile_picture'
        ]);
        $data['password'] = Hash::make($request->password);

        $model = match ($request->role) {
            'superadmin' => Superadmin::create($data),
            'admin'      => Admin::create($data),
            'enforcer'   => TomecoEnforcer::create($data),
        };

        return redirect()->route('login')->with(
            'success',
            'Account created: '.$model->fullname.' ('.$request->role.'). You can now log in.'
        );
    }
}
