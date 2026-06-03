<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    /**
     * Display audit log listing.
     */
    public function index(\App\Http\Requests\Admin\AuditLogFilterRequest $request): View
    {
        // Validate inputs to prevent SQL injection
        $validated = $request->validated();

        $query = AuditLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $validated['action']);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $validated['user_id']);
        }

        // Filter by model type (validated to prevent injection)
        if ($request->filled('model_type')) {
            // Whitelist approach for extra safety
            $allowedModels = ['Worker', 'User', 'LeaveRequest'];
            if (in_array($validated['model_type'], $allowedModels)) {
                $query->where('auditable_type', 'App\\Models\\' . $validated['model_type']);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $validated['date_from']);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $validated['date_to']);
        }

        // Search with parameter binding (safe from SQL injection)
        if ($request->filled('search')) {
            $search = $validated['search'];
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('user_name', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%");
            });
        }

        $logs = $query->paginate(25)->withQueryString();

        // Get unique actions for filter dropdown
        $actions = AuditLog::select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        // Get unique model types for filter dropdown
        $modelTypes = AuditLog::select('auditable_type')
            ->whereNotNull('auditable_type')
            ->distinct()
            ->pluck('auditable_type')
            ->map(fn ($type) => class_basename($type))
            ->unique()
            ->sort()
            ->values();

        // Stats
        $stats = [
            'total' => AuditLog::count(),
            'today' => AuditLog::whereDate('created_at', today())->count(),
            'this_week' => AuditLog::where('created_at', '>=', now()->startOfWeek())->count(),
            'creates' => AuditLog::where('action', 'created')->count(),
            'updates' => AuditLog::where('action', 'updated')->count(),
            'deletes' => AuditLog::where('action', 'deleted')->count(),
        ];

        $filters = $request->only(['action', 'user_id', 'model_type', 'date_from', 'date_to', 'search']);

        return view('admin.audit-logs.index', compact('logs', 'actions', 'modelTypes', 'stats', 'filters'));
    }

    /**
     * Display a single audit log detail.
     */
    public function show(string $id): View
    {
        $log = AuditLog::with('user')->findOrFail($id);

        return view('admin.audit-logs.show', compact('log'));
    }
}
