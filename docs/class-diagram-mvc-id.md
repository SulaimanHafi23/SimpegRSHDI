# Class Diagram Versi MVC (SIMPEG)

Diagram ini disusun dengan pendekatan **controller-centric**:
semua alur fitur masuk lewat controller, lalu diteruskan ke service dan model.

Catatan istilah:
- `User` = akun login.
- `Worker` = data pegawai (profil SDM) yang terhubung ke akun.

```mermaid
classDiagram
    direction LR

    class BaseController

    class ShiftSwapController {
+ index()
+ store()
+ accept()
+ reject()
+ cancel()
    }

    class AttendanceController {
+ index()
+ checkIn()
+ checkOut()
+ show()
    }

    class LeaveController {
+ index()
+ store()
+ cancel()
+ show()
    }

    class BusinessTripController {
+ index()
+ store()
+ cancel()
+ show()
    }

    class CalendarController {
+ index()
+ events()
    }

    BaseController <|-- ShiftSwapController
    BaseController <|-- AttendanceController
    BaseController <|-- LeaveController
    BaseController <|-- BusinessTripController
    BaseController <|-- CalendarController

    class ShiftSwapService {
+ createRequest()
+ acceptRequest()
+ rejectRequest()
+ cancelRequest()
+ executeSwap()
+ revertSwap()
    }

    class AttendanceService {
+ checkIn()
+ checkOut()
+ getByWorker()
    }

    class LeaveRequestService {
+ getAll()
+ create()
+ approve()
+ reject()
    }

    class WorkerShiftService {
+ getAll()
+ getActiveByWorkerId()
+ getShiftHistories()
    }

    class ShiftOverrideService {
+ getAll()
+ create()
+ delete()
    }

    class User {
+ id
+ name
+ email
+ is_active
    }

    class Worker {
+ id
+ nip
+ name
+ department_id
+ user_id
    }

    class Department {
+ id
+ name
+ code
    }

    class Shift {
+ id
+ name
+ start_time
+ end_time
    }

    class WorkerShift {
+ id
+ worker_id
+ shift_id
+ effective_from
+ effective_until
    }

    class ShiftOverride
    class ShiftSwapRequest
    class ShiftSwapAuditLog
    class Attendance
    class AttendancePhoto
    class LeaveRequest
    class LeaveType
    class BusinessTrip
    class Holiday
    class WorkerDocument
    class Notification

    ShiftSwapController ..> ShiftSwapService : proses tukar shift
    ShiftSwapController ..> WorkerShiftService : validasi jadwal

    AttendanceController ..> AttendanceService : proses presensi
    AttendanceController ..> WorkerShiftService : referensi shift aktif

    LeaveController ..> LeaveRequestService : proses cuti
    BusinessTripController ..> BusinessTrip : kelola perjalanan dinas
    CalendarController ..> LeaveRequestService : leave events
    CalendarController ..> Holiday : holiday events
    CalendarController ..> BusinessTrip : trip events
    CalendarController ..> WorkerShiftService : shift events
    CalendarController ..> AttendanceService : attendance events

    ShiftSwapService ..> ShiftSwapRequest : persist
    ShiftSwapService ..> ShiftSwapAuditLog : audit
    ShiftSwapService ..> ShiftOverride : execute swap
    ShiftSwapService ..> Worker : validation
    ShiftSwapService ..> WorkerShift : schedule

    AttendanceService ..> Attendance : persist
    AttendanceService ..> AttendancePhoto : photo
    AttendanceService ..> Worker : actor

    LeaveRequestService ..> LeaveRequest : persist
    LeaveRequestService ..> LeaveType : reference

    WorkerShiftService ..> WorkerShift : read/write
    WorkerShiftService ..> Shift : reference
    ShiftOverrideService ..> ShiftOverride : read/write

    User "1" -- "0..1" Worker : akun pegawai
    Department "1" -- "*" Worker : anggota departemen
    Worker "1" -- "*" WorkerShift : jadwal kerja
    Worker "1" -- "*" ShiftOverride : overrides
    Worker "1" -- "*" Attendance : attendances
    Attendance "1" -- "*" AttendancePhoto : photos
    Worker "1" -- "*" LeaveRequest : leaves
    LeaveRequest "*" -- "1" LeaveType : type
    Worker "1" -- "*" BusinessTrip : trips
    Worker "1" -- "*" ShiftSwapRequest : sebagai pemohon
    Worker "1" -- "*" ShiftSwapRequest : sebagai penerima
    ShiftSwapRequest "1" -- "*" ShiftSwapAuditLog : logs
    Worker "1" -- "*" WorkerDocument : documents
```

## Catatan
- Struktur ini menempatkan **controller sebagai pintu masuk utama** untuk semua fitur.
- Jika diperlukan, diagram dapat dipecah lagi per controller (misalnya diagram khusus `ShiftSwapController`).
