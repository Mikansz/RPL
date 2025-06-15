<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::with(['department', 'employees']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhereHas('department', function($dq) use ($search) {
                      $dq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Level filter
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $positions = $query->withCount('employees')
                          ->orderBy('created_at', 'desc')
                          ->paginate(10);

        // Additional data for the view
        $departments = Department::where('is_active', true)->get();
        $total_employees = $positions->sum('employees_count');
        $average_salary = $positions->avg('base_salary');

        return view('positions.index', compact('positions', 'departments', 'total_employees', 'average_salary'));
    }

    public function create()
    {
        $departments = Department::where('is_active', true)->get();
        return view('positions.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:positions|max:10',
            'name' => 'required|max:100',
            'department_id' => 'required|exists:departments,id',
            'base_salary' => 'required|numeric|min:0',
            'level' => 'required|integer|min:1',
            'description' => 'nullable',
        ]);

        Position::create($request->all());

        return redirect()->route('positions.index')
                        ->with('success', 'Position created successfully.');
    }

    public function show(Position $position)
    {
        $position->load('department', 'employees');
        return view('positions.show', compact('position'));
    }

    public function edit(Position $position)
    {
        $departments = Department::where('is_active', true)->get();
        return view('positions.edit', compact('position', 'departments'));
    }

    public function update(Request $request, Position $position)
    {
        $request->validate([
            'code' => 'required|max:10|unique:positions,code,' . $position->id,
            'name' => 'required|max:100',
            'department_id' => 'required|exists:departments,id',
            'base_salary' => 'required|numeric|min:0',
            'level' => 'required|integer|min:1',
            'description' => 'nullable',
        ]);

        $position->update($request->all());

        return redirect()->route('positions.index')
                        ->with('success', 'Position updated successfully.');
    }

    public function destroy(Position $position)
    {
        $position->delete();

        return redirect()->route('positions.index')
                        ->with('success', 'Position deleted successfully.');
    }
}
