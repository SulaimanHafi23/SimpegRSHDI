#!/bin/bash
# filepath: reorganize.sh

echo "🔧 Starting File Reorganization..."

# Create directory structure
echo "📁 Creating directory structure..."

# Controllers
mkdir -p app/Http/Controllers/Admin/{Worker,User,Role,Attendance,Schedule,Leave,Overtime,BusinessTrip,Document,Master}
mkdir -p app/Http/Controllers/Auth

# Requests
mkdir -p app/Http/Requests/{Worker,Attendance,Schedule,Leave,Overtime,BusinessTrip,Document}

# Services
mkdir -p app/Services/{Worker,User}

# Repositories
mkdir -p app/Repositories/{Worker,User,Role,Permission,Attendance,Schedule,Leave,Document}
mkdir -p app/Repositories/Contracts/{Worker,User,Role,Permission,Attendance,Schedule,Leave,Document}

echo "✅ Directory structure created"

# ==================== MOVE CONTROLLERS ====================
echo "🔄 Moving Controllers..."

# Worker Controller
[ -f "app/Http/Controllers/WorkerController.php" ] && mv app/Http/Controllers/WorkerController.php app/Http/Controllers/Admin/Worker/

# User Controller
[ -f "app/Http/Controllers/UserController.php" ] && mv app/Http/Controllers/UserController.php app/Http/Controllers/Admin/User/

# Attendance Controller
[ -f "app/Http/Controllers/AbsentController.php" ] && mv app/Http/Controllers/AbsentController.php app/Http/Controllers/Admin/Attendance/

# Schedule Controller
[ -f "app/Http/Controllers/WorkerShiftScheduleController.php" ] && mv app/Http/Controllers/WorkerShiftScheduleController.php app/Http/Controllers/Admin/Schedule/

# Leave Controller
[ -f "app/Http/Controllers/LeaveRequestController.php" ] && mv app/Http/Controllers/LeaveRequestController.php app/Http/Controllers/Admin/Leave/

# Overtime Controller
[ -f "app/Http/Controllers/OvertimeController.php" ] && mv app/Http/Controllers/OvertimeController.php app/Http/Controllers/Admin/Overtime/

# Business Trip Controllers
[ -f "app/Http/Controllers/BusinessTripController.php" ] && mv app/Http/Controllers/BusinessTripController.php app/Http/Controllers/Admin/BusinessTrip/
[ -f "app/Http/Controllers/BusinessTripReportController.php" ] && mv app/Http/Controllers/BusinessTripReportController.php app/Http/Controllers/Admin/BusinessTrip/

# Document Controller
[ -f "app/Http/Controllers/BerkasController.php" ] && mv app/Http/Controllers/BerkasController.php app/Http/Controllers/Admin/Document/

echo "✅ Controllers moved"

# ==================== MOVE REQUESTS ====================
echo "🔄 Moving Requests..."

# Worker Request
[ -f "app/Http/Requests/WorkerRequest.php" ] && mv app/Http/Requests/WorkerRequest.php app/Http/Requests/Worker/

# Attendance Request
[ -f "app/Http/Requests/AbsentRequest.php" ] && mv app/Http/Requests/AbsentRequest.php app/Http/Requests/Attendance/

# Schedule Request
[ -f "app/Http/Requests/WorkerShiftScheduleRequest.php" ] && mv app/Http/Requests/WorkerShiftScheduleRequest.php app/Http/Requests/Schedule/

# Leave Request
[ -f "app/Http/Requests/LeaveRequestRequest.php" ] && mv app/Http/Requests/LeaveRequestRequest.php app/Http/Requests/Leave/

# Overtime Request
[ -f "app/Http/Requests/OvertimeRequest.php" ] && mv app/Http/Requests/OvertimeRequest.php app/Http/Requests/Overtime/

# Business Trip Requests
[ -f "app/Http/Requests/BusinessTripRequest.php" ] && mv app/Http/Requests/BusinessTripRequest.php app/Http/Requests/BusinessTrip/
[ -f "app/Http/Requests/BusinessTripReportRequest.php" ] && mv app/Http/Requests/BusinessTripReportRequest.php app/Http/Requests/BusinessTrip/

# Document Request
[ -f "app/Http/Requests/BerkasRequest.php" ] && mv app/Http/Requests/BerkasRequest.php app/Http/Requests/Document/

echo "✅ Requests moved"

# ==================== MOVE SERVICES ====================
echo "🔄 Moving Services..."

# Worker Service
[ -f "app/Services/WorkerService.php" ] && mv app/Services/WorkerService.php app/Services/Worker/

# User Service
[ -f "app/Services/UserService.php" ] && mv app/Services/UserService.php app/Services/User/

