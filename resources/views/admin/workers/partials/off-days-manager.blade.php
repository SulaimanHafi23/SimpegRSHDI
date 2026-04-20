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
        {{-- <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
            <p class="text-sm text-blue-700">
                <i class="fas fa-info-circle mr-1"></i>
                Fitur menggunakan satu sumber data: <strong>worker_off_days</strong>. Anda bisa membuat libur satu hari atau pola berulang.
            </p>
        </div> --}}

        <div class="p-4 bg-gray-50 rounded-lg space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Hari Libur</label>
                    <select x-model="offDayForm.type" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="single">Satu Hari</option>
                        <option value="recurring">Berulang Mingguan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan (Opsional)</label>
                    <input type="text" x-model="offDayForm.reason" placeholder="Contoh: Libur operasional" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div x-show="offDayForm.type === 'single'" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Libur</label>
                    <input type="date" x-model="offDayForm.single_date" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                </div>
            </div>

            <div x-show="offDayForm.type === 'recurring'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Hari Berulang</label>
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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Dari</label>
                        <input type="date" x-model="offDayForm.effective_from" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Berlaku Sampai (Opsional)</label>
                        <input type="date" x-model="offDayForm.effective_until" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                </div>
            </div>

            <button type="button"
                    @click="addOffDay()"
                    class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                <i class="fas fa-plus mr-2"></i>Simpan Hari Libur
            </button>
        </div>

        <div x-show="patterns.length > 0" class="mt-6">
            <h4 class="font-semibold text-gray-700 mb-3">Daftar Hari Libur Aktif:</h4>
            <div class="space-y-2">
                <template x-for="pattern in patterns" :key="pattern.id">
                    <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">
                                <span x-text="isSingleDay(pattern)
                                    ? 'Libur Satu Hari: ' + formatDate(pattern.effective_from)
                                    : 'Libur Berulang: ' + formatPatternDays(pattern.day_of_week)"></span>
                            </p>
                            <p class="text-xs text-gray-600" x-show="!isSingleDay(pattern)">
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
        storePattern: @json(route('admin.workers.off-days.store-pattern', ['workerId' => '__WORKER__'])),
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
        loading: false,
        error: '',
        success: '',
        patterns: [],

        offDayForm: {
            type: 'single',
            single_date: '',
            reason: '',
            day_of_week: [],
            effective_from: '',
            effective_until: '',
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
                this.patterns = data.patterns || [];
            } catch (err) {
                this.error = 'Gagal memuat data hari libur';
                console.error(err);
            }
            this.loading = false;
        },

        togglePatternDay(event) {
            const day = parseInt(event.target.value);
            if (event.target.checked) {
                if (!this.offDayForm.day_of_week.includes(day)) {
                    this.offDayForm.day_of_week.push(day);
                }
            } else {
                this.offDayForm.day_of_week = this.offDayForm.day_of_week.filter(d => d !== day);
            }
        },

        async addOffDay() {
            this.error = '';

            if (this.offDayForm.type === 'single' && !this.offDayForm.single_date) {
                this.error = 'Pilih tanggal libur';
                return;
            }

            if (this.offDayForm.type === 'recurring' && this.offDayForm.day_of_week.length === 0) {
                this.error = 'Pilih minimal satu hari';
                return;
            }

            if (this.offDayForm.type === 'recurring' && !this.offDayForm.effective_from) {
                this.error = 'Pilih tanggal mulai';
                return;
            }

            const payload = {
                type: this.offDayForm.type,
                reason: this.offDayForm.reason,
            };

            if (this.offDayForm.type === 'single') {
                payload.single_date = this.offDayForm.single_date;
            } else {
                payload.day_of_week = this.offDayForm.day_of_week;
                payload.effective_from = this.offDayForm.effective_from;
                payload.effective_until = this.offDayForm.effective_until;
            }

            this.loading = true;
            try {
                const response = await fetch(fillRoute(routeTemplates.storePattern, {
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
                    this.offDayForm = {
                        type: 'single',
                        single_date: '',
                        reason: '',
                        day_of_week: [],
                        effective_from: '',
                        effective_until: '',
                    };
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

        async deletePattern(patternId) {
            const result = await window.showConfirmDialog({
                title: 'Hapus Hari Libur?',
                text: 'Yakin ingin menghapus hari libur ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                confirmButtonText: 'Ya, Hapus!'
            });

            if (!result.isConfirmed) return;

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
                    this.success = 'Hari libur berhasil dihapus';
                    await this.loadData();
                    setTimeout(() => this.success = '', 3000);
                }
            } catch (err) {
                this.error = 'Gagal menghapus pola';
                console.error(err);
            }
            this.loading = false;
        },

        isSingleDay(rule) {
            if (!rule?.effective_from || !rule?.effective_until) {
                return false;
            }
            return rule.effective_from === rule.effective_until;
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
