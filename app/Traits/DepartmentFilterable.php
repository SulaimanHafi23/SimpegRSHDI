<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait DepartmentFilterable
{
    /**
     * Apply department filter if user is Manager
     * Returns department_id if user is Manager, null if Super Admin/HR (all departments)
     */
    public function getManagerDepartmentFilter(): ?string
    {
        $user = Auth::user();

        // Only Manager role gets filtered
        if (!$user || !$user->hasRole('Manager')) {
            return null; // No filter for Super Admin, HR, or other roles
        }

        // Get manager's department from their worker profile
        if ($user->worker && $user->worker->department_id) {
            return $user->worker->department_id;
        }

        return null;
    }

    /**
     * Add department filter to query if user is Manager
     */
    public function applyDepartmentFilter($query): mixed
    {
        $departmentId = $this->getManagerDepartmentFilter();

        if ($departmentId) {
            return $query->where('department_id', $departmentId);
        }

        return $query;
    }

    /**
     * Add worker department filter to query if user is Manager
     */
    public function applyWorkerDepartmentFilter($query, string $workerColumn = 'worker_id'): mixed
    {
        $departmentId = $this->getManagerDepartmentFilter();

        if ($departmentId) {
            return $query->whereHas('worker', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        return $query;
    }

    /**
     * Check if user can view/manage a worker (based on department)
     */
    public function canManageWorker($workerId): bool
    {
        $user = Auth::user();

        // Super Admin, HR can manage all workers
        if ($user->hasRole('Super Admin') || $user->hasRole('HR')) {
            return true;
        }

        // Manager can only manage workers in their department
        if ($user->hasRole('Manager')) {
            $worker = \App\Models\Worker::find($workerId);

            if (!$worker || !$user->worker) {
                return false;
            }

            return $worker->department_id === $user->worker->department_id;
        }

        return false;
    }
}
