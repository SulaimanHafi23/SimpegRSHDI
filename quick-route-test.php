#!/usr/bin/env php
<?php

/**
 * Quick API/Route Test Script
 * Tests critical endpoints untuk ensure tidak ada 500 errors
 */

echo "🧪 QUICK ROUTE TESTING\n";
echo str_repeat("=", 70) . "\n\n";

$baseUrl = "http://localhost";
$testResults = [];

// Test routes (yang tidak perlu authentication)
$publicRoutes = [
    'Login Page' => '/login',
];

// Admin routes yang perlu test (akan fail karena perlu auth, tapi kita check 302 redirect, bukan 500 error)
$protectedRoutes = [
    'Admin Dashboard' => '/',
    'Workers List' => '/workers',
    'Attendance List' => '/attendance',
    'Master Religions' => '/master/religions',
    'Roles List' => '/roles',
    'Users List' => '/users',
    'Holidays List' => '/holidays',
    'Leave Approvals' => '/approvals/leaves',
    'Overtime Approvals' => '/approvals/overtimes',
];

echo "📋 Testing Public Routes...\n";
echo str_repeat("-", 70) . "\n";

foreach ($publicRoutes as $name => $route) {
    $url = $baseUrl . $route;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "  ❌ $name: ERROR - $error\n";
        $testResults[$name] = "ERROR: $error";
    } elseif ($httpCode === 200) {
        echo "  ✅ $name: HTTP $httpCode\n";
        $testResults[$name] = "PASS";
    } else {
        echo "  ⚠️  $name: HTTP $httpCode\n";
        $testResults[$name] = "HTTP $httpCode";
    }
}

echo "\n📋 Testing Protected Routes (expect 302 redirect to login)...\n";
echo str_repeat("-", 70) . "\n";

foreach ($protectedRoutes as $name => $route) {
    $url = $baseUrl . $route;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        echo "  ❌ $name: ERROR - $error\n";
        $testResults[$name] = "ERROR: $error";
    } elseif ($httpCode === 302) {
        // Expected: redirect to login
        echo "  ✅ $name: HTTP $httpCode (redirected)\n";
        $testResults[$name] = "PASS";
    } elseif ($httpCode === 500) {
        echo "  ❌ $name: HTTP $httpCode (SERVER ERROR!)\n";
        $testResults[$name] = "FAIL: 500 ERROR";
    } else {
        echo "  ⚠️  $name: HTTP $httpCode\n";
        $testResults[$name] = "HTTP $httpCode";
    }
}

echo "\n";
echo "📊 SUMMARY\n";
echo str_repeat("-", 70) . "\n";

$passed = count(array_filter($testResults, fn($r) => $r === 'PASS'));
$failed = count(array_filter($testResults, fn($r) => str_contains($r, 'FAIL') || str_contains($r, 'ERROR')));
$total = count($testResults);

echo "Total Tests: $total\n";
echo "✅ Passed: $passed\n";
echo "❌ Failed: $failed\n";
echo "\n";

if ($failed > 0) {
    echo "⚠️  FAILED TESTS:\n";
    foreach ($testResults as $name => $result) {
        if (str_contains($result, 'FAIL') || str_contains($result, 'ERROR')) {
            echo "  • $name: $result\n";
        }
    }
    echo "\n";
}

echo "💡 NOTE: Untuk test lengkap dengan authentication,\n";
echo "   gunakan BROWSER-TESTING-CHECKLIST.md\n";
echo "\n";

echo "✅ Quick route testing complete!\n";
