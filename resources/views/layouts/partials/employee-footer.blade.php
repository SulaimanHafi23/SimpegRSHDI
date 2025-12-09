{{-- filepath: resources/views/layouts/partials/employee-footer.blade.php --}}
<nav class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 shadow-lg">
    <div class="flex justify-around items-center h-16">
        <a href="{{ route('employee.dashboard') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('employee.dashboard') ? 'text-primary-600' : 'text-gray-600' }} hover:text-primary-600 transition duration-200">
            <i class="fas fa-home text-xl"></i>
            <span class="text-xs font-medium">Home</span>
        </a>

        <a href="{{ route('employee.attendance.create') }}" class="flex flex-col items-center -mt-8">
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 text-white p-4 rounded-full shadow-lg hover:shadow-xl transition duration-200">
                <i class="fas fa-camera text-2xl"></i>
            </div>
            <span class="text-xs font-medium text-gray-600 mt-2">Absen</span>
        </a>

        <a href="{{ route('employee.schedule.index') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('employee.schedule.*') ? 'text-primary-600' : 'text-gray-600' }} hover:text-primary-600 transition duration-200">
            <i class="fas fa-calendar-alt text-xl"></i>
            <span class="text-xs font-medium">Jadwal</span>
        </a>

        <a href="{{ route('employee.profile.index') }}" class="flex flex-col items-center space-y-1 {{ request()->routeIs('employee.profile.*') ? 'text-primary-600' : 'text-gray-600' }} hover:text-primary-600 transition duration-200">
            <i class="fas fa-user text-xl"></i>
            <span class="text-xs font-medium">Profile</span>
        </a>
    </div>
</nav>
