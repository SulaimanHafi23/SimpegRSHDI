# 📚 SIDIA - Documentation Index

**Aplikasi**: SIMPEGRS (Sistem Informasi Manajemen Pegawai Rumah Sakit)  
**Versi**: 1.0 (March 2026)  
**Status**: Production Ready

---

## 🗂️ Dokumentasi Tersedia

### 1️⃣ **[APLIKASI_SIDIA_OVERVIEW.md](APLIKASI_SIDIA_OVERVIEW.md)** ⭐ START HERE
**Deskripsi Komprehensif Aplikasi SIDIA**

Dokumen lengkap yang mencakup:
- ✅ Pengenalan singkat sistem
- ✅ 4 aktor utama & role mereka
- ✅ 9 fitur utama dengan detail
- ✅ Arsitektur teknis & technology stack
- ✅ Permission & role management (42 permissions)
- ✅ Database schema overview
- ✅ 3 key workflows dengan flowchart
- ✅ User interfaces per role
- ✅ Notification system
- ✅ Security features & operations

**Cocok untuk**: Developer baru, AI assistants, technical documentation

---

### 2️⃣ **[ARCHITECTURE.md](ARCHITECTURE.md)**
**Dokumentasi Arsitektur Teknis Mendetail**

Mencakup:
- System overview & component diagrams
- Architecture patterns (MVC, Service Layer, Repository)
- Design patterns (DTO, Service, Repository)
- Directory structure
- Database architecture
- API architecture
- Security architecture
- Performance optimization

**Cocok untuk**: Architects, senior developers, system design discussions

---

### 3️⃣ **[USE-CASE-SISTEM-BERDASARKAN-FITUR.md](USE-CASE-SISTEM-BERDASARKAN-FITUR.md)**
**Use Cases per Fitur & Aktor**

Mencakup:
- Use case untuk Pegawai
- Use case untuk Manager
- Use case untuk HR
- Use case untuk Super Admin
- Daftar semua use case per modul

**Cocok untuk**: Business analyst, QA, feature testing

---

### 4️⃣ **[PERMISSION-GROUPING.md](PERMISSION-GROUPING.md)**
**Permission & Role Structure**

Mencakup:
- 7 kategori permission
- 42 total permissions detail
- Role hierarchy
- Permission assignment rules

**Cocok untuk**: Admin, HR, security specialists

---

### 5️⃣ **[SHIFT-SWAP-FEATURE.md](SHIFT-SWAP-FEATURE.md)**
**Detil Fitur Shift Swap**

Mencakup:
- Business rules (lead time, rest period, double shift)
- Architecture & design pattern
- Database schema
- API endpoints
- User flows
- Testing approach
- Audit & logging

**Cocok untuk**: Developers working on shift swap, feature deep-dive

---

### 6️⃣ **[HOLIDAY-MANAGEMENT.md](HOLIDAY-MANAGEMENT.md)**
**Detil Manajemen Hari Libur**

Mencakup:
- Holiday types
- Manual creation
- Bulk import
- Auto-generation (2025, 2026)

**Cocok untuk**: HR, features yang berkaitan dengan holiday

---

### 7️⃣ **[GOOGLE_CALENDAR_INTEGRATION.md](GOOGLE_CALENDAR_INTEGRATION.md)**
**Integrasi Google Calendar**

Mencakup:
- Integration architecture
- Setup instructions
- API usage
- Calendar sync logic

**Cocok untuk**: Developers implementing calendar sync

---

### 🎨 **[ERROR-PAGES.md](ERROR-PAGES.md)**
**Error Pages & Status Codes**

Mencakup:
- HTTP status codes digunakan
- Error page list
- Error handling strategy

**Cocok untuk**: Frontend developers, error handling

---

## 📊 Diagram & Visualisasi

### Sequence Diagrams
Folder: `SequenceDiagram/`
```
├── Leave Approval Sequence
├── Shift Swap Approval Sequence
├── Attendance Check-in Sequence
├── Business Trip Sequence
└── Document Upload Sequence
```

### Activity Diagrams  
Folder: `ActivityDiagram/`
```
├── Module-level Activity Diagrams
├── Detail/ (per-action level)
│   ├── Leave (view, create, list, update, delete, approve, reject)
│   ├── Shift Swap (view, create, list, approve, execute, revert)
│   ├── Attendance (checkin, checkout, list, export)
│   └── ... (other modules)
└── UseCaseDerived/ (swimlane style)
    ├── Holiday Management
    ├── Role Management  
    ├── Personnel Management
    └── Audit Log
```

### Class Diagrams
```
├── class-diagram-id.md (Indonesian labels)
├── class-diagram-mvc-id.md (MVC focused)
└── class-diagram-uml-murni-id.md (UML standard)
```

### Entity Relationship Diagrams
```
├── RELASI-TABEL.md (Table relationships)
├── RELASI-ANTAR-TABEL.txt (Detailed relations)
├── RELASI-REFERENSI-ID.txt (Reference IDs)
└── entitas-atribut-database.txt (Entities & attributes)
```

### Other Diagrams
```
├── dashboard-access.mermaid (Dashboard access control)
├── permission-architecture.mermaid (Permission structure)
├── permission-actions.mermaid (Permission action flows)
├── use-case-diagram.md (Complete use case)
└── use-case-diagram-id.md (Indonesian use case)
```

---

## 🚀 Quick Start Guide

