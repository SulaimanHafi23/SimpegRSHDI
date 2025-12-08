# Base path
$basePath = "resources/views"

# Fungsi untuk create file
function New-BladeFile {
    param($path)
    $fullPath = Join-Path $basePath $path
    $directory = Split-Path $fullPath -Parent

    if (!(Test-Path $directory)) {
        New-Item -ItemType Directory -Path $directory -Force | Out-Null
    }

    if (!(Test-Path $fullPath)) {
        New-Item -ItemType File -Path $fullPath -Force | Out-Null
        Write-Host "Created: $path" -ForegroundColor Green
    } else {
        Write-Host "Exists: $path" -ForegroundColor Yellow
    }
}

Write-Host "Creating view structure..." -ForegroundColor Cyan

# ===== AUTH =====
New-BladeFile "auth/login.blade.php"
New-BladeFile "auth/register.blade.php"
New-BladeFile "auth/forgot-password.blade.php"
New-BladeFile "auth/verify-email.blade.php"
New-BladeFile "auth/reset-password.blade.php"

# ===== LAYOUTS =====
New-BladeFile "layouts/app.blade.php"
New-BladeFile "layouts/admin.blade.php"
New-BladeFile "layouts/employee.blade.php"
New-BladeFile "layouts/guest.blade.php"

# ===== LAYOUTS PARTIALS =====
New-BladeFile "layouts/partials/admin-sidebar.blade.php"
New-BladeFile "layouts/partials/admin-navbar.blade.php"
New-BladeFile "layouts/partials/employee-navbar.blade.php"
New-BladeFile "layouts/partials/employee-bottom-nav.blade.php"
New-BladeFile "layouts/partials/footer.blade.php"

# ===== COMPONENTS - FORMS =====
New-BladeFile "components/forms/input.blade.php"
New-BladeFile "components/forms/select.blade.php"
New-BladeFile "components/forms/textarea.blade.php"
New-BladeFile "components/forms/file-upload.blade.php"
New-BladeFile "components/forms/datepicker.blade.php"
New-BladeFile "components/forms/timepicker.blade.php"
New-BladeFile "components/forms/checkbox.blade.php"
New-BladeFile "components/forms/radio.blade.php"
New-BladeFile "components/forms/toggle.blade.php"

# ===== COMPONENTS - UI =====
New-BladeFile "components/ui/modal.blade.php"
New-BladeFile "components/ui/card.blade.php"
New-BladeFile "components/ui/button.blade.php"
New-BladeFile "components/ui/alert.blade.php"
New-BladeFile "components/ui/table.blade.php"
New-BladeFile "components/ui/pagination.blade.php"
New-BladeFile "components/ui/breadcrumb.blade.php"
New-BladeFile "components/ui/dropdown.blade.php"
New-BladeFile "components/ui/tabs.blade.php"
New-BladeFile "components/ui/badge.blade.php"

# ===== COMPONENTS - OTHERS =====
New-BladeFile "components/status-badge.blade.php"
New-BladeFile "components/map-picker.blade.php"
New-BladeFile "components/camera-capture.blade.php"
New-BladeFile "components/loading.blade.php"
New-BladeFile "components/avatar.blade.php"
New-BladeFile "components/calendar.blade.php"
New-BladeFile "components/chart.blade.php"
New-BladeFile "components/stats-card.blade.php"

# ===== ADMIN - DASHBOARD =====
New-BladeFile "admin/dashboard/index.blade.php"

# ===== ADMIN - MASTER DATA =====
# Religions
New-BladeFile "admin/master/religions/index.blade.php"
New-BladeFile "admin/master/religions/create.blade.php"
New-BladeFile "admin/master/religions/edit.blade.php"

# Genders
New-BladeFile "admin/master/genders/index.blade.php"
New-BladeFile "admin/master/genders/create.blade.php"
New-BladeFile "admin/master/genders/edit.blade.php"

