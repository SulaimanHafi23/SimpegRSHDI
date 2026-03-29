{{-- filepath: resources/views/layouts/partials/unified-sidebar.blade.php --}}
@if(auth()->check())
<aside class="fixed top-0 left-0 z-50 w-64 h-screen text-white shadow-2xl transition-transform -translate-x-full lg:translate-x-0 flex flex-col overflow-hidden"
    style="background:linear-gradient(160deg,#0a3d1f 0%,#0d2b17 100%)"
    id="unified-sidebar">

    <!-- Gold radial glow top-right (matches landing page) -->
    <div class="pointer-events-none absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-30" style="background:radial-gradient(circle,#f5a623 0%,transparent 70%);filter:blur(40px)"></div>

    <!-- Modern Header with Glassmorphism -->
    <div class="relative p-5 border-b border-[#f5a623]/20 flex-shrink-0" style="background:rgba(245,166,35,0.06);backdrop-filter:blur(12px)">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="relative flex-shrink-0">
                    <div class="h-11 w-11 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-hospital text-white text-lg"></i>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 bg-green-400 border-2 border-green-900 rounded-full animate-pulse"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-yellow-400 font-semibold mb-0.5">SIDIA</p>
                    <h1 class="text-sm text-white font-medium break-words leading-tight">{{ auth()->user()->worker->name ?? auth()->user()->name }}</h1>
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

        {{-- ============================================================
             DASHBOARD — tampil sesuai role
        ============================================================ --}}
        @if(auth()->user()->hasRole('Super Admin'))
            <a href="{{ route('admin.dashboard') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-th-large text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Dashboard Admin</span>
            </a>
        @endif
        @if(auth()->user()->hasRole('HR'))
            <a href="{{ route('hr.dashboard') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('hr.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('hr.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-user-tie text-sm {{ request()->routeIs('hr.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('hr.dashboard') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Dashboard HR</span>
            </a>
        @endif
        @if(auth()->user()->hasRole('Manager'))
            <a href="{{ route('manager.dashboard') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('manager.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('manager.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-building text-sm {{ request()->routeIs('manager.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('manager.dashboard') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Dashboard Manager</span>
            </a>
        @endif
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('dashboard.employee'))
            <a href="{{ route('employee.dashboard') }}"
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('employee.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-th-large text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-white hover:text-yellow-300' }}">Dashboard</span>
            </a>
        @endif

        {{-- ============================================================
             1. DATA MASTER
             Referensi sistem: agama, jenis kelamin, departemen, shift, dll.
        ============================================================ --}}
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('department.manage') || auth()->user()->can('shift.manage') || auth()->user()->can('leave-type.manage') || auth()->user()->can('document-type.manage') || auth()->user()->can('department-document-type.manage') || auth()->user()->can('holiday.manage'))

        <div class="pt-4 pb-1.5 px-1">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-yellow-500/10 border border-yellow-500/20">
                <i class="fas fa-database text-xs text-yellow-400"></i>
                <span class="text-[11px] font-bold text-yellow-400 tracking-widest uppercase">Data Master</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department.manage'))
        <a href="{{ route('admin.master.departments.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.departments.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.departments.*') ? 'bg-white/20' : 'bg-yellow-500/15' }} rounded-lg flex-shrink-0">
                <i class="fas fa-building text-xs {{ request()->routeIs('admin.master.departments.*') ? 'text-white' : 'text-yellow-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.departments.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Departemen</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift.manage'))
        <a href="{{ route('admin.master.shifts.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.shifts.*') ? 'bg-white/20' : 'bg-yellow-500/15' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clock text-xs {{ request()->routeIs('admin.master.shifts.*') ? 'text-white' : 'text-yellow-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.shifts.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Shift Kerja</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave-type.manage'))
        <a href="{{ route('admin.master.leave-types.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-white/20' : 'bg-yellow-500/15' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('admin.master.leave-types.*') ? 'text-white' : 'text-yellow-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.leave-types.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Jenis Cuti</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.manage'))
        <a href="{{ route('admin.master.document-types.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.document-types.*') ? 'bg-white/20' : 'bg-yellow-500/15' }} rounded-lg flex-shrink-0">
                <i class="fas fa-file-alt text-xs {{ request()->routeIs('admin.master.document-types.*') ? 'text-white' : 'text-yellow-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.document-types.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Jenis Dokumen</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department-document-type.manage'))
        <a href="{{ route('admin.master.department-document-types.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-white/20' : 'bg-yellow-500/15' }} rounded-lg flex-shrink-0">
                <i class="fas fa-project-diagram text-xs {{ request()->routeIs('admin.master.department-document-types.*') ? 'text-white' : 'text-yellow-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.master.department-document-types.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Dokumen Posisi</span>
        </a>
        @endif
        @endif

        {{-- ============================================================
             2. MANAJERIAL
             Pengelolaan pegawai, absensi, jadwal — Admin / HR / Manager
        ============================================================ --}}
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('worker.manage') || auth()->user()->can('attendance.manage') || auth()->user()->can('schedule.manage') || auth()->user()->can('worker-document.manage'))

        <div class="pt-4 pb-1.5 px-1">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-blue-500/10 border border-blue-500/20">
                <i class="fas fa-briefcase text-xs text-blue-400"></i>
                <span class="text-[11px] font-bold text-blue-400 tracking-widest uppercase">Manajerial</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
        <a href="{{ route('admin.workers.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.workers.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-users text-xs {{ request()->routeIs('admin.workers.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.workers.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Data Pegawai</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('attendance.manage'))
        <a href="{{ route('admin.attendance.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.attendance.*') ? 'bg-white/20' : 'bg-blue-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-clipboard-check text-xs {{ request()->routeIs('admin.attendance.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.attendance.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Rekap Absensi</span>
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
                <i class="fas fa-folder-open text-xs {{ request()->routeIs('admin.worker-documents.*') ? 'text-white' : 'text-blue-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.worker-documents.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Berkas Pegawai</span>
        </a>
        @endif

        @endif

        {{-- ============================================================
             3. PERSETUJUAN
             Approval cuti, tukar shift, perjalanan dinas
        ============================================================ --}}
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve') || auth()->user()->can('shift-swap.manage') || auth()->user()->can('shift-swap.approve') || auth()->user()->can('business-trip.manage') || auth()->user()->can('business-trip.approve'))

        <div class="pt-4 pb-1.5 px-1">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-orange-500/10 border border-orange-500/20">
                <i class="fas fa-check-double text-xs text-orange-400"></i>
                <span class="text-[11px] font-bold text-orange-400 tracking-widest uppercase">Persetujuan</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve'))
        <a href="{{ route('admin.leave.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.leave.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.leave.*') ? 'bg-white/20' : 'bg-orange-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-times text-xs {{ request()->routeIs('admin.leave.*') ? 'text-white' : 'text-orange-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.leave.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Permohonan Cuti</span>
            @if(isset($pendingLeaves) && $pendingLeaves > 0)
                <span class="ml-auto bg-yellow-400 text-green-900 text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingLeaves }}</span>
            @endif
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

        {{-- ============================================================
             4. PORTAL PEGAWAI
             Fitur self-service: absensi, jadwal, cuti, dokumen pribadi
        ============================================================ --}}
        @if((auth()->user()->hasRole('Employee') || auth()->user()->hasRole('Manager') || auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR')) && auth()->user()->worker)

        <div class="pt-4 pb-1.5 px-1">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-green-500/10 border border-green-500/20">
                <i class="fas fa-user-circle text-xs text-green-400"></i>
                <span class="text-[11px] font-bold text-green-400 tracking-widest uppercase">Portal Pegawai</span>
            </div>
        </div>

        {{-- Kehadiran --}}
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

        {{-- Pengajuan --}}
        <a href="{{ route('employee.leaves.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.leaves.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.leaves.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-times text-xs {{ request()->routeIs('employee.leaves.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.leaves.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Cuti Saya</span>
        </a>
        <a href="{{ route('employee.business-trips.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.business-trips.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.business-trips.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-plane-departure text-xs {{ request()->routeIs('employee.business-trips.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.business-trips.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Perjalanan Dinas</span>
        </a>

        {{-- Dokumen & Kalender --}}
        <a href="{{ route('employee.documents.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.documents.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.documents.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-file-alt text-xs {{ request()->routeIs('employee.documents.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.documents.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Dokumen Saya</span>
        </a>
        <a href="{{ route('employee.calendar.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.calendar.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.calendar.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-day text-xs {{ request()->routeIs('employee.calendar.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.calendar.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Kalender</span>
        </a>

        {{-- Profil --}}
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('profile.view'))
        <a href="{{ route('employee.profile.show') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.profile.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.profile.*') ? 'bg-white/20' : 'bg-green-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-user text-xs {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Profil Saya</span>
        </a>
        @endif
        @endif

        {{-- ============================================================
             5. ADMINISTRASI SISTEM
             Konfigurasi, role, pengguna, audit log
        ============================================================ --}}
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Manager') || auth()->user()->can('holiday.manage') || auth()->user()->can('role.manage') || auth()->user()->can('user.manage') || auth()->user()->can('system-settings.manage'))

        <div class="pt-4 pb-1.5 px-1">
            <div class="flex items-center gap-2 px-2 py-1.5 rounded-md bg-slate-500/10 border border-slate-500/20">
                <i class="fas fa-shield-alt text-xs text-slate-400"></i>
                <span class="text-[11px] font-bold text-slate-400 tracking-widest uppercase">Administrasi</span>
            </div>
        </div>

        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Manager') || auth()->user()->can('holiday.manage'))
        <a href="{{ route('admin.holidays.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.holidays.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.holidays.*') ? 'bg-white/20' : 'bg-slate-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-calendar-day text-xs {{ request()->routeIs('admin.holidays.*') ? 'text-white' : 'text-slate-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.holidays.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Libur Nasional</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('role.manage'))
        <a href="{{ route('admin.roles.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.roles.*') ? 'bg-white/20' : 'bg-slate-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-user-tag text-xs {{ request()->routeIs('admin.roles.*') ? 'text-white' : 'text-slate-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.roles.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Hak Akses (Role)</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('user.manage'))
        <a href="{{ route('admin.users.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.users.*') ? 'bg-white/20' : 'bg-slate-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-user-shield text-xs {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-slate-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.users.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Akun Pengguna</span>
        </a>
        @endif
        @if(auth()->user()->hasRole('Super Admin'))
        <a href="{{ route('admin.audit-logs.index') }}"
           class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.audit-logs.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'bg-white/5 hover:bg-white/10' }} transition-all duration-200">
            <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-white/20' : 'bg-slate-500/20' }} rounded-lg flex-shrink-0">
                <i class="fas fa-history text-xs {{ request()->routeIs('admin.audit-logs.*') ? 'text-white' : 'text-slate-300' }}"></i>
            </div>
            <span class="text-sm {{ request()->routeIs('admin.audit-logs.*') ? 'text-white font-medium' : 'text-white hover:text-yellow-300' }}">Audit Log</span>
        </a>
        @endif
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
