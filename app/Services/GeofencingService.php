<?php

namespace App\Services;

use App\Models\Office;
use App\Models\User;
use App\Models\Schedule;
use Carbon\Carbon;

class GeofencingService
{
    const EARTH_RADIUS = 6371000; // Earth's radius in meters

    public function validateLocation($latitude, $longitude, $officeId, $userId = null)
    {
        $office = Office::find($officeId);
        
        if (!$office) {
            return [
                'valid' => false,
                'message' => 'Office not found',
                'distance' => null
            ];
        }

        $distance = $this->calculateDistance(
            $latitude, 
            $longitude, 
            $office->latitude, 
            $office->longitude
        );

        $isValid = $distance <= $office->radius;

        return [
            'valid' => $isValid,
            'message' => $isValid 
                ? 'Location is within office radius' 
                : "You are {$distance}m away from {$office->name}. Required: within {$office->radius}m",
            'distance' => $distance,
            'office' => $office,
            'required_radius' => $office->radius
        ];
    }

    public function validateScheduleLocation($latitude, $longitude, $userId, $date = null)
    {
        $date = $date ? Carbon::parse($date) : Carbon::today();
        
        $schedule = Schedule::where('user_id', $userId)
            ->where('schedule_date', $date)
            ->where('status', '!=', 'cancelled')
            ->with(['office', 'shift'])
            ->first();

        if (!$schedule) {
            return [
                'valid' => false,
                'message' => 'No active schedule found for this date',
                'schedule' => null
            ];
        }

        // WFA doesn't require location validation
        if ($schedule->work_type === 'WFA') {
            return [
                'valid' => true,
                'message' => 'WFA schedule - location validation not required',
                'schedule' => $schedule,
                'work_type' => 'WFA'
            ];
        }

        // WFO requires office location validation
        if (!$schedule->office) {
            return [
                'valid' => false,
                'message' => 'WFO schedule but no office assigned',
                'schedule' => $schedule
            ];
        }

        $locationValidation = $this->validateLocation(
            $latitude, 
            $longitude, 
            $schedule->office_id, 
            $userId
        );

        return array_merge($locationValidation, [
            'schedule' => $schedule,
            'work_type' => 'WFO'
        ]);
    }

    public function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round(self::EARTH_RADIUS * $c);
    }

    public function createGeofence($name, $latitude, $longitude, $radius, $type = 'office')
    {
        // This could be extended to create custom geofences
        return [
            'name' => $name,
            'center' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ],
            'radius' => $radius,
            'type' => $type
        ];
    }

    public function getPolygonGeofence($coordinates)
    {
        // For complex polygon-based geofencing
        // This would use more advanced algorithms like ray casting
        return $this->pointInPolygon($coordinates);
    }

    public function pointInPolygon($point, $polygon)
    {
        $x = $point['longitude'];
        $y = $point['latitude'];
        $inside = false;

        $j = count($polygon) - 1;
        for ($i = 0; $i < count($polygon); $i++) {
            $xi = $polygon[$i]['longitude'];
            $yi = $polygon[$i]['latitude'];
            $xj = $polygon[$j]['longitude'];
            $yj = $polygon[$j]['latitude'];

            if ((($yi > $y) != ($yj > $y)) && ($x < ($xj - $xi) * ($y - $yi) / ($yj - $yi) + $xi)) {
                $inside = !$inside;
            }
            $j = $i;
        }

        return $inside;
    }

    public function getNearbyOffices($latitude, $longitude, $maxDistance = 5000)
    {
        $offices = Office::where('is_active', true)->get();
        $nearbyOffices = [];

        foreach ($offices as $office) {
            $distance = $this->calculateDistance(
                $latitude, 
                $longitude, 
                $office->latitude, 
                $office->longitude
            );

            if ($distance <= $maxDistance) {
                $nearbyOffices[] = [
                    'office' => $office,
                    'distance' => $distance,
                    'within_radius' => $distance <= $office->radius
                ];
            }
        }

        // Sort by distance
        usort($nearbyOffices, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return $nearbyOffices;
    }

    public function trackLocationHistory($userId, $latitude, $longitude, $action = 'check')
    {
        // Store location history for audit purposes
        \DB::table('location_history')->insert([
            'user_id' => $userId,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'action' => $action,
            'timestamp' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function generateLocationReport($userId, $startDate, $endDate)
    {
        $locations = \DB::table('location_history')
            ->where('user_id', $userId)
            ->whereBetween('timestamp', [$startDate, $endDate])
            ->orderBy('timestamp')
            ->get();

        $report = [
            'total_locations' => $locations->count(),
            'unique_days' => $locations->groupBy(function($item) {
                return Carbon::parse($item->timestamp)->format('Y-m-d');
            })->count(),
            'locations' => $locations,
            'summary' => []
        ];

        // Group by office proximity
        foreach ($locations as $location) {
            $nearbyOffices = $this->getNearbyOffices(
                $location->latitude, 
                $location->longitude, 
                1000
            );

            if (!empty($nearbyOffices)) {
                $closestOffice = $nearbyOffices[0];
                $officeName = $closestOffice['office']->name;
                
                if (!isset($report['summary'][$officeName])) {
                    $report['summary'][$officeName] = 0;
                }
                $report['summary'][$officeName]++;
            }
        }

        return $report;
    }

    public function validateMovementPattern($userId, $locations)
    {
        // Detect suspicious movement patterns
        $suspiciousPatterns = [];

        for ($i = 1; $i < count($locations); $i++) {
            $prev = $locations[$i - 1];
            $current = $locations[$i];

            $distance = $this->calculateDistance(
                $prev['latitude'], 
                $prev['longitude'],
                $current['latitude'], 
                $current['longitude']
            );

            $timeDiff = Carbon::parse($current['timestamp'])
                ->diffInMinutes(Carbon::parse($prev['timestamp']));

            // Check for impossible movement (too fast)
            if ($timeDiff > 0) {
                $speed = ($distance / 1000) / ($timeDiff / 60); // km/h
                
                if ($speed > 120) { // Faster than 120 km/h
                    $suspiciousPatterns[] = [
                        'type' => 'impossible_speed',
                        'speed' => $speed,
                        'distance' => $distance,
                        'time_diff' => $timeDiff,
                        'from' => $prev,
                        'to' => $current
                    ];
                }
            }

            // Check for location spoofing (exact same coordinates)
            if ($prev['latitude'] == $current['latitude'] && 
                $prev['longitude'] == $current['longitude'] && 
                $timeDiff > 60) {
                $suspiciousPatterns[] = [
                    'type' => 'static_location',
                    'duration' => $timeDiff,
                    'location' => $current
                ];
            }
        }

        return $suspiciousPatterns;
    }

    public function getLocationAccuracy($latitude, $longitude, $accuracy = null)
    {
        // Assess GPS accuracy and reliability
        $assessment = [
            'coordinates' => [
                'latitude' => $latitude,
                'longitude' => $longitude
            ],
            'accuracy' => $accuracy,
            'reliability' => 'unknown'
        ];

        if ($accuracy !== null) {
            if ($accuracy <= 5) {
                $assessment['reliability'] = 'excellent';
            } elseif ($accuracy <= 10) {
                $assessment['reliability'] = 'good';
            } elseif ($accuracy <= 20) {
                $assessment['reliability'] = 'fair';
            } else {
                $assessment['reliability'] = 'poor';
            }
        }

        return $assessment;
    }
}
