@extends('layouts.employee')

@section('title', 'Kalender Saya')

@section('content')
<div x-data="calendarApp()" x-init="initCalendar()">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-calendar-alt mr-3 text-green-600"></i>
                Kalender Saya
            </h1>
            <p class="mt-1 text-sm text-gray-600">Lihat jadwal cuti, lembur, dan perjalanan dinas Anda</p>
        </div>
    </div>

    <!-- Legend -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Keterangan:</h3>
        <div class="flex flex-wrap gap-4">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded" style="background-color: #dc2626;"></div>
                <span class="text-sm text-gray-600">🇮🇩 Libur Nasional</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded" style="background-color: #10b981;"></div>
                <span class="text-sm text-gray-600">Cuti Disetujui</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded" style="background-color: #3b82f6;"></div>
                <span class="text-sm text-gray-600">Lembur Disetujui</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded" style="background-color: #8b5cf6;"></div>
                <span class="text-sm text-gray-600">✈️ Perjalanan Dinas</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded" style="background-color: #f59e0b;"></div>
                <span class="text-sm text-gray-600">Menunggu Persetujuan</span>
            </div>
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 rounded" style="background-color: #ef4444;"></div>
                <span class="text-sm text-gray-600">Ditolak</span>
            </div>
        </div>
    </div>

    <!-- Calendar Container -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <!-- Calendar Navigation -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-4 flex items-center justify-between">
            <button @click="previousMonth()" class="p-2 hover:bg-green-500 rounded-lg transition">
                <i class="fas fa-chevron-left"></i>
            </button>
            <h2 class="text-lg font-bold" x-text="currentMonthYear"></h2>
            <button @click="nextMonth()" class="p-2 hover:bg-green-500 rounded-lg transition">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        <!-- Calendar Grid -->
        <p class="text-xs text-gray-400 italic px-4 mb-1 md:hidden"><i class="fas fa-arrows-alt-h mr-1"></i>Geser untuk melihat kalender lengkap</p>
        <div class="p-4 overflow-x-auto scroll-smooth -mx-4 px-4 md:mx-0 md:px-4">
            <div class="min-w-[600px]">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 gap-2 mb-2">
                    <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']">
                        <div class="text-center text-sm font-semibold text-gray-600 py-2" x-text="day"></div>
                    </template>
                </div>

                <!-- Calendar Days -->
                <div class="grid grid-cols-7 gap-2">
                    <template x-for="(day, index) in calendarDays" :key="index">
                        <div :class="getDayClass(day)"
                             @click="day.date ? showDayEvents(day) : null"
                             class="min-h-24 border rounded-lg p-2 relative">
                            <!-- Date Number -->
                            <div class="text-sm font-semibold mb-1"
                                 :class="day.isToday ? 'text-white' : (day.isCurrentMonth ? 'text-gray-700' : 'text-gray-400')"
                                 x-text="day.day"></div>

                            <!-- Events for this day -->
                            <template x-if="day.events && day.events.length > 0">
                                <div class="space-y-1">
                                    <template x-for="event in day.events.slice(0, 2)" :key="event.id">
                                        <div class="text-xs px-2 py-1 rounded truncate text-white"
                                             :style="'background-color: ' + event.color"
                                             x-text="event.title"></div>
                                    </template>
                                    <template x-if="day.events.length > 2">
                                        <div class="text-xs text-gray-500 font-semibold">
                                            +<span x-text="day.events.length - 2"></span> lainnya
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- Event Detail Modal -->
    <div x-show="showModal"
         @click="showModal = false"
         x-transition
         class="fixed inset-0 z-50 flex items-center justify-center p-4 backdrop-blur-sm bg-white/30">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-gray-900" x-text="selectedDate"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <template x-if="selectedEvents.length === 0">
                <p class="text-gray-500 text-center py-4">Tidak ada event pada tanggal ini</p>
            </template>

            <div class="space-y-3">
                <template x-for="event in selectedEvents" :key="event.id">
                    <div class="border rounded-lg p-4" :style="'border-left: 4px solid ' + event.color">
                        <div class="flex items-start justify-between mb-2">
                            <h4 class="font-semibold text-gray-900" x-text="event.title"></h4>
                            <span class="text-xs px-2 py-1 rounded-full text-white"
                                  :style="'background-color: ' + event.color"
                                  x-text="getStatusText(event.status)"></span>
                        </div>
                        <p class="text-sm text-gray-600 mb-2" x-text="event.description"></p>
                        <template x-if="event.type === 'holiday'">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-flag mr-1"></i>
                                Libur Nasional Indonesia 🇮🇩
                            </p>
                        </template>
                        <template x-if="event.type === 'leave'">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-calendar-day mr-1"></i>
                                <span x-text="event.days"></span> hari
                            </p>
                        </template>
                        <template x-if="event.type === 'overtime'">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-clock mr-1"></i>
                                <span x-text="event.hours"></span> jam
                            </p>
                        </template>
                        <template x-if="event.type === 'business-trip'">
                            <p class="text-xs text-gray-500">
                                <i class="fas fa-plane-departure mr-1"></i>
                                <span x-text="'Tujuan: ' + event.destination"></span>
                            </p>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function calendarApp() {
    return {
        currentDate: new Date(),
        currentMonthYear: '',
        calendarDays: [],
        events: [],
        showModal: false,
        selectedDate: '',
        selectedEvents: [],

        initCalendar() {
            this.updateCalendar();
            this.loadEvents();
        },

        updateCalendar() {
            const year = this.currentDate.getFullYear();
            const month = this.currentDate.getMonth(); // 0-indexed (0=Jan, 11=Dec)

            // Update month/year display
            this.currentMonthYear = this.currentDate.toLocaleDateString('id-ID', {
                month: 'long',
                year: 'numeric'
            });

            // Get first day of month and last day
            const firstDay = new Date(year, month, 1);
            const lastDay = new Date(year, month + 1, 0);

            // Get day of week for first day (0 = Sunday)
            const startingDayOfWeek = firstDay.getDay();

            // Calculate days to show from previous month
            const daysFromPrevMonth = startingDayOfWeek;

            // Calculate total cells needed
            const totalDays = lastDay.getDate();
            const totalCells = Math.ceil((daysFromPrevMonth + totalDays) / 7) * 7;

            this.calendarDays = [];

            // Previous month days
            const prevMonthLastDay = new Date(year, month, 0).getDate();
            for (let i = daysFromPrevMonth - 1; i >= 0; i--) {
                const dayNum = prevMonthLastDay - i;
                const date = new Date(year, month - 1, dayNum);
                this.calendarDays.push({
                    day: dayNum,
                    date: date,
                    isCurrentMonth: false,
                    isToday: false,
                    events: []
                });
            }

            // Current month days
            const today = new Date();
            today.setHours(0, 0, 0, 0); // Reset time for comparison

            for (let i = 1; i <= totalDays; i++) {
                const date = new Date(year, month, i);
                date.setHours(0, 0, 0, 0); // Reset time for comparison

                this.calendarDays.push({
                    day: i,
                    date: date,
                    isCurrentMonth: true,
                    isToday: this.isSameDay(date, today),
                    events: []
                });
            }

            // Next month days
            const remainingCells = totalCells - this.calendarDays.length;
            for (let i = 1; i <= remainingCells; i++) {
                const date = new Date(year, month + 1, i);
                date.setHours(0, 0, 0, 0); // Reset time for comparison

                this.calendarDays.push({
                    day: i,
                    date: date,
                    isCurrentMonth: false,
                    isToday: false,
                    events: []
                });
            }

            this.assignEventsToCalendar();
        },

        loadEvents() {
            const start = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1)
                .toISOString().split('T')[0];
            const end = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0)
                .toISOString().split('T')[0];

            // Add cache buster to prevent browser caching
            const cacheBuster = new Date().getTime();
            fetch(`{{ route('employee.calendar.events') }}?start=${start}&end=${end}&_=${cacheBuster}`)
                .then(res => res.json())
                .then(data => {
                    console.log('Loaded events:', data); // Debug log
                    this.events = data;
                    this.assignEventsToCalendar();
                })
                .catch(err => console.error('Error loading events:', err));
        },

        assignEventsToCalendar() {
            this.calendarDays.forEach(day => {
                if (!day.date) return;

                // Reset day time to midnight for accurate comparison
                const dayDate = new Date(day.date);
                dayDate.setHours(0, 0, 0, 0);

                day.events = this.events.filter(event => {
                    // Parse event dates and reset to midnight
                    const eventStart = new Date(event.start);
                    eventStart.setHours(0, 0, 0, 0);

                    const eventEnd = new Date(event.end);
                    eventEnd.setHours(0, 0, 0, 0);

                    // Check if day matches event date range
                    return dayDate >= eventStart && dayDate < eventEnd;
                });
            });
        },

        previousMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() - 1, 1);
            this.updateCalendar();
            this.loadEvents();
        },

        nextMonth() {
            this.currentDate = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 1);
            this.updateCalendar();
            this.loadEvents();
        },

        getDayClass(day) {
            let classes = 'cursor-pointer hover:bg-gray-50 transition';

            if (day.isToday) {
                classes += ' bg-green-600';
            } else if (!day.isCurrentMonth) {
                classes += ' bg-gray-50';
            }

            return classes;
        },

        showDayEvents(day) {
            if (!day.date || !day.events || day.events.length === 0) return;

            this.selectedDate = day.date.toLocaleDateString('id-ID', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
            this.selectedEvents = day.events;
            this.showModal = true;
        },

        getStatusText(status) {
            const statusMap = {
                'holiday': 'Libur Nasional',
                'approved': 'Disetujui',
                'pending': 'Menunggu',
                'rejected': 'Ditolak'
            };
            return statusMap[status] || status;
        },

        isSameDay(date1, date2) {
            return date1.getFullYear() === date2.getFullYear() &&
                   date1.getMonth() === date2.getMonth() &&
                   date1.getDate() === date2.getDate();
        }
    }
}
</script>
@endpush
