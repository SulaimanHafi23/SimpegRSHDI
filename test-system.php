#!/usr/bin/env php
<?php
/**
 * System Test Script
 * Tests all major features and reports bugs/issues
 */

echo "🔍 SIMPEGRS HDI - SYSTEM TEST\n";
echo "================================\n\n";

// Test 1: Database Connection
echo "1️⃣ Testing Database Connection...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="try { DB::connection()->getPdo(); echo \'✅ Database Connected\'; } catch(Exception \$e) { echo \'❌ Database Error: \' . \$e->getMessage(); }"', $output, $return);
echo implode("\n", $output) . "\n\n";
$output = [];

// Test 2: Check required tables
echo "2️⃣ Checking Database Tables...\n";
$tables = ['users', 'workers', 'roles', 'permissions', 'departments', 'locations', 'shifts', 'attendances', 'worker_shifts', 'leave_requests', 'worker_documents', 'holidays'];
foreach ($tables as $table) {
    exec("cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute=\"echo Schema::hasTable('$table') ? '✅ $table exists' : '❌ $table missing';\"", $output, $return);
    echo $output[0] . "\n";
    $output = [];
}
echo "\n";

// Test 3: Check Super Admin exists
echo "3️⃣ Checking Super Admin User...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="$user = App\\Models\\User::where(\'email\', \'superadmin@rshdi.com\')->first(); if(\$user) { echo \'✅ Super Admin exists: \' . \$user->email . \' (Role: \' . \$user->roles->pluck(\'name\')->first() . \')\'; } else { echo \'❌ Super Admin not found\'; }"', $output, $return);
echo implode("\n", $output) . "\n\n";
$output = [];

// Test 4: Check Permissions
echo "4️⃣ Checking Permissions...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="$count = Spatie\\Permission\\Models\\Permission::count(); echo \'Total Permissions: \' . \$count; if(\$count >= 150) { echo \' ✅\'; } else { echo \' ⚠️  Expected at least 150 permissions\'; }"', $output, $return);
echo implode("\n", $output) . "\n\n";
$output = [];

// Test 5: Check Roles
echo "5️⃣ Checking Roles...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="$roles = Spatie\\Permission\\Models\\Role::pluck(\'name\'); echo \'Roles: \' . \$roles->implode(\', \'); if(\$roles->contains(\'Super Admin\') && \$roles->contains(\'HR\') && \$roles->contains(\'Manager\') && \$roles->contains(\'Employee\')) { echo \' ✅\'; } else { echo \' ❌ Missing required roles\'; }"', $output, $return);
echo implode("\n", $output) . "\n\n";
$output = [];

// Test 6: Check Master Data
echo "6️⃣ Checking Master Data...\n";
$masterData = [
    'Gender' => 'App\\Models\\Gender',
    'Religion' => 'App\\Models\\Religion',
    'Department' => 'App\\Models\\Department',
    'Location' => 'App\\Models\\Location',
    'Shift' => 'App\\Models\\Shift',
    'LeaveType' => 'App\\Models\\LeaveType',
    'DocumentType' => 'App\\Models\\DocumentType',
];
foreach ($masterData as $name => $model) {
    exec("cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute=\"\$count = $model::count(); echo '$name: ' . \$count . ' records'; if(\$count > 0) { echo ' ✅'; } else { echo ' ⚠️  No data'; }\"", $output, $return);
    echo $output[0] . "\n";
    $output = [];
}
echo "\n";

// Test 7: Check Workers
echo "7️⃣ Checking Workers Data...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="$count = App\\Models\\Worker::count(); echo \'Total Workers: \' . \$count; if(\$count > 0) { echo \' ✅\'; } else { echo \' ⚠️  No workers\'; }"', $output, $return);
echo implode("\n", $output) . "\n\n";
$output = [];

// Test 8: Check for recent errors in log
echo "8️⃣ Checking Recent Errors in Log...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && tail -100 storage/logs/laravel.log | grep -i "error\|exception" | tail -5', $output, $return);
if (empty($output)) {
    echo "✅ No recent errors found\n\n";
} else {
    echo "⚠️  Recent errors found:\n";
    foreach ($output as $line) {
        echo "   " . substr($line, 0, 100) . "...\n";
    }
    echo "\n";
}
$output = [];

// Test 9: Check storage permissions
echo "9️⃣ Checking Storage Permissions...\n";
$dirs = ['storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views', 'storage/app/public'];
foreach ($dirs as $dir) {
    $path = "/home/yungzhao/Documents/SimpegRSHDI/$dir";
    if (is_writable($path)) {
        echo "✅ $dir is writable\n";
    } else {
        echo "❌ $dir is NOT writable\n";
    }
}
echo "\n";

// Test 10: Check .env configuration
echo "🔟 Checking Environment Configuration...\n";
exec('cd /home/yungzhao/Documents/SimpegRSHDI && php artisan tinker --execute="echo \'APP_ENV: \' . env(\'APP_ENV\'); echo \' | APP_DEBUG: \' . (env(\'APP_DEBUG\') ? \'true\' : \'false\'); echo \' | DB: \' . env(\'DB_DATABASE\');"', $output, $return);
echo implode("\n", $output) . "\n\n";
$output = [];

echo "================================\n";
echo "✅ System Test Completed!\n";
echo "================================\n";
