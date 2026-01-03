# 📊 Activity Diagrams - SIMPEGRS RSUD Haji Darlan Ismail

**Project:** Sistem Informasi Manajemen Pegawai Rumah Sakit  
**Version:** 1.0.0  
**Last Updated:** January 3, 2026

---

## 📋 Table of Contents

1. [Authentication Flow](#1-authentication-flow)
2. [Check-In/Check-Out Flow](#2-check-incheck-out-flow)
3. [Leave Request Flow](#3-leave-request-flow)
4. [Overtime Request Flow](#4-overtime-request-flow)
5. [Shift Swap Flow](#5-shift-swap-flow)
6. [Document Upload & Verification Flow](#6-document-upload--verification-flow)
7. [Worker Import/Export Flow](#7-worker-importexport-flow)
8. [Approval Workflow](#8-approval-workflow)

---

## 1. Authentication Flow

```mermaid
flowchart TD
    Start([User Opens App]) --> LoginPage[Display Login Page]
    LoginPage --> InputCreds[User Enters NIP/Email & Password]
    InputCreds --> ValidateInput{Valid Input?}
    
    ValidateInput -->|No| ShowError1[Show Validation Error]
    ShowError1 --> LoginPage
    
    ValidateInput -->|Yes| CheckDB[Check Database]
    CheckDB --> UserExists{User Exists?}
    
    UserExists -->|No| ShowError2[Show 'Invalid Credentials']
    ShowError2 --> LoginPage
    
    UserExists -->|Yes| VerifyPass{Password Correct?}
    
    VerifyPass -->|No| ShowError2
    
    VerifyPass -->|Yes| GetRole[Get User Role]
    GetRole --> CheckRole{Check Role}
    
    CheckRole -->|Super Admin/HR/Manager| AdminDash[Redirect to Admin Dashboard]
    CheckRole -->|Employee| EmpDash[Redirect to Employee Dashboard]
    
    AdminDash --> EndSuccess([Success: Logged In])
    EmpDash --> EndSuccess
    
    style Start fill:#e1f5e1
    style EndSuccess fill:#e1f5e1
    style ShowError1 fill:#ffe1e1
    style ShowError2 fill:#ffe1e1
```

---

## 2. Check-In/Check-Out Flow

```mermaid
flowchart TD
    Start([Employee Opens Check-In Page]) --> CheckWorker{Has Worker Profile?}
    
    CheckWorker -->|No| Error1[Show Error: No Worker Profile]
    Error1 --> End1([End])
    
    CheckWorker -->|Yes| CheckAttendance{Already Checked In Today?}
    
    CheckAttendance -->|Yes - Not Checked Out| ShowCheckOut[Display Check-Out Form]
    CheckAttendance -->|Yes - Already Completed| Error2[Show: Already Checked In/Out]
    CheckAttendance -->|No| ShowCheckIn[Display Check-In Form]
    
    Error2 --> End1
    
    %% Check-In Process
    ShowCheckIn --> GetLocation1[Get GPS Location]
    GetLocation1 --> ValidateGPS1{Location Available?}
    
    ValidateGPS1 -->|No| ErrorGPS1[Show: Enable GPS]
    ErrorGPS1 --> ShowCheckIn
    
    ValidateGPS1 -->|Yes| SelectLocation1[User Selects Office Location]
    SelectLocation1 --> CalculateDistance1[Calculate Distance from Office]
    CalculateDistance1 --> CheckDistance1{Within Radius?}
    
    CheckDistance1 -->|No| MarkOutside1[Mark as Outside Radius]
    CheckDistance1 -->|Yes| MarkInside1[Mark as Inside Radius]
    
    MarkOutside1 --> TakePhoto1[Take Photo Optional]
    MarkInside1 --> TakePhoto1
    
    TakePhoto1 --> Submit1[Submit Check-In]
    Submit1 --> SaveAttendance1[Save Attendance Record]
    SaveAttendance1 --> CheckLate{Late?}
    
    CheckLate -->|Yes| MarkLate[Mark as Late + Calculate Minutes]
    CheckLate -->|No| MarkOnTime[Mark as On Time]
    
    MarkLate --> Success1[Show Success: Checked In]
    MarkOnTime --> Success1
    
    %% Check-Out Process
    ShowCheckOut --> GetLocation2[Get GPS Location]
    GetLocation2 --> ValidateGPS2{Location Available?}
    
    ValidateGPS2 -->|No| ErrorGPS2[Show: Enable GPS]
    ErrorGPS2 --> ShowCheckOut
    
    ValidateGPS2 -->|Yes| SelectLocation2[User Selects Office Location]
    SelectLocation2 --> CalculateDistance2[Calculate Distance from Office]
    CalculateDistance2 --> TakePhoto2[Take Photo Optional]
    
    TakePhoto2 --> Submit2[Submit Check-Out]
    Submit2 --> UpdateAttendance[Update Attendance Record]
    UpdateAttendance --> CheckEarlyLeave{Early Leave?}
    
    CheckEarlyLeave -->|Yes| MarkEarly[Mark Early Leave + Calculate Minutes]
    CheckEarlyLeave -->|No| MarkNormal[Mark Normal]
    
    MarkEarly --> CalcHours[Calculate Total Work Hours]
    MarkNormal --> CalcHours
    
    CalcHours --> Success2[Show Success: Checked Out]
    
    Success1 --> End2([End: Attendance Recorded])
    Success2 --> End2
    
    style Start fill:#e1f5e1
    style End1 fill:#ffe1e1
    style End2 fill:#e1f5e1
    style Error1 fill:#ffe1e1
    style Error2 fill:#ffe1e1
    style ErrorGPS1 fill:#ffe1e1
    style ErrorGPS2 fill:#ffe1e1
```

---

## 3. Leave Request Flow

```mermaid
flowchart TD
    Start([Employee Opens Leave Request Page]) --> ClickCreate[Click 'Create Leave Request']
    ClickCreate --> FillForm[Fill Leave Request Form]
    
    FillForm --> InputDetails[Enter: Leave Type, Start Date, End Date, Reason]
    InputDetails --> OptionalAttachment[Optional: Upload Supporting Document]
    OptionalAttachment --> ValidateForm{Form Valid?}
    
    ValidateForm -->|No| ShowValidationError[Show Validation Errors]
    ShowValidationError --> FillForm
    
    ValidateForm -->|Yes| SubmitRequest[Submit Leave Request]
    SubmitRequest --> SaveDB[Save to Database - Status: Pending]
    SaveDB --> NotifyManager[Send Notification to Manager/HR]
    NotifyManager --> ShowSuccess[Show Success Message]
    ShowSuccess --> EmployeeWaits[Employee Waits for Approval]
    
    %% Manager/HR Review Process
    EmployeeWaits --> ManagerChecks[Manager/HR Opens Approval Page]
    ManagerChecks --> ViewPending[View Pending Leave Requests]
    ViewPending --> SelectRequest[Select Leave Request]
    SelectRequest --> ViewDetails[View Request Details]
    
    ViewDetails --> ManagerDecision{Manager Decision}
    
    %% Approval Path
    ManagerDecision -->|Approve| EnterApprovalNotes[Enter Approval Notes Optional]
    EnterApprovalNotes --> ApproveRequest[Click Approve]
    ApproveRequest --> UpdateStatusApproved[Update Status: Approved]
    UpdateStatusApproved --> NotifyEmployeeApproved[Notify Employee: Approved]
    NotifyEmployeeApproved --> EndApproved([End: Leave Approved])
    
    %% Rejection Path
    ManagerDecision -->|Reject| EnterRejectionReason[Enter Rejection Reason Required]
    EnterRejectionReason --> RejectRequest[Click Reject]
    RejectRequest --> UpdateStatusRejected[Update Status: Rejected]
    UpdateStatusRejected --> NotifyEmployeeRejected[Notify Employee: Rejected]
    NotifyEmployeeRejected --> EndRejected([End: Leave Rejected])
    
    %% Cancellation Path
    EmployeeWaits -->|Before Approval| EmployeeCancel[Employee Cancels Request]
    EmployeeCancel --> UpdateStatusCancelled[Update Status: Cancelled]
    UpdateStatusCancelled --> EndCancelled([End: Leave Cancelled])
    
    style Start fill:#e1f5e1
    style EndApproved fill:#c8e6c9
    style EndRejected fill:#ffcdd2
    style EndCancelled fill:#fff9c4
    style ShowValidationError fill:#ffe1e1
```

---

## 4. Overtime Request Flow

```mermaid
flowchart TD
    Start([Employee Opens Overtime Page]) --> ClickCreate[Click 'Request Overtime']
    ClickCreate --> FillForm[Fill Overtime Request Form]
    
    FillForm --> InputDetails[Enter: Date, Start Time, End Time, Reason]
    InputDetails --> CalcHours[System Calculates Total Hours]
    CalcHours --> ValidateForm{Form Valid?}
    
    ValidateForm -->|No| ShowErrors[Show Validation Errors]
    ShowErrors --> FillForm
    
    ValidateForm -->|Yes| ValidateTime{Time Range Valid?}
    
    ValidateTime -->|No| ShowTimeError[Show: Invalid Time Range]
    ShowTimeError --> FillForm
    
    ValidateTime -->|Yes| SubmitRequest[Submit Overtime Request]
    SubmitRequest --> SaveDB[Save to Database - Status: Pending]
    SaveDB --> NotifyManager[Notify Manager/HR]
    NotifyManager --> ShowSuccess[Show Success Message]
    ShowSuccess --> EmployeeWaits[Employee Waits for Approval]
    
    %% Manager/HR Review
    EmployeeWaits --> ManagerOpens[Manager/HR Opens Overtime Approvals]
    ManagerOpens --> ViewPending[View Pending Requests]
    ViewPending --> SelectRequest[Select Overtime Request]
    SelectRequest --> ViewDetails[View Request Details]
    ViewDetails --> CheckDetails[Check: Date, Hours, Reason]
    
    CheckDetails --> ManagerDecision{Decision}
    
    %% Approval Path
    ManagerDecision -->|Approve| EnterNotes[Enter Approval Notes Optional]
    EnterNotes --> ClickApprove[Click Approve]
    ClickApprove --> UpdateApproved[Update Status: Approved]
    UpdateApproved --> RecordOvertimeHours[Record Overtime Hours to Payroll]
    RecordOvertimeHours --> NotifyEmpApproved[Notify Employee: Approved]
    NotifyEmpApproved --> EndApproved([End: Overtime Approved])
    
    %% Rejection Path
    ManagerDecision -->|Reject| EnterReason[Enter Rejection Reason Required]
    EnterReason --> ClickReject[Click Reject]
    ClickReject --> UpdateRejected[Update Status: Rejected]
    UpdateRejected --> NotifyEmpRejected[Notify Employee: Rejected]
    NotifyEmpRejected --> EndRejected([End: Overtime Rejected])
    
    %% Cancellation
    EmployeeWaits -->|Before Approval| EmpCancel[Employee Cancels]
    EmpCancel --> UpdateCancelled[Update Status: Cancelled]
    UpdateCancelled --> EndCancelled([End: Overtime Cancelled])
    
    style Start fill:#e1f5e1
    style EndApproved fill:#c8e6c9
    style EndRejected fill:#ffcdd2
    style EndCancelled fill:#fff9c4
    style ShowErrors fill:#ffe1e1
    style ShowTimeError fill:#ffe1e1
```

---

## 5. Shift Swap Flow

```mermaid
flowchart TD
    Start([Employee Opens Shift Swap Page]) --> ClickCreate[Click 'Request Shift Swap']
    ClickCreate --> SelectOwnShift[Select Own Shift to Swap]
    SelectOwnShift --> GetFutureShifts[System Shows Future Shifts]
    GetFutureShifts --> ChooseShift[Employee Chooses Shift]
    
    ChooseShift --> SwapType{Swap Type}
    
    %% Open Swap (No Target)
    SwapType -->|Open Swap| EnterReasonOpen[Enter Reason]
    EnterReasonOpen --> SubmitOpen[Submit Open Swap Request]
    SubmitOpen --> SaveOpen[Save: Status = Pending, No Target]
    SaveOpen --> NotifyDept[Notify All Department Workers]
    NotifyDept --> WaitResponse[Wait for Workers to Respond]
    
    %% Direct Swap (With Target)
    SwapType -->|Direct Swap| SelectTarget[Select Target Worker]
    SelectTarget --> GetTargetShifts[Load Target's Future Shifts]
    GetTargetShifts --> SelectTargetShift[Select Target Shift]
    SelectTargetShift --> EnterReasonDirect[Enter Reason]
    EnterReasonDirect --> SubmitDirect[Submit Direct Swap Request]
    SubmitDirect --> SaveDirect[Save: Status = Pending]
    SaveDirect --> NotifyTarget[Notify Target Worker]
    NotifyTarget --> TargetWaits[Target Worker Reviews]
    
    %% Target Worker Response
    TargetWaits --> TargetDecision{Target Decision}
    
    TargetDecision -->|Accept| TargetAccepts[Target Accepts]
    TargetDecision -->|Reject| TargetRejects[Target Rejects]
    
    TargetRejects --> UpdateRejected[Update Status: Rejected]
    UpdateRejected --> NotifyRequesterRej[Notify Requester]
    NotifyRequesterRej --> EndRejected([End: Swap Rejected])
    
    %% Worker Accepts Open Swap
    WaitResponse --> WorkerInterested[Worker Shows Interest]
    WorkerInterested --> WorkerAccepts[Worker Accepts Swap]
    WorkerAccepts --> UpdateTarget[Link Target Worker]
    UpdateTarget --> TargetAccepts
    
    %% Manager Approval
    TargetAccepts --> UpdateAccepted[Update Status: Accepted]
    UpdateAccepted --> NotifyManager[Notify Manager]
    NotifyManager --> ManagerReview[Manager Reviews Swap]
    
    ManagerReview --> CheckRules[Check: Lead Time, Rest Period, Min Staffing]
    CheckRules --> RulesValid{Rules Satisfied?}
    
    RulesValid -->|No| ShowWarning[Show Warning to Manager]
    ShowWarning --> ManagerDecision{Manager Decision}
    RulesValid -->|Yes| ManagerDecision
    
    %% Manager Approval
    ManagerDecision -->|Approve| ManagerApproves[Manager Approves]
    ManagerApproves --> UpdateApproved[Update Status: Approved]
    UpdateApproved --> SwapShifts[System Swaps WorkerShift Records]
    SwapShifts --> CreateAuditLog[Create Audit Log]
    CreateAuditLog --> UpdateCompleted[Update Status: Completed]
    UpdateCompleted --> NotifyBoth[Notify Both Workers]
    NotifyBoth --> EndSuccess([End: Swap Completed])
    
    %% Manager Rejection
    ManagerDecision -->|Reject| ManagerRejects[Manager Rejects]
    ManagerRejects --> EnterManagerReason[Enter Rejection Reason]
    EnterManagerReason --> UpdateManagerRej[Update Status: Rejected]
    UpdateManagerRej --> NotifyBothRej[Notify Both Workers]
    NotifyBothRej --> EndManagerRej([End: Swap Rejected by Manager])
    
    %% Cancellation
    WaitResponse -->|Before Accept| RequesterCancel[Requester Cancels]
    TargetWaits -->|Before Decision| RequesterCancel
    RequesterCancel --> UpdateCancelled[Update Status: Cancelled]
    UpdateCancelled --> EndCancelled([End: Swap Cancelled])
    
    style Start fill:#e1f5e1
    style EndSuccess fill:#c8e6c9
    style EndRejected fill:#ffcdd2
    style EndManagerRej fill:#ffcdd2
    style EndCancelled fill:#fff9c4
```

---

## 6. Document Upload & Verification Flow

```mermaid
flowchart TD
    Start([Employee Opens Documents Page]) --> ClickUpload[Click 'Upload Document']
    ClickUpload --> FillForm[Fill Upload Form]
    
    FillForm --> SelectType[Select Document Type]
    SelectType --> EnterNumber[Enter Document Number]
    EnterNumber --> SelectFile[Select File PDF/Image]
    SelectFile --> AddNotes[Add Notes Optional]
    AddNotes --> ValidateForm{Form Valid?}
    
    ValidateForm -->|No| ShowErrors[Show Validation Errors]
    ShowErrors --> FillForm
    
    ValidateForm -->|Yes| CheckFileSize{File Size OK?}
    
    CheckFileSize -->|> 5MB| ShowSizeError[Show: File Too Large]
    ShowSizeError --> SelectFile
    
    CheckFileSize -->|<= 5MB| CheckFileType{File Type Valid?}
    
    CheckFileType -->|No| ShowTypeError[Show: Invalid File Type]
    ShowTypeError --> SelectFile
    
    CheckFileType -->|Yes| UploadFile[Upload File to Storage]
    UploadFile --> SaveDB[Save Record - Status: Pending]
    SaveDB --> NotifyHR[Notify HR for Verification]
    NotifyHR --> ShowSuccess[Show Success Message]
    ShowSuccess --> EmployeeWaits[Employee Waits for Verification]
    
    %% HR Verification
    EmployeeWaits --> HROpens[HR Opens Document Approvals]
    HROpens --> ViewPending[View Pending Documents]
    ViewPending --> SelectDoc[Select Document]
    SelectDoc --> ViewDetails[View Document Details]
    ViewDetails --> DownloadFile[Download/View File]
    DownloadFile --> ReviewDoc[Review Document Content]
    
    ReviewDoc --> HRDecision{HR Decision}
    
    %% Verification Path
    HRDecision -->|Verify| EnterVerifyNotes[Enter Verification Notes Optional]
    EnterVerifyNotes --> ClickVerify[Click Verify]
    ClickVerify --> UpdateVerified[Update Status: Verified]
    UpdateVerified --> RecordVerification[Record Verified By & Date]
    RecordVerification --> NotifyEmpVerified[Notify Employee: Verified]
    NotifyEmpVerified --> EndVerified([End: Document Verified])
    
    %% Rejection Path
    HRDecision -->|Reject| EnterReason[Enter Rejection Reason Required]
    EnterReason --> ClickReject[Click Reject]
    ClickReject --> UpdateRejected[Update Status: Rejected]
    UpdateRejected --> NotifyEmpRejected[Notify Employee: Rejected]
    NotifyEmpRejected --> EmpCanReupload[Employee Can Re-upload]
    EmpCanReupload --> EndRejected([End: Document Rejected])
    
    %% Employee Delete
    EmployeeWaits -->|Before Verification| EmpDelete[Employee Deletes Document]
    EmpDelete --> SoftDelete[Soft Delete Record]
    SoftDelete --> EndDeleted([End: Document Deleted])
    
    style Start fill:#e1f5e1
    style EndVerified fill:#c8e6c9
    style EndRejected fill:#ffcdd2
    style EndDeleted fill:#fff9c4
    style ShowErrors fill:#ffe1e1
    style ShowSizeError fill:#ffe1e1
    style ShowTypeError fill:#ffe1e1
```

---

## 7. Worker Import/Export Flow

```mermaid
flowchart TD
    Start([Admin Opens Worker Management]) --> ActionChoice{Action}
    
    %% Export Path
    ActionChoice -->|Export| ClickExport[Click 'Export to Excel']
    ClickExport --> SelectFilters[Select Filters Optional]
    SelectFilters --> ApplyFilters[Apply: Status, Department, Employment]
    ApplyFilters --> GenerateExcel[Generate Excel File]
    GenerateExcel --> DownloadExcel[Download Excel File]
    DownloadExcel --> EndExport([End: Workers Exported])
    
    %% Import Path
    ActionChoice -->|Import| ClickImport[Click 'Import from Excel']
    ClickImport --> DownloadTemplate[Optional: Download Template]
    DownloadTemplate --> PrepareFile[Prepare Excel File]
    PrepareFile --> SelectFile[Select Excel File]
    SelectFile --> ValidateFile{File Valid?}
    
    ValidateFile -->|No - Wrong Format| ShowFormatError[Show: Invalid File Format]
    ValidateFile -->|No - Too Large| ShowSizeError[Show: File Too Large]
    ShowFormatError --> SelectFile
    ShowSizeError --> SelectFile
    
    ValidateFile -->|Yes| UploadFile[Upload File]
    UploadFile --> ParseFile[Parse Excel Rows]
    ParseFile --> ProcessRows[Process Each Row]
    
    ProcessRows --> ValidateRow{Row Valid?}
    
    ValidateRow -->|No| CollectError[Collect Error Message]
    CollectError --> NextRow{More Rows?}
    
    ValidateRow -->|Yes| CheckDuplicate{NIP Exists?}
    
    CheckDuplicate -->|Yes| CollectError
    
    CheckDuplicate -->|No| FindReferences[Find Department, Position, etc.]
    FindReferences --> CreateUser[Create User Account]
    CreateUser --> AssignRole[Assign Employee Role]
    AssignRole --> CreateWorker[Create Worker Record]
    CreateWorker --> IncrementSuccess[Increment Success Count]
    IncrementSuccess --> NextRow
    
    NextRow -->|Yes| ProcessRows
    NextRow -->|No| GenerateReport[Generate Import Report]
    
    GenerateReport --> HasErrors{Has Errors?}
    
    HasErrors -->|No| ShowSuccessAll[Show: All Imported Successfully]
    ShowSuccessAll --> EndImportSuccess([End: Import Completed])
    
    HasErrors -->|Yes - Partial| ShowPartialSuccess[Show: Partial Success + Errors]
    HasErrors -->|Yes - All Failed| ShowAllErrors[Show: All Failed + Errors]
    
    ShowPartialSuccess --> EndImportPartial([End: Import Partial])
    ShowAllErrors --> EndImportFailed([End: Import Failed])
    
    style Start fill:#e1f5e1
    style EndExport fill:#c8e6c9
    style EndImportSuccess fill:#c8e6c9
    style EndImportPartial fill:#fff9c4
    style EndImportFailed fill:#ffcdd2
    style ShowFormatError fill:#ffe1e1
    style ShowSizeError fill:#ffe1e1
```

---

## 8. Approval Workflow (Generic)

```mermaid
flowchart TD
    Start([Employee Submits Request]) --> SaveDB[Save to Database]
    SaveDB --> SetStatusPending[Set Status: Pending]
    SetStatusPending --> CreateNotif[Create Notification]
    CreateNotif --> DetermineApprover{Determine Approver}
    
    DetermineApprover -->|Leave/Overtime| CheckRole1{User Role}
    DetermineApprover -->|Document| NotifyHR[Notify HR]
    DetermineApprover -->|Shift Swap - Stage 1| NotifyTarget[Notify Target Worker]
    DetermineApprover -->|Shift Swap - Stage 2| NotifyManager[Notify Manager]
    
    CheckRole1 -->|Employee| NotifyManager
    CheckRole1 -->|Manager| NotifyManager
    
    %% Approver Reviews
    NotifyHR --> ApproverOpens[Approver Opens Request]
    NotifyTarget --> ApproverOpens
    NotifyManager --> ApproverOpens
    
    ApproverOpens --> ViewList[View Pending List]
    ViewList --> FilterByDept{Filter by Department?}
    
    FilterByDept -->|Yes - Manager| ShowDeptOnly[Show Only Department Requests]
    FilterByDept -->|No - HR/Admin| ShowAll[Show All Requests]
    
    ShowDeptOnly --> SelectRequest[Select Request]
    ShowAll --> SelectRequest
    
    SelectRequest --> ViewDetails[View Full Details]
    ViewDetails --> ReviewInfo[Review Information]
    ReviewInfo --> CheckPolicy[Check Company Policy]
    CheckPolicy --> ApproverDecision{Decision}
    
    %% Approval Path
    ApproverDecision -->|Approve| EnterNotes[Enter Notes Optional]
    EnterNotes --> ConfirmApprove[Confirm Approval]
    ConfirmApprove --> UpdateApproved[Update Status: Approved]
    UpdateApproved --> RecordApprover[Record Approver & Timestamp]
    RecordApprover --> TriggerAction[Trigger Post-Approval Action]
    
    TriggerAction --> ActionType{Action Type}
    ActionType -->|Leave| BlockCalendar[Block Calendar Dates]
    ActionType -->|Overtime| RecordHours[Record to Payroll]
    ActionType -->|Document| MarkVerified[Mark as Verified]
    ActionType -->|Shift Swap| SwapShifts[Execute Shift Swap]
    
    BlockCalendar --> NotifyEmpApproved[Notify Employee: Approved]
    RecordHours --> NotifyEmpApproved
    MarkVerified --> NotifyEmpApproved
    SwapShifts --> NotifyEmpApproved
    
    NotifyEmpApproved --> SendEmail[Optional: Send Email]
    SendEmail --> EndApproved([End: Request Approved])
    
    %% Rejection Path
    ApproverDecision -->|Reject| EnterReason[Enter Rejection Reason]
    EnterReason --> ReasonRequired{Reason Provided?}
    
    ReasonRequired -->|No| ShowReasonError[Show: Reason Required]
    ShowReasonError --> EnterReason
    
    ReasonRequired -->|Yes| ConfirmReject[Confirm Rejection]
    ConfirmReject --> UpdateRejected[Update Status: Rejected]
    UpdateRejected --> RecordRejector[Record Rejector & Timestamp]
    RecordRejector --> NotifyEmpRejected[Notify Employee: Rejected]
    NotifyEmpRejected --> EndRejected([End: Request Rejected])
    
    %% Cancellation by Employee
    ViewList -->|Before Review| EmpCancel[Employee Cancels]
    EmpCancel --> CheckCancelable{Cancelable?}
    
    CheckCancelable -->|Yes - Pending| ConfirmCancel[Confirm Cancellation]
    CheckCancelable -->|No - Already Processed| ShowCancelError[Show: Cannot Cancel]
    
    ShowCancelError --> EndError([End: Error])
    
    ConfirmCancel --> UpdateCancelled[Update Status: Cancelled]
    UpdateCancelled --> EndCancelled([End: Request Cancelled])
    
    style Start fill:#e1f5e1
    style EndApproved fill:#c8e6c9
    style EndRejected fill:#ffcdd2
    style EndCancelled fill:#fff9c4
    style EndError fill:#ffe1e1
    style ShowReasonError fill:#ffe1e1
    style ShowCancelError fill:#ffe1e1
```

---

## 📊 Key Decision Points Summary

### Attendance System
- **GPS Validation:** Check if location is within office radius
- **Late Check:** Compare check-in time with shift start + grace period
- **Early Leave Check:** Compare check-out time with shift end time

### Leave Request
- **Validation:** Check leave balance, date conflicts, policy compliance
- **Approval Level:** Manager for department, HR for cross-department

### Overtime Request
- **Validation:** Check time range, max hours per day/month
- **Approval Level:** Manager approval required, HR can override

### Shift Swap
- **Validation:** Lead time (48-72h), rest period (12h), min staffing
- **Multi-stage:** Target acceptance → Manager approval
- **Rules Engine:** Automatic validation of business rules

### Document Verification
- **Validation:** File type, size, required fields
- **HR Only:** Only HR can verify/reject documents

---

## 🔄 State Transitions

### Common States
```
pending → approved → completed
pending → rejected
pending → cancelled (by employee)
approved → cancelled (rare, requires admin)
```

### Shift Swap States
```
pending → accepted (by target) → approved (by manager) → completed
pending → rejected (by target/manager)
pending → cancelled (by requester)
```

---

**Document Version:** 1.0.0  
**Last Updated:** January 3, 2026  
**Maintained By:** Development Team
