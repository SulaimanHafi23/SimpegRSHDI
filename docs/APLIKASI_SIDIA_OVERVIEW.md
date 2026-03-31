# 📱 APLIKASI SIDIA - Sistem Informasi Manajemen Pegawai Rumah Sakit

**Aplikasi**: SIMPEGRS (Sistem Informasi Manajemen Pegawai Rumah Sakit)  
**Institusi**: RSUD Haji Darlan Ismail  
**Teknologi**: Laravel 10, PHP 8.1+, MySQL 8.0+, Alpine.js, Tailwind CSS  
**Status**: Production-Ready  
**Last Updated**: March 2026

---

## 📖 Pengenalan Singkat

**SIDIA** adalah sistem informasi terintegrasi yang mengelola seluruh aspek manajemen pegawai rumah sakit, dari absensi GPS real-time hingga manajemen shift dan payroll. Sistem ini dirancang untuk meningkatkan efisiensi operasional dengan workflow otomatis, validasi bisnis ketat, dan role-based access control.

---

## 🎯 Tujuan Utama Sistem

1. **Otomasi Manajemen Pegawai** - Mengurangi administrative overhead dengan digitalisasi proses manual
2. **Transparansi Attendance** - Tracking real-time kehadiran pegawai menggunakan GPS
3. **Workflow Approval Terstruktur** - Approval cuti, lembur, shift swap, dan perjalanan dinas dengan audit trail
4. **Data-Driven Insights** - Dashboard dan laporan untuk decision making
5. **Kepatuhan Regulasi** - Validasi rest period, minimum staffing, dan audit log lengkap

---

## 👥 Aktor Sistem & Role

Sistem mendukung 4 aktor utama dengan permission berbeda:

### 1. **Super Admin** (Sistem & IT Admin)
- **Akses**: Full system access
- **Tanggung Jawab**:
  - Manajemen role dan permission
  - Manajemen user accounts (CRUD)
  - Audit log sistem
  - Konfigurasi sistem
- **Contoh Workflow**: Membuat role baru → Assign permissions → Monitor user activities

### 2. **HR (Human Resources)**
- **Akses**: HR management functions
- **Tanggemen Pegawai**:
  - Import/export pegawai (Excel dengan validasi)
  - Create, update, delete, resign pegawai
  - Kelola status kepegawaian (Tetap, Kontrak, Percobaan, Magang)
  - Resign management
- **Kelola Master Data**:
  - Shift kerja (CRUD, generate schedule)
  - Tipe cuti dengan quota
  - Tipe dokumen
  - Hari libur (manual, bulk, auto-generate)
  - Off-day patterns pegawai
- **Manajemen Admin**:
  - Check-in/check-out manual untuk pegawai
  - Shift override, shift swap execution
  - Absensi histori dan statistik
- **Approval**:
  - Cuti, overtime, perjalanan dinas, shift swap
  - Verifikasi dokumen pegawai
- **Laporan**:
  - Export attendance, leave, documents
  - Analytics

### 3. **Manager (Managerial Staff)**
- **Akses**: Department-level management
- **Fungsi Utama**:
  - **Dashboard Manager** - statistik departemen (attendance rate, cuti pending, shift swaps, etc)
  - **Approval Requests**:
    - Cuti pegawai (setujui/tolak)
    - Overtime requests (setujui/tolak)
    - Shift swap requests (setujui/tolak/eksekusi/revert)
    - Perjalanan dinas (setujui/tolak)
  - **View Laporan** - attendance, leave, documents khusus departemen
- **Batasan**: Hanya melihat data departemen mereka sendiri (department filter)

