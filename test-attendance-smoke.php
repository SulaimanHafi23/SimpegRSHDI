<?php
/**
 * Smoke Test: Attendance Flow (Employee & Admin)
 * Verifies that attendance routes load without errors after cleanup
 */
require_once __DIR__ . '/bootstrap/app.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Test 1: Employee Attendance
echo "=== EMPLOYEE ATTENDANCE SMOKE TEST ===\n";
$request = \Illuminate\Http\Request::create('/employee/attendance', 'GET');
$request->setUserResolver(function () {
    return \App\Models\User::where('email', 'employee1@rshdi.com')->first();
});

try {
    \Auth::setUser($request->user() ?? \App\Models\User::factory()->create(['email' => 'employee1@rshdi.com', 'role' => 'employee']));
    $response = $kernel->handle($request);
    $status = $response->getStatusCode();

    if ($status >= 200 && $status < 300) {
        echo "✓ Employee attendance page: HTTP {$status} (OK)\n";
    } else {
        echo "✗ Employee attendance page: HTTP {$status}\n";
    }

    // Check for schema errors
    $content = $response->getContent();
    if (strpos($content, 'notifiable_type') !== false || strpos($content, 'SQLSTATE') !== false) {
        echo "✗ FOUND SCHEMA ERROR IN RESPONSE\n";
    } else {
        echo "✓ No schema errors in employee page\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

// Test 2: Admin Attendance
echo "\n=== ADMIN ATTENDANCE SMOKE TEST ===\n";
$adminRequest = \Illuminate\Http\Request::create('/attendances', 'GET');
$adminUser = \App\Models\User::where('email', 'superadmin@rshdi.com')->first();

try {
    \Auth::setUser($adminUser);
    $response = $kernel->handle($adminRequest);
    $status = $response->getStatusCode();

    if ($status >= 200 && $status < 300) {
        echo "✓ Admin attendance page: HTTP {$status} (OK)\n";
    } else {
        echo "✗ Admin attendance page: HTTP {$status}\n";
    }

    // Check for schema errors
    $content = $response->getContent();
    if (strpos($content, 'notifiable_type') !== false || strpos($content, 'SQLSTATE') !== false) {
        echo "✗ FOUND SCHEMA ERROR IN RESPONSE\n";
    } else {
        echo "✓ No schema errors in admin page\n";
    }
} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\n=== TEST COMPLETE ===\n";
