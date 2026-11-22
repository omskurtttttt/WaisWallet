<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Gate;

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
        Gate::authorize('update', $notification);

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