### 4. **Pegawai (Employee)**
- **Akses**: Self-service functions
- **Fungsi Utama**:
  - **Check-in/Check-out** - GPS based attendance
  - **Absensi** - Lihat histori absensi pribadi, export
  - **Jadwal Kerja** - Lihat shift schedule pribadi
  - **Cuti**:
    - Ajukan pengajuan cuti
    - Batalkan pengajuan cuti pending
    - Lihat histori dan saldo cuti
  - **Shift Swap** - Ajukan tukar shift dengan pegawai lain
  - **Overtime** - Ajukan pengajuan lembur
  - **Perjalanan Dinas** - Ajukan perjalanan dinas + batalkan
  - **Dokumen** - Upload, lihat, download, hapus dokumen pribadi
  - **Profil** - Lihat/update profil, ubah password
  - **Notifikasi** - Kelola notifikasi sistem

---

## 🔑 Fitur Utama

### 1️⃣ **Attendance Management (Manajemen Absensi)**

#### Teknologi GPS Real-Time
- **Check-in/Check-out**: Pegawai dapat melakukan check-in dari lokasi menggunakan GPS
- **Foto Proof**: Setiap check-in disertai dengan foto untuk validasi
- **Radius Validation**: Sistem mengecek apakah lokasi GPS berada dalam radius kantor (default ~km)
- **Late Detection**: Otomatis deteksi keterlambatan jika check-in melebihi jam kerja
- **Early Check-out**: Deteksi pulang cepat jika check-out kurang dari waktu yang dijadwalkan

#### Admin Management (HR Privileges)
- Manual check-in/check-out untuk pegawai
- Viewing attendance history dengan filter (date range, worker, status)
- Export attendance reports (PDF, Excel)

#### Statistics & Reports
- Attendance rate per departemen
- Late/early trends
- Export untuk payroll processing

#### Database Schema
```sql
-- Attendance records
• id (UUID) - Primary key
• worker_id (FK) - Pegawai
• check_in_time (datetime) - Waktu check-in
• check_in_latitude/longitude (decimal) - Koordinat check-in
• check_in_photo (string) - Path foto check-in
• check_out_time (datetime) - Waktu check-out
• check_out_latitude/longitude (decimal) - Koordinat check-out
• status (enum: present, late, early_checkout, absent)
• notes (text) - Catatan admin
```

---

### 2️⃣ **Leave Management (Manajemen Cuti)**

#### Fitur Utama
- **Multiple Leave Types**: Cuti tahunan, sakit, izin, tanpa gaji, dll
- **Quota Management**: Setiap pegawai memiliki quota cuti per tahun
- **Date Conflict Validation**: Cegah duplikasi pengajuan pada tanggal yang sama
- **Approval Workflow**:
  1. Pegawai submit → HR/Manager review
  2. Semua approval diperlukan dari manager departemen + HR
  3. Auto-notification ke approver
  4. Status: pending → approved/rejected

#### Use Case Pegawai
```
1. Buka "Cuti" menu
2. Klik "Ajukan Cuti"
3. Pilih tipe cuti, tanggal mulai, tanggal selesai
4. Masukkan alasan
5. Submit
→ Status berubah menjadi "Menunggu Persetujuan"
→ Manager menerima notifikasi
```

#### Use Case Manager
```
1. Buka "Approval → Cuti"
2. Lihat daftar pengajuan pending
3. Klik "Detail"
4. Pilih "Setujui" atau "Tolak" (dengan alasan)
5. Submit
→ Status updated
→ Pegawai menerima notifikasi
```

#### Database Schema
```sql
-- Leave Requests
• id (UUID)
• worker_id (FK) - Pengaju
• leave_type_id (FK) - Tipe cuti
• start_date (date)
• end_date (date)
• total_days (int)
• reason (text)
• status (enum: pending, approved, rejected, cancelled)
• approved_by (FK) - User yang approve
• approved_at (datetime)
• rejection_reason (text)

-- Leave Types
• id (UUID)
• name (string) - "Cuti Tahunan", "Sakit", etc
• annual_quota (int) - Quota per tahun
• requires_approval (boolean)
```

---

### 3️⃣ **Shift Management (Manajemen Jadwal Kerja)**

#### Shift Scheduling
- **Master Shifts**: Definisi shift kerja (nama, jam mulai, jam selesai, tipe)
- **Worker Shift Assignment**: Assign shift ke pegawai per date
- **Auto-generate Schedule**: Generate jadwal untuk periode tertentu
- **Schedule History**: Track perubahan shift dengan timestamp

