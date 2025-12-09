{{-- filepath: resources/views/layouts/partials/admin-sidebar.blade.php --}}
<aside class="w-64 bg-gradient-to-b from-primary-800 to-primary-900 text-white flex flex-col shadow-2xl">
    <!-- Logo -->
    <div class="p-6 border-b border-primary-700">
        <div class="flex items-center space-x-3">
            <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center">
                <i class="fas fa-hospital text-primary-700 text-xl"></i>
            </div>
            <div>
                <h1 class="text-lg font-bold">SIMPEGRS HDI</h1>
                <p class="text-xs text-primary-200">Admin Panel</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-primary-700 shadow-lg' : 'hover:bg-primary-700/50' }} transition duration-200">
            <i class="fas fa-home w-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Master Data -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wider mb-2">Master Data</p>
            <a href="{{ route('admin.master.religions.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.religions.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-mosque w-5"></i>
                <span>Agama</span>
            </a>
            <a href="{{ route('admin.master.genders.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.genders.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-venus-mars w-5"></i>
                <span>Jenis Kelamin</span>
            </a>
            <a href="{{ route('admin.master.positions.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.positions.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-briefcase w-5"></i>
                <span>Jabatan</span>
            </a>
            <a href="{{ route('admin.master.locations.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.locations.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-map-marker-alt w-5"></i>
                <span>Lokasi</span>
            </a>
            <a href="{{ route('admin.master.document-types.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-file-alt w-5"></i>
                <span>Tipe Dokumen</span>
            </a>
        </div>

        <!-- Workers -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wider mb-2">Manajemen</p>
            <a href="{{ route('admin.workers.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-users w-5"></i>
                <span>Pegawai</span>
            </a>
            <a href="{{ route('admin.attendance.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-clipboard-check w-5"></i>
                <span>Absensi</span>
            </a>
        </div>

        <!-- Approvals -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wider mb-2">Persetujuan</p>
            <a href="{{ route('admin.approvals.leaves.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.approvals.leaves.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-calendar-times w-5"></i>
                <span>Cuti</span>
                @if(isset($pendingLeaves) && $pendingLeaves > 0)
                    <span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">{{ $pendingLeaves }}</span>
                @endif
            </a>
            <a href="{{ route('admin.approvals.overtimes.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.approvals.overtimes.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-clock w-5"></i>
                <span>Lembur</span>
            </a>
        </div>

        <!-- Settings -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-primary-300 uppercase tracking-wider mb-2">Pengaturan</p>
            <a href="{{ route('admin.settings.shifts.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.settings.shifts.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-business-time w-5"></i>
                <span>Shift</span>
            </a>
            <a href="{{ route('admin.settings.roles.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.settings.roles.*') ? 'bg-primary-700' : 'hover:bg-primary-700/50' }} transition duration-200">
                <i class="fas fa-user-shield w-5"></i>
                <span>Roles & Permissions</span>
            </a>
        </div>
    </nav>

    <!-- User Profile -->
    <div class="p-4 border-t border-primary-700">
        <div class="flex items-center space-x-3">
            <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name) }}"
                 alt="Avatar"
                 class="h-10 w-10 rounded-full border-2 border-primary-500">
            <div class="flex-1">
                <p class="text-sm font-semibold">{{ auth()->user()->name }}</p>
                <p class="text-xs text-primary-300">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
        </div>
    </div>
</aside>
