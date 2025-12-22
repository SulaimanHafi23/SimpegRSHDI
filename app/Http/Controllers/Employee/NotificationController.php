<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Services\Notification\NotificationService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $filters = [
            'is_read' => $request->input('is_read'),
            'per_page' => 15
        ];

        $notifications = $this->notificationService->getByUserId(
            auth()->id(),
            $filters
        );

        return view('employee.notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications count (for badge)
     */
    public function getUnreadCount()
    {
        $count = $this->notificationService->getUnreadCount(auth()->id());
        return response()->json(['count' => $count]);
    }

    /**
     * Get unread notifications (for dropdown)
     */
    public function getUnread()
    {
        $notifications = $this->notificationService->getUnreadByUserId(auth()->id());
        return response()->json($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $this->notificationService->markAsRead($id);
            return response()->json(['message' => 'Notifikasi ditandai sudah dibaca']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menandai notifikasi'], 500);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            $this->notificationService->markAllAsRead(auth()->id());
            return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca']);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal menandai notifikasi'], 500);
        }
    }

    /**
     * Delete notification
     */
    public function destroy($id)
    {
        try {
            $this->notificationService->delete($id);
            return redirect()->route('employee.notifications.index')
                ->with('success', 'Notifikasi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus notifikasi');
        }
    }
}
