<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'event',
        'enabled',
        'settings',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'settings' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByEvent($query, $event)
    {
        return $query->where('event', $event);
    }

    // Helper methods
    public static function getAvailableTypes()
    {
        return [
            'email' => 'Email',
            'push' => 'Push Notification',
            'in_app' => 'In-App Notification',
        ];
    }

    public static function getAvailableEvents()
    {
        return [
            'schedule_reminder' => 'Pengingat Jadwal Kerja',
            'schedule_approved' => 'Jadwal Disetujui',
            'schedule_cancelled' => 'Jadwal Dibatalkan',
            'attendance_reminder' => 'Pengingat Absensi',
            'late_attendance' => 'Terlambat Absensi',
            'missing_attendance' => 'Tidak Absen',
            'overtime_alert' => 'Alert Lembur',
        ];
    }

    public function isEnabled()
    {
        return $this->enabled;
    }

    public function getSetting($key, $default = null)
    {
        return $this->settings[$key] ?? $default;
    }

    public function setSetting($key, $value)
    {
        $settings = $this->settings ?? [];
        $settings[$key] = $value;
        $this->settings = $settings;
        return $this;
    }
}
