<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class DayExchange extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'original_work_date',
        'replacement_date',
        'reason',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'original_work_date' => 'date',
        'replacement_date' => 'date',
        'approved_at' => 'datetime',
        'completed_at' => 'datetime',
        'is_completed' => 'boolean',
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

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('original_work_date', [$startDate, $endDate])
              ->orWhereBetween('replacement_date', [$startDate, $endDate]);
        });
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
        return $this->status === 'pending';
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

    // Validation methods
    public function isValidExchange()
    {
        // Check if original date is a working day
        $originalDay = $this->original_work_date->dayOfWeek;
        if ($originalDay == Carbon::SATURDAY || $originalDay == Carbon::SUNDAY) {
            return false;
        }

        // Check if replacement date is a weekend
        $replacementDay = $this->replacement_date->dayOfWeek;
        if ($replacementDay != Carbon::SATURDAY && $replacementDay != Carbon::SUNDAY) {
            return false;
        }

        // Check if dates are not in the past (except for today)
        if ($this->original_work_date->lt(today()) || $this->replacement_date->lt(today())) {
            return false;
        }

        return true;
    }

    public function hasConflict()
    {
        // Check for existing exchange requests on the same dates
        return static::where('user_id', $this->user_id)
                    ->where('id', '!=', $this->id)
                    ->where(function ($query) {
                        $query->where('original_work_date', $this->original_work_date)
                              ->orWhere('replacement_date', $this->replacement_date);
                    })
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();
    }
}
