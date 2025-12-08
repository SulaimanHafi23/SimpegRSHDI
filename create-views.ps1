# Base path
$basePath = "resources\views"

# Create directory function
function Create-Directory {
    param($path)
    if (-not (Test-Path $path)) {
        New-Item -ItemType Directory -Path $path -Force | Out-Null
        Write-Host "Created: $path" -ForegroundColor Green
    }
}

# Create file function
function Create-File {
    param($path)
    if (-not (Test-Path $path)) {
        New-Item -ItemType File -Path $path -Force | Out-Null
        Write-Host "Created: $path" -ForegroundColor Cyan
    }
}

Write-Host "Starting to create view structure..." -ForegroundColor Yellow
Write-Host ""

# Auth
Create-Directory "$basePath\auth"
Create-File "$basePath\auth\login.blade.php"
Create-File "$basePath\auth\register.blade.php"
Create-File "$basePath\auth\forgot-password.blade.php"
Create-File "$basePath\auth\reset-password.blade.php"
Create-File "$basePath\auth\verify-email.blade.php"

# Layouts
Create-Directory "$basePath\layouts"
Create-File "$basePath\layouts\app.blade.php"
Create-File "$basePath\layouts\admin.blade.php"
Create-File "$basePath\layouts\employee.blade.php"
Create-File "$basePath\layouts\guest.blade.php"

# Partials (untuk navbar, sidebar, footer)
Create-Directory "$basePath\layouts\partials"
Create-File "$basePath\layouts\partials\admin-navbar.blade.php"
Create-File "$basePath\layouts\partials\admin-sidebar.blade.php"
Create-File "$basePath\layouts\partials\admin-footer.blade.php"
Create-File "$basePath\layouts\partials\employee-navbar.blade.php"
Create-File "$basePath\layouts\partials\employee-footer.blade.php"

# Components - Forms
Create-Directory "$basePath\components\forms"
Create-File "$basePath\components\forms\input.blade.php"
Create-File "$basePath\components\forms\select.blade.php"
Create-File "$basePath\components\forms\textarea.blade.php"
Create-File "$basePath\components\forms\datepicker.blade.php"
Create-File "$basePath\components\forms\timepicker.blade.php"
Create-File "$basePath\components\forms\file-upload.blade.php"
Create-File "$basePath\components\forms\checkbox.blade.php"
Create-File "$basePath\components\forms\radio.blade.php"
Create-File "$basePath\components\forms\label.blade.php"
Create-File "$basePath\components\forms\error.blade.php"

# Components - UI
Create-Directory "$basePath\components\ui"
Create-File "$basePath\components\ui\modal.blade.php"
Create-File "$basePath\components\ui\card.blade.php"
Create-File "$basePath\components\ui\button.blade.php"
Create-File "$basePath\components\ui\alert.blade.php"
Create-File "$basePath\components\ui\badge.blade.php"
Create-File "$basePath\components\ui\table.blade.php"
Create-File "$basePath\components\ui\pagination.blade.php"
Create-File "$basePath\components\ui\breadcrumb.blade.php"
Create-File "$basePath\components\ui\loading.blade.php"
Create-File "$basePath\components\ui\empty-state.blade.php"
Create-File "$basePath\components\status-badge.blade.php"

# Admin - Dashboard
Create-Directory "$basePath\admin\dashboard"
Create-File "$basePath\admin\dashboard\index.blade.php"

# Admin - Master Data - Religions
Create-Directory "$basePath\admin\master\religions"
Create-File "$basePath\admin\master\religions\index.blade.php"
Create-File "$basePath\admin\master\religions\create.blade.php"
Create-File "$basePath\admin\master\religions\edit.blade.php"
Create-File "$basePath\admin\master\religions\show.blade.php"

# Admin - Master Data - Genders
Create-Directory "$basePath\admin\master\genders"
Create-File "$basePath\admin\master\genders\index.blade.php"
Create-File "$basePath\admin\master\genders\create.blade.php"
Create-File "$basePath\admin\master\genders\edit.blade.php"
Create-File "$basePath\admin\master\genders\show.blade.php"

# Admin - Master Data - Positions
Create-Directory "$basePath\admin\master\positions"
Create-File "$basePath\admin\master\positions\index.blade.php"
Create-File "$basePath\admin\master\positions\create.blade.php"
Create-File "$basePath\admin\master\positions\edit.blade.php"
Create-File "$basePath\admin\master\positions\show.blade.php"

# Admin - Master Data - Locations
Create-Directory "$basePath\admin\master\locations"
Create-File "$basePath\admin\master\locations\index.blade.php"
Create-File "$basePath\admin\master\locations\create.blade.php"
Create-File "$basePath\admin\master\locations\edit.blade.php"
Create-File "$basePath\admin\master\locations\show.blade.php"