#### Shift Override (Overtime/Lembur)
- **Definition**: Penugasan shift tambahan di luar jadwal regular
- **Bulk Create**: HR dapat membuat multiple shift override sekaligus
- **Admin Approval**: HR approval untuk shift override

#### Shift Swap (Tukar Shift)
- **Peer-to-Peer Request**: Pegawai A meminta tukar shift dengan pegawai B
- **Same Department**: Tidak perlu manager approval
- **Cross-Department**: Memerlukan manager approval dari kedua departemen
- **Approval Workflow**:
  1. Requestor (Pegawai A) submit
  2. Swap partner (Pegawai B) approve/decline
  3. If cross-dept → Manager dari kedua dept approve
  4. HR execute/revert

#### Business Rules/Validation
```
✓ Lead Time Validation:
  - Regular dept: minimal 48 jam sebelum shift
  - Critical dept (IGD, ICU, Satpam): minimal 72 jam

✓ Rest Period Validation:
  - Minimum 12 jam rest antara shift
  - Check attendance records + scheduled shift

✓ Double Shift Prevention:
  - Cegah 2 shift sama-hari untuk worker yang sama

✓ Minimum Staffing:
  - Tidak boleh <75% dari scheduled workers per shift

✓ Department-Based Approval:
  - Same dept = no manager approval needed
  - Cross-dept = both managers must approve
```

#### Database Schema
```sql
-- Shifts (Master)
• id (UUID)
• name (string) - "Pagi", "Sore", "Malam"
• start_time (time)
• end_time (time)
• shift_type (enum: regular, flex, oncall)

-- Worker Shifts (Assignment)
• id (UUID)
• worker_id (FK)
• shift_id (FK)
• date (date)
• notes (text)

-- Shift Swap Requests
• id (UUID)
• requester_id (FK) - Pegawai A (peminta)
• partner_id (FK) - Pegawai B (partner)
• requester_shift_id (FK) - Shift Pegawai A
• partner_shift_id (FK) - Shift Pegawai B
• swap_date (date)
• status (enum: pending, approved, declined, executed, reverted)
• approval_notes (text)

-- Shift Swap Audit Log
• id (UUID)
• swap_id (FK)
• action (enum: requested, partner_approved, manager_approved, executed, reverted)
• actor_id (FK)
• department_id (FK)
• timestamp
• details (JSON)
```

---

### 4️⃣ **Overtime Management (Manajemen Lembur)**

#### Fitur
- **Request Submission**: Pegawai submit pengajuan lembur dengan tanggal, jam, alasan
- **Approval Workflow**: Manager/HR setujui atau tolak
- **Calculation**: System auto-calculate jam lembur berdasarkan attendance
- **Report**: Export lembur untuk payroll

#### Use Case
```
Pegawai:
1. Buka "Lembur"
2. Klik "Ajukan Lembur"
3. Pilih tanggal, jam mulai, jam selesai
4. Masukkan keterangan pekerjaan
5. Submit

Manager:
1. Buka "Approval → Lembur"
2. Review request
3. Setujui atau tolak dengan alasan
```

---

### 5️⃣ **Business Trip (Perjalanan Dinas)**

#### Fitur
- **Trip Request**: Pegawai submit perjalanan dinas dengan tujuan, tanggal, estimasi biaya
- **Approval**: Manager/HR approval
- **Duration**: Half-day atau full-day trips
- **Transportation & Accommodation**: Pencatatan transportasi dan akomodasi
- **Document Upload**: Laporan perjalanan can be attached

#### Database Schema
```sql
-- Business Trips
• id (UUID)
• worker_id (FK)
• destination (string)
• purpose (text)
• start_date (date)
• end_date (date)
• trip_duration_type (enum: full_day, half_day)
• half_day_session (enum: morning, afternoon) - if half_day
• transportation (string) - "Flight", "Car", "Train"
• accommodation (string) - "Hotel", "Friend's House"
• notes (text)
• estimated_cost (decimal)
• status (enum: pending, approved, rejected, cancelled)
• approved_by (FK)
• approved_at (datetime)
• rejection_reason (text)
```

