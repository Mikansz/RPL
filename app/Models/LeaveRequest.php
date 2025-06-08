<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'notes',
        'emergency_contact',
        'emergency_phone',
        'work_handover',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'attachments',
        'is_half_day',
        'half_day_type',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'approved_at' => 'datetime',
        'attachments' => 'array',
        'is_half_day' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
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

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q2) use ($startDate, $endDate) {
                  $q2->where('start_date', '<=', $startDate)
                     ->where('end_date', '>=', $endDate);
              });
        });
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('start_date', now()->year);
    }

    public function scopeByLeaveType($query, $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
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

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function canBeEdited()
    {
        return $this->status === 'pending' && $this->start_date->gt(today());
    }

    public function canBeApproved()
    {
        return $this->status === 'pending';
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']) && $this->start_date->gt(today());
    }

    public function getStatusBadgeAttribute()
    {
        $badges = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'secondary',
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    public function getStatusTextAttribute()
    {
        $texts = [
            'pending' => 'Menunggu Persetujuan',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
        ];

        return $texts[$this->status] ?? 'Unknown';
    }

    // Calculation methods
    public function calculateTotalDays()
    {
        if ($this->is_half_day) {
            return 0.5;
        }

        if (!$this->start_date || !$this->end_date) {
            return 0;
        }

        $start = Carbon::parse($this->start_date);
        $end = Carbon::parse($this->end_date);

        // Count only working days (Monday to Friday)
        $totalDays = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isWeekday()) {
                $totalDays++;
            }
            $current->addDay();
        }

        return $totalDays;
    }

    public function updateTotalDays()
    {
        $this->total_days = $this->calculateTotalDays();
        $this->save();
        return $this->total_days;
    }

    // Validation methods
    public function isValidRequest()
    {
        // Check if start date is not in the past
        if ($this->start_date->lt(today())) {
            return false;
        }

        // Check if end date is after start date
        if ($this->end_date->lt($this->start_date)) {
            return false;
        }

        // Check leave balance
        if (!$this->hasEnoughBalance()) {
            return false;
        }

        return true;
    }

    public function hasEnoughBalance()
    {
        $leaveType = $this->leaveType;
        if (!$leaveType) {
            return false;
        }

        $remainingDays = $leaveType->getRemainingDays($this->user_id, $this->start_date->year);
        return $remainingDays >= $this->total_days;
    }

    public function hasConflict()
    {
        // Check for overlapping leave requests
        return static::where('user_id', $this->user_id)
                    ->where('id', '!=', $this->id)
                    ->where(function ($query) {
                        $query->whereBetween('start_date', [$this->start_date, $this->end_date])
                              ->orWhereBetween('end_date', [$this->start_date, $this->end_date])
                              ->orWhere(function ($q) {
                                  $q->where('start_date', '<=', $this->start_date)
                                    ->where('end_date', '>=', $this->end_date);
                              });
                    })
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();
    }

    public function getFormattedDurationAttribute()
    {
        if ($this->is_half_day) {
            return '0.5 hari (' . ucfirst($this->half_day_type) . ')';
        }

        return $this->total_days . ' hari';
    }

    public function getFormattedDateRangeAttribute()
    {
        if ($this->start_date->eq($this->end_date)) {
            return $this->start_date->format('d M Y');
        }

        return $this->start_date->format('d M Y') . ' - ' . $this->end_date->format('d M Y');
    }

    // File attachment methods
    public function addAttachment($filename, $originalName, $size)
    {
        $attachments = $this->attachments ?? [];
        $attachments[] = [
            'filename' => $filename,
            'original_name' => $originalName,
            'size' => $size,
            'uploaded_at' => now()->toISOString(),
        ];
        
        $this->attachments = $attachments;
        $this->save();
    }

    public function removeAttachment($filename)
    {
        $attachments = $this->attachments ?? [];
        $this->attachments = array_filter($attachments, function ($attachment) use ($filename) {
            return $attachment['filename'] !== $filename;
        });
        $this->save();
    }

    public function hasAttachments()
    {
        return !empty($this->attachments);
    }
}
