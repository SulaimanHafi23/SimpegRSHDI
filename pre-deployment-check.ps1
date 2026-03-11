# ========================================
# SIMPEGRS Pre-Deployment PowerShell Script
# Rumah Sakit Haji Darlan Ismail
# ========================================

Write-Host "🏥 SIMPEGRS - Pre-Deployment Verification Script" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""

# Counters
$script:Errors = 0
$script:Warnings = 0
$script:Passed = 0

# Function to check command
function Test-Command {
    param([string]$CommandName)

    if (Get-Command $CommandName -ErrorAction SilentlyContinue) {
        Write-Host "✓ $CommandName is installed" -ForegroundColor Green
        $script:Passed++
        return $true
    } else {
        Write-Host "✗ $CommandName is NOT installed" -ForegroundColor Red
        $script:Errors++
        return $false
    }
}

# Function to check PHP extension
function Test-PHPExtension {
    param([string]$ExtensionName)

    $extensions = php -m
    if ($extensions -match $ExtensionName) {
        Write-Host "✓ PHP extension $ExtensionName is installed" -ForegroundColor Green
        $script:Passed++
        return $true
    } else {
        Write-Host "✗ PHP extension $ExtensionName is NOT installed" -ForegroundColor Red
        $script:Errors++
        return $false
    }
}

# Function to check file/folder writable
function Test-Writable {
    param([string]$Path)

    if (Test-Path $Path) {
        try {
            $testFile = Join-Path $Path "test_write_$(Get-Random).tmp"
            New-Item -Path $testFile -ItemType File -Force | Out-Null
            Remove-Item $testFile -Force
            Write-Host "✓ $Path is writable" -ForegroundColor Green
            $script:Passed++
            return $true
        } catch {
            Write-Host "✗ $Path is NOT writable" -ForegroundColor Red
            $script:Errors++
            return $false
        }
    } else {
        Write-Host "✗ $Path does NOT exist" -ForegroundColor Red
        $script:Errors++
        return $false
    }
}

Write-Host "1. Checking System Requirements..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

# PHP Version
try {
    $phpVersion = php -r "echo PHP_VERSION;"
    $versionParts = $phpVersion -split '\.'
    $major = [int]$versionParts[0]
    $minor = [int]$versionParts[1]

    if ($major -ge 8 -and $minor -ge 1) {
        Write-Host "✓ PHP version: $phpVersion (>= 8.1 required)" -ForegroundColor Green
        $script:Passed++
    } else {
        Write-Host "✗ PHP version: $phpVersion (PHP 8.1 or higher required)" -ForegroundColor Red
        $script:Errors++
    }
} catch {
    Write-Host "✗ PHP is NOT installed or not in PATH" -ForegroundColor Red
    $script:Errors++
}

# Check required commands
Test-Command "composer"
Test-Command "npm"
Test-Command "mysql"
Test-Command "node"

Write-Host ""
Write-Host "2. Checking PHP Extensions..." -ForegroundColor Yellow
Write-Host "-------------------------------"

$requiredExtensions = @("bcmath", "ctype", "fileinfo", "json", "mbstring", "openssl", "pdo", "tokenizer", "xml", "gd", "zip")

foreach ($ext in $requiredExtensions) {
    Test-PHPExtension $ext
}

Write-Host ""
Write-Host "3. Checking File Permissions..." -ForegroundColor Yellow
Write-Host "--------------------------------"

Test-Writable "storage"
Test-Writable "bootstrap\cache"

if (Test-Path "public\storage" -PathType Container) {
    Write-Host "✓ Storage link exists" -ForegroundColor Green
    $script:Passed++
} else {
    Write-Host "⚠ Storage link not created (run: php artisan storage:link)" -ForegroundColor Yellow
    $script:Warnings++
}

Write-Host ""
Write-Host "4. Checking Environment Configuration..." -ForegroundColor Yellow
Write-Host "------------------------------------------"

if (Test-Path ".env") {
    Write-Host "✓ .env file exists" -ForegroundColor Green
    $script:Passed++

    $envContent = Get-Content ".env" -Raw

    # Check APP_ENV
    if ($envContent -match "APP_ENV=local") {
        Write-Host "⚠ APP_ENV is set to 'local' (change to 'production' for deployment)" -ForegroundColor Yellow
        $script:Warnings++
    } else {
        Write-Host "✓ APP_ENV is not 'local'" -ForegroundColor Green
        $script:Passed++
    }

    # Check APP_DEBUG
    if ($envContent -match "APP_DEBUG=true") {
        Write-Host "⚠ APP_DEBUG is set to 'true' (change to 'false' for production)" -ForegroundColor Yellow
        $script:Warnings++
    } else {
        Write-Host "✓ APP_DEBUG is set to 'false'" -ForegroundColor Green
        $script:Passed++
    }

    # Check APP_KEY
    if ($envContent -match "APP_KEY=base64:") {
        Write-Host "✓ APP_KEY is generated" -ForegroundColor Green
        $script:Passed++
    } else {
        Write-Host "✗ APP_KEY not generated (run: php artisan key:generate)" -ForegroundColor Red
        $script:Errors++
    }

    # Check DB_PASSWORD
    if ($envContent -match "DB_PASSWORD=$" -or $envContent -match 'DB_PASSWORD=""') {
        Write-Host "⚠ DB_PASSWORD is empty (set a strong password for production)" -ForegroundColor Yellow
        $script:Warnings++
    } else {
        Write-Host "✓ DB_PASSWORD is set" -ForegroundColor Green
        $script:Passed++
    }
} else {
    Write-Host "✗ .env file NOT found" -ForegroundColor Red
    Write-Host "   → Copy .env.example to .env and configure it" -ForegroundColor Gray
    $script:Errors++
}

