<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollPeriod extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'pay_date',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'pay_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function payrolls()
    {
        return $this->hasMany(Payroll::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCurrent($query)
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    // Helper methods
    public function isActive()
    {
        return now()->between($this->start_date, $this->end_date);
    }

    public function canBeEdited()
    {
        return in_array($this->status, ['draft', 'calculated']);
    }

    public function getTotalPayrollsAttribute()
    {
        return $this->payrolls()->count();
    }

    public function getTotalNetSalaryAttribute()
    {
        return $this->payrolls()->sum('net_salary');
    }
}