---

### 6️⃣ **Master Data Management (Kelola Data Master)**

#### 📊 Data Master yang Tersedia

| Data | Fungsi | Managed By |
|------|--------|-----------|
| **Department** | Departemen/Unit organisasi | HR/Super Admin |
| **Shift** | Definisi shift kerja | HR |
| **Leave Type** | Jenis-jenis cuti | HR |
| **Document Type** | Tipe dokumen pegawai | HR |
| **Holiday** | Hari libur nasional/cuti bersama | HR |
| **Location** | Lokasi kantor untuk GPS validation | HR/Super Admin |
| **Religion** | Agama (e.g., untuk ibadah schedule) | HR |
| **Gender** | Jenis kelamin | HR |

#### Holiday Management
- **Manual Create**: HR input holiday satu per satu
- **Bulk Create**: HR upload multiple holidays sekaligus (Excel)
- **Auto-Generate**: System auto-generate holiday untuk tahun tertentu (2025, 2026)
- **Duplicate Prevention**: Jika tanggal sudah ada, data dilewati (tidak duplikasi)

---

### 7️⃣ **Worker Document Management (Kelola Dokumen Pegawai)**

#### Fitur
- **Document Types**: Various required documents (SIM, NPWP, Vaksin, SKD, dll)
- **Department Mapping**: Tipe dokumen yang wajib per departemen
- **Upload**: Pegawai upload dokumen di sistem
- **Verification Workflow**:
  1. Pegawai upload dokumen
  2. HR review (valid/invalid dengan alasan)
  3. Status: uploaded → verified/rejected
- **Download**: Download dokumen yang sudah terverifikasi
- **Expiry Tracking**: Track dokumen yang sudah expired

#### Database Schema
```sql
-- Document Types
• id (UUID)
• name (string) - "SIM", "NPWP", "Vaksin COVID-19"
• is_required (boolean)

-- Department Document Type Mapping
• id (UUID)
• department_id (FK)
• document_type_id (FK)
• is_required (boolean)
• notes (text)

-- Worker Documents
• id (UUID)
• worker_id (FK)
• document_type_id (FK)
• file_path (string)
• file_name (string)
• uploaded_at (datetime)
• status (enum: uploaded, verified, rejected)
• verified_by (FK) - HR user
• verified_at (datetime)
• rejection_reason (text)
• expiry_date (date)
```

---

### 8️⃣ **Notifications (Notifikasi Sistem)**

#### Tipe Notifikasi
- **Leave Approval**: Notifikasi saat cuti disetujui/ditolak
- **Shift Swap**: Notifikasi saat swap request, approval, execution
- **Document Verification**: Notifikasi saat dokumen verified/rejected
- **Overtime Approval**: Notifikasi saat lembur disetujui
- **Business Trip**: Notifikasi saat perjalanan dinas approved/rejected

#### Channel
- In-app notifications (database stored)
- Email notifications (SMTP)
- Optional: SMS (dapat di-extend)

#### Notification Management
```
Pegawai dapat:
• Lihat semua notifikasi
• Mark as read
• Filter berdasarkan tipe
• Delete notifikasi
```

---

### 9️⃣ **Dashboard & Reports**

#### Employee Dashboard
- **Quick Stats**:
  - Attendance rate bulan ini
  - Sisa cuti tahunan
  - Shift sekarang & shift berikutnya
  - Dokumen yang pending verifikasi
- **Activity Feed**: Recent approvals, notifications, deadlines

#### Manager Dashboard
- **Department Overview**:
  - Total pegawai
  - Attendance rate departemen
  - Pending approvals (cuti, shift swap, etc)
  - Late employees count
- **Charts & Analytics**:
  - Attendance trend
  - Leave usage
  - Shift swap statistics

