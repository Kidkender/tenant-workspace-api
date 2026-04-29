<?php

namespace App\Notifications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->where('data->tenant_id', app('tenant')->id)
            ->latest()
            ->paginate(10);

        return $this->success($notifications->items(), [
            'total' => $notifications->total(),
            'per_page' => $notifications->perPage(),
            'current_page' => $notifications->currentPage(),
            'last_page' => $notifications->lastPage(),
        ]);
    }

    public function unread(Request $request)
    {
        $notifications = $request->user()
            ->unreadNotifications()
            ->where('data->tenant_id', app('tenant')->id)
            ->latest()
            ->get();

        return $this->success($notifications);

    }

    public function markAsRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->findOrFail($id);
        $notification->markAsRead();

        return $this->success();
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()
            ->unreadNotifications()
            ->where('data->tenant_id', app('tenant')->id)
            ->markAsRead();

        return $this->success();
    }
}
