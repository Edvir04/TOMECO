<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        // Search functionality
        $query = Employee::query();
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('username', 'LIKE', "%$search%")
                  ->orWhere('email', 'LIKE', "%$search%")
                  ->orWhere('phone', 'LIKE', "%$search%");
        }

        // Pagination
        $employees = $query->paginate(10);

        return view('layouts.employeeAccounts', compact('employees'));
    }

    public function create()
{
    // No employees needed here, just return the create form.
    return view('layouts.employeeAccounts');
}

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'email' => 'required|email|unique:e_ticket_employees,email',
            'birthdate' => 'required|date',
            'gender' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        // Create new employee
        Employee::create($request->all());

        return redirect()->route('employees-account')->with('success', 'Employee account created successfully.');
    }

    public function edit($id)
{
    $employee = Employee::findOrFail($id);
    
    // Pass the employee object to the edit view.
    return view('layouts.employeeAccounts', compact('employee'));
}

    public function update(Request $request, $id)
    {
        // Validation
        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'email' => 'required|email|unique:e_ticket_employees,email,' . $id,
            'birthdate' => 'required|date',
            'gender' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
        ]);

        // Update employee data
        $employee = Employee::findOrFail($id);
        $employee->update($request->all());

        return redirect()->route('employees-account')->with('success', 'Employee account updated successfully.');
    }

    public function destroy($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();

        return redirect()->route('employees-account')->with('success', 'Employee account deleted successfully.');
    }
}
