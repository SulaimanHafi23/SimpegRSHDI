{{-- filepath: resources/views/layouts/partials/unified-sidebar.blade.php --}}
@if(auth()->check())
<aside class="fixed top-16 lg:top-0 left-0 z-40 w-64 h-screen bg-gradient-to-b from-green-700 via-green-800 to-green-900 text-white shadow-2xl transition-transform -translate-x-full lg:translate-x-0 flex flex-col"
    id="unified-sidebar">

    <!-- Modern Header with Glassmorphism -->
    <div class="relative p-5 bg-white/10 backdrop-blur-md border-b border-white/20 flex-shrink-0">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="relative flex-shrink-0">
                    <div class="h-11 w-11 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-hospital text-white text-lg"></i>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 bg-green-400 border-2 border-green-900 rounded-full animate-pulse"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-yellow-400 font-semibold mb-0.5">SIMPEGRS HDI</p>
                    <h1 class="text-sm text-white font-medium truncate">{{ auth()->user()->worker->name ?? auth()->user()->name }}</h1>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button onclick="toggleUnifiedSidebar()"
                    class="lg:hidden flex-shrink-0 p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Navigation with Smooth Scrolling -->
    <nav id="sidebar-nav" class="flex-1 px-3 py-4 space-y-1.5 overflow-y-auto custom-scrollbar min-h-0">

        <!-- Dashboard Admin - if user has admin roles or dashboard.admin permission -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('dashboard.admin'))
            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-th-large text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Dashboard Admin</span>
            </a>
        @endif

        <!-- Dashboard Employee - if user has employee role or dashboard.employee permission -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('dashboard.employee'))
            <a href="{{ route('employee.dashboard') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('employee.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-th-large text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Dashboard Employee</span>
            </a>
        @endif

        <!-- Master Data Section - Admin/HR/Manager with permissions -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('religion.manage') || auth()->user()->can('gender.manage') || auth()->user()->can('department.manage') || auth()->user()->can('location.manage') || auth()->user()->can('shift.manage') || auth()->user()->can('leave-type.manage') || auth()->user()->can('document-type.manage') || auth()->user()->can('department-document-type.manage') || auth()->user()->can('holiday.manage'))

        <!-- DATA MASTER Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-database text-xs text-yellow-400"></i>
                <span class="text-xs font-bold text-yellow-400 tracking-wider uppercase">Data Master</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('religion.manage'))
        <a href="{{ route('admin.master.religions.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.religions.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.religions.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-mosque text-xs {{ request()->routeIs('admin.master.religions.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.religions.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Agama</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('gender.manage'))
        <a href="{{ route('admin.master.genders.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.genders.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.genders.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-venus-mars text-xs {{ request()->routeIs('admin.master.genders.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.genders.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Jenis Kelamin</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department.manage'))
        <a href="{{ route('admin.master.departments.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.departments.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.departments.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-building text-xs {{ request()->routeIs('admin.master.departments.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.departments.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Departemen</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('location.manage'))
        <a href="{{ route('admin.master.locations.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.locations.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.locations.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-map-marker-alt text-xs {{ request()->routeIs('admin.master.locations.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.locations.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Lokasi</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift.manage'))
        <a href="{{ route('admin.master.shifts.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.shifts.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clock text-xs {{ request()->routeIs('admin.master.shifts.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.shifts.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Shift</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave-type.manage'))
        <a href="{{ route('admin.master.leave-types.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('admin.master.leave-types.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.leave-types.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Tipe Cuti</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.manage'))
        <a href="{{ route('admin.master.document-types.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.document-types.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-file-alt text-xs {{ request()->routeIs('admin.master.document-types.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.document-types.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Tipe Dokumen</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department-document-type.manage'))
        <a href="{{ route('admin.master.department-document-types.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-project-diagram text-xs {{ request()->routeIs('admin.master.department-document-types.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.department-document-types.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Dokumen Posisi</span>
        </a>
        @endif
        @endif

        <!-- Management Section - Admin/HR/Manager -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('worker.manage') || auth()->user()->can('attendance.manage') || auth()->user()->can('schedule.manage') || auth()->user()->can('worker-document.manage'))

        <!-- MANAJEMEN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-tasks text-xs text-blue-400"></i>
                <span class="text-xs font-bold text-blue-400 tracking-wider uppercase">Manajemen</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
        <a href="{{ route('admin.workers.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.workers.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-users text-xs {{ request()->routeIs('admin.workers.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.workers.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Pegawai</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('attendance.manage'))
        <a href="{{ route('admin.attendance.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.attendance.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clipboard-check text-xs {{ request()->routeIs('admin.attendance.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.attendance.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Absensi</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('schedule.manage'))
        <a href="{{ route('admin.worker-shifts.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-user-clock text-xs {{ request()->routeIs('admin.worker-shifts.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.worker-shifts.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Jadwal Pegawai</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker-document.manage'))
        <a href="{{ route('admin.worker-documents.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.worker-documents.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.worker-documents.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-file-alt text-xs {{ request()->routeIs('admin.worker-documents.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.worker-documents.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Dokumen Pegawai</span>
        </a>
        @endif

        @endif

        <!-- Attendance Section - Employee or with permissions -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('attendance.view') || auth()->user()->can('attendance.checkin') || auth()->user()->can('schedule.view') || auth()->user()->can('shift-swap.request'))

        <!-- KEHADIRAN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-calendar-check text-xs text-green-400"></i>
                <span class="text-xs font-bold text-green-400 tracking-wider uppercase">Kehadiran</span>
            </div>
        </div>

        <a href="{{ route('employee.attendance.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.attendance.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.attendance.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clipboard-check text-xs {{ request()->routeIs('employee.attendance.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.attendance.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Absensi Saya</span>
        </a>

        <a href="{{ route('employee.shifts.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.shifts.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('employee.shifts.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.shifts.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Jadwal Kerja</span>
        </a>

        <a href="{{ route('employee.shift-swaps.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.shift-swaps.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.shift-swaps.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-exchange-alt text-xs {{ request()->routeIs('employee.shift-swaps.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.shift-swaps.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Tukar Shift</span>
        </a>
        @endif

        <!-- Request Section - Employee or with permissions -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('leave.request') || auth()->user()->can('leave.view') || auth()->user()->can('overtime.request') || auth()->user()->can('overtime.view') || auth()->user()->can('business-trip.request') || auth()->user()->can('business-trip.view'))

        <!-- PENGAJUAN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-paper-plane text-xs text-cyan-400"></i>
                <span class="text-xs font-bold text-cyan-400 tracking-wider uppercase">Pengajuan</span>
            </div>
        </div>

        <a href="{{ route('employee.leaves.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.leaves.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.leaves.*') ? 'bg-white/20' : 'bg-cyan-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-times text-xs {{ request()->routeIs('employee.leaves.*') ? 'text-white' : 'text-cyan-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.leaves.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Cuti Saya</span>
        </a>

        <a href="{{ route('employee.overtimes.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.overtimes.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.overtimes.*') ? 'bg-white/20' : 'bg-cyan-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clock text-xs {{ request()->routeIs('employee.overtimes.*') ? 'text-white' : 'text-cyan-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.overtimes.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Lembur Saya</span>
        </a>

        <a href="{{ route('employee.business-trips.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.business-trips.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.business-trips.*') ? 'bg-white/20' : 'bg-cyan-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-plane-departure text-xs {{ request()->routeIs('employee.business-trips.*') ? 'text-white' : 'text-cyan-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.business-trips.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Perjalanan Dinas</span>
        </a>
        @endif

        <!-- Approval Section - Admin/HR/Manager -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve') || auth()->user()->can('overtime.manage') || auth()->user()->can('overtime.approve') || auth()->user()->can('shift-swap.manage') || auth()->user()->can('shift-swap.approve') || auth()->user()->can('business-trip.manage') || auth()->user()->can('business-trip.approve'))

        <!-- PERSETUJUAN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-check-circle text-xs text-orange-400"></i>
                <span class="text-xs font-bold text-orange-400 tracking-wider uppercase">Persetujuan</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve'))
        <a href="{{ route('approvals.leaves.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('approvals.leaves.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('approvals.leaves.*') ? 'bg-white/20' : 'bg-orange-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-times text-xs {{ request()->routeIs('admin.leave.*') ? 'text-white' : 'text-orange-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.leave.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Cuti</span>
            @if(isset($pendingLeaves) && $pendingLeaves > 0)
                <span class="ml-auto bg-yellow-400 text-green-900 text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingLeaves }}</span>
            @endif
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('overtime.manage') || auth()->user()->can('overtime.approve'))
        <a href="{{ route('approvals.overtimes.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('approvals.overtimes.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('approvals.overtimes.*') ? 'bg-white/20' : 'bg-orange-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clock text-xs {{ request()->routeIs('admin.overtime.*') ? 'text-white' : 'text-orange-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.overtime.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Lembur</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift-swap.manage') || auth()->user()->can('shift-swap.approve'))
        <a href="{{ route('manager.shift-swap-approvals.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'bg-white/20' : 'bg-orange-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-exchange-alt text-xs {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'text-white' : 'text-orange-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Tukar Shift</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('business-trip.manage') || auth()->user()->can('business-trip.approve'))
        <a href="{{ route('approvals.business-trips.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('approvals.business-trips.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('approvals.business-trips.*') ? 'bg-white/20' : 'bg-orange-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-plane-departure text-xs {{ request()->routeIs('approvals.business-trips.*') ? 'text-white' : 'text-orange-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('approvals.business-trips.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Perjalanan Dinas</span>
        </a>
        @endif
        @endif

        <!-- Reports Section - Admin/HR/Manager or anyone with report.view permission -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('report.view'))

        <!-- LAPORAN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-chart-bar text-xs text-sky-400"></i>
                <span class="text-xs font-bold text-sky-400 tracking-wider uppercase">Laporan</span>
            </div>
        </div>

        <a href="{{ route('reports.attendance') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.attendance') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.attendance') ? 'bg-white/20' : 'bg-sky-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clipboard-list text-xs {{ request()->routeIs('reports.attendance') ? 'text-white' : 'text-sky-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('reports.attendance') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Attendance</span>
        </a>

        <a href="{{ route('reports.leaves') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.leaves') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.leaves') ? 'bg-white/20' : 'bg-sky-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('reports.leaves') ? 'text-white' : 'text-sky-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('reports.leaves') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Cuti</span>
        </a>

        <a href="{{ route('reports.overtimes') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.overtimes') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.overtimes') ? 'bg-white/20' : 'bg-sky-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clock text-xs {{ request()->routeIs('reports.overtimes') ? 'text-white' : 'text-sky-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('reports.overtimes') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Lembur</span>
        </a>

        <a href="{{ route('reports.worker-documents') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.worker-documents') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.worker-documents') ? 'bg-white/20' : 'bg-sky-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-file-alt text-xs {{ request()->routeIs('reports.worker-documents') ? 'text-white' : 'text-sky-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('reports.worker-documents') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Dokumen Pegawai</span>
        </a>
        @endif

        <!-- HR & Finance Section - Employee with permissions -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('worker-document.view') || auth()->user()->can('calendar.view'))

        <!-- HR & KEUANGAN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-briefcase text-xs text-teal-400"></i>
                <span class="text-xs font-bold text-teal-400 tracking-wider uppercase">Pemberkasan</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('worker-document.view'))
        <a href="{{ route('employee.documents.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.documents.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.documents.*') ? 'bg-white/20' : 'bg-teal-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-file-alt text-xs {{ request()->routeIs('employee.documents.*') ? 'text-white' : 'text-teal-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.documents.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Dokumen Saya</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Employee') || auth()->user()->hasRole('Manager') || auth()->user()->can('calendar.view'))
        <a href="{{ route('employee.calendar.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.calendar.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.calendar.*') ? 'bg-white/20' : 'bg-teal-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-day text-xs {{ request()->routeIs('employee.calendar.*') ? 'text-white' : 'text-teal-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.calendar.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Kalender</span>
        </a>
        @endif
        @endif

        <!-- Settings Section - Admin Only -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('holiday.manage') || auth()->user()->can('role.manage') || auth()->user()->can('user.manage') || auth()->user()->can('system-settings.manage'))

        <!-- PENGATURAN Label -->
        <div class="pt-3 pb-1.5 px-3">
            <div class="flex items-center space-x-2">
                <i class="fas fa-cog text-xs text-gray-400"></i>
                <span class="text-xs font-bold text-gray-400 tracking-wider uppercase">Pengaturan</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('holiday.manage'))
        <a href="{{ route('admin.holidays.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.holidays.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.holidays.*') ? 'bg-white/20' : 'bg-gray-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-day text-xs {{ request()->routeIs('admin.holidays.*') ? 'text-white' : 'text-gray-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.holidays.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Libur Nasional</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('role.manage'))
        <a href="{{ route('admin.roles.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.roles.*') ? 'bg-white/20' : 'bg-gray-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-user-tag text-xs {{ request()->routeIs('admin.roles.*') ? 'text-white' : 'text-gray-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.roles.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Role</span>
        </a>
        @endif

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('user.manage'))
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.users.*') ? 'bg-white/20' : 'bg-gray-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-user-shield text-xs {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-gray-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.users.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Users</span>
        </a>
        @endif
        @endif

        <!-- Profile - Employee with permission -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('profile.view'))
            <a href="{{ route('employee.profile.show') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('employee.profile.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.profile.*') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-user text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Profile Saya</span>
            </a>
        @endif

    </nav>

    <!-- Modern Logout Button with Hover Effect -->
    <div class="p-4 bg-white/5 backdrop-blur-md border-t border-white/10">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="group w-full flex items-center justify-center space-x-3 px-4 py-3 rounded-xl bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white transition-all duration-300 transform hover:scale-105">
                <i class="fas fa-sign-out-alt group-hover:rotate-12 transition-transform"></i>
                <span class="font-semibold">Logout</span>
            </button>
        </form>
    </div>
