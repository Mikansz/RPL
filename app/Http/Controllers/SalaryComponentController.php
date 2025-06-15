<?php

namespace App\Http\Controllers;

use App\Models\SalaryComponent;
use Illuminate\Http\Request;

class SalaryComponentController extends Controller
{
    public function index()
    {
        $salaryComponents = SalaryComponent::orderBy('sort_order')->orderBy('name')->paginate(10);

        // Calculate summary data
        $allowances = SalaryComponent::where('type', 'allowance')->where('is_active', true)->count();
        $deductions = SalaryComponent::where('type', 'deduction')->where('is_active', true)->count();
        $benefits = SalaryComponent::where('type', 'benefit')->where('is_active', true)->count();
        $total = SalaryComponent::where('is_active', true)->count();

        return view('salary-components.index', compact('salaryComponents', 'allowances', 'deductions', 'benefits', 'total'));
    }

    public function create()
    {
        return view('salary-components.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'code' => 'required|unique:salary_components|max:20',
            'type' => 'required|in:allowance,deduction',
            'calculation_type' => 'required|in:fixed,percentage',
            'default_amount' => 'required|numeric',
            'is_taxable' => 'boolean',
            'description' => 'nullable',
        ]);

        SalaryComponent::create($request->all());

        return redirect()->route('salary-components.index')
                        ->with('success', 'Salary component created successfully.');
    }

    public function show(SalaryComponent $salaryComponent)
    {
        return view('salary-components.show', compact('salaryComponent'));
    }

    public function edit(SalaryComponent $salaryComponent)
    {
        return view('salary-components.edit', compact('salaryComponent'));
    }

    public function update(Request $request, SalaryComponent $salaryComponent)
    {
        $request->validate([
            'name' => 'required|max:100',
            'code' => 'required|max:20|unique:salary_components,code,' . $salaryComponent->id,
            'type' => 'required|in:allowance,deduction',
            'calculation_type' => 'required|in:fixed,percentage',
            'default_amount' => 'required|numeric',
            'is_taxable' => 'boolean',
            'description' => 'nullable',
        ]);

        $salaryComponent->update($request->all());

        return redirect()->route('salary-components.index')
                        ->with('success', 'Salary component updated successfully.');
    }

    public function destroy(SalaryComponent $salaryComponent)
    {
        $salaryComponent->delete();

        return redirect()->route('salary-components.index')
                        ->with('success', 'Salary component deleted successfully.');
    }
}
