<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\User;
use App\Models\Department;
use App\Models\Position;
use App\Models\SalaryComponent;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['user', 'department', 'position', 'supervisor']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('employment_status')) {
            $query->where('employment_status', $request->employment_status);
        }

        $employees = $query->orderBy('created_at', 'desc')->paginate(20);
        $departments = Department::where('is_active', true)->get();

        return view('employees.index', compact('employees', 'departments'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        $users = User::whereDoesntHave('employee')->get();
        $supervisors = User::whereHas('employee')->get();
        
        return view('employees.create', compact('departments', 'users', 'supervisors'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|unique:employees',
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'hire_date' => 'required|date',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after:contract_start',
            'employment_type' => 'required|in:permanent,contract,internship,freelance',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'bank_account_name' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:30',
            'social_security_id' => 'nullable|string|max:30',
        ]);

        Employee::create([
            'user_id' => $request->user_id,
            'department_id' => $request->department_id,
            'position_id' => $request->position_id,
            'supervisor_id' => $request->supervisor_id,
            'hire_date' => $request->hire_date,
            'contract_start' => $request->contract_start,
            'contract_end' => $request->contract_end,
            'employment_type' => $request->employment_type,
            'employment_status' => 'active',
            'basic_salary' => $request->basic_salary,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'bank_account_name' => $request->bank_account_name,
            'tax_id' => $request->tax_id,
            'social_security_id' => $request->social_security_id,
        ]);

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil dibuat.');
    }

    public function show(Employee $employee)
    {
        $employee->load(['user', 'department', 'position', 'supervisor']);
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $departments = Department::where('is_active', true)->get();
        $positions = Position::where('is_active', true)->get();
        $supervisors = User::whereHas('employee')->where('id', '!=', $employee->user_id)->get();
        
        return view('employees.edit', compact('employee', 'departments', 'positions', 'supervisors'));
    }

    public function update(Request $request, Employee $employee)
    {
        $request->validate([
            'department_id' => 'required|exists:departments,id',
            'position_id' => 'required|exists:positions,id',
            'supervisor_id' => 'nullable|exists:users,id',
            'hire_date' => 'required|date',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date|after:contract_start',
            'employment_type' => 'required|in:permanent,contract,internship,freelance',
            'employment_status' => 'required|in:active,resigned,terminated,retired',
            'basic_salary' => 'required|numeric|min:0',
            'bank_name' => 'nullable|string|max:50',
            'bank_account' => 'nullable|string|max:30',
            'bank_account_name' => 'nullable|string|max:100',
            'tax_id' => 'nullable|string|max:30',
            'social_security_id' => 'nullable|string|max:30',
        ]);

        $employee->update($request->only([
            'department_id', 'position_id', 'supervisor_id', 'hire_date',
            'contract_start', 'contract_end', 'employment_type', 'employment_status',
            'basic_salary', 'bank_name', 'bank_account', 'bank_account_name',
            'tax_id', 'social_security_id'
        ]));

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index')
                        ->with('success', 'Data karyawan berhasil dihapus.');
    }

    public function salary(Employee $employee)
    {
        $employee->load(['user.salaryComponents' => function($query) {
            $query->wherePivot('is_active', true)
                  ->wherePivot('effective_date', '<=', now())
                  ->where(function($q) {
                      $q->wherePivotNull('end_date')
                        ->orWherePivot('end_date', '>=', now());
                  });
        }]);

        $availableComponents = SalaryComponent::where('is_active', true)
                                            ->whereNotIn('id', $employee->user->salaryComponents->pluck('id'))
                                            ->get();

        return view('employees.salary', compact('employee', 'availableComponents'));
    }

    public function updateSalary(Request $request, Employee $employee)
    {
        $request->validate([
            'basic_salary' => 'required|numeric|min:0',
            'components' => 'array',
            'components.*.component_id' => 'required|exists:salary_components,id',
            'components.*.amount' => 'required|numeric|min:0',
            'components.*.effective_date' => 'required|date',
        ]);

        // Update basic salary
        $employee->update(['basic_salary' => $request->basic_salary]);

        // Update salary components
        if ($request->has('components')) {
            foreach ($request->components as $component) {
                $employee->user->salaryComponents()->syncWithoutDetaching([
                    $component['component_id'] => [
                        'amount' => $component['amount'],
                        'effective_date' => $component['effective_date'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                ]);
            }
        }

        return back()->with('success', 'Komponen gaji berhasil diperbarui.');
    }
}
