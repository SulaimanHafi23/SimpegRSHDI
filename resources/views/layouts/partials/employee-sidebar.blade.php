{{-- filepath: resources/views/layouts/partials/employee-sidebar.blade.php --}}
<aside class="fixed top-0 left-0 z-40 w-64 h-screen bg-white border-r border-gray-200 shadow-xl transition-transform -translate-x-full lg:translate-x-0" id="employee-sidebar">
    <!-- Header -->
    <div class="p-6 border-b border-gray-200 bg-gradient-to-r from-green-600 to-green-700">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3 text-white">
                <div class="h-10 w-10 bg-white rounded-lg flex items-center justify-center">
                    <i class="fas fa-user-circle text-green-700 text-xl"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold">Employee Panel</h1>
                    <p class="text-xs text-yellow-100">{{ auth()->user()->worker->name ?? auth()->user()->name }}</p>
                </div>
            </div>
            <!-- Close Button for Mobile -->
            <button onclick="toggleEmployeeSidebar()" class="lg:hidden p-2 text-white hover:bg-green-600 rounded-lg transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('employee.dashboard') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
            <i class="fas fa-home w-5"></i>
            <span>Dashboard</span>
        </a>

        <!-- Attendance Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">KEHADIRAN</p>
            
            <a href="{{ route('employee.attendance.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.attendance.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-clipboard-check w-5"></i>
                <span>Absensi Saya</span>
            </a>

            <a href="{{ route('employee.shifts.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.shifts.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Jadwal Kerja</span>
            </a>
        </div>

        <!-- Request Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">PENGAJUAN</p>
            
            <a href="{{ route('employee.leaves.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.leaves.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-calendar-times w-5"></i>
                <span>Cuti Saya</span>
            </a>

            <a href="{{ route('employee.overtimes.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.overtimes.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-clock w-5"></i>
                <span>Lembur Saya</span>
            </a>

            <a href="{{ route('employee.documents.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.documents.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-file-alt w-5"></i>
                <span>Dokumen Saya</span>
            </a>

            <a href="{{ route('employee.calendar.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.calendar.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-calendar-alt w-5"></i>
                <span>Kalender Cuti & Lembur</span>
            </a>
        </div>

        <!-- Profile Section -->
        <div class="pt-4">
            <p class="px-4 text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">AKUN</p>
            
            <a href="{{ route('employee.profile.show') }}" class="flex items-center space-x-3 px-4 py-3 rounded-lg {{ request()->routeIs('employee.profile.*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }} transition duration-200">
                <i class="fas fa-user w-5"></i>
                <span>Profile Saya</span>
            </a>
        </div>
    </nav>

    <!-- Logout Button -->
    <div class="p-4 border-t border-gray-200">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
