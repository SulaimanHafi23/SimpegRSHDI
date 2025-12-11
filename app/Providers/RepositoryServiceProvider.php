<?php
// filepath: app/Providers/RepositoryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Master Repositories
use App\Repositories\Contracts\Master\ReligionRepositoryInterface;
use App\Repositories\Master\ReligionRepository;
use App\Repositories\Contracts\Master\GenderRepositoryInterface;
use App\Repositories\Master\GenderRepository;
use App\Repositories\Contracts\Master\PositionRepositoryInterface;
use App\Repositories\Master\PositionRepository;
use App\Repositories\Contracts\Master\LocationRepositoryInterface;
use App\Repositories\Master\LocationRepository;
use App\Repositories\Contracts\Master\DocumentTypeRepositoryInterface;
use App\Repositories\Master\DocumentTypeRepository;
use App\Repositories\Contracts\Master\FileRequirementRepositoryInterface;
use App\Repositories\Master\FileRequirementRepository;
use App\Repositories\Contracts\Master\ShiftRepositoryInterface;
use App\Repositories\Master\ShiftRepository;
use App\Repositories\Contracts\Master\ShiftPatternRepositoryInterface;
use App\Repositories\Master\ShiftPatternRepository;

// Worker & User Repositories
use App\Repositories\Contracts\WorkerRepositoryInterface;
use App\Repositories\WorkerRepository;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\UserRepository;

// Attendance & Schedule Repositories
use App\Repositories\Contracts\AbsentRepositoryInterface;
use App\Repositories\AbsentRepository;
use App\Repositories\Contracts\WorkerShiftScheduleRepositoryInterface;
use App\Repositories\WorkerShiftScheduleRepository;

// Leave & Overtime Repositories
use App\Repositories\Contracts\LeaveRequestRepositoryInterface;
use App\Repositories\LeaveRequestRepository;
use App\Repositories\Contracts\OvertimeRepositoryInterface;
use App\Repositories\OvertimeRepository;

// Business Trip Repositories
use App\Repositories\Contracts\BusinessTripRepositoryInterface;
use App\Repositories\BusinessTripRepository;
use App\Repositories\Contracts\BusinessTripReportRepositoryInterface;
use App\Repositories\BusinessTripReportRepository;

// Document Repositories
use App\Repositories\Contracts\BerkasRepositoryInterface;
use App\Repositories\BerkasRepository;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // ========== MASTER DATA REPOSITORIES ==========
        $this->app->bind(ReligionRepositoryInterface::class, ReligionRepository::class);
        $this->app->bind(GenderRepositoryInterface::class, GenderRepository::class);
        $this->app->bind(PositionRepositoryInterface::class, PositionRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(DocumentTypeRepositoryInterface::class, DocumentTypeRepository::class);
        $this->app->bind(FileRequirementRepositoryInterface::class, FileRequirementRepository::class);
        $this->app->bind(ShiftRepositoryInterface::class, ShiftRepository::class);
        $this->app->bind(ShiftPatternRepositoryInterface::class, ShiftPatternRepository::class);

        // ========== WORKER & USER REPOSITORIES ==========
        $this->app->bind(WorkerRepositoryInterface::class, WorkerRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // ========== ATTENDANCE & SCHEDULE REPOSITORIES ==========
        $this->app->bind(AbsentRepositoryInterface::class, AbsentRepository::class);
        $this->app->bind(WorkerShiftScheduleRepositoryInterface::class, WorkerShiftScheduleRepository::class);

        // ========== LEAVE & OVERTIME REPOSITORIES ==========
        $this->app->bind(LeaveRequestRepositoryInterface::class, LeaveRequestRepository::class);
        $this->app->bind(OvertimeRepositoryInterface::class, OvertimeRepository::class);

        // ========== BUSINESS TRIP REPOSITORIES ==========
        $this->app->bind(BusinessTripRepositoryInterface::class, BusinessTripRepository::class);
        $this->app->bind(BusinessTripReportRepositoryInterface::class, BusinessTripReportRepository::class);

        // ========== DOCUMENT REPOSITORIES ==========
        $this->app->bind(BerkasRepositoryInterface::class, BerkasRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}