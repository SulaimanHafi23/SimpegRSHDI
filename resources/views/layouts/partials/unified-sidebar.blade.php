{{-- filepath: resources/views/layouts/partials/unified-sidebar.blade.php --}}
<aside x-data="{ 
    openMenu: '{{ 
        request()->routeIs('admin.master.*') ? 'master' : 
        (request()->routeIs('admin.workers.*', 'admin.attendance.*', 'admin.worker-shifts.*', 'admin.worker-documents.*', 'admin.payroll.*') ? 'management' : 
        (request()->routeIs('employee.attendance.*', 'employee.shifts.*', 'employee.shift-swaps.*') ? 'attendance' : 
        (request()->routeIs('employee.leaves.*', 'employee.overtimes.*', 'employee.business-trips.*') ? 'requests' : 
        (request()->routeIs('admin.leave.*', 'admin.overtime.*', 'approvals.*', 'manager.shift-swap-approvals.*') ? 'approval' : 
        (request()->routeIs('reports.*') ? 'reports' : 
        (request()->routeIs('employee.documents.*', 'employee.payroll.*', 'employee.calendar.*') ? 'hr' : 
        (request()->routeIs('admin.roles.*', 'admin.users.*', 'admin.holidays.*') ? 'settings' : ''))))))) 
    }}' }" 
    class="fixed top-16 lg:top-0 left-0 z-40 w-64 h-screen bg-gradient-to-b from-green-700 via-green-800 to-green-900 text-white shadow-2xl transition-transform -translate-x-full lg:translate-x-0" 
    id="unified-sidebar">
    
    <!-- Modern Header with Glassmorphism -->
    <div class="relative p-5 bg-white/10 backdrop-blur-md border-b border-white/20">
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
    <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto custom-scrollbar">
        
        <!-- Dashboard Admin - if user has admin roles or dashboard.admin permission -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('dashboard.admin'))
            <a href="{{ route('admin.dashboard') }}" 
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('admin.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('admin.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-th-large text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('admin.dashboard') ? 'text-white' : 'text-green-100' }}">Dashboard Admin</span>
            </a>
        @endif

        <!-- Dashboard Employee - if user has employee role or dashboard.employee permission -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('dashboard.employee'))
            <a href="{{ route('employee.dashboard') }}" 
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('employee.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.dashboard') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-th-large text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-green-100' }}">Dashboard Employee</span>
            </a>
        @endif

        <!-- Master Data Section - Admin/HR/Manager with permissions -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('religion.manage') || auth()->user()->can('gender.manage') || auth()->user()->can('department.manage') || auth()->user()->can('location.manage') || auth()->user()->can('shift.manage') || auth()->user()->can('leave-type.manage') || auth()->user()->can('document-type.manage') || auth()->user()->can('department-document-type.manage') || auth()->user()->can('holiday.manage'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'master' ? '' : 'master'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-blue-500/20 border border-blue-500/40 rounded-lg group-hover:bg-blue-500/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-database text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Data Master</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'master' }"></i>
            </button>

            <div x-show="openMenu === 'master'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-blue-500/30 pl-3">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('religion.manage'))
                <a href="{{ route('admin.master.religions.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.religions.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.religions.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-mosque text-xs {{ request()->routeIs('admin.master.religions.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Agama</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('gender.manage'))
                <a href="{{ route('admin.master.genders.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.genders.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.genders.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-venus-mars text-xs {{ request()->routeIs('admin.master.genders.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Jenis Kelamin</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department.manage'))
                <a href="{{ route('admin.master.departments.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.departments.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.departments.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-building text-xs {{ request()->routeIs('admin.master.departments.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Departemen</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('location.manage'))
                <a href="{{ route('admin.master.locations.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.locations.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.locations.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-map-marker-alt text-xs {{ request()->routeIs('admin.master.locations.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Lokasi</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift.manage'))
                <a href="{{ route('admin.master.shifts.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.shifts.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clock text-xs {{ request()->routeIs('admin.master.shifts.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Shift</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave-type.manage'))
                <a href="{{ route('admin.master.leave-types.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('admin.master.leave-types.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Tipe Cuti</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.manage'))
                <a href="{{ route('admin.master.document-types.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.document-types.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-file-alt text-xs {{ request()->routeIs('admin.master.document-types.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Tipe Dokumen</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department-document-type.manage'))
                <a href="{{ route('admin.master.department-document-types.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-white/20 border border-white/30' : 'bg-blue-500/20 border border-blue-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-project-diagram text-xs {{ request()->routeIs('admin.master.department-document-types.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Dokumen Posisi</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Management Section - Admin/HR/Manager -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('worker.manage') || auth()->user()->can('attendance.manage') || auth()->user()->can('schedule.manage') || auth()->user()->can('worker-document.manage') || auth()->user()->can('payroll.manage'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'management' ? '' : 'management'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-purple-500/20 border border-purple-500/40 rounded-lg group-hover:bg-purple-500/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-tasks text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Manajemen</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'management' }"></i>
            </button>

            <div x-show="openMenu === 'management'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-purple-500/30 pl-3">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                <a href="{{ route('admin.workers.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.workers.*') ? 'bg-white/20 border border-white/30' : 'bg-purple-500/20 border border-purple-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-users text-xs {{ request()->routeIs('admin.workers.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('attendance.manage'))
                <a href="{{ route('admin.attendance.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.attendance.*') ? 'bg-white/20 border border-white/30' : 'bg-purple-500/20 border border-purple-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clipboard-check text-xs {{ request()->routeIs('admin.attendance.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Absensi</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('schedule.manage'))
                <a href="{{ route('admin.worker-shifts.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-white/20 border border-white/30' : 'bg-purple-500/20 border border-purple-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-user-clock text-xs {{ request()->routeIs('admin.worker-shifts.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Jadwal Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker-document.manage'))
                <a href="{{ route('admin.worker-documents.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.worker-documents.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.worker-documents.*') ? 'bg-white/20 border border-white/30' : 'bg-purple-500/20 border border-purple-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-file-alt text-xs {{ request()->routeIs('admin.worker-documents.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Dokumen Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR'))
                <a href="{{ route('admin.payroll.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.payroll.index') || request()->routeIs('admin.payroll.show') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.payroll.*') ? 'bg-white/20 border border-white/30' : 'bg-purple-500/20 border border-purple-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-xs {{ request()->routeIs('admin.payroll.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Payroll</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Attendance Section - Employee or with permissions -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('attendance.view') || auth()->user()->can('attendance.checkin') || auth()->user()->can('schedule.view') || auth()->user()->can('shift-swap.request'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'attendance' ? '' : 'attendance'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-green-500/20 border border-green-500/40 rounded-lg group-hover:bg-green-500/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-calendar-check text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Kehadiran</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'attendance' }"></i>
            </button>

            <div x-show="openMenu === 'attendance'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-green-500/30 pl-3">
                <a href="{{ route('employee.attendance.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.attendance.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.attendance.*') ? 'bg-white/20 border border-white/30' : 'bg-green-500/20 border border-green-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clipboard-check text-xs {{ request()->routeIs('employee.attendance.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Absensi Saya</span>
                </a>

                <a href="{{ route('employee.shifts.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.shifts.*') ? 'bg-white/20 border border-white/30' : 'bg-green-500/20 border border-green-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('employee.shifts.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Jadwal Kerja</span>
                </a>

                <a href="{{ route('employee.shift-swaps.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.shift-swaps.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.shift-swaps.*') ? 'bg-white/20 border border-white/30' : 'bg-green-500/20 border border-green-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-exchange-alt text-xs {{ request()->routeIs('employee.shift-swaps.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Tukar Shift</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Request Section - Employee or with permissions -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('leave.request') || auth()->user()->can('leave.view') || auth()->user()->can('overtime.request') || auth()->user()->can('overtime.view') || auth()->user()->can('business-trip.request') || auth()->user()->can('business-trip.view'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'requests' ? '' : 'requests'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-green-400/20 border border-green-400/40 rounded-lg group-hover:bg-green-400/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-paper-plane text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Pengajuan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'requests' }"></i>
            </button>

            <div x-show="openMenu === 'requests'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-green-400/30 pl-3">
                <a href="{{ route('employee.leaves.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.leaves.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.leaves.*') ? 'bg-white/20 border border-white/30' : 'bg-green-400/20 border border-green-400/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-times text-xs {{ request()->routeIs('employee.leaves.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Cuti Saya</span>
                </a>

                <a href="{{ route('employee.overtimes.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.overtimes.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.overtimes.*') ? 'bg-white/20 border border-white/30' : 'bg-green-400/20 border border-green-400/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clock text-xs {{ request()->routeIs('employee.overtimes.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Lembur Saya</span>
                </a>

                <a href="{{ route('employee.business-trips.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.business-trips.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.business-trips.*') ? 'bg-white/20 border border-white/30' : 'bg-green-400/20 border border-green-400/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-plane-departure text-xs {{ request()->routeIs('employee.business-trips.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Perjalanan Dinas</span>
                </a>
            </div>
        </div>
        @endif

        <!-- Approval Section - Admin/HR/Manager -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve') || auth()->user()->can('overtime.manage') || auth()->user()->can('overtime.approve') || auth()->user()->can('shift-swap.manage') || auth()->user()->can('shift-swap.approve') || auth()->user()->can('business-trip.manage') || auth()->user()->can('business-trip.approve'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'approval' ? '' : 'approval'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-orange-500/20 border border-orange-500/40 rounded-lg group-hover:bg-orange-500/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-check-circle text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Persetujuan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'approval' }"></i>
            </button>

            <div x-show="openMenu === 'approval'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-orange-500/30 pl-3">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave.manage') || auth()->user()->can('leave.approve'))
                <a href="{{ route('admin.leave.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.leave.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.leave.*') ? 'bg-white/20 border border-white/30' : 'bg-orange-500/20 border border-orange-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-times text-xs {{ request()->routeIs('admin.leave.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Cuti</span>
                    @if(isset($pendingLeaves) && $pendingLeaves > 0)
                        <span class="ml-auto bg-yellow-400 text-green-900 text-xs font-bold px-2 py-0.5 rounded-full">{{ $pendingLeaves }}</span>
                    @endif
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('overtime.manage') || auth()->user()->can('overtime.approve'))
                <a href="{{ route('admin.overtime.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.overtime.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.overtime.*') ? 'bg-white/20 border border-white/30' : 'bg-orange-500/20 border border-orange-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clock text-xs {{ request()->routeIs('admin.overtime.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Lembur</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift-swap.manage') || auth()->user()->can('shift-swap.approve'))
                <a href="{{ route('manager.shift-swap-approvals.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'bg-white/20 border border-white/30' : 'bg-orange-500/20 border border-orange-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-exchange-alt text-xs {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Tukar Shift</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->can('business-trip.manage') || auth()->user()->can('business-trip.approve'))
                <a href="{{ route('approvals.business-trips.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('approvals.business-trips.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('approvals.business-trips.*') ? 'bg-white/20 border border-white/30' : 'bg-orange-500/20 border border-orange-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-plane-departure text-xs {{ request()->routeIs('approvals.business-trips.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Perjalanan Dinas</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Reports Section - Admin/HR/Manager or anyone with report.view permission -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR') || auth()->user()->hasRole('Manager') || auth()->user()->can('report.view'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'reports' ? '' : 'reports'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-pink-500/20 border border-pink-500/40 rounded-lg group-hover:bg-pink-500/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-chart-bar text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Laporan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'reports' }"></i>
            </button>

            <div x-show="openMenu === 'reports'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-pink-500/30 pl-3">
                <a href="{{ route('reports.attendance') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.attendance') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.attendance') ? 'bg-white/20 border border-white/30' : 'bg-pink-500/20 border border-pink-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clipboard-list text-xs {{ request()->routeIs('reports.attendance') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Attendance</span>
                </a>
                <a href="{{ route('reports.leaves') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.leaves') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.leaves') ? 'bg-white/20 border border-white/30' : 'bg-pink-500/20 border border-pink-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('reports.leaves') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Cuti</span>
                </a>
                <a href="{{ route('reports.overtimes') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.overtimes') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.overtimes') ? 'bg-white/20 border border-white/30' : 'bg-pink-500/20 border border-pink-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-clock text-xs {{ request()->routeIs('reports.overtimes') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Lembur</span>
                </a>
                <a href="{{ route('reports.worker-documents') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('reports.worker-documents') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('reports.worker-documents') ? 'bg-white/20 border border-white/30' : 'bg-pink-500/20 border border-pink-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-file-alt text-xs {{ request()->routeIs('reports.worker-documents') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Dokumen Pegawai</span>
                </a>
            </div>
        </div>
        @endif

        <!-- HR & Finance Section - Employee with permissions -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('worker-document.view') || auth()->user()->can('payroll.view') || auth()->user()->can('calendar.view'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'hr' ? '' : 'hr'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-green-600/20 border border-green-600/40 rounded-lg group-hover:bg-green-600/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-briefcase text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">HR & Keuangan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'hr' }"></i>
            </button>

            <div x-show="openMenu === 'hr'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-green-600/30 pl-3">
                @if(auth()->user()->hasRole('Employee') || auth()->user()->can('worker-document.view'))
                <a href="{{ route('employee.documents.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.documents.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.documents.*') ? 'bg-white/20 border border-white/30' : 'bg-green-600/20 border border-green-600/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-file-alt text-xs {{ request()->routeIs('employee.documents.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Dokumen Saya</span>
                </a>
                @endif

                @if(auth()->user()->hasRole('Employee') || auth()->user()->can('payroll.view'))
                <a href="{{ route('employee.payroll.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.payroll.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.payroll.*') ? 'bg-white/20 border border-white/30' : 'bg-green-600/20 border border-green-600/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-money-bill-wave text-xs {{ request()->routeIs('employee.payroll.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Payroll Saya</span>
                </a>
                @endif

                @if(auth()->user()->hasRole('Employee') || auth()->user()->can('calendar.view'))
                <a href="{{ route('employee.calendar.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.calendar.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('employee.calendar.*') ? 'bg-white/20 border border-white/30' : 'bg-green-600/20 border border-green-600/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-day text-xs {{ request()->routeIs('employee.calendar.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Kalender</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Settings Section - Admin Only -->
        @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('holiday.manage') || auth()->user()->can('role.manage') || auth()->user()->can('user.manage') || auth()->user()->can('system-settings.manage'))
        <div class="bg-white/10 border-2 border-white/20 rounded-lg hover:bg-white/15 hover:border-white/30 transition-all duration-300">
            <button @click="openMenu = openMenu === 'settings' ? '' : 'settings'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-gray-500/20 border border-gray-500/40 rounded-lg group-hover:bg-gray-500/30 group-hover:scale-110 transition-all flex-shrink-0">
                        <i class="fas fa-cog text-sm text-green-300"></i>
                    </div>
                    <span class="font-semibold text-sm text-green-100">Pengaturan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform duration-300 text-green-300 text-xs"
                   :class="{ 'rotate-180': openMenu === 'settings' }"></i>
            </button>

            <div x-show="openMenu === 'settings'"
                 x-collapse
                 class="ml-3 mt-1.5 space-y-1 border-l-2 border-gray-500/30 pl-3">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('holiday.manage'))
                <a href="{{ route('admin.holidays.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.holidays.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.holidays.*') ? 'bg-white/20 border border-white/30' : 'bg-gray-500/20 border border-gray-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-calendar-day text-xs {{ request()->routeIs('admin.holidays.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Libur Nasional</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('role.manage'))
                <a href="{{ route('admin.roles.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.roles.*') ? 'bg-white/20 border border-white/30' : 'bg-gray-500/20 border border-gray-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-user-tag text-xs {{ request()->routeIs('admin.roles.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Role</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('user.manage'))
                <a href="{{ route('admin.users.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <div class="flex items-center justify-center w-7 h-7 {{ request()->routeIs('admin.users.*') ? 'bg-white/20 border border-white/30' : 'bg-gray-500/20 border border-gray-500/40' }} rounded-lg flex-shrink-0">
                        <i class="fas fa-user-shield text-xs {{ request()->routeIs('admin.users.*') ? 'text-white' : 'text-green-300' }}"></i>
                    </div>
                    <span class="text-sm">Users</span>
                </a>
                @endif
            </div>
        </div>
        @endif

        <!-- Profile - Employee with permission -->
        @if(auth()->user()->hasRole('Employee') || auth()->user()->can('profile.view'))
            <a href="{{ route('employee.profile.show') }}" 
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg bg-white/10 border-2 border-white/20 {{ request()->routeIs('employee.profile.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 border-yellow-400' : 'hover:bg-white/15 hover:border-white/30' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.profile.*') ? 'bg-white/20 border border-white/30' : 'bg-white/10 border border-white/20' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-user text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-green-100' }}">Profile Saya</span>
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
</script>