#### HR Dashboard
- **System-wide Statistics**:
  - Total pegawai, departemen
  - Attendance KPI
  - Leave/overtime trends
  - Document compliance status
  - Audit log activities
- **Alerts**:
  - Data yang pending approval
  - Workers dengan attendance issues
  - Document expiry reminders

#### Reports Module
- **Attendance Report**: Filter by date range, department, worker
- **Leave Report**: Leave usage, remaining quota, trends
- **Worker Document Report**: Verification status, expiry status
- **Custom Export**: CSV, PDF, Excel formats

---

## 🏛️ Arsitektur Teknis

### Struktur Aplikasi
```
Laravel MVC Architecture:
├── Routes (routes/web.php)
│   ├── Auth Routes
│   ├── Dashboard Routes
│   ├── Admin Routes (HR functions)
│   ├── Manager Routes
│   ├── Employee Routes
│   ├── Approval Routes
│   └── API Routes (future)
│
├── Controllers
│   ├── Auth/ (Login, Password reset)
│   ├── Admin/ (HR management)
│   ├── Manager/ (Manager dashboard, approvals)
│   ├── Employee/ (Self-service)
│   ├── Approval/ (Approval workflows)
│   └── Api/ (API endpoints)
│
├── Models (Eloquent)
│   ├── User
│   ├── Worker
│   ├── Attendance
│   ├── LeaveRequest
│   ├── OvertimeRequest
│   ├── BusinessTrip
│   ├── ShiftSwapRequest
│   ├── WorkerDocument
│   ├── Holiday
│   ├── Shift
│   └── ... (24+ models total)
│
├── Services (Business Logic)
│   ├── AttendanceService
│   ├── LeaveRequestService
│   ├── ShiftSwapService
│   ├── NotificationService
│   └── ... (centralized business logic)
│
├── Repositories (Data Access)
│   ├── AttendanceRepository
│   ├── LeaveRequestRepository
│   └── ... (data query abstraction)
│
├── DTOs (Data Transfer Objects)
│   ├── AttendanceDTO
│   ├── LeaveRequestDTO
│   ├── UserDTO
│   └── ... (data transfer standardization)
│
├── Requests (Form Validation)
│   ├── LeaveRequestRequest
│   ├── OvertimeRequestRequest
│   └── ... (request validation)
│
├── Traits (Reusable Logic)
│   ├── DepartmentFilterable (Manager department filter)
│   └── ... (cross-cutting concerns)
│
├── Mail (Email Templates)
│   ├── LeaveApproved
│   ├── LeaveRejected
│   └── ... (notification emails)
│
└── Views (Blade Templates)
    ├── layouts/ (Base layouts)
    ├── admin/ (HR interface)
    ├── manager/ (Manager interface)
    ├── employee/ (Employee interface)
    └── approvals/ (Approval workflows)
```

### Technology Stack
```
Backend:
• Laravel 10.x - Web framework
• PHP 8.1+ - Programming language
• MySQL 8.0+ - Database
• Redis - Cache & Queue

Frontend:
• Blade Templates - Server-side rendering
• Alpine.js - Reactive components
• Tailwind CSS - Styling
• Vite - Asset bundler

External Integrations:
• Spatie Permission - RBAC
• Maatwebsite/Excel - Excel processing
• Intervention/Image - Image manipulation
• Barryvdh/DomPDF - PDF export
```

---

## 🔐 Permission & Role Management

### Role Hierarchy
```
1. Super Admin (Root access)
   └─ Dashboard admin
   └─ User management
   └─ Role management
   └─ All HR functions
   └─ All features

2. HR (Human Resources)
   └─ Employee management (CRUD, resign)
   └─ Attendance management
   └─ Master data management
   └─ Leave/Overtime/Trip approval
   └─ Document verification
   └─ Reports & export
   └─ NO role/user management

3. Manager (Department Manager)
   └─ Dashboard (department-level)
   └─ Approval functions (cuti, lembur, shift swap, trip)
   └─ Department reports
   └─ NO employee CRUD
   └─ NO master data access

4. Employee (Pegawai)
   └─ Check-in/check-out
   └─ Submit requests (cuti, lembur, trip, shift swap)
   └─ View personal data
   └─ View personal reports
   └─ NO approval functions
   └─ NO admin access
```

