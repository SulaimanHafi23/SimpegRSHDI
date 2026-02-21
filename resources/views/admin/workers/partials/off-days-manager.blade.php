@php
    $days = [
        0 => 'Minggu',
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
    ];
@endphp

<div class="bg-white rounded-lg shadow-md p-6">
    <h3 class="text-lg font-semibold text-gray-800 mb-4">
        <i class="fas fa-calendar-times text-red-500 mr-2"></i>Pengaturan Hari Libur
    </h3>

    <div id="off-day-manager" x-data="offDayManager(@js($worker->id ?? null))" x-init="init()">
        <!-- Tab Navigation -->
        <div class="flex gap-4 mb-6 border-b border-gray-200">
            <button type="button"
                    @click="activeTab = 'exceptions'"
                    :class="{ 'border-b-2 border-blue-600 text-blue-600': activeTab === 'exceptions', 'text-gray-600': activeTab !== 'exceptions' }"
                    class="pb-2 px-1 font-medium transition">
                <i class="fas fa-plus-circle mr-1"></i>Pengecualian Hari Libur
            </button>
            <button type="button"
                    @click="activeTab = 'patterns'"
                    :class="{ 'border-b-2 border-blue-600 text-blue-600': activeTab === 'patterns', 'text-gray-600': activeTab !== 'patterns' }"
                    class="pb-2 px-1 font-medium transition">
                <i class="fas fa-sync-alt mr-1"></i>Pola Hari Libur (Rotating)
            </button>
        </div>

        <!-- Tab 1: Exceptions -->
        <div x-show="activeTab === 'exceptions'" class="space-y-4">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Tambahkan hari libur spesifik (satu kali) atau libur yang berulang setiap minggu dengan pola tertentu.
                </p>
            </div>

            <!-- Exception Type Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Pengecualian</label>
                    <select x-model="exceptionForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="single">Satu Hari (Single)</option>
                        <option value="recurring">Berulang Mingguan (Recurring)</option>
                    </select>
                </div>
            </div>

            <!-- Single Exception -->
            <div x-show="exceptionForm.type === 'single'" class="space-y-3 p-4 bg-gray-50 rounded-lg">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Libur</label>
                        <input type="date" x-model="exceptionForm.off_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                        <input type="text" x-model="exceptionForm.reason" placeholder="Cuti, Hari Penting, dll" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
                <button type="button"
                        @click="addException()"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Tambah Hari Libur Spesifik
                </button>
            </div>

            <!-- Recurring Exception -->
            <div x-show="exceptionForm.type === 'recurring'" class="space-y-3 p-4 bg-gray-50 rounded-lg">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari Berulang</label>
                    <div class="grid grid-cols-7 gap-2">
                        @foreach($days as $dayNum => $dayName)
                            <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-blue-50">
                                <input type="checkbox"
                                       value="{{ $dayNum }}"
                                       @change="toggleRecurringDay($event)"
                                       class="mr-2">
                                <span class="text-xs font-medium">{{ substr($dayName, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mulai Dari</label>
                        <input type="date" x-model="exceptionForm.recurring_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sampai (Opsional)</label>
                        <input type="date" x-model="exceptionForm.recurring_until" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                        <input type="text" x-model="exceptionForm.reason" placeholder="Shift Libur" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <button type="button"
                    @click="addException()"
                        class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                    <i class="fas fa-sync-alt mr-2"></i>Tambah Pola Berulang
                </button>
            </div>

            <!-- List of Exceptions -->
            <div x-show="exceptions.length > 0" class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Pengecualian Hari Libur Aktif:</h4>
                <div class="space-y-2">
                    <template x-for="exception in exceptions" :key="exception.id">
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <span x-text="exception.type === 'single' ? 'Libur: ' + formatDate(exception.off_date) : 'Libur Berulang'"></span>
                                </p>
                                <p x-show="exception.reason" class="text-xs text-gray-600" x-text="'Alasan: ' + exception.reason"></p>
                            </div>
                            <button type="button"
                                    @click="deleteException(exception.id)"
                                    class="px-3 py-1 text-red-600 hover:bg-red-200 rounded transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Tab 2: Patterns -->
        <div x-show="activeTab === 'patterns'" class="space-y-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-green-700">
                    <i class="fas fa-info-circle mr-1"></i>
                    Atur pola hari libur mingguan yang berulang (misal: Senin dan Rabu libur setiap minggu).
                </p>
            </div>

            <!-- Add New Pattern -->
            <div class="p-4 bg-gray-50 rounded-lg space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari Libur dalam Seminggu</label>
                    <div class="grid grid-cols-7 gap-2">
                        @foreach($days as $dayNum => $dayName)
                            <label class="flex items-center p-2 border border-gray-300 rounded-lg cursor-pointer hover:bg-green-50">
                                <input type="checkbox"
                                       value="{{ $dayNum }}"
                                       @change="togglePatternDay($event)"
                                       class="mr-2">
                                <span class="text-xs font-medium">{{ substr($dayName, 0, 3) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Dari</label>
                        <input type="date" x-model="patternForm.effective_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai (Opsional)</label>
                        <input type="date" x-model="patternForm.effective_until" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alasan</label>
                        <input type="text" x-model="patternForm.reason" placeholder="Pola shift library, dll" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>

                <button type="button"
                        @click="addPattern()"
                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    <i class="fas fa-plus mr-2"></i>Simpan Pola Hari Libur
                </button>
            </div>

            <!-- List of Patterns -->
            <div x-show="patterns.length > 0" class="mt-6">
                <h4 class="font-semibold text-gray-700 mb-3">Pola Hari Libur Aktif:</h4>
                <div class="space-y-2">
                    <template x-for="pattern in patterns" :key="pattern.id">
                        <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">
                                    <span x-text="formatPatternDays(pattern.day_of_week)"></span>
                                </p>
                                <p class="text-xs text-gray-600">
                                    <span x-text="'Berlaku: ' + formatDate(pattern.effective_from) + ' - ' + (pattern.effective_until ? formatDate(pattern.effective_until) : 'Tanpa batas')"></span>
                                </p>
                                <p x-show="pattern.reason" class="text-xs text-gray-600" x-text="'Alasan: ' + pattern.reason"></p>
                            </div>
                            <button type="button"
                                    @click="deletePattern(pattern.id)"
                                    class="px-3 py-1 text-red-600 hover:bg-red-200 rounded transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>
        </div>

        <!-- Loading Spinner -->
        <div x-show="loading" class="flex items-center justify-center p-4">
            <div class="animate-spin">
                <i class="fas fa-spinner text-2xl text-blue-600"></i>
            </div>
        </div>

        <!-- Error Message -->
        <div x-show="error" class="mt-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span x-text="error"></span>
        </div>

        <!-- Success Message -->
        <div x-show="success" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg">
            <i class="fas fa-check-circle mr-2"></i>
            <span x-text="success"></span>
        </div>
    </div>
</div>

@push('scripts')
<script>
function offDayManager(workerId) {
    const routeTemplates = {
        index: @json(route('admin.workers.off-days.index', ['workerId' => '__WORKER__'])),
        storeException: @json(route('admin.workers.off-days.store-exception', ['workerId' => '__WORKER__'])),
        storePattern: @json(route('admin.workers.off-days.store-pattern', ['workerId' => '__WORKER__'])),
        deleteException: @json(route('admin.workers.off-days.destroy-exception', ['workerId' => '__WORKER__', 'exceptionId' => '__EXCEPTION__'])),
        deletePattern: @json(route('admin.workers.off-days.destroy-pattern', ['workerId' => '__WORKER__', 'patternId' => '__PATTERN__'])),
    };

    const fillRoute = (template, params = {}) => {
        let result = template;
        Object.entries(params).forEach(([key, value]) => {
            result = result.replace(key, value);
        });
        return result;
    };

    const dayLabels = {
        0: 'Minggu', 1: 'Senin', 2: 'Selasa', 3: 'Rabu',
        4: 'Kamis', 5: 'Jumat', 6: 'Sabtu'
    };

    return {
        workerId,
        activeTab: 'exceptions',
        loading: false,
        error: '',
        success: '',
        exceptions: [],
        patterns: [],

        exceptionForm: {
            type: 'single',
            off_date: '',
            reason: '',
            recurring_days: [],
            recurring_from: '',
            recurring_until: '',
        },

        patternForm: {
            day_of_week: [],
            effective_from: '',
            effective_until: '',
            reason: '',
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                || document.querySelector('[name=_token]')?.value
                || '';
        },

        async init() {
            await this.loadData();
        },

        async loadData() {
            if (!this.workerId) return;
            this.loading = true;
            try {
                const response = await fetch(fillRoute(routeTemplates.index, {
                    '__WORKER__': this.workerId,
                }));
                const data = await response.json();
                this.exceptions = data.exceptions || [];
                this.patterns = data.patterns || [];
            } catch (err) {
                this.error = 'Gagal memuat data hari libur';
                console.error(err);
            }
            this.loading = false;
        },

        toggleRecurringDay(event) {
            const day = parseInt(event.target.value);
            if (event.target.checked) {
                if (!this.exceptionForm.recurring_days.includes(day)) {
                    this.exceptionForm.recurring_days.push(day);
                }
            } else {
                this.exceptionForm.recurring_days = this.exceptionForm.recurring_days.filter(d => d !== day);
            }
        },

        togglePatternDay(event) {
            const day = parseInt(event.target.value);
            if (event.target.checked) {
                if (!this.patternForm.day_of_week.includes(day)) {
                    this.patternForm.day_of_week.push(day);
                }
            } else {
                this.patternForm.day_of_week = this.patternForm.day_of_week.filter(d => d !== day);
            }
        },

        async addException() {
            this.error = '';
            if (this.exceptionForm.type === 'single' && !this.exceptionForm.off_date) {
                this.error = 'Pilih tanggal libur';
                return;
            }
            if (this.exceptionForm.type === 'recurring' && this.exceptionForm.recurring_days.length === 0) {
                this.error = 'Pilih minimal satu hari';
                return;
            }
            if (this.exceptionForm.type === 'recurring' && !this.exceptionForm.recurring_from) {
                this.error = 'Pilih tanggal mulai pola berulang';
                return;
            }

            const payload = {
                type: this.exceptionForm.type,
                off_date: this.exceptionForm.type === 'single' ? this.exceptionForm.off_date : this.exceptionForm.recurring_from,
                reason: this.exceptionForm.reason,
            };

            if (this.exceptionForm.type === 'recurring') {
                payload.recurring_pattern = {
                    day_of_week: this.exceptionForm.recurring_days,
                    until: this.exceptionForm.recurring_until,
                };
            }

            this.loading = true;
            try {
                const response = await fetch(fillRoute(routeTemplates.storeException, {
                    '__WORKER__': this.workerId,
                }), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
                    body: JSON.stringify(payload),
                });
                const data = await response.json();

                if (data.success) {
                    this.success = 'Hari libur berhasil ditambahkan';
                    await this.loadData();
                    this.exceptionForm = { type: 'single', off_date: '', reason: '', recurring_days: [], recurring_from: '', recurring_until: '' };
                    setTimeout(() => this.success = '', 3000);
                } else {
                    this.error = data.message || 'Gagal menambahkan hari libur';
                }
            } catch (err) {
                this.error = 'Terjadi kesalahan';
                console.error(err);
            }
            this.loading = false;
        },

        async addPattern() {
            this.error = '';
            if (this.patternForm.day_of_week.length === 0) {
                this.error = 'Pilih minimal satu hari';
                return;
            }
            if (!this.patternForm.effective_from) {
                this.error = 'Pilih tanggal berlaku';
                return;
            }

            this.loading = true;
            try {
                const response = await fetch(fillRoute(routeTemplates.storePattern, {
                    '__WORKER__': this.workerId,
                }), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': this.csrfToken() },
                    body: JSON.stringify(this.patternForm),
                });
                const data = await response.json();

                if (data.success) {
                    this.success = 'Pola hari libur berhasil ditambahkan';
                    await this.loadData();
                    this.patternForm = { day_of_week: [], effective_from: '', effective_until: '', reason: '' };
                    setTimeout(() => this.success = '', 3000);
                } else {
                    this.error = data.message || 'Gagal menambahkan pola';
                }
            } catch (err) {
                this.error = 'Terjadi kesalahan';
                console.error(err);
            }
            this.loading = false;
        },

        async deleteException(exceptionId) {
            if (!confirm('Yakin ingin menghapus pengecualian hari libur ini?')) return;

            this.loading = true;
            try {
                const response = await fetch(fillRoute(routeTemplates.deleteException, {
                    '__WORKER__': this.workerId,
                    '__EXCEPTION__': exceptionId,
                }), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken() },
                });
                const data = await response.json();

                if (data.success) {
                    this.success = 'Pengecualian berhasil dihapus';
                    await this.loadData();
                    setTimeout(() => this.success = '', 3000);
                }
            } catch (err) {
                this.error = 'Gagal menghapus pengecualian';
                console.error(err);
            }
            this.loading = false;
        },

        async deletePattern(patternId) {
            if (!confirm('Yakin ingin menghapus pola hari libur ini?')) return;

            this.loading = true;
            try {
                const response = await fetch(fillRoute(routeTemplates.deletePattern, {
                    '__WORKER__': this.workerId,
                    '__PATTERN__': patternId,
                }), {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': this.csrfToken() },
                });
                const data = await response.json();

                if (data.success) {
                    this.success = 'Pola berhasil dihapus';
                    await this.loadData();
                    setTimeout(() => this.success = '', 3000);
                }
            } catch (err) {
                this.error = 'Gagal menghapus pola';
                console.error(err);
            }
            this.loading = false;
        },

        formatDate(dateStr) {
            return new Date(dateStr).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
        },

        formatPatternDays(daysArray) {
            if (!Array.isArray(daysArray)) {
                return '-';
            }
            return daysArray.map(d => dayLabels[d] || '').join(', ');
        },
    };
}
</script>
@endpush
