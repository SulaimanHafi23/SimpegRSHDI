# 🏥 SIMPEGRS - Sistem Informasi Manajemen Pegawai Rumah Sakit

**RSUD Haji Darlan Ismail**

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0+-4479A1?style=flat&logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/License-MIT-green.svg)

Sistem Informasi Manajemen Pegawai untuk Rumah Sakit, mencakup manajemen karyawan, absensi GPS, cuti, lembur, shift, dan payroll.

---

## 📋 Daftar Isi

- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
- [Konfigurasi](#-konfigurasi)
- [Struktur Database](#-struktur-database)
- [Penggunaan](#-penggunaan)
- [Dokumentasi](#-dokumentasi)
- [Testing](#-testing)
- [Deployment](#-deployment)
- [Kontribusi](#-kontribusi)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Utama

### 👥 Manajemen Karyawan
- ✅ CRUD Karyawan dengan UUID
- ✅ Import/Export Excel dengan validasi
- ✅ Template import dengan sample data
- ✅ Multi-departemen & posisi
- ✅ Status kepegawaian (Tetap, Kontrak, Percobaan, Magang)
- ✅ Manajemen dokumen karyawan

### 📍 Absensi GPS
- ✅ Check-in/Check-out dengan GPS
- ✅ Validasi radius kantor
- ✅ Foto absensi
- ✅ Deteksi terlambat otomatis
- ✅ Deteksi pulang cepat
- ✅ Kalkulasi jam kerja

### 🗓️ Manajemen Cuti
- ✅ Pengajuan cuti dengan multiple types
- ✅ Approval workflow (Manager/HR)
- ✅ Cek saldo cuti
- ✅ Validasi tanggal konflik
- ✅ History cuti

### ⏰ Manajemen Lembur
- ✅ Pengajuan lembur
- ✅ Approval Manager/HR
- ✅ Kalkulasi jam lembur
- ✅ Export laporan lembur

### 🔄 Shift Management
- ✅ Penjadwalan shift dinamis
- ✅ Shift swap dengan approval
- ✅ Shift override
- ✅ Validasi rest period (12 jam)
- ✅ Audit log shift swap

### 📊 Dashboard & Reports
- ✅ HR Dashboard (system-wide statistics)
- ✅ Manager Dashboard (department-specific)
- ✅ Export attendance reports
- ✅ Analytics & charts

### 🔐 Role & Permission
- ✅ Super Admin (full access)
- ✅ HR (employee management, approvals)
- ✅ Manager (department approvals)
- ✅ Employee (self-service)
- ✅ Spatie Laravel Permission

---

## 🛠️ Teknologi

### Backend
- **Laravel 10.x** - PHP Framework
- **PHP 8.1+** - Programming Language
- **MySQL 8.0+** - Database
- **Spatie Laravel Permission** - Role-based Access Control
- **Maatwebsite/Excel** - Excel Import/Export
- **Intervention/Image** - Image Processing

### Frontend
- **Blade Templates** - Templating Engine
- **Alpine.js** - JavaScript Framework
- **Tailwind CSS** - CSS Framework
- **Vite** - Asset Bundler

### Development Tools
- **Composer** - Dependency Manager
- **NPM** - Package Manager
- **Laravel Telescope** - Debug Tool
- **PHPUnit** - Testing Framework

---

## 📦 Persyaratan Sistem

### Minimum Requirements
```
PHP >= 8.1
MySQL >= 8.0 / PostgreSQL >= 13
Composer >= 2.x
Node.js >= 18.x
NPM >= 9.x
```

### PHP Extensions
```
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD (untuk image processing)
- ZIP (untuk export)
```

### Server Requirements
```
Nginx >= 1.18 atau Apache >= 2.4
RAM: 2GB minimum, 4GB recommended
Storage: 10GB minimum
```

---

## 🚀 Instalasi

### 1. Clone Repository
```bash
git clone https://github.com/your-org/simpegrs.git
cd simpegrs
```

### 2. Install Dependencies
```bash
# Install PHP dependencies
composer install

# Install JavaScript dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 4. Database Setup
```bash
# Edit .env file dengan konfigurasi database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=simpegrs
DB_USERNAME=root
DB_PASSWORD=

# Run migrations
php artisan migrate

# Seed default data (users, roles, permissions)
php artisan db:seed
```

### 5. Storage Setup
```bash
# Create symbolic link for storage
php artisan storage:link

# Set permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
```

### 6. Build Assets
```bash
# Development
npm run dev

# Production
npm run build
```

### 7. Run Application
```bash
# Development server
php artisan serve

# Access at: http://localhost:8000
```

---

## ⚙️ Konfigurasi

### Default Admin Account
```
Email: admin@simpegrs.com
Password: password123
Role: Super Admin
```

### GPS Configuration
Edit `config/app.php` untuk konfigurasi GPS:
```php
'gps' => [
    'default_radius' => 100, // meters
    'enable_photo' => true,
    'max_photo_size' => 5120, // KB
],
```

### Email Configuration
Edit `.env` untuk email notifications:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@simpegrs.com
MAIL_FROM_NAME="SIMPEGRS"
```

### Queue Configuration
```bash
# Setup queue worker
php artisan queue:work

# Setup supervisor (production)
sudo nano /etc/supervisor/conf.d/simpegrs-worker.conf
```

---

## 🗄️ Struktur Database

Sistem menggunakan **26 tabel** yang terbagi dalam 5 kategori:

### Authentication & Users
- `users` - User accounts
- `personal_access_tokens` - API tokens
- `cache`, `cache_locks` - Cache system

### Master Data
- `departments` - Departemen
- `genders` - Jenis kelamin
- `religions` - Agama
- `locations` - Lokasi kantor
- `document_types` - Tipe dokumen
- `leave_types` - Tipe cuti
- `shifts` - Master shift

### HR Management
- `workers` - Data karyawan
- `worker_documents` - Dokumen karyawan
- `department_document_types` - Mapping dokumen required per departemen

### Attendance
- `attendances` - Data absensi
- `attendance_photos` - Foto absensi
- `worker_shifts` - Jadwal shift karyawan
- `shift_overrides` - Override shift
- `custom_working_days` - Hari kerja custom
- `holidays` - Data libur

### Leave & Overtime
- `leave_requests` - Pengajuan cuti
- `overtime_requests` - Pengajuan lembur
- `shift_swap_requests` - Request tukar shift
- `shift_swap_audit_logs` - Audit log shift swap

### System
- `notifications` - Notifikasi
- `jobs`, `job_batches`, `failed_jobs` - Queue system

**Detail lengkap:** Lihat [DATABASE-DOCUMENTATION.md](DATABASE-DOCUMENTATION.md)

---

## 📖 Penggunaan

### Login
1. Akses aplikasi di browser
2. Gunakan kredensial default atau akun yang sudah dibuat
3. Sistem akan redirect sesuai role:
   - **Admin/HR/Manager** → Admin Dashboard
   - **Employee** → Employee Dashboard

### Check-in/Check-out
1. Pastikan GPS aktif
2. Klik tombol "Check-in" atau "Check-out"
3. Sistem akan validasi lokasi otomatis
4. Ambil foto (opsional)
5. Submit absensi

### Pengajuan Cuti
1. Pilih menu "Cuti"
2. Klik "Buat Pengajuan Cuti"
3. Isi form: tipe, tanggal, alasan
4. Upload dokumen pendukung (opsional)
5. Submit dan tunggu approval

### Approval (Manager/HR)
1. Buka menu "Approvals"
2. Pilih jenis: Cuti, Lembur, atau Dokumen
3. Review detail pengajuan
4. Approve atau Reject dengan catatan

### Export Data
1. Pilih menu yang ingin diexport
2. Set filter (opsional)
3. Klik "Export to Excel"
4. File akan terdownload otomatis

### Import Karyawan
1. Download template: `/admin/workers/template`
2. Isi data sesuai template
3. Upload file Excel
4. Sistem akan validasi dan tampilkan error (jika ada)

---

## 📚 Dokumentasi

Dokumentasi lengkap tersedia dalam folder docs:

- **[DATABASE-DOCUMENTATION.md](DATABASE-DOCUMENTATION.md)** - ERD & struktur database lengkap
- **[ACTIVITY-DIAGRAMS.md](ACTIVITY-DIAGRAMS.md)** - Workflow diagrams untuk semua proses
- **[USER-MANUAL.md](USER-MANUAL.md)** - Panduan penggunaan untuk end-user
- **[INSTALLATION-GUIDE.md](INSTALLATION-GUIDE.md)** - Panduan instalasi & deployment detail
- **[ARCHITECTURE.md](ARCHITECTURE.md)** - Arsitektur sistem & design patterns

### API Documentation (Coming Soon)
Jika REST API diaktifkan, dokumentasi tersedia di `/api/documentation`

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test
php artisan test --filter AttendanceTest

# Run with coverage
php artisan test --coverage

# Run PHPUnit directly
./vendor/bin/phpunit
```

---

## 🚢 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production` di `.env`
- [ ] Set `APP_DEBUG=false`
- [ ] Generate production key: `php artisan key:generate`
- [ ] Optimize configuration: `php artisan config:cache`
- [ ] Optimize routes: `php artisan route:cache`
- [ ] Optimize views: `php artisan view:cache`
- [ ] Build assets: `npm run build`
- [ ] Setup queue worker (Supervisor)
- [ ] Setup scheduled tasks (Cron)
- [ ] Configure SSL certificate
- [ ] Setup backup automation
- [ ] Configure logging & monitoring

### Nginx Configuration
```nginx
server {
    listen 80;
    server_name simpegrs.yourdomain.com;
    root /var/www/simpegrs/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

**Detail lengkap:** Lihat [INSTALLATION-GUIDE.md](INSTALLATION-GUIDE.md)

---

## 🤝 Kontribusi

Contributions are welcome! Please follow these steps:

1. Fork repository
2. Create feature branch: `git checkout -b feature/AmazingFeature`
3. Commit changes: `git commit -m 'Add some AmazingFeature'`
4. Push to branch: `git push origin feature/AmazingFeature`
5. Open Pull Request

### Coding Standards
- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Use meaningful commit messages

---

## 📄 Lisensi

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 👨‍💻 Tim Pengembang

**RSUD Haji Darlan Ismail - IT Team**

---

## 📞 Kontak & Support

- **Email:** support@simpegrs.com
- **Website:** https://simpegrs.yourdomain.com
- **Issue Tracker:** https://github.com/your-org/simpegrs/issues

---

**Version:** 1.0.0  
**Last Updated:** January 3, 2026

---

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
