<div x-data="calendarData()">
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-filter text-indigo-600 mr-2"></i>
            Filter Kalender
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Department Filter -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Department</label>
                <select x-model="selectedDepartment" @change="loadCalendar()"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">Semua Department</option>
                    @foreach($departments ?? [] as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Shift Filter (Multiple) -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Filter Shift</label>
                <div class="space-y-2">
                    @foreach($shifts ?? [] as $shift)
                        <label class="inline-flex items-center mr-4">
                            <input type="checkbox"
                                   x-model="selectedShifts"
                                   value="{{ $shift->id }}"
                                   @change="loadCalendar()"
                                   class="w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">{{ $shift->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Month Navigation -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                <div class="flex items-center gap-2">
                    <button @click="previousMonth()"
                            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <input type="month" x-model="currentMonth" @change="loadCalendar()"
                           class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <button @click="nextMonth()"
                            class="px-3 py-2 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                        <i class="fas fa-chevron-right"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Selected Filters Info -->
        <div class="mt-4 flex flex-wrap gap-2" x-show="selectedShifts.length > 0 || selectedDepartment">
            <span class="text-sm text-gray-600">Filter aktif:</span>
            <template x-if="selectedDepartment">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    <span x-text="getDepartmentName(selectedDepartment)"></span>
                    <button @click="selectedDepartment = ''; loadCalendar()" class="ml-2 hover:text-blue-900">&times;</button>
                </span>
            </template>
            <template x-for="shiftId in selectedShifts" :key="shiftId">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <span x-text="getShiftName(shiftId)"></span>
                    <button @click="removeShift(shiftId)" class="ml-2 hover:text-green-900">&times;</button>
                </span>
            </template>
        </div>
    </div>

    <!-- Loading State -->
    <div x-show="loading" class="bg-white rounded-lg shadow p-8 text-center">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
        <p class="mt-2 text-gray-600">Memuat kalender...</p>
    </div>

    <!-- Calendar -->
    <div x-show="!loading" class="bg-white rounded-lg shadow overflow-hidden">
        <!-- Calendar Header -->
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4">
            <h2 class="text-xl font-bold" x-text="getMonthYearText()"></h2>
            <p class="text-sm text-indigo-100 mt-1">
                <span x-text="getTotalWorkers()"></span> pegawai terjadwal di bulan ini
            </p>
        </div>

        <!-- Calendar Grid -->
        <div class="p-6">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 gap-2 mb-2">
                <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']">
                    <div class="text-center text-sm font-semibold text-gray-700 py-2" x-text="day"></div>
                </template>
            </div>

            <!-- Calendar Days -->
            <div class="grid grid-cols-7 gap-2">
                <template x-for="(day, index) in calendarDays" :key="index">
                    <div
                        :class="{
                            'bg-gray-50': !day.isCurrentMonth,
                            'bg-white border-2 border-indigo-500': day.isToday,
                            'bg-white border border-gray-200': !day.isToday && day.isCurrentMonth,
                            'hover:bg-gray-50': day.isCurrentMonth,
                            'cursor-pointer': day.workers.length > 0
                        }"
                        @click="day.workers.length > 0 ? showDayDetail(day) : null"
                        class="min-h-24 p-2 rounded-lg transition-colors relative group"
                    >
                        <!-- Date Number -->
                        <div class="flex justify-between items-start mb-1">
                            <span
                                :class="{
                                    'text-gray-400': !day.isCurrentMonth,
                                    'text-white bg-indigo-600 w-6 h-6 flex items-center justify-center rounded-full text-xs': day.isToday,
                                    'text-gray-700 font-medium': day.isCurrentMonth && !day.isToday
                                }"
                                class="text-sm"
                                x-text="day.date"
                            ></span>
                            <span x-show="day.workers.length > 0"
                                  class="text-xs bg-indigo-100 text-indigo-700 px-2 py-0.5 rounded-full font-semibold"
                                  x-text="day.workers.length">
                            </span>
                        </div>

                        <!-- Workers Preview -->
                        <div class="space-y-1">
                            <template x-for="(worker, idx) in day.workers.slice(0, 3)" :key="idx">
                                <div class="text-xs truncate"
                                     :class="getShiftColorClass(worker.shift_id)">
                                    <i class="fas fa-user-circle mr-1"></i>
                                    <span x-text="worker.name"></span>
                                </div>
                            </template>
                            <div x-show="day.workers.length > 3"
                                 class="text-xs text-gray-500 italic">
                                +<span x-text="day.workers.length - 3"></span> lainnya
                            </div>
                        </div>

                        <!-- Hover Tooltip -->
                                <div x-show="day.workers.length > 0"
                                    class="hidden group-hover:block absolute z-50 bg-white border-2 border-indigo-500 rounded-lg shadow-2xl p-4 min-w-72 max-w-md left-0 top-full mt-2"
                             style="display: none;"
                             @click.stop
                             x-cloak>
                            <div class="max-h-96 overflow-y-auto">
                                <!-- Tooltip Header -->
                                <div class="flex items-center justify-between mb-3 pb-2 border-b border-gray-200 sticky top-0 bg-white z-10">
                                    <h4 class="font-bold text-gray-900 text-sm">
                                        <i class="fas fa-calendar-day text-indigo-600 mr-2"></i>
                                        Tanggal <span x-text="day.date"></span>
                                    </h4>
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded-full font-semibold">
                                        <span x-text="day.workers.length"></span> pegawai
                                    </span>
                                </div>

                                <!-- Workers grouped by shift -->
                                <template x-for="shift in groupWorkersByShift(day.workers)" :key="shift.id">
                                    <div class="mb-3 last:mb-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span :class="getShiftBadgeClass(shift.id)"
                                                  class="text-xs font-bold px-2 py-1 rounded">
                                                <i class="fas fa-clock mr-1"></i>
                                                <span x-text="shift.name"></span>
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                (<span x-text="shift.workers.length"></span> orang)
                                            </span>
                                        </div>
                                        <div class="space-y-1.5 ml-1">
                                            <template x-for="(worker, idx) in shift.workers" :key="idx">
                                                <div class="flex items-start gap-2 text-xs py-1.5 px-2 hover:bg-gray-50 rounded transition-colors">
                                                    <div class="flex-shrink-0 w-7 h-7 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                                        <span x-text="worker.name ? worker.name.charAt(0).toUpperCase() : '?'"></span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="font-semibold text-gray-900 leading-tight mb-0.5" x-text="worker.name || 'Tidak ada nama'"></p>
                                                        <p class="text-gray-600 leading-tight text-[11px]">
                                                            <i class="fas fa-id-badge mr-1"></i>
                                                            <span x-text="worker.employee_number || '-'"></span>
                                                        </p>
                                                        <p class="text-gray-500 leading-tight text-[11px]">
                                                            <i class="fas fa-building mr-1"></i>
                                                            <span x-text="worker.department_name || '-'"></span>
                                                        </p>
                                                    </div>
                                                    <div class="flex-shrink-0 text-right">
                                                        <p class="text-[11px] text-gray-600 font-medium whitespace-nowrap">
                                                            <i class="far fa-clock mr-1"></i>
                                                            <span x-text="worker.shift_time || '-'"></span>
                                                        </p>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <!-- Click info -->
                                <div class="mt-3 pt-2 border-t border-gray-100 text-xs text-center text-gray-500 italic">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Klik tanggal untuk detail lengkap
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Legend -->
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-green-100 border border-green-300 rounded mr-2"></div>
                    <span class="text-gray-700">Shift Pagi</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-yellow-100 border border-yellow-300 rounded mr-2"></div>
                    <span class="text-gray-700">Shift Siang</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-blue-100 border border-blue-300 rounded mr-2"></div>
                    <span class="text-gray-700">Shift Malam</span>
                </div>
                <div class="flex items-center">
                    <div class="w-4 h-4 bg-gray-100 border border-gray-300 rounded mr-2"></div>
                    <span class="text-gray-700">Shift Lainnya</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Day Detail Modal -->
    <div x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">
        <!-- Background overlay (blur + close on click) -->
        <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" @click="showModal = false"></div>

        <!-- Scrollable wrapper -->
        <div class="flex min-h-full items-center justify-center p-4">
        <!-- Modal panel -->
        <div class="relative w-full max-w-2xl bg-white rounded-lg shadow-xl flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium text-white" x-text="modalTitle"></h3>
                        <button @click="showModal = false" class="text-white hover:text-gray-200">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-6 py-4 overflow-y-auto flex-1">
                    <template x-if="modalWorkers.length === 0">
                        <p class="text-center text-gray-500 py-8">Tidak ada pegawai terjadwal</p>
                    </template>

                    <!-- Group by Shift -->
                    <template x-for="shift in groupWorkersByShift(modalWorkers)" :key="shift.id">
                        <div class="mb-4">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                <span :class="getShiftBadgeClass(shift.id)"
                                      class="px-2 py-1 rounded text-xs mr-2"
                                      x-text="shift.name"></span>
                                <span class="text-gray-500" x-text="`(${shift.workers.length} pegawai)`"></span>
                            </h4>
                            <div class="space-y-2">
                                <template x-for="worker in shift.workers" :key="worker.id">
                                    <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex items-center space-x-3">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-bold">
                                                    <span x-text="worker.name.charAt(0).toUpperCase()"></span>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-sm font-medium text-gray-900" x-text="worker.name"></p>
                                                <p class="text-xs text-gray-500" x-text="worker.employee_number"></p>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500" x-text="worker.department_name"></p>
                                            <p class="text-xs text-gray-600 font-medium" x-text="worker.shift_time"></p>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Footer -->
                <div class="bg-gray-50 px-6 py-3 flex justify-end">
                    <button @click="showModal = false"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function calendarData() {
    return {
        loading: false,
        currentMonth: '{{ now()->format("Y-m") }}',
        selectedDepartment: '{{ $departmentId ?? "" }}',
        selectedShifts: [],
        calendarDays: [],
        showModal: false,
        modalTitle: '',
        modalWorkers: [],

        departments: @json($departments ?? []),
        shifts: @json($shifts ?? []),

        init() {
            this.loadCalendar();
        },

        async loadCalendar() {
            this.loading = true;

            try {
                const params = new URLSearchParams({
                    month: this.currentMonth,
                    department_id: this.selectedDepartment || '',
                    shift_ids: this.selectedShifts.join(',')
                });

                const response = await fetch(`{{ route('admin.worker-shifts.calendar-data') }}?${params}`);
                const data = await response.json();

                this.calendarDays = data.days || [];

                // Debug: Log first day with workers
                const dayWithWorkers = this.calendarDays.find(d => d.workers && d.workers.length > 0);
                if (dayWithWorkers) {
                    console.log('Sample worker data:', dayWithWorkers.workers[0]);
                }
            } catch (error) {
                console.error('Error loading calendar:', error);
            } finally {
                this.loading = false;
            }
        },

        previousMonth() {
            const date = new Date(this.currentMonth + '-01');
            date.setMonth(date.getMonth() - 1);
            this.currentMonth = date.toISOString().substr(0, 7);
            this.loadCalendar();
        },

        nextMonth() {
            const date = new Date(this.currentMonth + '-01');
            date.setMonth(date.getMonth() + 1);
            this.currentMonth = date.toISOString().substr(0, 7);
            this.loadCalendar();
        },

        getMonthYearText() {
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            const date = new Date(this.currentMonth + '-01');
            return `${monthNames[date.getMonth()]} ${date.getFullYear()}`;
        },

        getTotalWorkers() {
            const uniqueWorkers = new Set();
            this.calendarDays.forEach(day => {
                day.workers.forEach(worker => uniqueWorkers.add(worker.id));
            });
            return uniqueWorkers.size;
        },

        getDepartmentName(id) {
            const dept = this.departments.find(d => d.id == id);
            return dept ? dept.name : '';
        },

        getShiftName(id) {
            const shift = this.shifts.find(s => s.id == id);
            return shift ? shift.name : '';
        },

        removeShift(shiftId) {
            this.selectedShifts = this.selectedShifts.filter(id => id !== shiftId);
            this.loadCalendar();
        },

        getShiftColorClass(shiftId) {
            const shift = this.shifts.find(s => s.id == shiftId);
            if (!shift) return 'text-gray-600 bg-gray-50 px-2 py-1 rounded';

            const name = shift.name.toLowerCase();
            if (name.includes('pagi')) return 'text-green-700 bg-green-50 px-2 py-1 rounded';
            if (name.includes('siang')) return 'text-yellow-700 bg-yellow-50 px-2 py-1 rounded';
            if (name.includes('malam')) return 'text-blue-700 bg-blue-50 px-2 py-1 rounded';
            return 'text-gray-700 bg-gray-50 px-2 py-1 rounded';
        },

        getShiftBadgeClass(shiftId) {
            const shift = this.shifts.find(s => s.id == shiftId);
            if (!shift) return 'bg-gray-200 text-gray-700';

            const name = shift.name.toLowerCase();
            if (name.includes('pagi')) return 'bg-green-100 text-green-800';
            if (name.includes('siang')) return 'bg-yellow-100 text-yellow-800';
            if (name.includes('malam')) return 'bg-blue-100 text-blue-800';
            return 'bg-gray-100 text-gray-800';
        },

        showDayDetail(day) {
            const date = new Date(this.currentMonth + '-' + String(day.date).padStart(2, '0'));
            const dayNames = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const monthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                              'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            this.modalTitle = `${dayNames[date.getDay()]}, ${day.date} ${monthNames[date.getMonth()]} ${date.getFullYear()}`;
            this.modalWorkers = day.workers;
            this.showModal = true;
        },

        groupWorkersByShift(workers) {
            const grouped = {};

            workers.forEach(worker => {
                if (!grouped[worker.shift_id]) {
                    grouped[worker.shift_id] = {
                        id: worker.shift_id,
                        name: worker.shift_name,
                        workers: []
                    };
                }
                grouped[worker.shift_id].workers.push(worker);
            });

            return Object.values(grouped);
        }
    };
}
</script>

<style>
[x-cloak] { display: none !important; }
</style>
