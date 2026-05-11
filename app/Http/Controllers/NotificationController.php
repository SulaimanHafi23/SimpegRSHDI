<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $userId = Auth::id();
        $query = Notification::query()
            ->with('user')
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere(function ($sq) use ($userId) {
                        $sq->where('notifiable_id', $userId)
                            ->where('notifiable_type', \App\Models\User::class);
                    });
            })
            ->orderBy('created_at', 'desc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('is_read')) {
            if ($request->boolean('is_read')) {
                $query->whereNotNull('read_at');
            } else {
                $query->whereNull('read_at');
            }
        }

        $notifications = $query->paginate(15);
        $unreadCount = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere(function ($sq) use ($userId) {
                    $sq->where('notifiable_id', $userId)
                        ->where('notifiable_type', \App\Models\User::class);
                });
        })->whereNull('read_at')->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    /**
     * Get unread notifications (for dropdown)
     */
    public function unread()
    {
        $userId = Auth::id();
        $notifications = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere(function ($sq) use ($userId) {
                    $sq->where('notifiable_id', $userId)
                        ->where('notifiable_type', \App\Models\User::class);
                });
        })
            ->whereNull('read_at')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        $unreadCount = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere(function ($sq) use ($userId) {
                    $sq->where('notifiable_id', $userId)
                        ->where('notifiable_type', \App\Models\User::class);
                });
        })->whereNull('read_at')->count();

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
        $userId = Auth::id();
        return response()->json([
            'count' => Notification::where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                    ->orWhere(function ($sq) use ($userId) {
                        $sq->where('notifiable_id', $userId)
                            ->where('notifiable_type', \App\Models\User::class);
                    });
            })->whereNull('read_at')->count(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id)
    {
        $userId = Auth::id();
        $notification = Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere(function ($sq) use ($userId) {
                    $sq->where('notifiable_id', $userId)
                        ->where('notifiable_type', \App\Models\User::class);
                });
        })->findOrFail($id);
        $notification->markAsRead();

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
        $userId = Auth::id();
        Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere(function ($sq) use ($userId) {
                    $sq->where('notifiable_id', $userId)
                        ->where('notifiable_type', \App\Models\User::class);
                });
        })
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

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
        $userId = Auth::id();
        Notification::where(function ($q) use ($userId) {
            $q->where('user_id', $userId)
                ->orWhere(function ($sq) use ($userId) {
                    $sq->where('notifiable_id', $userId)
                        ->where('notifiable_type', \App\Models\User::class);
                });
        })->findOrFail($id)->delete();

        if (request()->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notifikasi dihapus');
    }
}
