{{-- filepath: resources/views/layouts/partials/employee-sidebar.blade.php --}}
<aside x-data="{ openMenu: '{{ request()->routeIs('employee.attendance.*', 'employee.shifts.*', 'employee.shift-swaps.*') ? 'attendance' : (request()->routeIs('employee.leaves.*', 'employee.overtimes.*', 'employee.business-trips.*') ? 'requests' : (request()->routeIs('employee.documents.*', 'employee.payroll.*', 'employee.calendar.*') ? 'hr' : '')) }}' }" 
    class="fixed top-16 lg:top-0 left-0 z-40 w-64 h-screen bg-gradient-to-b from-green-700 via-green-800 to-green-900 text-white shadow-2xl transition-transform -translate-x-full lg:translate-x-0" 
       id="employee-sidebar">
    
    <!-- Modern Header with Glassmorphism -->
    <div class="relative p-5 bg-white/10 backdrop-blur-md border-b border-white/20">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="relative flex-shrink-0">
                    <div class="h-11 w-11 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-tie text-white text-lg"></i>
                    </div>
                    <span class="absolute -bottom-0.5 -right-0.5 h-3 w-3 bg-green-400 border-2 border-green-900 rounded-full animate-pulse"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-xs text-yellow-400 font-semibold mb-0.5">Employee Portal</p>
                    <h1 class="text-sm text-white font-medium truncate">{{ auth()->user()->worker->name ?? auth()->user()->name }}</h1>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button onclick="toggleEmployeeSidebar()" 
                    class="lg:hidden flex-shrink-0 p-2 text-white/80 hover:text-white hover:bg-white/10 rounded-xl transition-all duration-200">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Navigation with Smooth Scrolling -->
    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
        
        <!-- Dashboard - Standalone with Icon Animation (no dropdown arrow) -->
        <a href="{{ route('employee.dashboard') }}" 
           class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg {{ request()->routeIs('employee.dashboard') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'hover:bg-white/10' }} transition-all duration-300">
            <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.dashboard') ? 'bg-white/20' : 'bg-white/5' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                <i class="fas fa-th-large text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-green-300' }}"></i>
            </div>
            <span class="font-medium text-sm {{ request()->routeIs('employee.dashboard') ? 'text-white' : 'text-green-100' }}">Dashboard</span>
        </a>

        <!-- Attendance Section with Dropdown -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'attendance' ? '' : 'attendance'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all duration-300 group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-green-500/20 rounded-lg group-hover:bg-green-500/30 group-hover:scale-110 transition-all flex-shrink-0">
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
                    <i class="fas fa-clipboard-check text-xs {{ request()->routeIs('employee.attendance.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Absensi Saya</span>
                </a>

                <a href="{{ route('employee.shifts.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.shifts.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-calendar-alt text-xs {{ request()->routeIs('employee.shifts.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Jadwal Kerja</span>
                </a>

                <a href="{{ route('employee.shift-swaps.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.shift-swaps.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-exchange-alt text-xs {{ request()->routeIs('employee.shift-swaps.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Tukar Shift</span>
                </a>
            </div>
        </div>

        <!-- Request Section with Dropdown -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'requests' ? '' : 'requests'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all duration-300 group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-green-400/20 rounded-lg group-hover:bg-green-400/30 group-hover:scale-110 transition-all flex-shrink-0">
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
                    <i class="fas fa-calendar-times text-xs {{ request()->routeIs('employee.leaves.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Cuti Saya</span>
                </a>

                <a href="{{ route('employee.overtimes.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.overtimes.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-clock text-xs {{ request()->routeIs('employee.overtimes.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Lembur Saya</span>
                </a>

                <a href="{{ route('employee.business-trips.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.business-trips.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-plane-departure text-xs {{ request()->routeIs('employee.business-trips.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Perjalanan Dinas</span>
                </a>
            </div>
        </div>

        <!-- HR & Finance Section with Dropdown -->
        <div class="pt-2">
            <button @click="openMenu = openMenu === 'hr' ? '' : 'hr'"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg hover:bg-white/10 transition-all duration-300 group">
                <div class="flex items-center space-x-2.5">
                    <div class="flex items-center justify-center w-9 h-9 bg-green-600/20 rounded-lg group-hover:bg-green-600/30 group-hover:scale-110 transition-all flex-shrink-0">
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
                <a href="{{ route('employee.documents.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.documents.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-file-alt text-xs {{ request()->routeIs('employee.documents.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Dokumen Saya</span>
                </a>

                <a href="{{ route('employee.payroll.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.payroll.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-money-bill-wave text-xs {{ request()->routeIs('employee.payroll.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Payroll Saya</span>
                </a>

                <a href="{{ route('employee.calendar.index') }}" 
                   class="flex items-center space-x-2.5 px-3 py-2 rounded-lg {{ request()->routeIs('employee.calendar.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50 text-white font-medium' : 'text-green-200 hover:bg-white/5 hover:text-white' }} transition-all duration-200">
                    <i class="fas fa-calendar-day text-xs {{ request()->routeIs('employee.calendar.*') ? 'text-white' : '' }}"></i>
                    <span class="text-sm">Kalender</span>
                </a>
            </div>
        </div>

        <!-- Profile - Standalone -->
        <div class="pt-2">
            <a href="{{ route('employee.profile.show') }}" 
               class="group flex items-center space-x-2.5 px-3 py-2.5 rounded-lg {{ request()->routeIs('employee.profile.*') ? 'bg-gradient-to-r from-yellow-500 to-orange-500 shadow-lg shadow-yellow-500/50' : 'hover:bg-white/10' }} transition-all duration-300">
                <div class="flex items-center justify-center w-9 h-9 {{ request()->routeIs('employee.profile.*') ? 'bg-white/20' : 'bg-white/5' }} rounded-lg group-hover:scale-110 transition-transform flex-shrink-0">
                    <i class="fas fa-user text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-green-300' }}"></i>
                </div>
                <span class="font-medium text-sm {{ request()->routeIs('employee.profile.*') ? 'text-white' : 'text-green-100' }}">Profile Saya</span>
            </a>
        </div>

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
