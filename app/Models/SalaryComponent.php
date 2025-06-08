<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalaryComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'type',
        'calculation_type',
        'default_amount',
        'percentage',
        'formula',
        'is_taxable',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function employees()
    {
        return $this->belongsToMany(User::class, 'employee_salary_components')
                    ->withPivot(['amount', 'effective_date', 'end_date', 'is_active'])
                    ->withTimestamps();
    }

    public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeAllowances($query)
    {
        return $query->where('type', 'allowance');
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', 'deduction');
    }

    public function scopeBenefits($query)
    {
        return $query->where('type', 'benefit');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods
    public function isAllowance()
    {
        return $this->type === 'allowance';
    }

    public function isDeduction()
    {
        return $this->type === 'deduction';
    }

    public function isBenefit()
    {
        return $this->type === 'benefit';
    }

    public function calculateAmount($basicSalary, $customAmount = null)
    {
        if ($customAmount !== null) {
            return $customAmount;
        }

        switch ($this->calculation_type) {
            case 'fixed':
                return $this->default_amount;
            case 'percentage':
                return ($basicSalary * $this->percentage) / 100;
            case 'formula':
                // Implement formula calculation logic here
                return $this->evaluateFormula($basicSalary);
            default:
                return 0;
        }
    }

    private function evaluateFormula($basicSalary)
    {
        // Simple formula evaluation - can be extended
        $formula = str_replace('basic_salary', $basicSalary, $this->formula);
        
        // For security, only allow basic math operations
        if (preg_match('/^[\d\+\-\*\/\(\)\.\s]+$/', $formula)) {
            try {
                return eval("return $formula;");
            } catch (Exception $e) {
                return 0;
            }
        }
        
        return 0;
    }
}
