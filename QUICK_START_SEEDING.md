# 🌱 Quick Start - Database Seeding

## Cara Paling Cepat

### Linux/Mac:
```bash
./seed-database.sh
```

### Windows:
```cmd
seed-database.bat
```

Atau manual:
```bash
php artisan migrate:fresh
php artisan db:seed --class=ComprehensiveDatabaseSeeder
```

---

## 🔐 Login Setelah Seeding

| Email | Password | Role |
|-------|----------|------|
| admin@rshdi.com | password | Super Admin |
| hr@rshdi.com | password | HR |
| manager.it@rshdi.com | password | Manager IT |
| employee1@rshdi.com | password | Employee |

---

## 📊 Data yang Akan Tersedia

✅ **Attendance** - 800+ records dengan berbagai skenario (normal, late, early, absent)
✅ **Leave Requests** - 50+ requests dengan status pending/approved/rejected/cancelled
✅ **Overtime** - 40+ requests dengan berbagai kondisi
✅ **Shift Swap** - 20+ requests dengan audit logs lengkap
✅ **Documents** - 60+ dokumen dengan status verifikasi berbeda
✅ **Business Trips** - 30+ perjalanan dinas dengan berbagai tahapan

---

## 📚 Dokumentasi Lengkap

Lihat **SEEDER_GUIDE.md** untuk detail lengkap tentang:
- Data yang di-generate
- Skenario testing yang tercakup
- Customization options
- Troubleshooting

---

## ⚠️ Important

**JANGAN jalankan di PRODUCTION!** 
Seeder ini akan **menghapus semua data** existing!