# Admin - Master Data - Document Types
Create-Directory "$basePath\admin\master\document-types"
Create-File "$basePath\admin\master\document-types\index.blade.php"
Create-File "$basePath\admin\master\document-types\create.blade.php"
Create-File "$basePath\admin\master\document-types\edit.blade.php"
Create-File "$basePath\admin\master\document-types\show.blade.php"

# Admin - Settings - File Requirements
Create-Directory "$basePath\admin\settings\file-requirements"
Create-File "$basePath\admin\settings\file-requirements\index.blade.php"
Create-File "$basePath\admin\settings\file-requirements\create.blade.php"
Create-File "$basePath\admin\settings\file-requirements\edit.blade.php"
Create-File "$basePath\admin\settings\file-requirements\show.blade.php"

# Admin - Settings - Shifts
Create-Directory "$basePath\admin\settings\shifts"
Create-File "$basePath\admin\settings\shifts\index.blade.php"
Create-File "$basePath\admin\settings\shifts\create.blade.php"
Create-File "$basePath\admin\settings\shifts\edit.blade.php"
Create-File "$basePath\admin\settings\shifts\show.blade.php"

# Admin - Settings - Shift Patterns (TAMBAHAN)
Create-Directory "$basePath\admin\settings\shift-patterns"
Create-File "$basePath\admin\settings\shift-patterns\index.blade.php"
Create-File "$basePath\admin\settings\shift-patterns\create.blade.php"
Create-File "$basePath\admin\settings\shift-patterns\edit.blade.php"
Create-File "$basePath\admin\settings\shift-patterns\show.blade.php"

# Admin - Settings - Roles & Permissions
Create-Directory "$basePath\admin\settings\roles"
Create-File "$basePath\admin\settings\roles\index.blade.php"
Create-File "$basePath\admin\settings\roles\create.blade.php"
Create-File "$basePath\admin\settings\roles\edit.blade.php"
Create-File "$basePath\admin\settings\roles\show.blade.php"

Create-Directory "$basePath\admin\settings\permissions"
Create-File "$basePath\admin\settings\permissions\index.blade.php"
Create-File "$basePath\admin\settings\permissions\create.blade.php"
Create-File "$basePath\admin\settings\permissions\edit.blade.php"

# Admin - Workers
Create-Directory "$basePath\admin\workers"
Create-File "$basePath\admin\workers\index.blade.php"
Create-File "$basePath\admin\workers\create.blade.php"
Create-File "$basePath\admin\workers\edit.blade.php"
Create-File "$basePath\admin\workers\show.blade.php"
Create-File "$basePath\admin\workers\schedule.blade.php"
Create-File "$basePath\admin\workers\import.blade.php"
Create-File "$basePath\admin\workers\export.blade.php"

# Admin - Attendance
Create-Directory "$basePath\admin\attendance"
Create-File "$basePath\admin\attendance\index.blade.php"
Create-File "$basePath\admin\attendance\show.blade.php"
Create-File "$basePath\admin\attendance\report.blade.php"
Create-File "$basePath\admin\attendance\export.blade.php"

# Admin - Approvals - Leaves
Create-Directory "$basePath\admin\approvals\leaves"
Create-File "$basePath\admin\approvals\leaves\index.blade.php"
Create-File "$basePath\admin\approvals\leaves\show.blade.php"
Create-File "$basePath\admin\approvals\leaves\approve.blade.php"
Create-File "$basePath\admin\approvals\leaves\reject.blade.php"

# Admin - Approvals - Overtimes
Create-Directory "$basePath\admin\approvals\overtimes"
Create-File "$basePath\admin\approvals\overtimes\index.blade.php"
Create-File "$basePath\admin\approvals\overtimes\show.blade.php"
Create-File "$basePath\admin\approvals\overtimes\approve.blade.php"
Create-File "$basePath\admin\approvals\overtimes\reject.blade.php"

# Admin - Approvals - Business Trips
Create-Directory "$basePath\admin\approvals\business-trips"
Create-File "$basePath\admin\approvals\business-trips\index.blade.php"
Create-File "$basePath\admin\approvals\business-trips\show.blade.php"
Create-File "$basePath\admin\approvals\business-trips\approve.blade.php"
Create-File "$basePath\admin\approvals\business-trips\reject.blade.php"
Create-File "$basePath\admin\approvals\business-trips\report-review.blade.php"

# Admin - Approvals - Documents
Create-Directory "$basePath\admin\approvals\documents"
Create-File "$basePath\admin\approvals\documents\index.blade.php"
Create-File "$basePath\admin\approvals\documents\show.blade.php"
Create-File "$basePath\admin\approvals\documents\verify.blade.php"
Create-File "$basePath\admin\approvals\documents\reject.blade.php"

