# Class Diagram UML Murni (Domain SIMPEG)

Diagram ini fokus pada **entity domain + atribut inti + relasi kardinalitas**.
Tidak menampilkan Controller/Service atau operasi CRUD generik.

```mermaid
classDiagram
    direction LR

    class User {
      +id: uuid
      +name: string
      +email: string
      +is_active: bool
    }

    class Worker {
      +id: uuid
      +nip: string
      +name: string
      +email: string
      +phone_number: string
      +gender: enum
      +religion: enum
      +employment_status: enum
      +status: enum
      +hire_date: date
      +resign_date: date?
    }

    class Department {
      +id: uuid
      +name: string
      +code: string
      +requires_holiday_attendance: bool
    }

    class Shift {
      +id: uuid
      +name: string
      +start_time: time
      +end_time: time
      +is_overnight: bool
      +is_active: bool
    }

    class WorkerShift {
      +id: uuid
      +pattern_type: enum
      +effective_from: date
      +effective_until: date?
      +is_active: bool
    }

    class ShiftOverride {
      +id: uuid
      +override_date: date
      +reason: string?
      +created_by: uuid?
    }

    class ShiftSwapRequest {
      +id: uuid
      +swap_type: enum
      +swap_start_date: date?
      +swap_end_date: date?
      +swap_dates: json?
      +status: enum
      +requires_manager_approval: bool
      +requested_at: datetime
      +executed_at: datetime?
    }

    class ShiftSwapAuditLog {
      +id: bigint
      +action: string
      +old_status: string?
      +new_status: string?
      +notes: text?
      +created_at: datetime
    }

    class Attendance {
      +id: uuid
      +attendance_date: date
      +check_in: datetime?
      +check_out: datetime?
      +status: enum
      +notes: text?
    }

    class AttendancePhoto {
      +id: uuid
      +path: string
      +captured_at: datetime?
    }

    class LeaveType {
      +id: uuid
      +name: string
      +max_days: int?
      +is_paid: bool
    }

    class LeaveRequest {
      +id: uuid
      +start_date: date
      +end_date: date
      +status: enum
      +reason: text?
      +approved_at: datetime?
    }

    class BusinessTrip {
      +id: uuid
      +destination: string
      +start_date: date
      +end_date: date
      +status: enum
      +purpose: text?
    }

    class Holiday {
      +id: uuid
      +name: string
      +date: date
      +is_national: bool
    }

    class WorkerDocument {
      +id: uuid
      +file_name: string
      +file_path: string
      +status: enum
      +verified_at: datetime?
    }

    class Notification {
      +id: uuid
      +title: string
      +message: text
      +is_read: bool
      +created_at: datetime
    }

    User "1" -- "0..1" Worker : account
    Department "1" -- "*" Worker : members

    Worker "1" -- "*" WorkerShift : assignments
    WorkerShift "*" -- "1" Shift : uses

    Worker "1" -- "*" ShiftOverride : special schedule
    ShiftOverride "*" -- "1" Shift : override to

    Worker "1" -- "*" ShiftSwapRequest : requester
    Worker "1" -- "*" ShiftSwapRequest : target
    ShiftSwapRequest "*" -- "1" WorkerShift : requester_shift
    ShiftSwapRequest "*" -- "0..1" WorkerShift : target_shift
    ShiftSwapRequest "1" -- "*" ShiftSwapAuditLog : audit trail

    Worker "1" -- "*" Attendance : attendance records
    Attendance "1" -- "*" AttendancePhoto : evidence

    Worker "1" -- "*" LeaveRequest : requests
    LeaveRequest "*" -- "1" LeaveType : categorized as

    Worker "1" -- "*" BusinessTrip : assignments
    Worker "1" -- "*" WorkerDocument : documents
    Worker "1" -- "*" Notification : notifications
```

## Catatan
- Ini adalah UML class diagram domain murni.
- Controller, Service, Repository, dan method CRUD sengaja tidak dimasukkan.
- Cocok untuk komunikasi struktur data/relasi bisnis inti.