### Permission Categories (42 total permissions)
```
1. Dashboard (2): dashboard.admin, dashboard.employee
2. Master Data (9): religion.manage, gender.manage, dept.manage, shift.manage, etc
3. Management (4): worker.manage, attendance.manage, schedule.manage, document.manage
4. Approvals (12): leave.manage/approve, overtime.manage/approve, shift-swap.manage/approve, trip.manage/approve
5. Employee Access (13): worker.view, attendance.checkin, leave.request, etc
6. Reports (3): report.view, report.export, report.personal
7. Settings (2): role.manage, user.manage
```

---

## 📊 Database Schema Overview

### Core Entities
```
┌─────────────┐
│    User     │ ← Authentication
│ – id        │
│ – username  │
│ – email     │
│ – password  │
└─────────────┘
       │
       │ has_one
       ▼
┌──────────────┐
│   Worker     │ ← Pegawai/Employee
│ – id (UUID)  │
│ – user_id    │
│ – nip        │
│ – name       │
│ – dept_id    │
│ – position   │
│ – status     │
└──────────────┘
       │
       ├────────┬────────┬─────────┬──────────┐
       │        │        │         │          │
    has_many    │        │         │          │
       │        │        │         │          │
       ▼        ▼        ▼         ▼          ▼
   Attendance  Leave   Overtime  Business   WorkerShift
                       Trip
```

### Key Tables
```
• users - Authentication
• workers - Pegawai data
• departments - Departemen
• attendances - GPS attendance records
• leave_requests - Cuti requests
• leaf_types - Jenis cuti
• overtime_requests - Lembur requests
• business_trips - Perjalanan dinas
• shift_swap_requests - Tukar shift requests
• worker_shifts - Schedule assignments
• shifts - Master shift definitions
• worker_documents - Dokumen pegawai
• document_types - Jenis dokumen
• holidays - Hari libur
• notifications - In-app notifications
• audit_logs - System audit trail
• roles - Spatie Permission roles
• permissions - Spatie Permission permissions
```

---

## 🔄 Key Workflows

### 1. Leave Request Workflow
```
START
  ↓
Pegawai ajukan cuti
  ↓
System validasi:
  ✓ Saldo cuti cukup?
  ✓ Tidak ada duplikasi tanggal?
  ✓ Tanggal valid?
  ├─ VALID → Next
  └─ INVALID → Reject & show error
  ↓
Status = PENDING
Notifikasi ke Manager/HR
  ↓
Manager/HR review
  ├─ SETUJUI → Status = APPROVED
  │   Notifikasi Pegawai
  │   Deduct saldo cuti
  │   END (SUCCESS)
  │
  └─ TOLAK → Status = REJECTED
      Notifikasi Pegawai + alasan
      END (FAIL)
```

### 2. Shift Swap Approval Workflow
```
START
  ↓
Pegawai A request swap dengan Pegawai B
  ↓
System validasi:
  ✓ Lead time (48/72 jam)?
  ✓ Rest period (12 jam)?
  ✓ Minimum staffing (75%)?
  ✓ Double shift check?
  ├─ VALID → Next
  └─ INVALID → Reject & show error
  ↓
IF same department:
  Status = AUTO_APPROVED
  ↓
  Pegawai B notifikasi (partner approval)
  Pegawai B accept/reject
  ├─ ACCEPT → Next
  └─ REJECT → Status = DECLINED, END

IF cross department:
  Status = PENDING_MANAGER
  ↓
  Both managers notifikasi
  ├─ Both APPROVE → Next
  ├─ Any REJECT → Status = DECLINED, END
  └─ Timeout → Status = EXPIRED, END

↓
Status = APPROVED
HR execute swap (update worker_shifts)
↓
Audit log created
Notifikasi all parties
END (SUCCESS)
```

