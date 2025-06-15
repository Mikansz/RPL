<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// API Routes
Route::middleware('auth:sanctum')->group(function () {

    // Geofencing API
    Route::post('/geofencing/validate', [App\Http\Controllers\GeofencingController::class, 'validateLocation']);
    Route::get('/geofencing/nearby-offices', [App\Http\Controllers\GeofencingController::class, 'getNearbyOffices']);

    // Notifications API
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'apiIndex']);
    Route::post('/notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'apiMarkAsRead']);
});

// Debug API Routes (for troubleshooting)
Route::middleware('auth')->group(function () {
    Route::get('/debug/schedule', function() {
        $user = Auth::user();
        $todaySchedule = $user->getTodaySchedule();

        $debugData = [
            'user_id' => $user->id,
            'user_name' => $user->full_name,
            'today' => today()->format('Y-m-d'),
            'schedule' => $todaySchedule,
            'can_clock_in' => $user->canClockInToday(),
            'work_type' => $user->getTodayWorkType(),
            'all_schedules_count' => $user->schedules()->count(),
            'today_schedules_count' => $user->schedules()->where('schedule_date', today())->count(),
        ];

        if ($todaySchedule && $todaySchedule->shift) {
            $debugData['shift_details'] = [
                'id' => $todaySchedule->shift->id,
                'name' => $todaySchedule->shift->name,
                'start_time' => $todaySchedule->shift->start_time,
                'end_time' => $todaySchedule->shift->end_time,
                'formatted_start_time' => $todaySchedule->shift->formatted_start_time,
                'formatted_end_time' => $todaySchedule->shift->formatted_end_time,
                'shift_duration' => $todaySchedule->shift->shift_duration,
            ];
        }

        return response()->json($debugData);
    });
});
