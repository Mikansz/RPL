<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PermitSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    // Helper methods to get typed values
    public function getTypedValue()
    {
        switch ($this->type) {
            case 'boolean':
                return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
            case 'integer':
                return (int) $this->value;
            case 'float':
                return (float) $this->value;
            case 'json':
                return json_decode($this->value, true);
            default:
                return $this->value;
        }
    }

    public function setTypedValue($value)
    {
        switch ($this->type) {
            case 'boolean':
                $this->value = $value ? '1' : '0';
                break;
            case 'json':
                $this->value = json_encode($value);
                break;
            default:
                $this->value = (string) $value;
        }

        return $this;
    }

    // Static helper methods
    public static function get($key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return $default;
        }

        return $setting->getTypedValue();
    }

    public static function set($key, $value, $type = 'string', $description = null)
    {
        $setting = static::firstOrNew(['key' => $key]);
        $setting->type = $type;
        $setting->description = $description;
        $setting->setTypedValue($value);
        $setting->save();

        return $setting;
    }

    public static function getMultiple(array $keys)
    {
        $settings = static::whereIn('key', $keys)->get();
        $result = [];

        foreach ($settings as $setting) {
            $result[$setting->key] = $setting->getTypedValue();
        }

        // Fill missing keys with null
        foreach ($keys as $key) {
            if (!isset($result[$key])) {
                $result[$key] = null;
            }
        }

        return $result;
    }

    public static function setMultiple(array $settings)
    {
        foreach ($settings as $key => $config) {
            if (is_array($config)) {
                static::set(
                    $key,
                    $config['value'],
                    $config['type'] ?? 'string',
                    $config['description'] ?? null
                );
            } else {
                static::set($key, $config);
            }
        }
    }

    // Default permit settings
    public static function getDefaultSettings()
    {
        return [
            'max_overtime_hours_per_month' => [
                'value' => 40,
                'type' => 'integer',
                'description' => 'Maximum overtime hours per month per employee'
            ],


            'require_approval_overtime' => [
                'value' => true,
                'type' => 'boolean',
                'description' => 'Require approval for overtime requests'
            ],

            'auto_approve_sick_leave' => [
                'value' => false,
                'type' => 'boolean',
                'description' => 'Automatically approve sick leave with medical certificate'
            ],
            'max_leave_days_advance' => [
                'value' => 30,
                'type' => 'integer',
                'description' => 'Maximum days in advance to request leave'
            ],
        ];
    }

    public static function initializeDefaults()
    {
        $defaults = static::getDefaultSettings();
        
        foreach ($defaults as $key => $config) {
            $existing = static::where('key', $key)->first();
            if (!$existing) {
                static::set($key, $config['value'], $config['type'], $config['description']);
            }
        }
    }
}