### 3. Attendance Check-in Workflow
```
START
  ↓
Pegawai open mobile app
  ↓
System request GPS permission
  ↓
Pegawai click Check-in
  ↓
System capture:
  ✓ GPS coordinates
  ✓ Current timestamp
  ✓ Photo proof
  ↓
Validate:
  ✓ GPS dalam radius kantor (2km)?
  ├─ YES → Continue
  └─ NO → Show error, allow retry

  ✓ Worker memiliki shift hari ini?
  ├─ YES → Continue
  └─ NO → Show warning

  ✓ Check-in time vs scheduled start?
  ├─ On-time → Status = PRESENT
  ├─ Late (>15 min) → Status = LATE
  └─ Invalid time → Reject
  ↓
Create attendance record:
  • worker_id, check_in_time, coords, photo
  • status → PRESENT/LATE
  • created_at = now()
  ↓
Show success message
  ↓
END
```

---

## 🚀 Key Features & Business Rules

### Attendance System
```
✓ GPS validation (radius checking)
✓ Photo proof attachment
✓ Late/early detection
✓ Auto calculation of working hours
✓ Admin manual check-in override
```

### Leave System
```
✓ Multiple leave type support (tahunan, sakit, izin)
✓ Annual quota management
✓ Date conflict prevention
✓ Approval chain (Manager + HR)
✓ Auto-deduction from quota
```

### Shift System
```
✓ Master shift definitions
✓ Schedule generation
✓ Shift swap with validations
✓ Lead time requirements (48/72 hours)
✓ Rest period validation (12 hours)
✓ Minimum staffing (75%)
✓ Department-based approval
✓ Swap audit trail
```

### Business Trip
```
✓ Full/half-day trip support
✓ Transportation & accommodation tracking
✓ Estimated cost recording
✓ Manager approval
✓ Document linkage
```

### Document Management
```
✓ Multiple document types
✓ Department-specific requirements
✓ Upload & verification workflow
✓ Expiry date tracking
✓ Compliance reporting
```

---

## 📱 User Interfaces

### Employee Portal
```
Sidebar Menu:
├── Dashboard - Quick stats & activity
├── Absensi - Check-in/check-out, history, export
├── Cuti - Ajukan, lihat riwayat, saldo
├── Lembur - Ajukan lembur
├── Perjalanan Dinas - Ajukan trip
├── Shift - Lihat jadwal, ajukan tukar shift
├── Dokumen - Upload, lihat, download
├── Profil - View/edit profile
├── Notifikasi - Kelola notifikasi
└── Logout
```

### Manager Portal
```
Sidebar Menu:
├── Dashboard - Department stats
├── Persetujuan - Cuti, lembur, trip, shift swap
├── Laporan - Attendance, leave, documents
├── Notifikasi
└── Logout
```

### HR Portal
```
Sidebar Menu:
├── Dashboard - System stats
├── Data Pegawai - CRUD, import, export, resign
├── Absensi - Admin check-in, history, export
├── Cuti - CRUD, approve/reject
├── Lembur - CRUD, approve/reject
├── Perjalanan Dinas - CRUD, approve/reject
├── Shift
│   ├── Master Shift - CRUD
│   ├── Jadwal Pegawai - Assign, generate
│   ├── Shift Override - CRUD, bulk create
│   └── Tukar Shift - Approve/reject/execute/revert
├── Master Data
│   ├── Departemen
│   ├── Jenis Cuti
│   ├── Tipe Dokumen
│   ├── Hari Libur
│   └── ... (other master data)
├── Dokumen Pegawai - Verify/reject, compliance
├── Laporan - Export reports
├── Notifikasi
└── Logout
```

### Super Admin Portal
```
All HR access +
├── Manajemen User - CRUD users
├── Manajemen Role - CRUD roles, assign permissions
├── Audit Log - View system activities
└── Pengaturan Sistem
```

---

## 🔔 Notification System

