@extends('layouts.admin')

@section('title', 'Auto Generate Libur Nasional')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
            <a href="{{ route('admin.holidays.index') }}" class="hover:text-green-600">Libur Nasional</a>
            <i class="fas fa-chevron-right text-xs"></i>
            <span class="text-gray-900 font-medium">Auto Generate</span>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 flex items-center">
            <i class="fas fa-magic mr-3 text-green-600"></i>
            Auto Generate Libur Nasional Indonesia
        </h1>
        <p class="mt-2 text-sm text-gray-600">Buat daftar libur nasional Indonesia secara otomatis berdasarkan tahun</p>
    </div>

    <!-- Info Banner -->
    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Tentang Fitur Ini</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Fitur ini akan mengisi libur nasional Indonesia secara otomatis berdasarkan SKB 3 Menteri dan kalender resmi pemerintah Indonesia, termasuk:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Hari libur nasional resmi</li>
                        <li>Hari raya keagamaan (Islam, Kristen, Hindu, Buddha)</li>
                        <li>Cuti bersama yang ditetapkan pemerintah</li>
                        <li>Hari besar nasional (Kemerdekaan, Pancasila, dll)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Year Selection Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pilih Tahun untuk Di-Generate</h2>
        
        <form action="{{ route('admin.holidays.auto-generate.store') }}" method="POST">
            @csrf

            <div class="space-y-4">
                @foreach($availableYears as $yearData)
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-green-500 transition-colors">
                        <label class="flex items-start cursor-pointer">
                            <input type="radio" name="year" value="{{ $yearData['year'] }}" required
                                   class="mt-1 h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-lg font-semibold text-gray-900">Tahun {{ $yearData['year'] }}</span>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        {{ $yearData['count'] }} hari libur
                                    </span>
                                </div>
                                <p class="mt-1 text-sm text-gray-600">
                                    Termasuk hari raya keagamaan, libur nasional, dan cuti bersama resmi
                                </p>
                            </div>
                        </label>
                    </div>
                @endforeach
            </div>

            @error('year')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror

            <!-- Warning Notice -->
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            <span class="font-medium">Perhatian:</span> Libur yang sudah ada di database akan dilewati secara otomatis untuk menghindari duplikasi.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex gap-3">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md font-medium">
                    <i class="fas fa-magic mr-2"></i>
                    Generate Sekarang
                </button>
                <a href="{{ route('admin.holidays.index') }}" class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg text-center transition duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
            </div>
        </form>
    </div>

    <!-- Preview Section (Optional) -->
    <div class="mt-6 bg-white rounded-lg shadow-md p-6" x-data="{ showPreview: false }">
        <button @click="showPreview = !showPreview" class="w-full flex items-center justify-between text-left">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-eye mr-2 text-green-600"></i>
                Preview Libur Nasional
            </h2>
            <i class="fas fa-chevron-down transform transition-transform" :class="{ 'rotate-180': showPreview }"></i>
        </button>

        <div x-show="showPreview" x-collapse class="mt-4">
            <div class="space-y-4">
                <!-- 2025 Preview -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-900 mb-3">📅 Tahun 2025</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Tahun Baru Masehi</span>
                            <span class="font-medium">1 Jan 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Isra Mi'raj</span>
                            <span class="font-medium">27 Jan 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Tahun Baru Imlek</span>
                            <span class="font-medium">29 Jan 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Hari Raya Nyepi</span>
                            <span class="font-medium">29 Mar 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Idul Fitri (2 hari)</span>
                            <span class="font-medium">31 Mar - 1 Apr 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Cuti Bersama (4 hari)</span>
                            <span class="font-medium">28 Mar, 2-4 Apr</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Jumat Agung</span>
                            <span class="font-medium">18 Apr 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Hari Buruh</span>
                            <span class="font-medium">1 Mei 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Kenaikan Isa Al-Masih</span>
                            <span class="font-medium">29 Mei 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Hari Lahir Pancasila</span>
                            <span class="font-medium">1 Jun 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Idul Adha</span>
                            <span class="font-medium">7 Jun 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Tahun Baru Islam</span>
                            <span class="font-medium">27 Jun 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">HUT RI ke-80</span>
                            <span class="font-medium">17 Agu 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Maulid Nabi</span>
                            <span class="font-medium">5 Sep 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Natal</span>
                            <span class="font-medium">25 Des 2025</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Cuti Bersama Natal</span>
                            <span class="font-medium">26 Des 2025</span>
                        </div>
                    </div>
                </div>

                <!-- 2026 Preview -->
                <div class="border-t pt-4">
                    <h3 class="font-semibold text-gray-900 mb-3">📅 Tahun 2026</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Tahun Baru Masehi</span>
                            <span class="font-medium">1 Jan 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Isra Mi'raj</span>
                            <span class="font-medium">16 Feb 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Tahun Baru Imlek</span>
                            <span class="font-medium">17 Feb 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Hari Raya Nyepi</span>
                            <span class="font-medium">19 Mar 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Idul Fitri (2 hari)</span>
                            <span class="font-medium">20-21 Mar 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Cuti Bersama (2 hari)</span>
                            <span class="font-medium">23-24 Mar 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Jumat Agung</span>
                            <span class="font-medium">3 Apr 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Hari Buruh</span>
                            <span class="font-medium">1 Mei 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Kenaikan Isa Al-Masih</span>
                            <span class="font-medium">14 Mei 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Idul Adha</span>
                            <span class="font-medium">27 Mei 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Hari Lahir Pancasila</span>
                            <span class="font-medium">1 Jun 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Tahun Baru Islam</span>
                            <span class="font-medium">16 Jun 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">HUT RI ke-81</span>
                            <span class="font-medium">17 Agu 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Maulid Nabi</span>
                            <span class="font-medium">25 Agu 2026</span>
                        </div>
                        <div class="flex justify-between p-2 bg-gray-50 rounded">
                            <span class="text-gray-600">Natal</span>
                            <span class="font-medium">25 Des 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