Write-Host ""
Write-Host "5. Checking Database Connection..." -ForegroundColor Yellow
Write-Host "-----------------------------------"

try {
    $dbTest = php artisan db:show 2>&1
    if ($LASTEXITCODE -eq 0) {
        Write-Host "✓ Database connection successful" -ForegroundColor Green
        $script:Passed++
    } else {
        Write-Host "✗ Database connection FAILED" -ForegroundColor Red
        Write-Host "   → Check DB_* values in .env file" -ForegroundColor Gray
        $script:Errors++
    }
} catch {
    Write-Host "✗ Database connection FAILED" -ForegroundColor Red
    Write-Host "   → Check DB_* values in .env file" -ForegroundColor Gray
    $script:Errors++
}

Write-Host ""
Write-Host "6. Checking Dependencies..." -ForegroundColor Yellow
Write-Host "----------------------------"

if (Test-Path "vendor") {
    Write-Host "✓ Composer dependencies installed" -ForegroundColor Green
    $script:Passed++
} else {
    Write-Host "✗ Composer dependencies NOT installed (run: composer install)" -ForegroundColor Red
    $script:Errors++
}

if (Test-Path "node_modules") {
    Write-Host "✓ NPM dependencies installed" -ForegroundColor Green
    $script:Passed++
} else {
    Write-Host "⚠ NPM dependencies NOT installed (run: npm install)" -ForegroundColor Yellow
    $script:Warnings++
}

if (Test-Path "public\build") {
    Write-Host "✓ Assets compiled" -ForegroundColor Green
    $script:Passed++
} else {
    Write-Host "⚠ Assets NOT compiled (run: npm run build)" -ForegroundColor Yellow
    $script:Warnings++
}

Write-Host ""
Write-Host "7. Checking Critical Files..." -ForegroundColor Yellow
Write-Host "-------------------------------"

$criticalFiles = @(
    "artisan",
    "composer.json",
    "package.json",
    "vite.config.js",
    "app\Http\Kernel.php",
    "routes\web.php",
    "database\migrations"
)

foreach ($file in $criticalFiles) {
    if (Test-Path $file) {
        Write-Host "✓ $file exists" -ForegroundColor Green
        $script:Passed++
    } else {
        Write-Host "✗ $file NOT found" -ForegroundColor Red
        $script:Errors++
    }
}

Write-Host ""
Write-Host "8. Security Checks..." -ForegroundColor Yellow
Write-Host "----------------------"

# Check if .git folder is exposed in public
if (Test-Path "public\.git") {
    Write-Host "✗ .git folder found in public directory (SECURITY RISK!)" -ForegroundColor Red
    $script:Errors++
} else {
    Write-Host "✓ .git folder not in public directory" -ForegroundColor Green
    $script:Passed++
}

# Check if .env is in public
if (Test-Path "public\.env") {
    Write-Host "✗ .env file found in public directory (SECURITY RISK!)" -ForegroundColor Red
    $script:Errors++
} else {
    Write-Host "✓ .env file not in public directory" -ForegroundColor Green
    $script:Passed++
}

# Check if debug mode is off in config
$appConfig = Get-Content "config\app.php" -Raw
if ($appConfig -match "'debug' => false" -or $appConfig -match "'debug' => \(bool\) env\('APP_DEBUG', false\)") {
    Write-Host "✓ Debug mode default is false in config" -ForegroundColor Green
    $script:Passed++
} else {
    Write-Host "⚠ Check debug mode configuration" -ForegroundColor Yellow
    $script:Warnings++
}

Write-Host ""
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host "VERIFICATION SUMMARY" -ForegroundColor Cyan
Write-Host "==================================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Passed: $($script:Passed) checks" -ForegroundColor Green
Write-Host "Warnings: $($script:Warnings) checks" -ForegroundColor Yellow
Write-Host "Errors: $($script:Errors) checks" -ForegroundColor Red
Write-Host ""

if ($script:Errors -gt 0) {
    Write-Host "❌ DEPLOYMENT NOT READY" -ForegroundColor Red
    Write-Host "Please fix the errors above before deploying." -ForegroundColor Gray
    exit 1
} elseif ($script:Warnings -gt 0) {
    Write-Host "⚠️  DEPLOYMENT READY WITH WARNINGS" -ForegroundColor Yellow
    Write-Host "Please review the warnings above." -ForegroundColor Gray
    Write-Host "It's recommended to fix warnings before deploying to production." -ForegroundColor Gray
    exit 0
} else {
    Write-Host "✅ DEPLOYMENT READY" -ForegroundColor Green
    Write-Host "All checks passed! You can proceed with deployment." -ForegroundColor Gray
    exit 0
}
