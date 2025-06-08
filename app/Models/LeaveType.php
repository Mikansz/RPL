<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'max_days_per_year',
        'is_paid',
        'requires_approval',
        'is_active',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePaid($query)
    {
        return $query->where('is_paid', true);
    }

    // Helper methods
    public function getRemainingDays($userId, $year = null)
    {
        $year = $year ?? now()->year;
        
        $usedDays = $this->leaves()
                         ->where('user_id', $userId)
                         ->whereYear('start_date', $year)
                         ->where('status', 'approved')
                         ->sum('total_days');
                         
        return max(0, $this->max_days_per_year - $usedDays);
    }
}