</aside>

<!-- Custom Scrollbar Styles -->
<style>
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.2);
    border-radius: 10px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.3);
}
</style>

<!-- Toggle Sidebar Script -->
<script>
function toggleUnifiedSidebar() {
    const sidebar = document.getElementById('unified-sidebar');
    const backdrop = document.getElementById('sidebar-backdrop');

    // Toggle sidebar
    sidebar.classList.toggle('-translate-x-full');

    // Toggle backdrop
    if (sidebar.classList.contains('-translate-x-full')) {
        // Sidebar is closing
        backdrop.classList.add('opacity-0', 'pointer-events-none');
        backdrop.classList.remove('opacity-100', 'pointer-events-auto');
    } else {
        // Sidebar is opening
        backdrop.classList.remove('opacity-0', 'pointer-events-none');
        backdrop.classList.add('opacity-100', 'pointer-events-auto');
    }
}

// Save sidebar scroll position before navigation
function saveSidebarScrollPosition() {
    const sidebarNav = document.getElementById('sidebar-nav');
    if (sidebarNav) {
        const scrollPosition = sidebarNav.scrollTop;
        localStorage.setItem('sidebarScrollPosition', scrollPosition);
    }
}

// Restore sidebar scroll position after page load
function restoreSidebarScrollPosition() {
    const sidebarNav = document.getElementById('sidebar-nav');
    const savedPosition = localStorage.getItem('sidebarScrollPosition');
    if (sidebarNav && savedPosition) {
        // Use setTimeout to ensure the sidebar is fully rendered
        setTimeout(() => {
            sidebarNav.scrollTop = parseInt(savedPosition);
        }, 50);
    }
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Restore scroll position when page loads
    restoreSidebarScrollPosition();

    // Additional restoration after a short delay for safety
    setTimeout(() => {
        restoreSidebarScrollPosition();
    }, 200);

    // Save scroll position before navigating to any link
    const sidebarLinks = document.querySelectorAll('#sidebar-nav a[href]');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            saveSidebarScrollPosition();
        });
    });

    // Save scroll position on window unload (fallback)
    window.addEventListener('beforeunload', function() {
        saveSidebarScrollPosition();
    });

    // Save scroll position periodically while scrolling
    const sidebarNav = document.getElementById('sidebar-nav');
    if (sidebarNav) {
        let scrollTimeout;
        sidebarNav.addEventListener('scroll', function() {
            clearTimeout(scrollTimeout);
            scrollTimeout = setTimeout(() => {
                saveSidebarScrollPosition();
            }, 100);
        });
    }
});

// Also try to restore position when window is fully loaded
window.addEventListener('load', function() {
    setTimeout(() => {
        restoreSidebarScrollPosition();
    }, 100);
});

// Save position when page becomes hidden (for page refresh scenarios)
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        saveSidebarScrollPosition();
    }
});

// Save position when user is about to leave the page
window.addEventListener('pagehide', function() {
    saveSidebarScrollPosition();
});
</script>

@endif
