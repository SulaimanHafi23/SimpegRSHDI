{{-- filepath: resources/views/layouts/partials/admin-sidebar.blade.php --}}
<aside class="fixed top-0 left-0 z-40 w-64 h-screen text-white flex flex-col shadow-2xl transition-transform -translate-x-full lg:translate-x-0 overflow-hidden" style="background:linear-gradient(160deg,#0a3d1f 0%,#0d2b17 100%)" id="admin-sidebar">
    <!-- Gold radial glow -->
    <div class="pointer-events-none absolute -top-16 -right-16 w-64 h-64 rounded-full opacity-30" style="background:radial-gradient(circle,#f5a623 0%,transparent 70%);filter:blur(40px)"></div>
    <!-- Logo -->
    <div class="relative p-6 border-b border-[#f5a623]/20" style="background:rgba(245,166,35,0.06);backdrop-filter:blur(12px)">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-hospital text-green-700 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold">SIDIA</h1>
                    <p class="text-xs text-yellow-100">Sistem Informasi Darlan Ismail dan Absensi</p>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button onclick="toggleSidebar()" class="lg:hidden p-2 hover:bg-green-600 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto" x-data="{
        openMenu: '{{
            request()->routeIs('admin.master.*') ? 'master' :
            (request()->routeIs('admin.workers.*', 'admin.attendance.*', 'admin.worker-shifts.*', 'admin.worker-documents.*') ? 'managerial' :
            (request()->routeIs('admin.leave.*', 'approvals.*', 'manager.shift-swap-approvals.*') ? 'approval' :
            (request()->routeIs('admin.roles.*', 'admin.users.*', 'admin.holidays.*', 'admin.audit-logs.*') ? 'admin' : '')))
        }}'
    }">
        <!-- Dashboard -->
        <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-yellow-500 text-green-900 shadow-lg' : 'hover:bg-green-600' }} transition duration-200">
            <i class="fas fa-home w-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        {{-- ── 1. DATA MASTER ───────────────────────────────────────── --}}
        <div class="pt-3">
            <div class="flex items-center gap-2 px-2 py-1.5 mb-1 rounded-md bg-yellow-400/10 border border-yellow-400/20">
                <i class="fas fa-database text-xs text-yellow-300"></i>
                <span class="text-[10px] font-bold text-yellow-300 tracking-widest uppercase">Data Master</span>
            </div>
            <button @click="openMenu = openMenu === 'master' ? '' : 'master'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-layer-group w-5"></i>
                    <span class="font-medium">Referensi Sistem</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform"
                   :class="{ 'rotate-180': openMenu === 'master' }"></i>
            </button>
            <div x-show="openMenu === 'master'" x-collapse class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department.manage'))
                <a href="{{ route('admin.master.departments.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.departments.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-building w-4"></i><span>Departemen</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift.manage'))
                <a href="{{ route('admin.master.shifts.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.shifts.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-clock w-4"></i><span>Shift Kerja</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave-type.manage'))
                <a href="{{ route('admin.master.leave-types.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.leave-types.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-calendar-alt w-4"></i><span>Jenis Cuti</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('document-type.manage'))
                <a href="{{ route('admin.master.document-types.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.document-types.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-file-alt w-4"></i><span>Jenis Dokumen</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('department-document-type.manage'))
                <a href="{{ route('admin.master.department-document-types.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.master.department-document-types.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-project-diagram w-4"></i><span>Dokumen Posisi</span>
                </a>
                @endif
            </div>
        </div>

        {{-- ── 2. MANAJERIAL ────────────────────────────────────────── --}}
        <div class="pt-3">
            <div class="flex items-center gap-2 px-2 py-1.5 mb-1 rounded-md bg-blue-400/10 border border-blue-400/20">
                <i class="fas fa-briefcase text-xs text-blue-300"></i>
                <span class="text-[10px] font-bold text-blue-300 tracking-widest uppercase">Manajerial</span>
            </div>
            <button @click="openMenu = openMenu === 'managerial' ? '' : 'managerial'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-users-cog w-5"></i>
                    <span class="font-medium">Kelola Pegawai</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform"
                   :class="{ 'rotate-180': openMenu === 'managerial' }"></i>
            </button>
            <div x-show="openMenu === 'managerial'" x-collapse class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker.manage'))
                <a href="{{ route('admin.workers.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.workers.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-users w-4"></i><span>Data Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('attendance.manage'))
                <a href="{{ route('admin.attendance.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.attendance.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-clipboard-check w-4"></i><span>Rekap Absensi</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('schedule.manage'))
                <a href="{{ route('admin.worker-shifts.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.worker-shifts.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-user-clock w-4"></i><span>Jadwal Pegawai</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('worker-document.manage'))
                <a href="{{ route('admin.worker-documents.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.worker-documents.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-folder-open w-4"></i><span>Berkas Pegawai</span>
                </a>
                @endif
            </div>
        </div>

        {{-- ── 3. PERSETUJUAN ───────────────────────────────────────── --}}
        <div class="pt-3">
            <div class="flex items-center gap-2 px-2 py-1.5 mb-1 rounded-md bg-orange-400/10 border border-orange-400/20">
                <i class="fas fa-check-double text-xs text-orange-300"></i>
                <span class="text-[10px] font-bold text-orange-300 tracking-widest uppercase">Persetujuan</span>
            </div>
            <button @click="openMenu = openMenu === 'approval' ? '' : 'approval'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-tasks w-5"></i>
                    <span class="font-medium">Kelola Pengajuan</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform"
                   :class="{ 'rotate-180': openMenu === 'approval' }"></i>
            </button>
            <div x-show="openMenu === 'approval'" x-collapse class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('leave.manage'))
                <a href="{{ route('admin.leave.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.leave.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-calendar-times w-4"></i>
                    <span>Permohonan Cuti</span>
                    @if(isset($pendingLeaves) && $pendingLeaves > 0)
                        <span class="ml-auto bg-yellow-400 text-green-900 text-xs font-bold px-2 py-1 rounded-full">{{ $pendingLeaves }}</span>
                    @endif
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('shift-swap.manage'))
                <a href="{{ route('manager.shift-swap-approvals.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('manager.shift-swap-approvals.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-exchange-alt w-4"></i><span>Tukar Shift</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->hasRole('HR'))
                <a href="{{ route('approvals.business-trips.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('approvals.business-trips.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-plane-departure w-4"></i><span>Perjalanan Dinas</span>
                </a>
                @endif
            </div>
        </div>

        {{-- ── 4. ADMINISTRASI ──────────────────────────────────────── --}}
        <div class="pt-3">
            <div class="flex items-center gap-2 px-2 py-1.5 mb-1 rounded-md bg-slate-400/10 border border-slate-400/20">
                <i class="fas fa-shield-alt text-xs text-slate-300"></i>
                <span class="text-[10px] font-bold text-slate-300 tracking-widest uppercase">Administrasi</span>
            </div>
            <button @click="openMenu = openMenu === 'admin' ? '' : 'admin'"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg hover:bg-green-600 transition duration-200">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-cog w-5"></i>
                    <span class="font-medium">Pengaturan Sistem</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform"
                   :class="{ 'rotate-180': openMenu === 'admin' }"></i>
            </button>
            <div x-show="openMenu === 'admin'" x-collapse class="ml-4 mt-2 space-y-1">
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('holiday.manage'))
                <a href="{{ route('admin.holidays.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.holidays.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-calendar-day w-4"></i><span>Libur Nasional</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('role.manage'))
                <a href="{{ route('admin.roles.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.roles.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-user-tag w-4"></i><span>Hak Akses (Role)</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin') || auth()->user()->can('user.manage'))
                <a href="{{ route('admin.users.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-user-shield w-4"></i><span>Akun Pengguna</span>
                </a>
                @endif
                @if(auth()->user()->hasRole('Super Admin'))
                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg {{ request()->routeIs('admin.audit-logs.*') ? 'bg-yellow-500 text-green-900' : 'hover:bg-green-600' }} transition duration-200 text-sm">
                    <i class="fas fa-history w-4"></i><span>Audit Log</span>
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
                if ($worker && ($worker->photo_url ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($worker->photo_url)) {
                    $avatarUrl = \Illuminate\Support\Facades\Storage::url($worker->photo_url);
                } elseif (($user->photo ?? false) && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->photo)) {
                    $avatarUrl = \Illuminate\Support\Facades\Storage::url($user->photo);
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
