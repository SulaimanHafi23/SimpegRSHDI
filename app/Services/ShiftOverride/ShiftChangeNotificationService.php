<?php

namespace App\Services\ShiftOverride;

use App\Models\ShiftOverride;
use App\Models\User;
use App\Notifications\ShiftChangeNotification;
use Illuminate\Support\Facades\Notification;

class ShiftChangeNotificationService
{
    /**
     * Send shift change notification when override is created
     */
    public function notifyOnShiftOverrideCreated(ShiftOverride $shiftOverride): bool
    {
        $user = $shiftOverride->worker->user;

        if (!$user) {
            return false;
        }

        Notification::send($user, new ShiftChangeNotification($shiftOverride, 'created'));
        return true;
    }

    /**
     * Send shift change notification when override is approved
     */
    public function notifyOnShiftOverrideApproved(ShiftOverride $shiftOverride, ?string $notes = null): bool
    {
        $user = $shiftOverride->worker->user;

        if (!$user) {
            return false;
        }

        Notification::send($user, new ShiftChangeNotification($shiftOverride, 'approved', $notes));
        return true;
    }

    /**
     * Send shift change notification when override is rejected
     */
    public function notifyOnShiftOverrideRejected(ShiftOverride $shiftOverride, ?string $reason = null): bool
    {
        $user = $shiftOverride->worker->user;

        if (!$user) {
            return false;
        }

        Notification::send($user, new ShiftChangeNotification($shiftOverride, 'rejected', $reason));
        return true;
    }

    /**
     * Send shift change notification when override is executed
     */
    public function notifyOnShiftOverrideExecuted(ShiftOverride $shiftOverride): bool
    {
        $user = $shiftOverride->worker->user;

        if (!$user) {
            return false;
        }

        Notification::send($user, new ShiftChangeNotification($shiftOverride, 'executed'));
        return true;
    }

    /**
     * Send shift change notification when override is cancelled
     */
    public function notifyOnShiftOverrideCancelled(ShiftOverride $shiftOverride, ?string $reason = null): bool
    {
        $user = $shiftOverride->worker->user;

        if (!$user) {
            return false;
        }

        Notification::send($user, new ShiftChangeNotification($shiftOverride, 'cancelled', $reason));
        return true;
    }

    /**
     * Send notification to manager about shift override that needs approval
     */
    public function notifyManagerAboutPendingApproval(ShiftOverride $shiftOverride): int
    {
        $count = 0;

        // Get manager/supervisor for the worker's department
        $managers = User::role(['Manager', 'Supervisor', 'HR'])
            ->whereHas('worker.department', function ($query) use ($shiftOverride) {
                $query->where('id', $shiftOverride->worker->department_id);
            })
            ->get();

        foreach ($managers as $manager) {
            Notification::send($manager, new ShiftChangeNotification($shiftOverride, 'created'));
            $count++;
        }

        return $count;
    }

    /**
     * Send bulk notifications for shift override to multiple users
     */
    public function notifyMultipleUsers(ShiftOverride $shiftOverride, array $userIds, string $action, ?string $message = null): int
    {
        $count = 0;

        $users = User::whereIn('id', $userIds)->get();

        foreach ($users as $user) {
            try {
                Notification::send($user, new ShiftChangeNotification($shiftOverride, $action, $message));
                $count++;
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send shift notification to user {$user->id}: " . $e->getMessage());
            }
        }

        return $count;
    }
}
