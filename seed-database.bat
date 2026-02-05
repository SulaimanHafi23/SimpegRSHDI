@echo off
REM Script untuk menjalankan comprehensive database seeder
REM Author: SIMPEGRS HDI Development Team
REM Description: Reset dan seed database dengan data komprehensif untuk testing

echo ================================================================
echo      SIMPEGRS HDI - Comprehensive Database Seeder
echo ================================================================
echo.

REM Warning
echo [91mWARNING: This will DELETE ALL existing data![0m
echo [93mThis action cannot be undone![0m
echo.
findstr "APP_ENV" .env
echo.

REM Confirmation
set /p confirmation="Are you sure you want to continue? (yes/no): "

if /i not "%confirmation%"=="yes" (
    echo [93mSeeding cancelled.[0m
    exit /b 0
)

echo.
echo [94mStarting database reset and seeding...[0m
echo.

REM Step 1: Fresh migration
echo [94mStep 1: Running fresh migration...[0m
php artisan migrate:fresh

if errorlevel 1 (
    echo [91mMigration failed![0m
    exit /b 1
)

echo [92mMigration completed![0m
echo.

REM Step 2: Run comprehensive seeder
echo [94mStep 2: Running comprehensive seeder...[0m
php artisan db:seed --class=ComprehensiveDatabaseSeeder

if errorlevel 1 (
    echo [91mSeeding failed![0m
    exit /b 1
)

echo.
echo [92mSeeding completed![0m
echo.

REM Step 3: Clear cache
echo [94mStep 3: Clearing cache...[0m
php artisan optimize:clear
echo [92mCache cleared![0m
echo.

REM Success message
echo ================================================================
echo           Database Seeding Completed!
echo ================================================================
echo.
echo [92mYour database is now ready for testing![0m
echo.
echo Login Credentials:
echo +------------------+----------------------------+----------+
echo ^| Role             ^| Email                      ^| Password ^|
echo +------------------+----------------------------+----------+
echo ^| Super Admin      ^| admin@rshdi.com            ^| password ^|
echo ^| HR               ^| hr@rshdi.com               ^| password ^|
echo ^| Manager IT       ^| manager.it@rshdi.com       ^| password ^|
echo ^| Manager Nursing  ^| manager.nursing@rshdi.com  ^| password ^|
echo ^| Employee         ^| employee1@rshdi.com        ^| password ^|
echo ^| Employee         ^| employee2@rshdi.com        ^| password ^|
echo +------------------+----------------------------+----------+
echo.
echo Access your application:
echo    Local: http://localhost:8000
echo.
echo For more information, see: SEEDER_GUIDE.md
echo.
pause
