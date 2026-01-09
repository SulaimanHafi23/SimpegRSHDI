# 🧪 MANUAL BROWSER TESTING CHECKLIST

**Tester:** _________________  
**Date:** _________________  
**Browser:** Chrome/Firefox/Safari/Edge  
**Screen Resolution:** _________________  

---

## 🔑 **1. AUTHENTICATION & AUTHORIZATION**

### Login Testing
- [ ] Akses `/login` tampil form login
- [ ] Login dengan Super Admin (superadmin@rshdi.com / password) → berhasil
- [ ] Login dengan HR user → berhasil
- [ ] Login dengan Manager user → berhasil  
- [ ] Login dengan Employee user → berhasil
- [ ] Login dengan credentials salah → error message muncul
- [ ] Logout berhasil redirect ke login

### Role-Based Access
- [ ] Super Admin dapat akses semua menu
- [ ] HR tidak dapat akses menu yang tidak diizinkan
- [ ] Manager hanya dapat akses approval & view
- [ ] Employee hanya dapat akses self-service menu

---

## 📊 **2. DASHBOARD**

### Admin Dashboard (/)
- [ ] Dashboard tampil tanpa error
- [ ] Widget statistik menampilkan data benar
- [ ] Chart/graph loading dengan benar
- [ ] Navigasi sidebar berfungsi

### HR Dashboard (/hr/dashboard)
- [ ] Dashboard HR tampil
- [ ] Data HR-specific muncul

### Manager Dashboard (/manager/dashboard)
- [ ] Dashboard Manager tampil
- [ ] Pending approvals muncul

---

## 👥 **3. WORKER MANAGEMENT** ✅ CRITICAL

### List Workers (/workers)
- [ ] Halaman list tampil tanpa 403 error ✅ FIXED
- [ ] Data workers tampil di table
- [ ] Pagination berfungsi
- [ ] Search/filter berfungsi
- [ ] Export button tampil & berfungsi
- [ ] Import button tampil & berfungsi

### Create Worker (/workers/create)
- [ ] Form create tampil
- [ ] Dropdown religion, gender, department terisi
- [ ] Required field validation berfungsi
- [ ] NIP unique validation berfungsi
- [ ] Email unique validation berfungsi
- [ ] Phone unique validation berfungsi
- [ ] Submit berhasil → redirect ke list
- [ ] Success message muncul

### View Worker (/workers/{id})
- [ ] Detail worker tampil lengkap
- [ ] Foto worker tampil (jika ada)
- [ ] Tab attendance history berfungsi
- [ ] Tab documents berfungsi

### Edit Worker (/workers/{id}/edit)
- [ ] Form edit terisi data existing
- [ ] Update tanpa ubah NIP → berhasil (tidak trigger unique error)
- [ ] Update tanpa ubah email → berhasil
- [ ] Update tanpa ubah phone → berhasil
- [ ] Update data → berhasil
- [ ] Success message muncul

### Delete Worker
- [ ] Confirm dialog muncul
- [ ] Delete berhasil
- [ ] Worker terhapus dari list

### Resign Worker
- [ ] Resign button tampil
- [ ] Confirm dialog muncul
- [ ] Resign berhasil
- [ ] Status berubah jadi "Resign"

### Import/Export
- [ ] Download template berhasil
- [ ] Import Excel berhasil
- [ ] Validation error saat import data invalid
- [ ] Export to Excel berhasil download
- [ ] Export data sesuai dengan filter

---

## 📅 **4. ATTENDANCE MANAGEMENT**

### List Attendance (/attendance)
- [ ] List attendance tampil
- [ ] Filter by date berfungsi
- [ ] Filter by worker berfungsi
- [ ] Export attendance berfungsi

### Manual Attendance Entry (/attendance/create)
- [ ] Form create tampil
- [ ] Dropdown worker terisi
- [ ] Date picker berfungsi
- [ ] Time picker berfungsi
- [ ] Submit berhasil

### Check-In
- [ ] Check-in button tampil
- [ ] Geolocation request muncul (jika enabled)
- [ ] Foto check-in upload (jika enabled)
- [ ] Check-in berhasil
- [ ] Check-in time tersimpan

### Check-Out
- [ ] Check-out button tampil setelah check-in
- [ ] Foto check-out upload (jika enabled)
- [ ] Check-out berhasil
- [ ] Working hours terhitung

