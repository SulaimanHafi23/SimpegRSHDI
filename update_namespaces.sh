#!/bin/bash
# filepath: update_namespaces.sh

echo "🔄 Updating namespaces..."

# ==================== UPDATE CONTROLLERS ====================
echo "📝 Updating Controller namespaces..."

# Worker Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\Worker;/' app/Http/Controllers/Admin/Worker/WorkerController.php

# User Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\User;/' app/Http/Controllers/Admin/User/UserController.php

# Attendance Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\Attendance;/' app/Http/Controllers/Admin/Attendance/AbsentController.php

# Schedule Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\Schedule;/' app/Http/Controllers/Admin/Schedule/WorkerShiftScheduleController.php

# Leave Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\Leave;/' app/Http/Controllers/Admin/Leave/LeaveRequestController.php

# Overtime Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\Overtime;/' app/Http/Controllers/Admin/Overtime/OvertimeController.php

# Business Trip Controllers
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\BusinessTrip;/' app/Http/Controllers/Admin/BusinessTrip/BusinessTripController.php
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\BusinessTrip;/' app/Http/Controllers/Admin/BusinessTrip/BusinessTripReportController.php

# Document Controller
sed -i 's/namespace App\\Http\\Controllers;/namespace App\\Http\\Controllers\\Admin\\Document;/' app/Http/Controllers/Admin/Document/BerkasController.php

# ==================== UPDATE REQUESTS ====================
echo "📝 Updating Request namespaces..."

sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\Worker;/' app/Http/Requests/Worker/WorkerRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\Attendance;/' app/Http/Requests/Attendance/AbsentRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\Schedule;/' app/Http/Requests/Schedule/WorkerShiftScheduleRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\Leave;/' app/Http/Requests/Leave/LeaveRequestRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\Overtime;/' app/Http/Requests/Overtime/OvertimeRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\BusinessTrip;/' app/Http/Requests/BusinessTrip/BusinessTripRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\BusinessTrip;/' app/Http/Requests/BusinessTrip/BusinessTripReportRequest.php
sed -i 's/namespace App\\Http\\Requests;/namespace App\\Http\\Requests\\Document;/' app/Http/Requests/Document/BerkasRequest.php

# ==================== UPDATE SERVICES ====================
echo "📝 Updating Service namespaces..."

sed -i 's/namespace App\\Services;/namespace App\\Services\\Worker;/' app/Services/Worker/WorkerService.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\User;/' app/Services/User/UserService.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\BusinessTrip;/' app/Services/BusinessTrip/BusinessTripService.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\BusinessTrip;/' app/Services/BusinessTrip/BusinessTripReportService.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\Document;/' app/Services/Document/BerkasService.php
sed -i 's/namespace App\\Services;/namespace App\\Services\\Schedule;/' app/Services/Schedule/WorkerShiftScheduleService.php

# ==================== UPDATE REPOSITORIES ====================
echo "📝 Updating Repository namespaces..."

sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Worker;/' app/Repositories/Worker/WorkerRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\User;/' app/Repositories/User/UserRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Role;/' app/Repositories/Role/RoleRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Permission;/' app/Repositories/Permission/PermissionRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Attendance;/' app/Repositories/Attendance/AbsentRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Schedule;/' app/Repositories/Schedule/WorkerShiftScheduleRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Leave;/' app/Repositories/Leave/LeaveRequestRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\BusinessTrip;/' app/Repositories/BusinessTrip/BusinessTripReportRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Document;/' app/Repositories/Document/BerkasRepository.php
sed -i 's/namespace App\\Repositories;/namespace App\\Repositories\\Master;/' app/Repositories/Master/ReligionRepository.php

# ==================== UPDATE REPOSITORY INTERFACES ====================
echo "📝 Updating Repository Interface namespaces..."

sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Worker;/' app/Repositories/Contracts/Worker/WorkerRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\User;/' app/Repositories/Contracts/User/UserRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Role;/' app/Repositories/Contracts/Role/RoleRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Permission;/' app/Repositories/Contracts/Permission/PermissionRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Attendance;/' app/Repositories/Contracts/Attendance/AbsentRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Schedule;/' app/Repositories/Contracts/Schedule/WorkerShiftScheduleRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Leave;/' app/Repositories/Contracts/Leave/LeaveRequestRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\BusinessTrip;/' app/Repositories/Contracts/BusinessTrip/BusinessTripReportRepositoryInterface.php
sed -i 's/namespace App\\Repositories\\Contracts;/namespace App\\Repositories\\Contracts\\Document;/' app/Repositories/Contracts/Document/BerkasRepositoryInterface.php

echo "✅ Namespace update completed!"
echo "🔄 Running composer dump-autoload..."
composer dump-autoload

echo "✅ All done! Please test your application."