@extends('layouts.admin')

@section('title', 'Tambah Bulk Libur Nasional')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.holidays.index') }}" class="hover:text-green-600">Libur Nasional</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 font-medium">Tambah Bulk</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-layer-group mr-3 text-green-600"></i>
            Tambah Bulk Libur Nasional
        </h1>
        <p class="mt-2 text-sm text-gray-600">Tambahkan beberapa libur nasional sekaligus untuk satu tahun</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-md p-6" x-data="bulkHolidayForm()">
        <form action="{{ route('admin.holidays.bulk-store') }}" method="POST">
            @csrf

            <!-- Year Selection -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Tahun <span class="text-red-500">*</span>
                </label>
                <input type="number" name="year" x-model="year" min="2024" max="2050" required
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <!-- Holidays List -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">
                        Daftar Libur <span class="text-red-500">*</span>
                    </label>
                    <button type="button" @click="addHoliday()" class="text-sm text-green-600 hover:text-green-700">
                        <i class="fas fa-plus mr-1"></i>
                        Tambah Libur
                    </button>
                </div>

                <div class="space-y-4">
                    <template x-for="(holiday, index) in holidays" :key="index">
                        <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-start gap-4">
                                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- Name -->
                                    <div class="md:col-span-1">
                                        <input type="text" 
                                               :name="'holidays[' + index + '][name]'" 
                                               x-model="holiday.name"
                                               placeholder="Nama Libur"
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>

                                    <!-- Date -->
                                    <div>
                                        <input type="date" 
                                               :name="'holidays[' + index + '][date]'" 
                                               x-model="holiday.date"
                                               required
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>

                                    <!-- Description -->
                                    <div>
                                        <input type="text" 
                                               :name="'holidays[' + index + '][description]'" 
                                               x-model="holiday.description"
                                               placeholder="Keterangan (opsional)"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                                    </div>
                                </div>

                                <button type="button" @click="removeHoliday(index)" class="text-red-600 hover:text-red-800 mt-2">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>

                <template x-if="holidays.length === 0">
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-calendar-times text-4xl mb-2"></i>
                        <p>Belum ada libur ditambahkan</p>
                        <button type="button" @click="addHoliday()" class="mt-3 text-green-600 hover:text-green-700">
                            <i class="fas fa-plus mr-1"></i>
                            Tambah Libur Pertama
                        </button>
                    </div>
                </template>
            </div>

            <!-- Quick Template -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <h3 class="text-sm font-semibold text-blue-900 mb-2">💡 Template Cepat 2026</h3>
                <p class="text-xs text-blue-700 mb-3">Klik untuk mengisi otomatis dengan template libur 2026</p>
                <button type="button" @click="loadTemplate2026()" class="text-sm px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    <i class="fas fa-magic mr-2"></i>
                    Gunakan Template 2026
                </button>
            </div>

            <!-- Actions -->
            <div class="flex gap-3 pt-6 border-t border-gray-200">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Semua
                </button>
                <a href="{{ route('admin.holidays.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-center transition duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function bulkHolidayForm() {
    return {
        year: new Date().getFullYear() + 1,
        holidays: [],

        addHoliday() {
            this.holidays.push({
                name: '',
                date: '',
                description: ''
            });
        },

        removeHoliday(index) {
            this.holidays.splice(index, 1);
        },

        loadTemplate2026() {
            this.year = 2026;
            this.holidays = [
                { name: 'Tahun Baru Masehi', date: '2026-01-01', description: 'Tahun Baru 2026' },
                { name: 'Isra Mikraj Nabi Muhammad SAW', date: '2026-01-16', description: 'Libur Nasional' },
                { name: 'Tahun Baru Imlek 2577', date: '2026-02-17', description: 'Tahun Baru Imlek' },
                { name: 'Hari Raya Nyepi', date: '2026-03-19', description: 'Tahun Baru Saka 1948' },
                { name: 'Hari Raya Idul Fitri 1447 H', date: '2026-03-20', description: 'Lebaran Hari Ke-1' },
                { name: 'Hari Raya Idul Fitri 1447 H', date: '2026-03-21', description: 'Lebaran Hari Ke-2' },
                { name: 'Wafat Yesus Kristus', date: '2026-04-03', description: 'Jumat Agung' },
                { name: 'Hari Buruh Internasional', date: '2026-05-01', description: 'May Day' },
                { name: 'Hari Raya Waisak 2570', date: '2026-05-01', description: 'Hari Raya Waisak' },
                { name: 'Kenaikan Yesus Kristus', date: '2026-05-14', description: 'Kenaikan Isa Almasih' },
                { name: 'Hari Raya Idul Adha 1447 H', date: '2026-05-27', description: 'Idul Adha' },
                { name: 'Hari Lahir Pancasila', date: '2026-06-01', description: 'Hari Pancasila' },
                { name: 'Tahun Baru Islam 1448 H', date: '2026-06-17', description: '1 Muharram 1448 H' },
                { name: 'Hari Kemerdekaan RI', date: '2026-08-17', description: 'HUT RI Ke-81' },
                { name: 'Maulid Nabi Muhammad SAW', date: '2026-08-26', description: 'Maulid Nabi' },
                { name: 'Hari Raya Natal', date: '2026-12-25', description: 'Natal' },
            ];
        }
    }
}
</script>
@endpush
@endsection