### Reports
- [ ] Daily report tampil
- [ ] Monthly report tampil
- [ ] Export report berhasil

---

## 🗓️ **5. SCHEDULE MANAGEMENT (WORKER SHIFTS)**

### List Schedules (/worker-shifts)
- [ ] List schedule tampil
- [ ] Calendar view berfungsi (jika ada)
- [ ] Filter by worker berfungsi

### Create Schedule (/worker-shifts/create)
- [ ] Form create tampil
- [ ] Dropdown worker terisi
- [ ] Dropdown shift terisi
- [ ] Date range picker berfungsi
- [ ] Submit berhasil

### Edit Schedule (/worker-shifts/{id}/edit)
- [ ] Form edit terisi data existing
- [ ] Update berhasil

### Override Shift
- [ ] Override form tampil
- [ ] Override berhasil
- [ ] Schedule asli tetap ada

---

## 📄 **6. WORKER DOCUMENTS**

### List Documents (/worker-documents)
- [ ] List documents tampil
- [ ] Filter by worker berfungsi
- [ ] Filter by document type berfungsi
- [ ] Filter by status berfungsi

### Upload Document (/worker-documents/create)
- [ ] Form upload tampil
- [ ] Dropdown worker terisi
- [ ] Dropdown document type terisi
- [ ] File upload berfungsi
- [ ] File size validation (max 5MB)
- [ ] File type validation (PDF/JPG/PNG)
- [ ] Submit berhasil

### View Document (/worker-documents/{id})
- [ ] Document detail tampil
- [ ] Preview document (jika PDF/image)
- [ ] Download button berfungsi

### Verify Document
- [ ] Verify button tampil (untuk approver)
- [ ] Verify berhasil
- [ ] Status berubah jadi "Verified"

### Reject Document
- [ ] Reject button tampil
- [ ] Reject reason form tampil
- [ ] Reject berhasil
- [ ] Status berubah jadi "Rejected"

---

## 💰 **7. PAYROLL MANAGEMENT**

### List Payroll (/payroll)
- [ ] Halaman payroll tampil
- [ ] Sidebar dropdown management tetap terbuka ✅ FIXED
- [ ] Filter by period berfungsi
- [ ] Filter by worker berfungsi

### Generate Payroll (/payroll/generate)
- [ ] Form generate tampil
- [ ] Select period berfungsi
- [ ] Select workers berfungsi
- [ ] Generate berhasil
- [ ] Payroll data tersimpan

### View Payroll Detail (/payroll/{id})
- [ ] Detail payroll tampil lengkap
- [ ] Gaji pokok tampil
- [ ] Tunjangan tampil
- [ ] Potongan tampil
- [ ] Total gaji bersih dihitung benar

### Edit Payroll (/payroll/{id}/edit)
- [ ] Form edit tampil
- [ ] Update component gaji berhasil
- [ ] Re-calculate total berhasil

### Export Payroll
- [ ] Export to Excel berhasil
- [ ] Export to PDF berhasil (jika ada)

---

## ✅ **8. APPROVAL SYSTEM**

### Leave Approval (/approvals/leaves)
- [ ] List leave requests tampil
- [ ] Filter by status berfungsi
- [ ] Pending requests highlighted

### Approve Leave
- [ ] Approve button tampil
- [ ] Confirm dialog muncul
- [ ] Approve berhasil
- [ ] Status berubah jadi "Approved"
- [ ] Notification terkirim (jika ada)

### Reject Leave
- [ ] Reject button tampil
- [ ] Rejection reason form tampil
- [ ] Reject berhasil
- [ ] Status berubah jadi "Rejected"

### Overtime Approval (/approvals/overtimes)
- [ ] List overtime requests tampil
- [ ] Approve/Reject berfungsi

### Shift Swap Approval (/manager/shift-swap-approvals)
- [ ] List swap requests tampil
- [ ] Approve swap berhasil
- [ ] Execute swap berhasil
- [ ] Schedule bertukar

### Business Trip Approval (/approvals/business-trips)
- [ ] List trip requests tampil
- [ ] Approve/Reject berfungsi

### Document Verification (/approvals/documents)
- [ ] List documents pending tampil
- [ ] Verify/Reject berfungsi

