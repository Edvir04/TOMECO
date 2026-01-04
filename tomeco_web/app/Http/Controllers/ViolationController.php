<?php

namespace App\Http\Controllers;

use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ViolationController extends Controller
{
    /**
     * Display a listing of violations.
     */
    public function index()
    {
        // Only superadmin can access violations page
        if (!Auth::guard('superadmin')->check()) {
            abort(403, 'Unauthorized access. Only superadmin can access this page.');
        }

        $violations = Violation::orderBy('created_at', 'desc')->paginate(15);
        return view('layout.violations', compact('violations'));
    }

    /**
     * Store a newly created violation.
     */
    public function store(Request $request)
    {
        // Only superadmin can create violations
        if (!Auth::guard('superadmin')->check()) {
            abort(403, 'Unauthorized access. Only superadmin can create violations.');
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:500',
            'price' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.violations')
                ->withErrors($validator)
                ->withInput();
        }

        Violation::create([
            'name' => $request->name,
            'price' => $request->price,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.violations')
            ->with('status', 'Violation created successfully.');
    }

    /**
     * Display the specified violation.
     */
    public function show($id)
    {
        $violation = Violation::findOrFail($id);
        return response()->json($violation);
    }

    /**
     * Update the specified violation.
     */
    public function update(Request $request, $id)
    {
        // Only superadmin can update violations
        if (!Auth::guard('superadmin')->check()) {
            abort(403, 'Unauthorized access. Only superadmin can update violations.');
        }

        $violation = Violation::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:500',
            'price' => 'required|numeric|min:0|max:999999.99',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->route('admin.violations')
                ->withErrors($validator)
                ->withInput();
        }

        $violation->update([
            'name' => $request->name,
            'price' => $request->price,
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.violations')
            ->with('status', 'Violation updated successfully.');
    }

    /**
     * Remove the specified violation.
     */
    public function destroy($id)
    {
        // Only superadmin can delete violations
        if (!Auth::guard('superadmin')->check()) {
            abort(403, 'Unauthorized access. Only superadmin can delete violations.');
        }

        $violation = Violation::findOrFail($id);
        $violation->delete();

        return redirect()->route('admin.violations')
            ->with('status', 'Violation deleted successfully.');
    }
}

