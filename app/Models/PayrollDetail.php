<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'salary_component_id',
        'amount',
        'calculation_notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relationships
    public function payroll()
    {
        return $this->belongsTo(Payroll::class);
    }

    public function salaryComponent()
    {
        return $this->belongsTo(SalaryComponent::class);
    }
}