# Admin - Payroll
Create-Directory "$basePath\admin\payroll"
Create-File "$basePath\admin\payroll\index.blade.php"
Create-File "$basePath\admin\payroll\create.blade.php"
Create-File "$basePath\admin\payroll\show.blade.php"
Create-File "$basePath\admin\payroll\edit.blade.php"
Create-File "$basePath\admin\payroll\generate.blade.php"
Create-File "$basePath\admin\payroll\export.blade.php"

# Admin - Reports (TAMBAHAN)
Create-Directory "$basePath\admin\reports"
Create-File "$basePath\admin\reports\attendance.blade.php"
Create-File "$basePath\admin\reports\leaves.blade.php"
Create-File "$basePath\admin\reports\overtimes.blade.php"
Create-File "$basePath\admin\reports\business-trips.blade.php"
Create-File "$basePath\admin\reports\payroll.blade.php"

# Employee - Dashboard
Create-Directory "$basePath\employee\dashboard"
Create-File "$basePath\employee\dashboard\index.blade.php"

# Employee - Attendance
Create-Directory "$basePath\employee\attendance"
Create-File "$basePath\employee\attendance\create.blade.php"
Create-File "$basePath\employee\attendance\index.blade.php"
Create-File "$basePath\employee\attendance\show.blade.php"

# Employee - Schedule
Create-Directory "$basePath\employee\schedule"
Create-File "$basePath\employee\schedule\index.blade.php"
Create-File "$basePath\employee\schedule\calendar.blade.php"

# Employee - Requests - Leaves
Create-Directory "$basePath\employee\requests\leaves"
Create-File "$basePath\employee\requests\leaves\index.blade.php"
Create-File "$basePath\employee\requests\leaves\create.blade.php"
Create-File "$basePath\employee\requests\leaves\show.blade.php"
Create-File "$basePath\employee\requests\leaves\edit.blade.php"

# Employee - Requests - Overtimes
Create-Directory "$basePath\employee\requests\overtimes"
Create-File "$basePath\employee\requests\overtimes\index.blade.php"
Create-File "$basePath\employee\requests\overtimes\create.blade.php"
Create-File "$basePath\employee\requests\overtimes\show.blade.php"
Create-File "$basePath\employee\requests\overtimes\edit.blade.php"

# Employee - Requests - Business Trips
Create-Directory "$basePath\employee\requests\business-trips"
Create-File "$basePath\employee\requests\business-trips\index.blade.php"
Create-File "$basePath\employee\requests\business-trips\create.blade.php"
Create-File "$basePath\employee\requests\business-trips\show.blade.php"
Create-File "$basePath\employee\requests\business-trips\edit.blade.php"
Create-File "$basePath\employee\requests\business-trips\report-create.blade.php"
Create-File "$basePath\employee\requests\business-trips\report-edit.blade.php"

# Employee - Documents
Create-Directory "$basePath\employee\documents"
Create-File "$basePath\employee\documents\index.blade.php"
Create-File "$basePath\employee\documents\upload.blade.php"
Create-File "$basePath\employee\documents\show.blade.php"

# Employee - Payroll
Create-Directory "$basePath\employee\payroll"
Create-File "$basePath\employee\payroll\index.blade.php"
Create-File "$basePath\employee\payroll\show.blade.php"

# Employee - Profile
Create-Directory "$basePath\employee\profile"
Create-File "$basePath\employee\profile\index.blade.php"
Create-File "$basePath\employee\profile\edit.blade.php"
Create-File "$basePath\employee\profile\change-password.blade.php"

# Error Pages (TAMBAHAN)
Create-Directory "$basePath\errors"
Create-File "$basePath\errors\403.blade.php"
Create-File "$basePath\errors\404.blade.php"
Create-File "$basePath\errors\500.blade.php"
Create-File "$basePath\errors\503.blade.php"

# Email Templates (TAMBAHAN)
Create-Directory "$basePath\emails"
Create-File "$basePath\emails\welcome.blade.php"
Create-File "$basePath\emails\leave-approved.blade.php"
Create-File "$basePath\emails\leave-rejected.blade.php"
Create-File "$basePath\emails\overtime-approved.blade.php"
Create-File "$basePath\emails\payroll-notification.blade.php"

Write-Host ""
Write-Host "View structure created successfully!" -ForegroundColor Green
Write-Host "Counting total files..." -ForegroundColor Yellow

$totalFiles = (Get-ChildItem -Path $basePath -Recurse -File).Count
$totalDirs = (Get-ChildItem -Path $basePath -Recurse -Directory).Count

Write-Host "Total Directories: $totalDirs" -ForegroundColor Cyan
Write-Host "Total Files: $totalFiles" -ForegroundColor Cyan
