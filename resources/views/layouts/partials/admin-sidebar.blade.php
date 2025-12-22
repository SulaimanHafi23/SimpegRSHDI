{{-- filepath: resources/views/layouts/partials/admin-sidebar.blade.php --}}
<aside class="fixed top-0 left-0 z-40 w-64 h-screen bg-gradient-to-b from-green-700 via-green-800 to-green-900 text-white flex flex-col shadow-2xl transition-transform -translate-x-full lg:translate-x-0" id="admin-sidebar">
    <!-- Logo -->
    <div class="p-6 border-b border-green-600">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-hospital text-green-700 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold">SIMPEGRS HDI</h1>
                    <p class="text-xs text-yellow-100">Admin Panel</p>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-green-600 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
            <i class="fas fa-home w-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Master Data Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-yellow-200 uppercase tracking-wider mb-2">DATA MASTER</p>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('religion.view'))
            <a href="{{ route('admin.master.religions.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.religions.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-mosque w-5"></i>
                <span>Agama</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('gender.view'))
            <a href="{{ route('admin.master.genders.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.genders.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-venus-mars w-5"></i>
                <span>Jenis Kelamin</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department.view'))
            <a href="{{ route('admin.master.departments.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.departments.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-building w-5"></i>
                <span>Departemen</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('location.view'))
            <a href="{{ route('admin.master.locations.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.locations.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-map-marker-alt w-5"></i>
                <span>Lokasi</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift.view'))
            <a href="{{ route('admin.master.shifts.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.shifts.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-clock w-5"></i>
                <span>Shift</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave-type.view'))
            <a href="{{ route('admin.master.leave-types.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Tipe Cuti</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.view'))
            <a href="{{ route('admin.master.document-types.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-file-alt w-5"></i>
                <span>Tipe Dokumen</span>
            </a>
            @endif
            @if(auth()->user()->hasRole(['Super Admin', 'HR']))
            <a href="{{ route('admin.holidays.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.holidays.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-calendar-day w-5"></i>
                <span>Libur Nasional</span>
            </a>
            @endif
        </div>

        <!-- Management Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-yellow-200 uppercase tracking-wider mb-2">MANAJEMEN</p>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-workers'))
            <a href="{{ route('admin.workers.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-users w-5"></i>
                <span>Pegawai</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-attendance'))
            <a href="{{ route('admin.attendance.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-clipboard-check w-5"></i>
                <span>Absensi</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-schedules'))
            <a href="{{ route('admin.worker-shifts.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-user-clock w-5"></i>
                <span>Jadwal Pegawai</span>
            </a>
            @endif
        </div>

        <!-- Approval Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-yellow-200 uppercase tracking-wider mb-2">PERSETUJUAN</p>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-leave-requests'))
            <a href="{{ route('admin.leave.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.leave.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-calendar-times w-5"></i>
                <span>Cuti</span>
                @if(isset($pendingLeaves) && $pendingLeaves > 0)
                    <span class="ml-auto bg-yellow-400 text-green-900 text-xs font-bold px-2 py-1 rounded-full">{{ $pendingLeaves }}</span>
                @endif
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-overtimes'))
            <a href="{{ route('admin.overtime.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.overtime.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-clock w-5"></i>
                <span>Lembur</span>
            </a>
            @endif
        </div>

        <!-- Settings Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-yellow-200 uppercase tracking-wider mb-2">PENGATURAN</p>
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-roles'))
            <a href="{{ route('admin.roles.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-user-tag w-5"></i>
                <span>Role</span>
            </a>
            @endif
            @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-users'))
            <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
                <i class="fas fa-user-shield w-5"></i>
                <span>Users</span>
            </a>
            @endif
        </div>
    </nav>

    <!-- User Profile -->
    <div class="p-4 border-t border-green-600">
        <div class="flex items-center space-x-3">
            <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                 alt="Avatar"
                 class="h-10 w-10 rounded-full border-2 border-yellow-400">
            <div class="flex-1">
                <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="text-xs text-yellow-100">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
        </div>
    </div>
</aside>