# Business Trip Service
[ -f "app/Services/BusinessTripService.php" ] && mv app/Services/BusinessTripService.php app/Services/BusinessTrip/
[ -f "app/Services/BusinessTripReportService.php" ] && mv app/Services/BusinessTripReportService.php app/Services/BusinessTrip/

# Document Service
[ -f "app/Services/BerkasService.php" ] && mv app/Services/BerkasService.php app/Services/Document/

# Schedule Service
[ -f "app/Services/WorkerShiftScheduleService.php" ] && mv app/Services/WorkerShiftScheduleService.php app/Services/Schedule/

echo "✅ Services moved"

# ==================== MOVE REPOSITORIES ====================
echo "🔄 Moving Repositories..."

# Worker Repository
[ -f "app/Repositories/WorkerRepository.php" ] && mv app/Repositories/WorkerRepository.php app/Repositories/Worker/

# User Repository
[ -f "app/Repositories/UserRepository.php" ] && mv app/Repositories/UserRepository.php app/Repositories/User/

# Role Repository
[ -f "app/Repositories/RoleRepository.php" ] && mv app/Repositories/RoleRepository.php app/Repositories/Role/

# Permission Repository
[ -f "app/Repositories/PermissionRepository.php" ] && mv app/Repositories/PermissionRepository.php app/Repositories/Permission/

# Attendance Repository
[ -f "app/Repositories/AbsentRepository.php" ] && mv app/Repositories/AbsentRepository.php app/Repositories/Attendance/

# Schedule Repository
[ -f "app/Repositories/WorkerShiftScheduleRepository.php" ] && mv app/Repositories/WorkerShiftScheduleRepository.php app/Repositories/Schedule/

# Leave Repository
[ -f "app/Repositories/LeaveRequestRepository.php" ] && mv app/Repositories/LeaveRequestRepository.php app/Repositories/Leave/

# Business Trip Repositories
[ -f "app/Repositories/BusinessTripReportRepository.php" ] && mv app/Repositories/BusinessTripReportRepository.php app/Repositories/BusinessTrip/

# Document Repository
[ -f "app/Repositories/BerkasRepository.php" ] && mv app/Repositories/BerkasRepository.php app/Repositories/Document/

# Master Religion Repository
[ -f "app/Repositories/MasterReligionRepository.php" ] && mv app/Repositories/MasterReligionRepository.php app/Repositories/Master/ReligionRepository.php

echo "✅ Repositories moved"

# ==================== MOVE REPOSITORY INTERFACES ====================
echo "🔄 Moving Repository Interfaces..."

# Worker Interface
[ -f "app/Repositories/Contracts/WorkerRepositoryInterface.php" ] && mv app/Repositories/Contracts/WorkerRepositoryInterface.php app/Repositories/Contracts/Worker/

# User Interface
[ -f "app/Repositories/Contracts/UserRepositoryInterface.php" ] && mv app/Repositories/Contracts/UserRepositoryInterface.php app/Repositories/Contracts/User/

# Role Interface
[ -f "app/Repositories/Contracts/RoleRepositoryInterface.php" ] && mv app/Repositories/Contracts/RoleRepositoryInterface.php app/Repositories/Contracts/Role/

# Permission Interface
[ -f "app/Repositories/Contracts/PermissionRepositoryInterface.php" ] && mv app/Repositories/Contracts/PermissionRepositoryInterface.php app/Repositories/Contracts/Permission/

# Attendance Interface
[ -f "app/Repositories/Contracts/AbsentRepositoryInterface.php" ] && mv app/Repositories/Contracts/AbsentRepositoryInterface.php app/Repositories/Contracts/Attendance/

# Schedule Interface
[ -f "app/Repositories/Contracts/WorkerShiftScheduleRepositoryInterface.php" ] && mv app/Repositories/Contracts/WorkerShiftScheduleRepositoryInterface.php app/Repositories/Contracts/Schedule/

# Leave Interface
[ -f "app/Repositories/Contracts/LeaveRequestRepositoryInterface.php" ] && mv app/Repositories/Contracts/LeaveRequestRepositoryInterface.php app/Repositories/Contracts/Leave/

# Business Trip Interfaces
[ -f "app/Repositories/Contracts/BusinessTripReportRepositoryInterface.php" ] && mv app/Repositories/Contracts/BusinessTripReportRepositoryInterface.php app/Repositories/Contracts/BusinessTrip/

# Document Interface
[ -f "app/Repositories/Contracts/BerkasRepositoryInterface.php" ] && mv app/Repositories/Contracts/BerkasRepositoryInterface.php app/Repositories/Contracts/Document/

echo "✅ Repository Interfaces moved"

# ==================== CLEANUP ====================
echo "🧹 Cleaning up..."
composer dump-autoload

echo "✅ File reorganization completed!"
echo "⚠️  Please update namespaces in moved files manually"
