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

        if (!$user) {
            return null;
        }

        // Admin/HR should always see all departments even if they also have manager permission.
        if ($user->can('dashboard.admin') || $user->can('dashboard.hr')) {
            return null;
        }

        // Only manager dashboard users get restricted by department.
        if (!$user->can('dashboard.manager')) {
            return null;
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

        // Admin and HR dashboard users can manage all workers
        if ($user && ($user->can('dashboard.admin') || $user->can('dashboard.hr'))) {
            return true;
        }

        // Manager dashboard users can only manage workers in their department
        if ($user && $user->can('dashboard.manager')) {
            $worker = \App\Models\Worker::find($workerId);

            if (!$worker || !$user->worker) {
                return false;
            }

            return $worker->department_id === $user->worker->department_id;
        }

        return false;
    }
}
