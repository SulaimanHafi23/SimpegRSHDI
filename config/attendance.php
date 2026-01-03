<?php

return [
    // Maximum allowed geolocation accuracy in meters for accepting a check-in/check-out
    // If accuracy reported by the browser is greater than this, server will reject.
    // Default changed to 300m to allow looser GPS/WiFi accuracy.
    'max_accuracy' => env('ATTENDANCE_MAX_ACCURACY', 300),

    // Automatically select nearest location if within this radius (meters)
    'auto_select_radius' => env('ATTENDANCE_AUTO_SELECT_RADIUS', 500),
    // Shift swap settings
    'shift_swap' => [
        // Minimum rest period between shifts (hours)
        'min_rest_period_hours' => env('SHIFT_SWAP_MIN_REST_HOURS', 12),

        // Lead time required for swap request (hours)
        'lead_time_hours' => env('SHIFT_SWAP_LEAD_TIME_HOURS', 48),

        // Lead time for critical roles (hours)
        'critical_lead_time_hours' => env('SHIFT_SWAP_CRITICAL_LEAD_TIME_HOURS', 72),

        // Critical departments that require longer lead time
        'critical_departments' => ['IGD', 'ICU', 'Satpam', 'Emergency'],

        // Minimum staffing level per shift (percentage)
        'min_staffing_percentage' => env('SHIFT_SWAP_MIN_STAFFING_PCT', 75),

        // Swap request expiration (hours)
        'request_expiration_hours' => env('SHIFT_SWAP_EXPIRATION_HOURS', 72),
    ],];
