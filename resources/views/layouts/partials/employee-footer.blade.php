{{-- filepath: resources/views/layouts/partials/employee-footer.blade.php --}}
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg lg:hidden">
    <div class="flex justify-around items-center h-16">
        <!-- Home -->
        <a href="{{ route('employee.dashboard') }}" class="flex flex-col items-center {{ request()->routeIs('employee.dashboard') ? '-mt-8' : 'space-y-1' }} transition duration-200">
            @if(request()->routeIs('employee.dashboard'))
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-200 ring-4 ring-yellow-300/30">
                    <i class="fas fa-home text-2xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2">Home</span>
            @else
                <i class="fas fa-home text-xl text-gray-600"></i>
                <span class="text-xs font-medium text-gray-600">Home</span>
            @endif
        </a>

        <!-- Absen -->
        <a href="{{ route('admin.attendance.index') }}" class="flex flex-col items-center {{ request()->routeIs('admin.attendance.*') ? '-mt-8' : 'space-y-1' }} transition duration-200">
            @if(request()->routeIs('admin.attendance.*'))
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-200 ring-4 ring-yellow-300/30">
                    <i class="fas fa-camera text-2xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2">Absen</span>
            @else
                <i class="fas fa-camera text-xl text-gray-600"></i>
                <span class="text-xs font-medium text-gray-600">Absen</span>
            @endif
        </a>

        <!-- Jadwal -->
        <a href="{{ route('admin.worker-shifts.index') }}" class="flex flex-col items-center {{ request()->routeIs('admin.worker-shifts.*') ? '-mt-8' : 'space-y-1' }} transition duration-200">
            @if(request()->routeIs('admin.worker-shifts.*'))
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-200 ring-4 ring-yellow-300/30">
                    <i class="fas fa-calendar-alt text-2xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2">Jadwal</span>
            @else
                <i class="fas fa-calendar-alt text-xl text-gray-600"></i>
                <span class="text-xs font-medium text-gray-600">Jadwal</span>
            @endif
        </a>

        <!-- Profile -->
        <a href="{{ route('profile.show') }}" class="flex flex-col items-center {{ request()->routeIs('profile.*') ? '-mt-8' : 'space-y-1' }} transition duration-200">
            @if(request()->routeIs('profile.*'))
                <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-200 ring-4 ring-yellow-300/30">
                    <i class="fas fa-user text-2xl"></i>
                </div>
                <span class="text-xs font-medium text-gray-600 mt-2">Profile</span>
            @else
                <i class="fas fa-user text-xl text-gray-600"></i>
                <span class="text-xs font-medium text-gray-600">Profile</span>
            @endif
        </a>
    </div>
</nav>
