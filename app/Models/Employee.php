<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'position_id',
        'supervisor_id',
        'hire_date',
        'contract_start',
        'contract_end',
        'employment_type',
        'employment_status',
        'basic_salary',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'tax_id',
        'social_security_id',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'contract_start' => 'date',
        'contract_end' => 'date',
        'basic_salary' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function supervisor()
    {
        return $this->belongsTo(User::class, 'supervisor_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('employment_status', 'active');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        return $query->where('department_id', $departmentId);
    }

    public function scopeByEmploymentType($query, $type)
    {
        return $query->where('employment_type', $type);
    }

    // Helper methods
    public function isActive()
    {
        return $this->employment_status === 'active';
    }

    public function getYearsOfServiceAttribute()
    {
        return $this->hire_date->diffInYears(now());
    }

    public function getMonthsOfServiceAttribute()
    {
        return $this->hire_date->diffInMonths(now());
    }
}
