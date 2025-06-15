<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Office;
use App\Services\GeofencingService;

class GeofencingController extends Controller
{
    protected $geofencingService;

    public function __construct(GeofencingService $geofencingService)
    {
        $this->geofencingService = $geofencingService;
    }

    /**
     * Validate user location against office geofence
     */
    public function validateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'office_id' => 'required|exists:offices,id',
        ]);

        $user = Auth::user();
        $validation = $this->geofencingService->validateLocation(
            $request->latitude,
            $request->longitude,
            $request->office_id,
            $user->id
        );

        return response()->json($validation);
    }

    /**
     * Get nearby offices based on user location
     */
    public function getNearbyOffices(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'nullable|numeric|min:100|max:10000', // Max 10km radius
        ]);

        $radius = $request->get('radius', 5000); // Default 5km
        $offices = Office::active()->get();
        $nearbyOffices = [];

        foreach ($offices as $office) {
            $distance = $this->geofencingService->calculateDistance(
                $request->latitude,
                $request->longitude,
                $office->latitude,
                $office->longitude
            );

            if ($distance <= $radius) {
                $nearbyOffices[] = [
                    'id' => $office->id,
                    'name' => $office->name,
                    'address' => $office->address,
                    'latitude' => $office->latitude,
                    'longitude' => $office->longitude,
                    'radius' => $office->radius,
                    'distance' => round($distance, 2),
                    'is_within_radius' => $distance <= $office->radius,
                ];
            }
        }

        // Sort by distance
        usort($nearbyOffices, function ($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });

        return response()->json([
            'success' => true,
            'offices' => $nearbyOffices,
            'search_radius' => $radius,
            'user_location' => [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
            ],
        ]);
    }
}
