<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OvertimeRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'overtime_date',
        'start_time',
        'end_time',
        'planned_hours',
        'actual_hours',
        'work_description',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'is_completed',
        'completed_at',
        'overtime_rate',
        'overtime_amount',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'start_time' => 'datetime:H:i:s',
        'end_time' => 'datetime:H:i:s',
        'planned_hours' => 'decimal:2',
        'actual_hours' => 'decimal:2',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
        'overtime_rate' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function approvals()
    {
        return $this->morphMany(PermitApproval::class, 'approvable');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('overtime_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('overtime_date', now()->month)
                    ->whereYear('overtime_date', now()->year);
    }

    // Helper methods
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCompleted()
    {
        return $this->status === 'completed' || $this->is_completed;
    }

    public function canBeEdited()
    {
        return $this->status === 'pending' && $this->overtime_date->gte(today());
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeCompleted()
    {
        return $this->status === 'approved' && !$this->is_completed;
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'completed' => 'info',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'completed' => 'Selesai',
        ];

        return $texts[$this->status] ?? 'Unknown';
    }

    // Calculation methods
    public function calculatePlannedHours()
    {
        if (!$this->start_time || !$this->end_time) {
            return 0;
        }

        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        // If end time is before start time, assume it's next day
        if ($end->lt($start)) {
            $end->addDay();
        }

        return $end->diffInHours($start, true);
    }

    public function calculateOvertimeAmount($hourlyRate = null)
    {
        if (!$this->actual_hours) {
            return 0;
        }

        if (!$hourlyRate) {
            // Calculate hourly rate from basic salary
            $basicSalary = $this->user->employee->basic_salary ?? 0;
            $hourlyRate = $basicSalary / 173; // 173 = average working hours per month
        }

        // Apply overtime multiplier (1.5x for regular overtime)
        $overtimeRate = $this->overtime_rate ?? ($hourlyRate * 1.5);
        
        return $this->actual_hours * $overtimeRate;
    }

    public function updateOvertimeAmount()
    {
        $amount = $this->calculateOvertimeAmount();
        $this->update([
            'overtime_amount' => $amount,
            'overtime_rate' => $this->overtime_rate ?? (($this->user->employee->basic_salary ?? 0) / 173 * 1.5),
        ]);

        return $amount;
    }

    // Validation methods
    public function isValidRequest()
    {
        // Check if overtime date is not in the past (except today)
        if ($this->overtime_date->lt(today())) {
            return false;
        }

        // Check if planned hours is reasonable (max 8 hours)
        if ($this->planned_hours > 8) {
            return false;
        }

        // Check if start time is after normal working hours
        $normalEndTime = Carbon::parse('17:00');
        $startTime = Carbon::parse($this->start_time);
        
        if ($startTime->lt($normalEndTime)) {
            return false;
        }

        return true;
    }

    public function hasConflict()
    {
        // Check for existing overtime requests on the same date
        return static::where('user_id', $this->user_id)
                    ->where('id', '!=', $this->id)
                    ->where('overtime_date', $this->overtime_date)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();
    }

    public function getFormattedDurationAttribute()
    {
        $hours = $this->actual_hours ?? $this->planned_hours;
        $wholeHours = floor($hours);
        $minutes = ($hours - $wholeHours) * 60;
        
        if ($minutes > 0) {
            return $wholeHours . 'j ' . round($minutes) . 'm';
        }
        
        return $wholeHours . ' jam';
    }
}
