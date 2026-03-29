# Class Diagram SIMPEG (Sistem Informasi Kepegawaian)

Berikut adalah *Class Diagram* untuk sistem SIMPEG, yang menggambarkan entitas utama, atribut-atribut penting, serta relasi atau kardinalitas antar entitas dalam database (berdasarkan Model Eloquent Laravel yang ada di sistem ini).

```mermaid
classDiagram
    class User {
        +BigInt id
        +String name
        +String email
        +String password
        +DateTime email_verified_at
        +Boolean is_active
        +hasWorker() Worker
        +hasRoles() Collection
    }

    class Worker {
        +BigInt id
        +BigInt user_id
        +BigInt department_id
        +String nip
        +String first_name
        +String last_name
        +String gender
        +String religion
        +String position
        +Date join_date
        +user() User
        +department() Department
        +attendances() Collection
        +shifts() Collection
        +leaveRequests() Collection
        +businessTrips() Collection
    }

    class Department {
        +BigInt id
        +String name
        +String code
        +String description
        +workers() Collection
    }

    class Shift {
        +BigInt id
        +String name
        +Time start_time
        +Time end_time
        +Boolean is_active
        +workers() Collection
    }

    class WorkerShift {
        +BigInt id
        +BigInt worker_id
        +BigInt shift_id
        +Date effective_date
    }

    class Attendance {
        +BigInt id
        +BigInt worker_id
        +Date work_date
        +Time check_in
        +Time check_out
        +String status
        +String notes
        +worker() Worker
        +photos() Collection
    }

    class AttendancePhoto {
        +BigInt id
        +BigInt attendance_id
        +String photo_path
        +String type
        +attendance() Attendance
    }

    class LeaveRequest {
        +BigInt id
        +BigInt worker_id
        +BigInt leave_type_id
        +Date start_date
        +Date end_date
        +String reason
        +String status
        +worker() Worker
        +leaveType() LeaveType
    }

    class LeaveType {
        +BigInt id
        +String name
        +Int default_days
        +Boolean is_active
        +leaveRequests() Collection
    }

    class BusinessTrip {
        +BigInt id
        +BigInt worker_id
        +String destination
        +Date start_date
        +Date end_date
        +String status
        +String purpose
        +worker() Worker
    }

    class ShiftSwapRequest {
        +BigInt id
        +BigInt requester_id
        +BigInt responder_id
        +String status
        +String reason
        +requester() Worker
        +responder() Worker
        +auditLogs() Collection
    }
    
    class ShiftSwapAuditLog {
        +BigInt id
        +BigInt shift_swap_request_id
        +String action
        +String comment
        +shiftSwapRequest() ShiftSwapRequest
    }

    class WorkerDocument {
        +BigInt id
        +BigInt worker_id
        +BigInt document_type_id
        +String file_path
        +Date expiry_date
        +worker() Worker
        +documentType() DocumentType
    }

    class Holiday {
        +BigInt id
        +String name
        +Date holiday_date
        +String description
    }

    %% Relationships
    User "1" -- "0..1" Worker : user_id
    Department "1" -- "*" Worker : department_id
    Worker "1" -- "*" Attendance : records
    Attendance "1" -- "*" AttendancePhoto : has
    Worker "*" -- "*" Shift : belongsToMany (WorkerShift)
    Worker "1" -- "*" LeaveRequest : initiates
    LeaveRequest "*" -- "1" LeaveType : categorized as
    Worker "1" -- "*" BusinessTrip : performs
    Worker "1" -- "*" ShiftSwapRequest : requests / responds
    ShiftSwapRequest "1" -- "*" ShiftSwapAuditLog : logged in
    Worker "1" -- "*" WorkerDocument : owns
```

### Penjelasan Entitas & Kardinalitas (Relationship)
- **User & Worker (One-to-One / One-to-Zero)**: Satu akun `User` merepresentasikan maksimal satu data pegawai / `Worker`.
- **Department & Worker (One-to-Many)**: Satu Departemen bisa menaungi banyak pekerja.
- **Worker & Shift (Many-to-Many)**: Diwakili menggunakan tabel pivot / entitas **WorkerShift**.
- **Worker & Attendance (One-to-Many)**: Satu pekerja mencatatkan banyak riwayat kehadiran. Setiap entitas `Attendance` tersebut memiliki foto bukti presensi pada entitas `AttendancePhoto`.
- **Leave Management**: Pekerja mengajukan `LeaveRequest` yang berkategori pada `LeaveType` tertentu.
- **Shift Swap**: Seorang pekerja dapat mengajukan pertukaran jadwal (`ShiftSwapRequest`) dengan pekerja lainnya. Riwayat status pertukaran ini dicatat pada `ShiftSwapAuditLog`.

Note: *Class ini menggambarkan arsitektur core data dari SIMPEG berdasarkan model dan migrasi terbaru tanpa mengikutsertakan model yang sudah tidak digunakan (seperti `ShiftOverride`, `OvertimeRequest`, dan `SalaryComponent`).*
