<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_period_id',
        'user_id',
        'basic_salary',
        'total_allowances',
        'total_deductions',
        'overtime_amount',
        'gross_salary',
        'tax_amount',
        'net_salary',
        'total_working_days',
        'total_present_days',
        'total_absent_days',
        'total_late_days',
        'total_overtime_hours',
        'notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'total_allowances' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payrollPeriod()
    {
        return $this->belongsTo(PayrollPeriod::class);
    }

    public function details()
    {
        return $this->hasMany(PayrollDetail::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByPeriod($query, $periodId)
    {
        return $query->where('payroll_period_id', $periodId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function getAttendanceRateAttribute()
    {
        if ($this->total_working_days == 0) {
            return 0;
        }
        
        return round(($this->total_present_days / $this->total_working_days) * 100, 2);
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isApproved()
    {
        return in_array($this->status, ['approved', 'paid']);
    }
}
