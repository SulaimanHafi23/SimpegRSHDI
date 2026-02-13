<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Worker;

// Test approval logic
echo "=== Shift Swap Approval Logic Test ===" . PHP_EOL;

// Get two doctors from same department
$doctors = Worker::whereHas('department', function($q) {
    $q->where('name', 'like', '%Dokter%');
})->with('department')->take(2)->get();

if ($doctors->count() < 2) {
    echo "Not enough doctors found for testing" . PHP_EOL;
    exit;
}

$doctor1 = $doctors[0];
$doctor2 = $doctors[1];

echo "Doctor 1: {$doctor1->name} - Dept: {$doctor1->department->name} (ID: {$doctor1->department_id})" . PHP_EOL;
echo "Doctor 2: {$doctor2->name} - Dept: {$doctor2->department->name} (ID: {$doctor2->department_id})" . PHP_EOL;

// Test approval requirement logic
$requiresApproval = ($doctor1->department_id !== $doctor2->department_id);

echo PHP_EOL . "=== RESULT ===" . PHP_EOL;
echo "Same Department: " . ($doctor1->department_id === $doctor2->department_id ? "YES" : "NO") . PHP_EOL;
echo "Requires Manager/HR Approval: " . ($requiresApproval ? "YES" : "NO") . PHP_EOL;

// Test with different departments
echo PHP_EOL . "=== Testing Different Departments ===" . PHP_EOL;

$worker1 = Worker::whereHas('department', function($q) {
    $q->where('name', 'like', '%Dokter%');
})->first();

$worker2 = Worker::whereHas('department', function($q) {
    $q->where('name', 'not like', '%Dokter%');
})->first();

if ($worker1 && $worker2) {
    echo "Worker 1: {$worker1->name} - Dept: {$worker1->department->name} (ID: {$worker1->department_id})" . PHP_EOL;
    echo "Worker 2: {$worker2->name} - Dept: {$worker2->department->name} (ID: {$worker2->department_id})" . PHP_EOL;

    $requiresApproval2 = ($worker1->department_id !== $worker2->department_id);
    echo "Same Department: " . ($worker1->department_id === $worker2->department_id ? "YES" : "NO") . PHP_EOL;
    echo "Requires Manager/HR Approval: " . ($requiresApproval2 ? "YES" : "NO") . PHP_EOL;
}

echo PHP_EOL . "Test completed!" . PHP_EOL;
