<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'office_id',
        'schedule_date',
        'work_type',
        'status',
        'notes',
        'created_by',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'schedule_date' => 'date',
        'approved_at' => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Scopes
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByShift($query, $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    public function scopeByOffice($query, $officeId)
    {
        return $query->where('office_id', $officeId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('schedule_date', $date);
    }

    public function scopeByWorkType($query, $workType)
    {
        return $query->where('work_type', $workType);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'cancelled');
    }

    public function scopeWFO($query)
    {
        return $query->where('work_type', 'WFO');
    }

    public function scopeWFA($query)
    {
        return $query->where('work_type', 'WFA');
    }

    // Helper methods
    public function canClockInAtLocation($latitude, $longitude)
    {
        // WFA can clock in from anywhere
        if ($this->work_type === 'WFA') {
            return true;
        }

        // WFO must be within office radius
        if ($this->work_type === 'WFO' && $this->office) {
            return $this->office->isWithinRadius($latitude, $longitude);
        }

        return false;
    }

    public function getDistanceFromOffice($latitude, $longitude)
    {
        if (!$this->office) {
            return null;
        }

        return $this->office->calculateDistance($latitude, $longitude);
    }

    public function isWFO()
    {
        return $this->work_type === 'WFO';
    }

    public function isWFA()
    {
        return $this->work_type === 'WFA';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isScheduled()
    {
        return $this->status === 'scheduled';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function requiresLocationValidation()
    {
        return $this->work_type === 'WFO';
    }

    public function getWorkTypeDisplayAttribute()
    {
        return $this->work_type === 'WFO' ? 'Work From Office' : 'Work From Anywhere';
    }
}
