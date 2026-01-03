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
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto" x-data="{ 
        openMenu: '{{ request()->routeIs('admin.master.*') ? 'master' : (request()->routeIs('admin.workers.*', 'admin.attendance.*', 'admin.worker-shifts.*', 'admin.worker-documents.*') ? 'management' : (request()->routeIs('admin.leave.*', 'admin.overtime.*') ? 'approval' : (request()->routeIs('admin.roles.*', 'admin.users.*') ? 'settings' : ''))) }}' 
    }">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
            <i class="fas fa-home w-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <!-- Master Data Section -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'master' ? '' : 'master'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-database w-5"></i>
                    <span class="font-medium">Data Master</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform" 
                   :class="{ 'rotate-180': openMenu === 'master' }"></i>
            </button>
            
            <div x-show="openMenu === 'master'" 
                 x-collapse 
                 class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('religion.view'))
                <a href="{{ route('admin.master.religions.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.religions.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-mosque w-4"></i>
                    <span>Agama</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('gender.view'))
                <a href="{{ route('admin.master.genders.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.genders.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-venus-mars w-4"></i>
                    <span>Jenis Kelamin</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department.view'))
                <a href="{{ route('admin.master.departments.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.departments.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-building w-4"></i>
                    <span>Departemen</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('location.view'))
                <a href="{{ route('admin.master.locations.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.locations.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-map-marker-alt w-4"></i>
                    <span>Lokasi</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift.view'))
                <a href="{{ route('admin.master.shifts.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.shifts.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-clock w-4"></i>
                    <span>Shift</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave-type.view'))
                <a href="{{ route('admin.master.leave-types.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-calendar-alt w-4"></i>
                    <span>Tipe Cuti</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.view'))
                <a href="{{ route('admin.master.document-types.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-file-alt w-4"></i>
                    <span>Tipe Dokumen</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.view'))
                <a href="{{ route('admin.master.department-document-types.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-project-diagram w-4"></i>
                    <span>Dokumen Posisi</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Management Section -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'management' ? '' : 'management'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-tasks w-5"></i>
                    <span class="font-medium">Manajemen</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform" 
                   :class="{ 'rotate-180': openMenu === 'management' }"></i>
            </button>
            
            <div x-show="openMenu === 'management'" 
                 x-collapse 
                 class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-workers'))
                <a href="{{ route('admin.workers.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-users w-4"></i>
                    <span>Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-attendance'))
                <a href="{{ route('admin.attendance.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-clipboard-check w-4"></i>
                    <span>Absensi</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-schedules'))
                <a href="{{ route('admin.worker-shifts.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-user-clock w-4"></i>
                    <span>Jadwal Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-worker-documents'))
                <a href="{{ route('admin.worker-documents.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.worker-documents.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-file-alt w-4"></i>
                    <span>Dokumen Pegawai</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Approval Section -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'approval' ? '' : 'approval'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-check-circle w-5"></i>
                    <span class="font-medium">Persetujuan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform" 
                   :class="{ 'rotate-180': openMenu === 'approval' }"></i>
            </button>
            
            <div x-show="openMenu === 'approval'" 
                 x-collapse 
                 class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-leave-requests'))
                <a href="{{ route('admin.leave.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.leave.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-calendar-times w-4"></i>
                    <span>Cuti</span>
                    @if(isset($pendingLeaves) && $pendingLeaves > 0)
                        <span class="ml-auto bg-yellow-400 text-green-900 text-xs font-bold px-2 py-1 rounded-full">{{ $pendingLeaves }}</span>
                    @endif
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-overtimes'))
                <a href="{{ route('admin.overtime.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.overtime.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-clock w-4"></i>
                    <span>Lembur</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('Manager'))
                <a href="{{ route('manager.shift-swap-approvals.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-exchange-alt w-4"></i>
                    <span>Tukar Shift</span>
                </a>
                @endif
            </div>
        </div>

        <!-- Settings Section -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'settings' ? '' : 'settings'" 
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-cog w-5"></i>
                    <span class="font-medium">Pengaturan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform" 
                   :class="{ 'rotate-180': openMenu === 'settings' }"></i>
            </button>
            
            <div x-show="openMenu === 'settings'" 
                 x-collapse 
                 class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR'))
                <a href="{{ route('admin.holidays.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.holidays.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-calendar-day w-4"></i>
                    <span>Libur Nasional</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-roles'))
                <a href="{{ route('admin.roles.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-user-tag w-4"></i>
                    <span>Role</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('view-users'))
                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-user-shield w-4"></i>
                    <span>Users</span>
                </a>
                @endif
            </div>
        </div>
    </nav>

    <!-- User Profile -->
    <div class="p-4 border-t border-green-600">
        <div class="flex items-center space-x-3">
            @php
                $worker = auth()->user()->worker ?? null;
                $user = auth()->user();
                $avatarUrl = null;
                if ($worker && ($worker->photo_url ?? false) && Storage::disk('public')->exists($worker->photo_url)) {
                    $avatarUrl = Storage::url($worker->photo_url);
                } elseif (($user->photo ?? false) && Storage::disk('public')->exists($user->photo)) {
                    $avatarUrl = Storage::url($user->photo);
                } else {
                    $nameForAvatar = $worker->name ?? $user->username ?? $user->email ?? $user->name ?? '';
                    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($nameForAvatar);
                }
            @endphp
            <img src="{{ $avatarUrl }}"
                 alt="Avatar"
                 class="h-10 w-10 rounded-full border-2 border-yellow-400">
            <div class="flex-1">
                <p class="text-sm font-semibold">{{ $worker->name ?? auth()->user()->username ?? auth()->user()->name ?? auth()->user()->email }}</p>
                <p class="text-xs text-yellow-100">{{ auth()->user()->getRoleNames()->first() }}</p>
            </div>
        </div>
    </div>
</aside>
