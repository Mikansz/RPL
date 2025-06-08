<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'requires_approval',
        'affects_attendance',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'requires_approval' => 'boolean',
        'affects_attendance' => 'boolean',
        'is_active' => 'boolean',
    ];

    // Relationships
    public function permits()
    {
        return $this->hasMany(Permit::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // Helper methods
    public function isActive()
    {
        return $this->is_active;
    }

    public function requiresApproval()
    {
        return $this->requires_approval;
    }

    public function affectsAttendance()
    {
        return $this->affects_attendance;
    }
}
