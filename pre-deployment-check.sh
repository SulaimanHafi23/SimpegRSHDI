#!/bin/bash

# ========================================
# SIMPEGRS Pre-Deployment Script
# Rumah Sakit Haji Darlan Ismail
# ========================================

echo "🏥 SIMPEGRS - Pre-Deployment Verification Script"
echo "=================================================="
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
ERRORS=0
WARNINGS=0
PASSED=0

# Function to check command
check_command() {
    if command -v $1 &> /dev/null; then
        echo -e "${GREEN}✓${NC} $1 is installed"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $1 is NOT installed"
        ((ERRORS++))
        return 1
    fi
}

# Function to check PHP extension
check_php_extension() {
    if php -m | grep -i "$1" &> /dev/null; then
        echo -e "${GREEN}✓${NC} PHP extension $1 is installed"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} PHP extension $1 is NOT installed"
        ((ERRORS++))
        return 1
    fi
}

# Function to check file permissions
check_permissions() {
    if [ -w "$1" ]; then
        echo -e "${GREEN}✓${NC} $1 is writable"
        ((PASSED++))
        return 0
    else
        echo -e "${RED}✗${NC} $1 is NOT writable"
        ((ERRORS++))
        return 1
    fi
}

echo "1. Checking System Requirements..."
echo "-----------------------------------"

# PHP Version
PHP_VERSION=$(php -r "echo PHP_VERSION;")
PHP_MAJOR=$(echo $PHP_VERSION | cut -d. -f1)
PHP_MINOR=$(echo $PHP_VERSION | cut -d. -f2)

if [ "$PHP_MAJOR" -ge 8 ] && [ "$PHP_MINOR" -ge 1 ]; then
    echo -e "${GREEN}✓${NC} PHP version: $PHP_VERSION (>= 8.1 required)"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} PHP version: $PHP_VERSION (PHP 8.1 or higher required)"
    ((ERRORS++))
fi

# Check required commands
check_command "composer"
check_command "npm"
check_command "mysql"
check_command "node"

echo ""
echo "2. Checking PHP Extensions..."
echo "-------------------------------"

REQUIRED_EXTENSIONS=("bcmath" "ctype" "fileinfo" "json" "mbstring" "openssl" "pdo" "tokenizer" "xml" "gd" "zip")

for ext in "${REQUIRED_EXTENSIONS[@]}"; do
    check_php_extension "$ext"
done

echo ""
echo "3. Checking File Permissions..."
echo "--------------------------------"

check_permissions "storage"
check_permissions "bootstrap/cache"

if [ ! -L "public/storage" ]; then
    echo -e "${YELLOW}⚠${NC} Storage link not created (run: php artisan storage:link)"
    ((WARNINGS++))
else
    echo -e "${GREEN}✓${NC} Storage link exists"
    ((PASSED++))
fi

echo ""
echo "4. Checking Environment Configuration..."
echo "------------------------------------------"

if [ ! -f ".env" ]; then
    echo -e "${RED}✗${NC} .env file NOT found"
    echo -e "   → Copy .env.example to .env and configure it"
    ((ERRORS++))
else
    echo -e "${GREEN}✓${NC} .env file exists"
    ((PASSED++))

    # Check critical .env values
    if grep -q "APP_ENV=local" .env; then
        echo -e "${YELLOW}⚠${NC} APP_ENV is set to 'local' (change to 'production' for deployment)"
        ((WARNINGS++))
    else
        echo -e "${GREEN}✓${NC} APP_ENV is not 'local'"
        ((PASSED++))
    fi

    if grep -q "APP_DEBUG=true" .env; then
        echo -e "${YELLOW}⚠${NC} APP_DEBUG is set to 'true' (change to 'false' for production)"
        ((WARNINGS++))
    else
        echo -e "${GREEN}✓${NC} APP_DEBUG is set to 'false'"
        ((PASSED++))
    fi

    if grep -q "APP_KEY=base64:" .env; then
        echo -e "${GREEN}✓${NC} APP_KEY is generated"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} APP_KEY not generated (run: php artisan key:generate)"
        ((ERRORS++))
    fi

    if grep -q "DB_PASSWORD=$" .env || grep -q "DB_PASSWORD=\"\"" .env; then
        echo -e "${YELLOW}⚠${NC} DB_PASSWORD is empty (set a strong password for production)"
        ((WARNINGS++))
    else
        echo -e "${GREEN}✓${NC} DB_PASSWORD is set"
        ((PASSED++))
    fi