---

## 📊 **9. REPORTS**

### Attendance Report (/reports/attendance)
- [ ] Report form tampil
- [ ] Date range picker berfungsi
- [ ] Filter by worker berfungsi
- [ ] Generate report berhasil
- [ ] Data tampil benar
- [ ] Export berhasil

### Leave Report (/reports/leaves)
- [ ] Report form tampil
- [ ] Filter berfungsi
- [ ] Generate report berhasil
- [ ] Export berhasil

### Overtime Report (/reports/overtimes)
- [ ] Report form tampil
- [ ] Generate report berhasil
- [ ] Export berhasil

### Document Report (/reports/worker-documents)
- [ ] Report form tampil
- [ ] Generate report berhasil
- [ ] Export berhasil

---

## ⚙️ **10. SETTINGS**

### Role Management (/roles)
- [ ] List roles tampil
- [ ] Create role button tampil

### Create Role
- [ ] Form create tampil
- [ ] Name field berfungsi
- [ ] Permission checkboxes tampil
- [ ] Permission grouped by module
- [ ] Select all module berfungsi
- [ ] Submit berhasil

### Edit Role (/roles/{id}/edit) ✅ CRITICAL
- [ ] Form edit tampil tanpa 403 error ✅ FIXED
- [ ] Data role terisi
- [ ] Assigned permissions ter-check
- [ ] Update tanpa ubah name → berhasil ✅ FIXED (tidak trigger duplicate error)
- [ ] Update permissions → berhasil
- [ ] Success message muncul

### Delete Role
- [ ] Delete button tampil
- [ ] Confirm dialog muncul
- [ ] Cannot delete if role has users
- [ ] Delete berhasil jika tidak ada users

### User Management (/users)
- [ ] List users tampil
- [ ] Status active/inactive tampil

### Create User (/users/create)
- [ ] Form create tampil
- [ ] Dropdown worker terisi
- [ ] Email validation berfungsi
- [ ] Username unique validation
- [ ] Password confirmation berfungsi
- [ ] Role assignment berfungsi
- [ ] Submit berhasil

### Edit User (/users/{id}/edit)
- [ ] Form edit terisi data existing
- [ ] Update tanpa ubah email → berhasil
- [ ] Update tanpa ubah username → berhasil
- [ ] Update tanpa password → berhasil
- [ ] Update with new password → berhasil
- [ ] Change roles → berhasil

### Activate/Deactivate User
- [ ] Toggle status berhasil
- [ ] Inactive user tidak bisa login

### Holiday Management (/holidays)
- [ ] List holidays tampil
- [ ] Calendar view tampil (jika ada)

### Create Holiday (/holidays/create)
- [ ] Form create tampil
- [ ] Name field berfungsi
- [ ] Date picker berfungsi
- [ ] Type selection berfungsi
- [ ] Submit berhasil

### Auto-Generate Holidays
- [ ] Auto-generate form tampil
- [ ] Select year berfungsi
- [ ] Generate berhasil
- [ ] National holidays terinput

### Bulk Create Holidays
- [ ] Bulk form tampil
- [ ] Multiple dates input berfungsi
- [ ] Submit berhasil

### Edit Holiday (/holidays/{id}/edit)
- [ ] Form edit terisi
- [ ] Update berhasil

### Delete Holiday
- [ ] Delete berhasil

---

## 📱 **11. MASTER DATA**

### Religion (/master/religions)
- [ ] List tampil
- [ ] Create/Edit/Delete berfungsi
- [ ] Validation berfungsi

### Gender (/master/genders)
- [ ] List tampil
- [ ] Create/Edit/Delete berfungsi

### Department (/master/departments)
- [ ] List tampil
- [ ] Create with parent department berfungsi
- [ ] Tree structure tampil benar
- [ ] Edit/Delete berfungsi

### Location (/master/locations)
- [ ] List tampil
- [ ] Create dengan lat/lng berfungsi
- [ ] Map integration (jika ada)
- [ ] Edit/Delete berfungsi

### Shift (/master/shifts)
- [ ] List tampil
- [ ] Create shift dengan time berfungsi
- [ ] Night shift handling benar
- [ ] Edit/Delete berfungsi