# Positions
New-BladeFile "admin/master/positions/index.blade.php"
New-BladeFile "admin/master/positions/create.blade.php"
New-BladeFile "admin/master/positions/edit.blade.php"
New-BladeFile "admin/master/positions/show.blade.php"

# Locations
New-BladeFile "admin/master/locations/index.blade.php"
New-BladeFile "admin/master/locations/create.blade.php"
New-BladeFile "admin/master/locations/edit.blade.php"
New-BladeFile "admin/master/locations/show.blade.php"

# Document Types
New-BladeFile "admin/master/document-types/index.blade.php"
New-BladeFile "admin/master/document-types/create.blade.php"
New-BladeFile "admin/master/document-types/edit.blade.php"

# ===== ADMIN - SETTINGS =====
# File Requirements
New-BladeFile "admin/settings/file-requirements/index.blade.php"
New-BladeFile "admin/settings/file-requirements/create.blade.php"
New-BladeFile "admin/settings/file-requirements/edit.blade.php"

# Shifts
New-BladeFile "admin/settings/shifts/index.blade.php"
New-BladeFile "admin/settings/shifts/create.blade.php"
New-BladeFile "admin/settings/shifts/edit.blade.php"

# Shift Patterns
New-BladeFile "admin/settings/shift-patterns/index.blade.php"
New-BladeFile "admin/settings/shift-patterns/create.blade.php"
New-BladeFile "admin/settings/shift-patterns/edit.blade.php"

# Roles & Permissions
New-BladeFile "admin/settings/roles/index.blade.php"
New-BladeFile "admin/settings/roles/create.blade.php"
New-BladeFile "admin/settings/roles/edit.blade.php"
New-BladeFile "admin/settings/roles/permissions.blade.php"

# System Settings
New-BladeFile "admin/settings/system/index.blade.php"
New-BladeFile "admin/settings/system/general.blade.php"
New-BladeFile "admin/settings/system/email.blade.php"
New-BladeFile "admin/settings/system/attendance.blade.php"

# ===== ADMIN - WORKERS =====
New-BladeFile "admin/workers/index.blade.php"
New-BladeFile "admin/workers/create.blade.php"
New-BladeFile "admin/workers/edit.blade.php"
New-BladeFile "admin/workers/show.blade.php"
New-BladeFile "admin/workers/schedule.blade.php"

# Worker Partials
New-BladeFile "admin/workers/_partials/profile-tab.blade.php"
New-BladeFile "admin/workers/_partials/account-tab.blade.php"
New-BladeFile "admin/workers/_partials/salary-tab.blade.php"
New-BladeFile "admin/workers/_partials/documents-tab.blade.php"
New-BladeFile "admin/workers/_partials/history-tab.blade.php"

# ===== ADMIN - ATTENDANCE =====
New-BladeFile "admin/attendance/index.blade.php"
New-BladeFile "admin/attendance/show.blade.php"
New-BladeFile "admin/attendance/report.blade.php"
New-BladeFile "admin/attendance/manual-input.blade.php"
New-BladeFile "admin/attendance/export.blade.php"

# ===== ADMIN - SCHEDULES =====
New-BladeFile "admin/schedules/index.blade.php"
New-BladeFile "admin/schedules/assign.blade.php"
New-BladeFile "admin/schedules/override.blade.php"
New-BladeFile "admin/schedules/calendar.blade.php"

# ===== ADMIN - APPROVALS =====
# Leaves
New-BladeFile "admin/approvals/leaves/index.blade.php"
New-BladeFile "admin/approvals/leaves/show.blade.php"

# Overtimes
New-BladeFile "admin/approvals/overtimes/index.blade.php"
New-BladeFile "admin/approvals/overtimes/show.blade.php"

# Business Trips
New-BladeFile "admin/approvals/business-trips/index.blade.php"
New-BladeFile "admin/approvals/business-trips/show.blade.php"