fi

echo ""
echo "5. Checking Database Connection..."
echo "-----------------------------------"

if php artisan db:show &> /dev/null; then
    echo -e "${GREEN}✓${NC} Database connection successful"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Database connection FAILED"
    echo -e "   → Check DB_* values in .env file"
    ((ERRORS++))
fi

echo ""
echo "6. Checking Dependencies..."
echo "----------------------------"

if [ -d "vendor" ]; then
    echo -e "${GREEN}✓${NC} Composer dependencies installed"
    ((PASSED++))
else
    echo -e "${RED}✗${NC} Composer dependencies NOT installed (run: composer install)"
    ((ERRORS++))
fi

if [ -d "node_modules" ]; then
    echo -e "${GREEN}✓${NC} NPM dependencies installed"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} NPM dependencies NOT installed (run: npm install)"
    ((WARNINGS++))
fi

if [ -d "public/build" ]; then
    echo -e "${GREEN}✓${NC} Assets compiled"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} Assets NOT compiled (run: npm run build)"
    ((WARNINGS++))
fi

echo ""
echo "7. Checking Critical Files..."
echo "-------------------------------"

CRITICAL_FILES=(
    "artisan"
    "composer.json"
    "package.json"
    "vite.config.js"
    "app/Http/Kernel.php"
    "routes/web.php"
    "database/migrations"
)

for file in "${CRITICAL_FILES[@]}"; do
    if [ -e "$file" ]; then
        echo -e "${GREEN}✓${NC} $file exists"
        ((PASSED++))
    else
        echo -e "${RED}✗${NC} $file NOT found"
        ((ERRORS++))
    fi
done

echo ""
echo "8. Security Checks..."
echo "----------------------"

# Check if .git folder is exposed in public
if [ -e "public/.git" ]; then
    echo -e "${RED}✗${NC} .git folder found in public directory (SECURITY RISK!)"
    ((ERRORS++))
else
    echo -e "${GREEN}✓${NC} .git folder not in public directory"
    ((PASSED++))
fi

# Check if .env is in public
if [ -e "public/.env" ]; then
    echo -e "${RED}✗${NC} .env file found in public directory (SECURITY RISK!)"
    ((ERRORS++))
else
    echo -e "${GREEN}✓${NC} .env file not in public directory"
    ((PASSED++))
fi

# Check if debug mode is off in config
if grep -q "'debug' => false" config/app.php || grep -q "'debug' => (bool) env('APP_DEBUG', false)" config/app.php; then
    echo -e "${GREEN}✓${NC} Debug mode default is false in config"
    ((PASSED++))
else
    echo -e "${YELLOW}⚠${NC} Check debug mode configuration"
    ((WARNINGS++))
fi

echo ""
echo "=================================================="
echo "VERIFICATION SUMMARY"
echo "=================================================="
echo ""
echo -e "${GREEN}Passed:${NC} $PASSED checks"
echo -e "${YELLOW}Warnings:${NC} $WARNINGS checks"
echo -e "${RED}Errors:${NC} $ERRORS checks"
echo ""

if [ $ERRORS -gt 0 ]; then
    echo -e "${RED}❌ DEPLOYMENT NOT READY${NC}"
    echo -e "Please fix the errors above before deploying."
    exit 1
elif [ $WARNINGS -gt 0 ]; then
    echo -e "${YELLOW}⚠️  DEPLOYMENT READY WITH WARNINGS${NC}"
    echo -e "Please review the warnings above."
    echo -e "It's recommended to fix warnings before deploying to production."
    exit 0
else
    echo -e "${GREEN}✅ DEPLOYMENT READY${NC}"
    echo -e "All checks passed! You can proceed with deployment."
    exit 0
fi
