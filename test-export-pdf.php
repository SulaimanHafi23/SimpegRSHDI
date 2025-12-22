<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Worker;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Services\Export\PdfExportService;
use Carbon\Carbon;

echo "=== Testing PDF Export Functionality ===\n\n";

// Get atau create test user dengan role Employee
$user = User::role('Employee')->first();
if (!$user) {
    echo "❌ Error: Tidak ada user dengan role Employee\n";
    echo "   Jalankan seeder terlebih dahulu: php artisan db:seed\n";
    exit(1);
}

echo "✓ User found: {$user->name} (ID: {$user->id})\n";

$worker = Worker::where('user_id', $user->id)->first();
if (!$worker) {
    echo "❌ Error: User tidak memiliki data worker\n";
    exit(1);
}

echo "✓ Worker found: {$worker->name} (ID: {$worker->id})\n\n";

$pdfService = new PdfExportService();

// Test 1: Export Attendance PDF
echo "1. Testing Attendance Export PDF...\n";
try {
    $attendances = Attendance::where('worker_id', $worker->id)
        ->latest('attendance_date')
        ->limit(10000)
        ->get()
        ->toArray();
    
    $attendanceCount = count($attendances);
    echo "   - Found {$attendanceCount} attendance records\n";
    
    if ($attendanceCount > 0) {
        $pdf = $pdfService->exportAttendanceReport($attendances, $worker);
        echo "   ✓ PDF generated successfully!\n";
        
        // Save to test file
        $filename = storage_path('app/test-attendance-export.pdf');
        file_put_contents($filename, $pdf->output());
        echo "   ✓ Saved to: {$filename}\n";
    } else {
        echo "   ⚠ No attendance data to export (creating sample data...)\n";
        
        // Create sample attendance
        $attendance = Attendance::create([
            'worker_id' => $worker->id,
            'attendance_date' => Carbon::now()->format('Y-m-d'),
            'check_in' => Carbon::now()->setTime(8, 0, 0),
            'check_out' => Carbon::now()->setTime(17, 0, 0),
            'status' => 'Hadir',
            'location_id' => 1,
        ]);
        
        $attendances = [$attendance->toArray()];
        $pdf = $pdfService->exportAttendanceReport($attendances, $worker);
        echo "   ✓ PDF generated with sample data!\n";
        
        $filename = storage_path('app/test-attendance-export.pdf');
        file_put_contents($filename, $pdf->output());
        echo "   ✓ Saved to: {$filename}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test 2: Export Leave Request PDF
echo "2. Testing Leave Request Export PDF...\n";
try {
    $leaves = LeaveRequest::where('worker_id', $worker->id)
        ->with(['leaveType'])
        ->latest('created_at')
        ->limit(10000)
        ->get()
        ->toArray();
    
    $leaveCount = count($leaves);
    echo "   - Found {$leaveCount} leave request records\n";
    
    if ($leaveCount > 0) {
        $pdf = $pdfService->exportLeaveReport($leaves, $worker);
        echo "   ✓ PDF generated successfully!\n";
        
        $filename = storage_path('app/test-leave-export.pdf');
        file_put_contents($filename, $pdf->output());
        echo "   ✓ Saved to: {$filename}\n";
    } else {
        echo "   ⚠ No leave request data - PDF will show empty state\n";
        
        // Test with empty array
        $pdf = $pdfService->exportLeaveReport([], $worker);
        echo "   ✓ PDF generated with empty data!\n";
        
        $filename = storage_path('app/test-leave-export.pdf');
        file_put_contents($filename, $pdf->output());
        echo "   ✓ Saved to: {$filename}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n";

// Test 3: Export Overtime Request PDF
echo "3. Testing Overtime Request Export PDF...\n";
try {
    $overtimes = OvertimeRequest::where('worker_id', $worker->id)
        ->latest('overtime_date')
        ->limit(10000)
        ->get()
        ->toArray();
    
    $overtimeCount = count($overtimes);
    echo "   - Found {$overtimeCount} overtime request records\n";
    
    if ($overtimeCount > 0) {
        $pdf = $pdfService->exportOvertimeReport($overtimes, $worker);
        echo "   ✓ PDF generated successfully!\n";
        
        $filename = storage_path('app/test-overtime-export.pdf');
        file_put_contents($filename, $pdf->output());
        echo "   ✓ Saved to: {$filename}\n";
    } else {
        echo "   ⚠ No overtime request data - PDF will show empty state\n";
        
        // Test with empty array
        $pdf = $pdfService->exportOvertimeReport([], $worker);
        echo "   ✓ PDF generated with empty data!\n";
        
        $filename = storage_path('app/test-overtime-export.pdf');
        file_put_contents($filename, $pdf->output());
        echo "   ✓ Saved to: {$filename}\n";
    }
} catch (\Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
    echo "   Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Testing Complete ===\n";
echo "\nGenerated PDF files:\n";
echo "- " . storage_path('app/test-attendance-export.pdf') . "\n";
echo "- " . storage_path('app/test-leave-export.pdf') . "\n";
echo "- " . storage_path('app/test-overtime-export.pdf') . "\n";
