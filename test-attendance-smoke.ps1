# Attendance Smoke Test - Employee & Admin Flows
# Tests attendance pages to ensure no schema errors after cleanup

Write-Host "=== ATTENDANCE SMOKE TEST ===" -ForegroundColor Green
Write-Host ""

# Employee Attendance Test
Write-Host "=== EMPLOYEE ATTENDANCE FLOW ===" -ForegroundColor Cyan

$cookieFile = "cookies_emp.txt"
Remove-Item $cookieFile -ErrorAction SilentlyContinue

# Get login form for CSRF token
$loginHtml = curl.exe -s -c $cookieFile http://localhost:8000/login
$csrf = [regex]::Match($loginHtml, 'name="_token" value="([^"]+)').Groups[1].Value

# Login as employee
curl.exe -s -b $cookieFile -c $cookieFile -X POST http://localhost:8000/login `
  --data "_token=$csrf&login=employee1@rshdi.com&password=password" | Out-Null

# Access employee attendance page
$attResponse = curl.exe -s -w "`n%{http_code}" -b $cookieFile http://localhost:8000/employee/attendance
$lines = $attResponse -split "`n"
$httpCode = $lines[-1]
$pageContent = $lines -join "`n"

Write-Host "HTTP Status: $httpCode"

# Check for schema errors
if ($pageContent -match 'notifiable_type|SQLSTATE') {
    Write-Host "✗ FOUND SCHEMA ERROR in employee page" -ForegroundColor Red
} else {
    Write-Host "✓ No schema errors in employee attendance page" -ForegroundColor Green
}

# Admin Attendance Test
Write-Host ""
Write-Host "=== ADMIN ATTENDANCE FLOW ===" -ForegroundColor Cyan

$cookieFile2 = "cookies_admin.txt"
Remove-Item $cookieFile2 -ErrorAction SilentlyContinue

# Get login form
$loginHtml2 = curl.exe -s -c $cookieFile2 http://localhost:8000/login
$csrf2 = [regex]::Match($loginHtml2, 'name="_token" value="([^"]+)').Groups[1].Value

# Login as superadmin
curl.exe -s -b $cookieFile2 -c $cookieFile2 -X POST http://localhost:8000/login `
  --data "_token=$csrf2&login=superadmin@rshdi.com&password=password" | Out-Null

# Access admin attendance page
$attAdmin = curl.exe -s -w "`n%{http_code}" -b $cookieFile2 http://localhost:8000/attendances
$lines2 = $attAdmin -split "`n"
$httpCode2 = $lines2[-1]
$pageContent2 = $lines2 -join "`n"

Write-Host "HTTP Status: $httpCode2"

# Check for schema errors
if ($pageContent2 -match 'notifiable_type|SQLSTATE') {
    Write-Host "✗ FOUND SCHEMA ERROR in admin page" -ForegroundColor Red
} else {
    Write-Host "✓ No schema errors in admin attendance page" -ForegroundColor Green
}

# Cleanup
Remove-Item $cookieFile -ErrorAction SilentlyContinue
Remove-Item $cookieFile2 -ErrorAction SilentlyContinue

Write-Host ""
Write-Host "=== TEST COMPLETE ===" -ForegroundColor Green