# Business Trip Reports
New-BladeFile "admin/approvals/business-trips/reports/index.blade.php"
New-BladeFile "admin/approvals/business-trips/reports/show.blade.php"

# Documents
New-BladeFile "admin/approvals/documents/index.blade.php"
New-BladeFile "admin/approvals/documents/show.blade.php"

# ===== ADMIN - PAYROLL =====
New-BladeFile "admin/payroll/index.blade.php"
New-BladeFile "admin/payroll/create.blade.php"
New-BladeFile "admin/payroll/edit.blade.php"
New-BladeFile "admin/payroll/show.blade.php"
New-BladeFile "admin/payroll/bulk-payment.blade.php"
New-BladeFile "admin/payroll/history.blade.php"

# ===== ADMIN - REPORTS =====
New-BladeFile "admin/reports/attendance.blade.php"
New-BladeFile "admin/reports/leaves.blade.php"
New-BladeFile "admin/reports/overtimes.blade.php"
New-BladeFile "admin/reports/business-trips.blade.php"
New-BladeFile "admin/reports/payroll.blade.php"

# ===== EMPLOYEE - DASHBOARD =====
New-BladeFile "employee/dashboard/index.blade.php"

# ===== EMPLOYEE - ATTENDANCE =====
New-BladeFile "employee/attendance/create.blade.php"
New-BladeFile "employee/attendance/index.blade.php"
New-BladeFile "employee/attendance/detail.blade.php"

# ===== EMPLOYEE - SCHEDULE =====
New-BladeFile "employee/schedule/index.blade.php"
New-BladeFile "employee/schedule/detail.blade.php"

# ===== EMPLOYEE - REQUESTS =====
# Leaves
New-BladeFile "employee/requests/leaves/index.blade.php"
New-BladeFile "employee/requests/leaves/create.blade.php"
New-BladeFile "employee/requests/leaves/show.blade.php"

# Overtimes
New-BladeFile "employee/requests/overtimes/index.blade.php"
New-BladeFile "employee/requests/overtimes/create.blade.php"
New-BladeFile "employee/requests/overtimes/show.blade.php"

# Business Trips
New-BladeFile "employee/requests/business-trips/index.blade.php"
New-BladeFile "employee/requests/business-trips/create.blade.php"
New-BladeFile "employee/requests/business-trips/show.blade.php"

# Business Trip Reports
New-BladeFile "employee/requests/business-trips/reports/create.blade.php"
New-BladeFile "employee/requests/business-trips/reports/show.blade.php"

# ===== EMPLOYEE - DOCUMENTS =====
New-BladeFile "employee/documents/index.blade.php"
New-BladeFile "employee/documents/upload.blade.php"
New-BladeFile "employee/documents/show.blade.php"

# ===== EMPLOYEE - PAYROLL =====
New-BladeFile "employee/payroll/index.blade.php"
New-BladeFile "employee/payroll/show.blade.php"

# ===== EMPLOYEE - PROFILE =====
New-BladeFile "employee/profile/edit.blade.php"
New-BladeFile "employee/profile/change-password.blade.php"

# ===== ERRORS =====
New-BladeFile "errors/404.blade.php"
New-BladeFile "errors/403.blade.php"
New-BladeFile "errors/500.blade.php"
New-BladeFile "errors/503.blade.php"

# ===== EMAILS =====
New-BladeFile "emails/leave-approved.blade.php"
New-BladeFile "emails/leave-rejected.blade.php"
New-BladeFile "emails/overtime-approved.blade.php"
New-BladeFile "emails/business-trip-approved.blade.php"
New-BladeFile "emails/document-verified.blade.php"
New-BladeFile "emails/salary-paid.blade.php"

# ===== WELCOME & HOME =====
New-BladeFile "welcome.blade.php"
New-BladeFile "home.blade.php"

Write-Host "`nView structure created successfully!" -ForegroundColor Green
Write-Host "Total files created: 150+" -ForegroundColor Cyan
