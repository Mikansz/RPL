<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'work_start_time',
        'work_end_time',
        'late_tolerance_minutes',
        'early_leave_tolerance_minutes',
        'overtime_multiplier',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'work_start_time' => 'datetime:H:i:s',
        'work_end_time' => 'datetime:H:i:s',
        'overtime_multiplier' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    // Helper methods
    public function getWorkingHoursAttribute()
    {
        $start = \Carbon\Carbon::parse($this->work_start_time);
        $end = \Carbon\Carbon::parse($this->work_end_time);
        
        $totalMinutes = $end->diffInMinutes($start);
        
        if ($this->break_start_time && $this->break_end_time) {
            $breakStart = \Carbon\Carbon::parse($this->break_start_time);
            $breakEnd = \Carbon\Carbon::parse($this->break_end_time);
            $breakMinutes = $breakEnd->diffInMinutes($breakStart);
            $totalMinutes -= $breakMinutes;
        }
        
        return round($totalMinutes / 60, 2);
    }

    public function calculateLateMinutes($clockInTime)
    {
        $workStart = \Carbon\Carbon::parse($this->work_start_time);
        $clockIn = \Carbon\Carbon::parse($clockInTime);
        
        if ($clockIn->gt($workStart)) {
            $lateMinutes = $clockIn->diffInMinutes($workStart);
            return max(0, $lateMinutes - $this->late_tolerance_minutes);
        }
        
        return 0;
    }

    public function calculateEarlyLeaveMinutes($clockOutTime)
    {
        $workEnd = \Carbon\Carbon::parse($this->work_end_time);
        $clockOut = \Carbon\Carbon::parse($clockOutTime);
        
        if ($clockOut->lt($workEnd)) {
            $earlyMinutes = $workEnd->diffInMinutes($clockOut);
            return max(0, $earlyMinutes - $this->early_leave_tolerance_minutes);
        }
        
        return 0;
    }

    public function calculateOvertimeMinutes($clockOutTime)
    {
        $workEnd = \Carbon\Carbon::parse($this->work_end_time);
        $clockOut = \Carbon\Carbon::parse($clockOutTime);
        
        if ($clockOut->gt($workEnd)) {
            return $clockOut->diffInMinutes($workEnd);
        }
        
        return 0;
    }
}
