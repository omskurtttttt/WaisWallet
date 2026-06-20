<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected NotificationService $service;

    public function __construct(NotificationService $service)
    {
        $this->service = $service;
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(Notification $notification)
    {
        // Check ownership
        if ($notification->user_id !== auth()->id()) {
            abort(403);
        }

        $this->service->markAsRead($notification);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        $this->service->markAllAsRead();

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}
