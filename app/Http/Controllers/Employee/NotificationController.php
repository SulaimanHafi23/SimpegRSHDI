<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct() {}

    /**
     * Display a listing of notifications
     */
    public function index(Request $request)
    {
        $query = Notification::query()
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');

        if ($request->filled('is_read')) {
            if ($request->boolean('is_read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->paginate(15);

        return view('employee.notifications.index', compact('notifications'));
    }

    /**
     * Get unread notifications count (for badge)
     */
    public function getUnreadCount()
    {
        $count = Notification::where('user_id', Auth::id())->whereNull('read_at')->count();
        return response()->json(['count' => $count]);
    }

    /**
     * Get unread notifications (for dropdown)
     */
    public function getUnread()
    {
        $notifications = Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        return response()->json($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id)
    {
        try {
            $notification = Notification::where('user_id', Auth::id())->findOrFail($id);
            $notification->markAsRead();
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
            Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
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
            Notification::where('user_id', Auth::id())->findOrFail($id)->delete();
            return redirect()->route('employee.notifications.index')
                ->with('success', 'Notifikasi berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus notifikasi');
        }
    }
}