### Leave Type (/master/leave-types)
- [ ] List tampil
- [ ] Create dengan quota berfungsi
- [ ] Edit/Delete berfungsi

### Document Type (/master/document-types)
- [ ] List tampil
- [ ] Create dengan requirement berfungsi
- [ ] Edit/Delete berfungsi

---

## 🎨 **12. UI/UX TESTING**

### Sidebar Navigation
- [ ] Sidebar collapse/expand berfungsi
- [ ] Active menu highlighted
- [ ] Dropdown menus berfungsi
- [ ] Management dropdown tetap open di payroll ✅ FIXED
- [ ] Icons tampil benar

### Responsive Design
- [ ] Desktop view (1920x1080) → tampil benar
- [ ] Laptop view (1366x768) → tampil benar
- [ ] Tablet view (768x1024) → tampil benar
- [ ] Mobile view (375x667) → tampil benar
- [ ] Sidebar responsive di mobile

### Forms
- [ ] Input fields styling consistent
- [ ] Validation messages tampil merah
- [ ] Success messages tampil hijau
- [ ] Required fields marked dengan *
- [ ] Date pickers berfungsi semua browser
- [ ] Dropdowns searchable (jika ada select2)

### Tables
- [ ] Sorting columns berfungsi
- [ ] Pagination berfungsi
- [ ] Search/filter berfungsi
- [ ] Action buttons tampil benar
- [ ] Responsive table di mobile

### Modals
- [ ] Modal open/close smooth
- [ ] Modal backdrop berfungsi
- [ ] Multiple modals handling (jika ada)
- [ ] Confirm dialogs berfungsi

### Notifications
- [ ] Success notification muncul & auto-hide
- [ ] Error notification muncul & auto-hide
- [ ] Toast position benar (top-right recommended)

---

## 🔒 **13. SECURITY TESTING**

### Permission Boundaries
- [ ] HR tidak bisa akses manager-only pages
- [ ] Manager tidak bisa akses HR-only pages
- [ ] Employee tidak bisa akses admin pages
- [ ] Direct URL access ke forbidden page → 403 error

### CSRF Protection
- [ ] Form submission tanpa CSRF token → error
- [ ] CSRF token auto-generated di form

### XSS Prevention
- [ ] Input dengan `<script>alert('XSS')</script>` → escaped
- [ ] Output di page tidak execute script

### SQL Injection Prevention
- [ ] Input dengan `' OR '1'='1` → no SQL error
- [ ] Prepared statements digunakan

---

## ⚡ **14. PERFORMANCE TESTING**

### Page Load Time
- [ ] Dashboard load < 3 seconds
- [ ] List pages load < 2 seconds
- [ ] Form pages load < 1 second

### Data Handling
- [ ] List dengan 100+ records → pagination berfungsi
- [ ] Export 1000+ records → berhasil tanpa timeout
- [ ] Search/filter di large dataset → fast response

### File Upload
- [ ] Upload file 1MB → berhasil
- [ ] Upload file 5MB → berhasil
- [ ] Upload file > 5MB → validation error

---

## 🐛 **15. ERROR HANDLING**

### Expected Errors
- [ ] 404 page tampil untuk route tidak ada
- [ ] 403 page tampil dengan debug info ✅ UPDATED
- [ ] 500 page tampil untuk server error (testing mode)
- [ ] Validation errors tampil di form

### Unexpected Errors
- [ ] Database connection error → handled gracefully
- [ ] File not found error → handled
- [ ] Permission denied error → handled

---

## 📝 **TESTING NOTES**

### Bugs Found:
1. _________________________________
2. _________________________________
3. _________________________________

### Performance Issues:
1. _________________________________
2. _________________________________

### UI/UX Improvements:
1. _________________________________
2. _________________________________

### Security Concerns:
1. _________________________________
2. _________________________________

---

## ✅ **SIGN OFF**

**All Critical Features Tested:** YES / NO  
**All Bugs Documented:** YES / NO  
**Ready for Production:** YES / NO  

**Tester Signature:** _________________  
**Date:** _________________  

---

**IMPORTANT REMINDERS:**
- ✅ Always clear browser cache before testing
- ✅ Test in incognito/private mode
- ✅ Test dengan different user roles
- ✅ Monitor `storage/logs/laravel.log` untuk errors
- ✅ Check browser console untuk JS errors
