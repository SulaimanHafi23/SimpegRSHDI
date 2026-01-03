# 🚀 INSTALLATION & DEPLOYMENT GUIDE

**SIMPEGRS - Sistem Informasi Manajemen Pegawai Rumah Sakit**  
**RSUD Haji Darlan Ismail**

Version: 1.0.0  
Last Updated: January 3, 2026

---

## 📋 Table of Contents

1. [Development Setup](#1-development-setup)
2. [Production Deployment](#2-production-deployment)
3. [Server Configuration](#3-server-configuration)
4. [Database Setup](#4-database-setup)
5. [SSL Configuration](#5-ssl-configuration)
6. [Queue & Scheduler](#6-queue--scheduler)
7. [Backup & Monitoring](#7-backup--monitoring)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Development Setup

### 1.1 System Requirements

**Minimum:**
```
PHP >= 8.1
MySQL >= 8.0 or PostgreSQL >= 13
Composer >= 2.x
Node.js >= 18.x
NPM >= 9.x
Git >= 2.x
```

**Recommended:**
```
PHP 8.2
MySQL 8.0.32
Composer 2.6+
Node.js 20.x LTS
NPM 10.x
RAM: 4GB
Storage: 20GB SSD
```

### 1.2 PHP Extensions

Install required PHP extensions:

```bash
# Ubuntu/Debian
sudo apt-get install php8.1-cli php8.1-fpm php8.1-mysql php8.1-xml \
php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath \
php8.1-intl php8.1-readline php8.1-redis

# CentOS/RHEL
sudo yum install php81-php php81-php-fpm php81-php-mysqlnd php81-php-xml \
php81-php-mbstring php81-php-curl php81-php-zip php81-php-gd \
php81-php-bcmath php81-php-intl php81-php-redis

# MacOS (Homebrew)
brew install php@8.1
brew install php@8.1-mysql php@8.1-gd php@8.1-zip
```

Verify installation:
```bash
php -v
php -m | grep -E 'gd|mysql|zip|mbstring'
```

### 1.3 Install Composer

```bash
# Download and install
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Verify
composer --version
```

### 1.4 Install Node.js & NPM

```bash
# Using NVM (recommended)
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.39.0/install.sh | bash
source ~/.bashrc
nvm install 20
nvm use 20

# Verify
node -v
npm -v
```

### 1.5 Clone Repository

```bash
# Clone from Git
git clone https://github.com/your-org/simpegrs.git
cd simpegrs

# Or if private repo
git clone git@github.com:your-org/simpegrs.git
cd simpegrs
```

### 1.6 Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install

# If error on maatwebsite/excel (missing ext-gd)
composer install --ignore-platform-req=ext-gd
```

### 1.7 Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

Edit `.env` file:

```env
APP_NAME="SIMPEGRS"
APP_ENV=local
APP_KEY=base64:xxxx... # auto-generated
APP_DEBUG=true
APP_URL=http://localhost:8000

LOG_CHANNEL=stack
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simpegrs_dev
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=log
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@simpegrs.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 1.8 Database Setup

```bash
# Create database
mysql -u root -p
CREATE DATABASE simpegrs_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;

# Run migrations
php artisan migrate

# Seed default data (users, roles, permissions)
php artisan db:seed

# Or run specific seeder
php artisan db:seed --class=RoleSeeder
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=MasterDataSeeder
```

### 1.9 Storage Setup

```bash
# Create symbolic link
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage
chmod -R 775 bootstrap/cache

# Or use sudo if needed
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 1.10 Build Frontend Assets

```bash
# Development (with hot-reload)
npm run dev

# Or build for production
npm run build
```

### 1.11 Run Development Server

```bash
# Start Laravel server
php artisan serve

# Access at: http://localhost:8000
```

**Default Login:**
```
Email: admin@simpegrs.com
Password: password123
Role: Super Admin
```

---

## 2. Production Deployment

### 2.1 Server Requirements

**Minimum Specs:**
```
CPU: 2 cores
RAM: 4GB
Storage: 50GB SSD
OS: Ubuntu 22.04 LTS or CentOS 8+
```

**Recommended Specs:**
```
CPU: 4 cores
RAM: 8GB
Storage: 100GB SSD
OS: Ubuntu 22.04 LTS
```

### 2.2 Prepare Server

#### 2.2.1 Update System

```bash
# Ubuntu
sudo apt update && sudo apt upgrade -y

# CentOS
sudo yum update -y
```

#### 2.2.2 Install Required Packages

```bash
# Ubuntu
sudo apt install -y software-properties-common curl wget git unzip

# Add PHP repository
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update

# Install PHP 8.1 and extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-xml \
php8.1-mbstring php8.1-curl php8.1-zip php8.1-gd php8.1-bcmath \
php8.1-intl php8.1-readline php8.1-redis

# Install MySQL
sudo apt install -y mysql-server mysql-client

# Install Nginx
sudo apt install -y nginx

# Install Redis (for cache/queue)
sudo apt install -y redis-server

# Install Supervisor (for queue worker)
sudo apt install -y supervisor
```

### 2.3 Deploy Application

#### 2.3.1 Create Deploy User

```bash
# Create user
sudo adduser deployer
sudo usermod -aG sudo deployer
sudo usermod -aG www-data deployer

# Switch to deployer user
su - deployer
```

#### 2.3.2 Clone Repository

```bash
# Create directory
sudo mkdir -p /var/www
sudo chown deployer:www-data /var/www

# Clone
cd /var/www
git clone https://github.com/your-org/simpegrs.git
cd simpegrs
```

#### 2.3.3 Install Dependencies

```bash
# Install Composer globally (if not installed)
curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Node.js (if not installed)
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Install JS dependencies and build
npm install
npm run build
```

#### 2.3.4 Environment Configuration

```bash
# Copy environment
cp .env.example .env

# Generate key
php artisan key:generate
```

Edit `.env` for production:

```env
APP_NAME="SIMPEGRS"
APP_ENV=production
APP_KEY=base64:xxxx...
APP_DEBUG=false
APP_URL=https://simpegrs.yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simpegrs_prod
DB_USERNAME=simpegrs_user
DB_PASSWORD=StrongPassword123!

BROADCAST_DRIVER=redis
CACHE_DRIVER=redis
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@simpegrs.com"
MAIL_FROM_NAME="${APP_NAME}"
```

#### 2.3.5 Set Permissions

```bash
# Set ownership
sudo chown -R deployer:www-data /var/www/simpegrs

# Set directory permissions
sudo find /var/www/simpegrs -type d -exec chmod 755 {} \;
sudo find /var/www/simpegrs -type f -exec chmod 644 {} \;

# Set write permissions for storage and cache
sudo chmod -R 775 /var/www/simpegrs/storage
sudo chmod -R 775 /var/www/simpegrs/bootstrap/cache

# Storage link
php artisan storage:link
```

#### 2.3.6 Optimize Application

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Optimize autoloader
composer dump-autoload --optimize
```

---

## 3. Server Configuration

### 3.1 MySQL Configuration

#### 3.1.1 Secure MySQL

```bash
sudo mysql_secure_installation
```

Follow prompts:
- Set root password: Yes
- Remove anonymous users: Yes
- Disallow root login remotely: Yes
- Remove test database: Yes
- Reload privilege tables: Yes

#### 3.1.2 Create Database & User

```bash
sudo mysql -u root -p
```

```sql
-- Create database
CREATE DATABASE simpegrs_prod CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'simpegrs_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';

-- Grant privileges
GRANT ALL PRIVILEGES ON simpegrs_prod.* TO 'simpegrs_user'@'localhost';

-- Flush privileges
FLUSH PRIVILEGES;

-- Exit
EXIT;
```

#### 3.1.3 Run Migrations

```bash
cd /var/www/simpegrs
php artisan migrate --force
php artisan db:seed --force
```

### 3.2 Nginx Configuration

#### 3.2.1 Create Server Block

```bash
sudo nano /etc/nginx/sites-available/simpegrs
```

**Configuration:**

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name simpegrs.yourdomain.com;

    root /var/www/simpegrs/public;
    index index.php index.html;

    access_log /var/log/nginx/simpegrs-access.log;
    error_log /var/log/nginx/simpegrs-error.log;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header X-XSS-Protection "1; mode=block";

    charset utf-8;

    # Increase upload size for documents
    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { 
        access_log off; 
        log_not_found off; 
    }

    location = /robots.txt  { 
        access_log off; 
        log_not_found off; 
    }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Cache static files
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

#### 3.2.2 Enable Site

```bash
# Create symbolic link
sudo ln -s /etc/nginx/sites-available/simpegrs /etc/nginx/sites-enabled/

# Remove default
sudo rm /etc/nginx/sites-enabled/default

# Test configuration
sudo nginx -t

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx
```

### 3.3 PHP-FPM Configuration

```bash
# Edit PHP-FPM pool config
sudo nano /etc/php/8.1/fpm/pool.d/www.conf
```

**Optimize:**

```ini
user = www-data
group = www-data
listen = /var/run/php/php8.1-fpm.sock
listen.owner = www-data
listen.group = www-data
pm = dynamic
pm.max_children = 50
pm.start_servers = 5
pm.min_spare_servers = 5
pm.max_spare_servers = 35
pm.max_requests = 500
```

**Edit PHP configuration:**

```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

**Important settings:**

```ini
upload_max_filesize = 10M
post_max_size = 10M
memory_limit = 256M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Jakarta
```

**Restart PHP-FPM:**

```bash
sudo systemctl restart php8.1-fpm
sudo systemctl enable php8.1-fpm
```

---

## 4. Database Setup

### 4.1 Optimize MySQL

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

**Add/Update:**

```ini
[mysqld]
max_connections = 200
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
innodb_flush_log_at_trx_commit = 2
query_cache_size = 0
query_cache_type = 0
```

**Restart MySQL:**

```bash
sudo systemctl restart mysql
sudo systemctl enable mysql
```

### 4.2 Database Backup Script

Create backup script:

```bash
sudo nano /usr/local/bin/backup-simpegrs.sh
```

**Script:**

```bash
#!/bin/bash

# Configuration
DB_NAME="simpegrs_prod"
DB_USER="simpegrs_user"
DB_PASS="StrongPassword123!"
BACKUP_DIR="/var/backups/simpegrs"
DATE=$(date +"%Y%m%d_%H%M%S")
RETENTION_DAYS=7

# Create backup directory
mkdir -p $BACKUP_DIR

# Dump database
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Delete old backups
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

echo "Backup completed: db_backup_$DATE.sql.gz"
```

**Make executable:**

```bash
sudo chmod +x /usr/local/bin/backup-simpegrs.sh
```

**Schedule with cron:**

```bash
sudo crontab -e
```

**Add line (daily at 2 AM):**

```
0 2 * * * /usr/local/bin/backup-simpegrs.sh >> /var/log/backup-simpegrs.log 2>&1
```

---

## 5. SSL Configuration

### 5.1 Install Certbot

```bash
# Ubuntu
sudo apt install -y certbot python3-certbot-nginx
```

### 5.2 Obtain SSL Certificate

```bash
# Get certificate
sudo certbot --nginx -d simpegrs.yourdomain.com

# Follow prompts:
# - Enter email
# - Agree to terms
# - Choose redirect HTTP to HTTPS: Yes
```

### 5.3 Auto-renewal

```bash
# Test renewal
sudo certbot renew --dry-run

# Cron is auto-created at:
# /etc/cron.d/certbot
```

### 5.4 Verify SSL

Visit: `https://simpegrs.yourdomain.com`

---

## 6. Queue & Scheduler

### 6.1 Configure Queue Worker

#### 6.1.1 Create Supervisor Config

```bash
sudo nano /etc/supervisor/conf.d/simpegrs-worker.conf
```

**Configuration:**

```ini
[program:simpegrs-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/simpegrs/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=deployer
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/simpegrs/storage/logs/worker.log
stopwaitsecs=3600
```

#### 6.1.2 Start Supervisor

```bash
# Reload configuration
sudo supervisorctl reread
sudo supervisorctl update

# Start worker
sudo supervisorctl start simpegrs-worker:*

# Check status
sudo supervisorctl status
```

### 6.2 Configure Scheduler

#### 6.2.1 Add Cron Job

```bash
crontab -e
```

**Add line:**

```
* * * * * cd /var/www/simpegrs && php artisan schedule:run >> /dev/null 2>&1
```

#### 6.2.2 Define Scheduled Tasks

Edit `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Backup database daily at 2 AM
    $schedule->command('backup:database')->dailyAt('02:00');
    
    // Clean old notifications
    $schedule->command('notifications:clean')->daily();
    
    // Reset daily attendance flag
    $schedule->command('attendance:reset')->daily();
    
    // Send attendance reminders
    $schedule->command('attendance:remind')->weekdays()->at('08:30');
    
    // Generate monthly reports
    $schedule->command('reports:monthly')->monthlyOn(1, '01:00');
}
```

---

## 7. Backup & Monitoring

### 7.1 Full Backup Script

```bash
sudo nano /usr/local/bin/full-backup-simpegrs.sh
```

**Script:**

```bash
#!/bin/bash

BACKUP_DIR="/var/backups/simpegrs"
APP_DIR="/var/www/simpegrs"
DATE=$(date +"%Y%m%d_%H%M%S")

# Database backup
mysqldump -u simpegrs_user -pStrongPassword123! simpegrs_prod | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Storage backup
tar -czf $BACKUP_DIR/storage_$DATE.tar.gz -C $APP_DIR storage

# Config backup
cp $APP_DIR/.env $BACKUP_DIR/env_$DATE.backup

# Delete old backups (7 days)
find $BACKUP_DIR -name "*.gz" -mtime +7 -delete
find $BACKUP_DIR -name "*.backup" -mtime +7 -delete

echo "Full backup completed: $DATE"
```

### 7.2 Monitor Application

#### 7.2.1 Install Laravel Telescope (Development)

```bash
composer require laravel/telescope --dev
php artisan telescope:install
php artisan migrate
```

**Only enable in development/staging!**

#### 7.2.2 Monitor Logs

```bash
# Real-time Laravel logs
tail -f /var/www/simpegrs/storage/logs/laravel.log

# Nginx access logs
tail -f /var/log/nginx/simpegrs-access.log

# Nginx error logs
tail -f /var/log/nginx/simpegrs-error.log

# Queue worker logs
tail -f /var/www/simpegrs/storage/logs/worker.log
```

#### 7.2.3 System Monitoring

Install monitoring tools:

```bash
# htop for process monitoring
sudo apt install -y htop

# iotop for disk I/O
sudo apt install -y iotop

# Check disk space
df -h

# Check memory
free -h

# Check CPU
htop
```

---

## 8. Troubleshooting

### 8.1 Permission Issues

```bash
# Fix ownership
sudo chown -R deployer:www-data /var/www/simpegrs

# Fix permissions
sudo chmod -R 755 /var/www/simpegrs
sudo chmod -R 775 /var/www/simpegrs/storage
sudo chmod -R 775 /var/www/simpegrs/bootstrap/cache
```

### 8.2 500 Internal Server Error

**Check logs:**

```bash
# Laravel logs
tail -n 50 /var/www/simpegrs/storage/logs/laravel.log

# Nginx error logs
tail -n 50 /var/log/nginx/simpegrs-error.log

# PHP-FPM logs
tail -n 50 /var/log/php8.1-fpm.log
```

**Common fixes:**

```bash
# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Re-cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 8.3 Database Connection Issues

```bash
# Test connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check MySQL status
sudo systemctl status mysql

# Restart MySQL
sudo systemctl restart mysql
```

### 8.4 Queue Not Processing

```bash
# Check supervisor status
sudo supervisorctl status

# Restart workers
sudo supervisorctl restart simpegrs-worker:*

# Check queue
php artisan queue:listen --timeout=60

# Clear failed jobs
php artisan queue:flush
```

### 8.5 High Memory Usage

```bash
# Check processes
htop

# Optimize PHP-FPM
# Edit /etc/php/8.1/fpm/pool.d/www.conf
# Reduce pm.max_children

# Clear cache
php artisan cache:clear
redis-cli FLUSHALL
```

### 8.6 Slow Performance

**Optimize database:**

```bash
php artisan migrate:optimize
```

**Enable Redis cache:**

```bash
# .env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

**Enable OPcache:**

```bash
sudo nano /etc/php/8.1/fpm/php.ini
```

```ini
opcache.enable=1
opcache.memory_consumption=256
opcache.max_accelerated_files=20000
opcache.revalidate_freq=60
```

---

## 🎉 Deployment Checklist

### Pre-Deployment

- [ ] Server meets minimum requirements
- [ ] All dependencies installed (PHP, MySQL, Nginx, Redis)
- [ ] Database created with proper user
- [ ] `.env` configured for production
- [ ] SSL certificate obtained
- [ ] Firewall configured (allow 80, 443, 22)

### Deployment

- [ ] Code deployed from Git
- [ ] Dependencies installed (`composer install --no-dev`)
- [ ] Assets built (`npm run build`)
- [ ] Migrations run (`php artisan migrate --force`)
- [ ] Storage linked (`php artisan storage:link`)
- [ ] Permissions set correctly
- [ ] Caches cleared and re-cached

### Post-Deployment

- [ ] Application accessible via HTTPS
- [ ] Login works with admin account
- [ ] Queue workers running
- [ ] Scheduler configured (cron)
- [ ] Backups configured
- [ ] Monitoring setup
- [ ] Logs rotated

---

**Version:** 1.0.0  
**Last Updated:** January 3, 2026  
**Prepared by:** IT Team - RSUD Haji Darlan Ismail
