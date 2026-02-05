#!/bin/bash

# Script untuk menjalankan comprehensive database seeder
# Author: SIMPEGRS HDI Development Team
# Description: Reset dan seed database dengan data komprehensif untuk testing

echo "╔════════════════════════════════════════════════════════════╗"
echo "║     SIMPEGRS HDI - Comprehensive Database Seeder          ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Warning
echo -e "${RED}⚠️  WARNING: This will DELETE ALL existing data!${NC}"
echo -e "${YELLOW}This action cannot be undone!${NC}"
echo ""
echo "Current environment: $(grep APP_ENV .env | cut -d '=' -f2)"
echo ""

# Confirmation
read -p "Are you sure you want to continue? (yes/no): " confirmation

if [ "$confirmation" != "yes" ]; then
    echo -e "${YELLOW}❌ Seeding cancelled.${NC}"
    exit 0
fi

echo ""
echo -e "${BLUE}🚀 Starting database reset and seeding...${NC}"
echo ""

# Step 1: Fresh migration
echo -e "${BLUE}📦 Step 1: Running fresh migration...${NC}"
php artisan migrate:fresh

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Migration failed!${NC}"
    exit 1
fi

echo -e "${GREEN}✅ Migration completed!${NC}"
echo ""

# Step 2: Run comprehensive seeder
echo -e "${BLUE}🌱 Step 2: Running comprehensive seeder...${NC}"
php artisan db:seed --class=ComprehensiveDatabaseSeeder

if [ $? -ne 0 ]; then
    echo -e "${RED}❌ Seeding failed!${NC}"
    exit 1
fi

echo ""
echo -e "${GREEN}✅ Seeding completed!${NC}"
echo ""

# Step 3: Clear cache
echo -e "${BLUE}🧹 Step 3: Clearing cache...${NC}"
php artisan optimize:clear
echo -e "${GREEN}✅ Cache cleared!${NC}"
echo ""

# Success message
echo "╔════════════════════════════════════════════════════════════╗"
echo "║          🎉 Database Seeding Completed!                    ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""
echo -e "${GREEN}Your database is now ready for testing!${NC}"
echo ""
echo "📝 Login Credentials:"
echo "┌──────────────────┬────────────────────────────┬──────────┐"
echo "│ Role             │ Email                      │ Password │"
echo "├──────────────────┼────────────────────────────┼──────────┤"
echo "│ Super Admin      │ admin@rshdi.com            │ password │"
echo "│ HR               │ hr@rshdi.com               │ password │"
echo "│ Manager IT       │ manager.it@rshdi.com       │ password │"
echo "│ Manager Nursing  │ manager.nursing@rshdi.com  │ password │"
echo "│ Employee         │ employee1@rshdi.com        │ password │"
echo "│ Employee         │ employee2@rshdi.com        │ password │"
echo "└──────────────────┴────────────────────────────┴──────────┘"
echo ""
echo "🌐 Access your application:"
echo "   Local: http://localhost:8000"
echo ""
echo "📚 For more information, see: SEEDER_GUIDE.md"
echo ""
