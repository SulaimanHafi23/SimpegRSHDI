<?php

return [
    // Single source of truth for attendance location.
    'location' => [
        'name' => env('ATTENDANCE_LOCATION_NAME', 'Kantor Utama'),
        'latitude' => (float) env('ATTENDANCE_LOCATION_LATITUDE', -3.5794142511),
        'longitude' => (float) env('ATTENDANCE_LOCATION_LONGITUDE', 114.6277823657),
        'radius' => (int) env('ATTENDANCE_LOCATION_RADIUS', 100),
        'enforce_geofence' => (bool) env('ATTENDANCE_LOCATION_ENFORCE_GEOFENCE', true),
    ],

    // Maximum allowed geolocation accuracy in meters for accepting a check-in/check-out
    // If accuracy reported by the browser is greater than this, server will reject.
    // Default changed to 300m to allow looser GPS/WiFi accuracy.
    'max_accuracy' => env('ATTENDANCE_MAX_ACCURACY', 300),

    // Automatically select nearest location if within this radius (meters)
    'auto_select_radius' => env('ATTENDANCE_AUTO_SELECT_RADIUS', 500),

    // ========== TIME WINDOW VALIDATION ==========

    // Maximum hours BEFORE shift start time that check-in is allowed
    // Example: If shift starts at 08:00 and this is set to 2, earliest check-in is 06:00
    'check_in_window_before_hours' => (float) env('ATTENDANCE_CHECKIN_WINDOW_BEFORE', 0.5),

    // Maximum hours AFTER shift end time that check-out is allowed
    // Example: If shift ends at 14:30 and this is set to 4, latest check-out is 18:30
    'check_out_window_after_hours' => (float) env('ATTENDANCE_CHECKOUT_WINDOW_AFTER', 1.5),

    // Grace period for early check-in (minutes)
    // If employee checks in earlier than the window, they'll get a warning but still allowed
    'early_checkin_grace_minutes' => (int) env('ATTENDANCE_EARLY_CHECKIN_GRACE', 0),

    // Enable/disable strict time window enforcement
    // If false, time window violations will only show warnings, not block check-in/out
    'strict_time_window' => env('ATTENDANCE_STRICT_TIME_WINDOW', false),
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