### Notification Types
```
1. Leave Approval
   • "Cuti Anda telah disetujui oleh [Manager]"
   • "Pengajuan cuti Anda ditolak: [Alasan]"

2. Shift Swap
   • "[Partner] meminta pertukaran shift dengan Anda"
   • "Permintaan pertukaran shift Anda disetujui"
   • "Manager menyetujui pertukaran shift"

3. Overtime
   • "Pengajuan lembur Anda disetujui"

4. Business Trip
   • "Perjalanan dinas Anda disetujui"

5. Document
   • "Dokumen Anda sudah diverifikasi"
   • "Dokumen Anda ditolak: [Alasan]"

6. System
   • "Jadwal Anda berubah"
   • "Dokumen Anda akan expire dalam 30 hari"
```

### Notification Channels
```
• Database (in-app notifications)
• Email (SMTP)
• Optional SMS (future)
```

---

## 🧪 Testing & Quality

### Test Coverage
```
• Unit tests untuk Services & Repositories
• Feature tests untuk workflows
• API tests untuk endpoints
• Validation tests untuk form requests
```

### Quality Assurance
```
✓ Input validation (server-side)
✓ Business logic validation
✓ Permission checking
✓ Audit logging
✓ Error handling & graceful degradation
```

---

## 📈 Extensibility & Future Enhancements

### Planned Features
```
1. API Authentication (Sanctum/OAuth)
2. Mobile app native (iOS/Android)
3. Biometric attendance integration
4. Payroll module integration
5. Google Calendar sync
6. SMS notifications
7. Performance management
8. Training management
9. Leave encashment calculation
10. Pension/benefits management
```

### Integration Points
```
• Google Calendar (sync events)
• GPS service provider (location validation)
• Email service (notifications)
• Storage (file uploads)
• Payroll system (future)
```

---

## 🔒 Security Features

### Authentication
```
✓ Laravel Sanctum (API auth)
✓ Session-based (web auth)
✓ Password hashing (bcrypt)
✓ Password reset flow
✓ Remember me functionality
```

### Authorization
```
✓ Spatie Permission Laravel
✓ Role-based access control (RBAC)
✓ Department-level filtering (Manager)
✓ Permission gates & policies
✓ Audit logging
```

### Data Protection
```
✓ SQL injection prevention (Eloquent ORM)
✓ XSS protection (Blade escaping)
✓ CSRF protection (Laravel middleware)
✓ Rate limiting
✓ Data encryption (sensitive fields)
```

---

## 📋 Operational Procedures

### User Onboarding
```
1. HR create user account (email, username)
2. System generate temporary password
3. Send email with login link
4. User change password on first login
5. HR assign role & permissions
6. User access system
```

### Employee Offboarding (Resign)
```
1. HR mark worker as resigned
2. System set end_date
3. Worker account access revoked
4. Historical data preserved (audit trail)
5. No future approvals from this worker
```

### Backup & Recovery
```
✓ Daily database backups
✓ File backup for uploaded documents
✓ Recovery procedures documented
```

---

## 📞 Support & Maintenance

### Common Issues & Solutions
```
1. GPS not working
   → Check device GPS enabled
   → Check app has location permission

2. Cannot approve request
   → Check if user has permission
   → Check if request status is pending

3. Shift swap rejected
   → Check lead time (48/72 hours)
   → Check rest period (12 hours)
   → Check staff minimum (75%)

4. Leave not deducted
   → Check if approval status = approved
   → Check if leave hasn't been processed yet
```

---

## 🎓 Conclusion

**SIDIA** adalah sistem manajemen pegawai rumah sakit yang komprehensif dengan fokus pada:
- **Automasi**: Mengurangi pekerjaan manual
- **Validasi**: Business rules yang ketat
- **Transparency**: Audit trail lengkap
- **Efficiency**: Workflows yang efisien
- **Scalability**: Dapat diperluas dengan fitur baru

Sistem ini dirancang untuk meningkatkan operational excellence dalam manajemen sumber daya manusia di rumah sakit modern.

---

**Dokumentasi ini dirancang untuk memberikan pemahaman komprehensif tentang SIDIA kepada developer, analyst, atau stakeholder lain.**