### Untuk Pemula/New Developer
```
1. Baca: APLIKASI_SIDIA_OVERVIEW.md (5-10 min)
2. Pahami: Fitur utama & role
3. Review: Relevant use case di USE-CASE-SISTEM
4. Cek: Database schema di docs/RELASI-TABEL.md
5. Explore: Source code sesuai fitur
```

### Untuk Feature Development
```
1. Baca: APLIKASI_SIDIA_OVERVIEW.md → bagian fitur
2. Review: Sequence diagram untuk feature tersebut
3. Review: Activity diagram (detail level)
4. Cek: Database schema (RELASI-TABEL.md)
5. Review: Permission yang diperlukan
6. Implement: Ikuti design patterns di ARCHITECTURE.md
```

### Untuk Bug Fix/Troubleshooting
```
1. Identifikasi: Module/fitur mana yang bermasalah
2. Cek: Use case di USE-CASE-SISTEM
3. Trace: Sequence diagram untuk understand flow
4. Debug: Ikuti code path di ARCHITECTURE.md
5. Test: Validate dengan feature specification
```

### Untuk Role/Permission Changes
```
1. Baca: PERMISSION-GROUPING.md (understand current structure)
2. Cek: USE-CASE-SISTEM (identify which features affected)
3. Review: permission-architecture.mermaid (see relationships)
4. Implement: In roles & permissions management
5. Test: Verify all affected workflows
```

---

## 📱 Fitur Utama (Quick Overview)

| Fitur | Deskripsi | Status |
|-------|-----------|--------|
| **Attendance GPS** | Check-in/out dengan GPS & foto | ✅ Active |
| **Leave Management** | Pengajuan cuti dengan workflow approval | ✅ Active |
| **Shift Management** | Jadwal kerja, swap, override | ✅ Active |
| **Shift Swap** | Tukar shift dengan validasi ketat | ✅ Active |
| **Overtime** | Pengajuan lembur | ✅ Active |
| **Business Trip** | Perjalanan dinas | ✅ Active |
| **Master Data** | Kelola departemen, shift, jenis cuti, dll | ✅ Active |
| **Worker Documents** | Upload & verify dokumen pegawai | ✅ Active |
| **Notifications** | In-app & email notifications | ✅ Active |
| **Reports & Export** | Dashboard, reports, data export | ✅ Active |
| **Role & Permission** | RBAC dengan 42 permissions | ✅ Active |
| **Google Calendar** | Sync dengan Google Calendar | 🔄 Development |
| **Mobile App** | Native iOS/Android app | 🔄 Planned |
| **Payroll Integration** | Integrasi dengan sistem payroll | 🔄 Planned |

---

## 🎯 Key Contacts & Resources

### Database Models
```
24+ Eloquent models dalam app/Models/
Termasuk: User, Worker, Attendance, LeaveRequest, etc
```

### Services
```
Business logic centralized dalam app/Services/
Termasuk: AttendanceService, LeaveRequestService, etc
```

### Controllers
```
Request handling dalam app/Http/Controllers/
Termasuk: Admin/, Manager/, Employee/, Approval/
```

### Views
```
UI templates dalam resources/views/
Termasuk: admin/, manager/, employee/, approvals/
```

### API (Future)
```
API endpoints dalam app/Http/Controllers/Api/
Akan support: Mobile app, third-party integrations
```

---

## 🔗 Useful Commands

### Running Tests
```bash
php artisan test                    # Run all tests
php artisan test --filter=Leave     # Run Leave tests
```

### Database Management
```bash
php artisan migrate                 # Run migrations
php artisan migrate:refresh         # Reset database
php artisan db:seed                 # Seed test data
php artisan tinker                  # Interactive shell
```

### Cache & Optimization
```bash
php artisan cache:clear            # Clear cache
php artisan config:cache           # Cache config
php artisan view:cache             # Cache views
php artisan route:cache            # Cache routes
```

### Local Development
```bash
php artisan serve                  # Start development server
npm run dev                        # Start Vite asset watcher
php artisan queue:listen           # Start queue worker (if used)
```

---

## 💾 Architecture Patterns Used

### Service Layer Pattern
```
Controller → Service → Repository → Model → Database
```

### DTO (Data Transfer Object)
```
AttendanceDTO, LeaveRequestDTO, UserDTO, etc
(Untuk standardize data transfer & type safety)
```

### Repository Pattern
```
Abstraction layer untuk data access
Enable easy mocking untuk testing
```

### Policy Pattern (Laravel)
```
Authorization logic untuk resource access
Contoh: LeavePolicy, AttendancePolicy
```

### Middleware
```
Request validation, permission checking
Contoh: 'role:HR', 'permission:leave.manage'
```

---

## 🔒 Security Best Practices

✅ SQL injection prevention (Eloquent ORM)  
✅ XSS protection (Blade escaping)  
✅ CSRF protection (Laravel middleware)  
✅ Password hashing (bcrypt)  
✅ Role-based access control (Spatie)  
✅ Audit logging (track all changes)  
✅ Rate limiting (brute force protection)  
✅ Data validation (server-side)  

---

## 📞 Support Resources

- **Documentation Folder**: `/docs/` (at repository root)
- **Source Code**: `/app/`, `/resources/views/`
- **Database**: See migrations in `/database/migrations/`
- **Configuration**: `/config/` folder

---

## 📝 Last Updated

**March 30, 2026** - Documentation complete and comprehensive

---

**Gunakan dokumentasi ini sebagai panduan komprehensif untuk memahami, develop, dan maintain aplikasi SIDIA.**
