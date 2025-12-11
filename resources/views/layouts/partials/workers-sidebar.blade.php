{{-- filepath: resources/views/layouts/partials/workers-sidebar.blade.php --}}
<!-- Sidebar for Desktop/Tablet (hidden on mobile) -->
<aside class="hidden lg:block fixed left-0 top-0 h-screen w-64 bg-white border-r border-gray-200 shadow-sm z-30 pt-16">
    <div class="flex flex-col h-full">
        <!-- User Info -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center space-x-3">
                @if(auth()->user()->photo)
                    <img src="{{ asset(auth()->user()->photo) }}" alt="{{ auth()->user()->name }}" 
                         class="w-12 h-12 rounded-full object-cover border-2 border-green-500">
                @else
                    <div class="w-12 h-12 rounded-full bg-green-500 flex items-center justify-center text-white text-lg font-bold">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500 truncate">{{ auth()->user()->worker->position->name ?? 'Pegawai' }}</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-1">
            <a href="{{ route('workers.dashboard') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.dashboard') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-home w-5 text-center"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('workers.attendance.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.attendance*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-camera w-5 text-center"></i>
                <span>Absensi</span>
            </a>

            <a href="{{ route('workers.schedule') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.schedule') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-alt w-5 text-center"></i>
                <span>Jadwal Shift</span>
            </a>

            <a href="{{ route('workers.leaves.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.leaves*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-calendar-plus w-5 text-center"></i>
                <span>Cuti</span>
            </a>

            <a href="{{ route('workers.overtimes.index') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.overtimes*') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-clock w-5 text-center"></i>
                <span>Lembur</span>
            </a>

            <a href="{{ route('workers.documents') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.documents') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-file-alt w-5 text-center"></i>
                <span>Dokumen</span>
            </a>

            <div class="border-t border-gray-200 my-2"></div>

            <a href="{{ route('workers.profile') }}" 
               class="flex items-center space-x-3 px-4 py-3 rounded-lg transition {{ request()->routeIs('workers.profile') ? 'bg-green-50 text-green-700 font-semibold' : 'text-gray-700 hover:bg-gray-50' }}">
                <i class="fas fa-user w-5 text-center"></i>
                <span>Profile</span>
            </a>
        </nav>

        <!-- Logout Button -->
        <div class="p-4 border-t border-gray-200">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </div>
</aside>
