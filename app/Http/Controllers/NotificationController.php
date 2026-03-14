<?php

namespace App\Http\Controllers;

use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {
        $this->middleware('auth');
    }

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $userId = auth()->id();
        $filters = [
            'is_read' => $request->input('is_read'),
            'type' => $request->input('type'),
            'per_page' => 15
        ];

        $notifications = $this->notificationService->getByUserId($userId, $filters);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread notifications (for dropdown)
     */
    public function unread()
    {
        $userId = auth()->id();
        $notifications = $this->notificationService->getUnreadByUserId($userId);
        $unreadCount = $this->notificationService->getUnreadCount($userId);

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $unreadCount
        ]);
    }

    /**
     * Get unread notifications count.
     */
    public function unreadCount()
    {
        return response()->json([
            'count' => $this->notificationService->getUnreadCount(auth()->id()),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id)
    {
        $this->notificationService->markAsRead($id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi ditandai sebagai dibaca');
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        $userId = auth()->id();
        $this->notificationService->markAllAsRead($userId);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Semua notifikasi ditandai sebagai dibaca');
    }

    /**
     * Delete notification
     */
    public function destroy(string $id)
    {
        $this->notificationService->delete($id);

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi dihapus');
    }
}
