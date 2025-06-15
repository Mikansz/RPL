<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\NotificationSetting;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->paginate(20);
        $unreadCount = $user->unreadNotifications()->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function markAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', 'Notification marked as read');
    }

    public function markAllAsRead()
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read');
    }

    public function settings()
    {
        $user = Auth::user();
        $settings = $user->notificationSettings()->get()->groupBy(['type', 'event']);
        $availableTypes = NotificationSetting::getAvailableTypes();
        $availableEvents = NotificationSetting::getAvailableEvents();

        return view('notifications.settings', compact('settings', 'availableTypes', 'availableEvents'));
    }

    public function updateSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $request->get('settings', []);

        foreach ($settings as $type => $events) {
            foreach ($events as $event => $enabled) {
                NotificationSetting::updateOrCreate([
                    'user_id' => $user->id,
                    'type' => $type,
                    'event' => $event,
                ], [
                    'enabled' => (bool) $enabled,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Notification settings updated successfully');
    }

    // API Methods
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $limit = $request->get('limit', 10);
        
        $notifications = $user->notifications()
            ->limit($limit)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'data' => $notification->data,
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at,
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function apiMarkAsRead($id)
    {
        $user = Auth::user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
}
