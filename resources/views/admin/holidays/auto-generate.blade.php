@extends('layouts.admin')

@section('title', 'Auto Generate Libur Nasional')

@section('content')
<div class="max-w-4xl mx-auto">
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
        <p class="mt-2 text-sm text-gray-600">Ambil data libur nasional langsung dari API libur.deno.dev berdasarkan tahun pilihan.</p>
    </div>

    <div class="bg-blue-50 border-l-4 border-blue-500 p-4 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-500 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Tentang Fitur Ini</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>Alur penggunaan:</p>
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Pilih tahun yang ingin diambil datanya</li>
                        <li>Klik tombol Preview untuk melihat daftar hari libur</li>
                        <li>Simpan hasil preview ke database</li>
                        <li>Data tanggal yang sudah ada akan dilewati otomatis</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Filter Tahun &amp; Preview</h2>

        <form action="{{ route('admin.holidays.auto-generate') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
            <div class="md:col-span-2">
                <label for="year" class="block text-sm font-medium text-gray-700 mb-2">Pilih Tahun</label>
                <select id="year" name="year" class="w-full border-gray-300 rounded-lg focus:ring-green-500 focus:border-green-500" required>
                    @for($year = 2011; $year <= 2100; $year++)
                        <option value="{{ $year }}" {{ (int) old('year', $selectedYear) === $year ? 'selected' : '' }}>
                            {{ $year }}
                        </option>
                    @endfor
                </select>
                <p class="mt-1 text-xs text-gray-500">Daftar tahun bisa di-scroll dari 2011 dst.</p>
            </div>
            <div>
                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg transition duration-200 font-medium">
                    <i class="fas fa-eye mr-2"></i>
                    Preview
                </button>
            </div>
        </form>

        @error('year')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror

        @if(session('error'))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ session('error') }}
            </div>
        @endif

        @if(!empty($fetchError))
            <div class="mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ $fetchError }}
            </div>
        @endif

        @if(!is_null($previewHolidays))
            @php
                $total = $previewHolidays->count();
                $existingCount = $previewHolidays->where('already_exists', true)->count();
                $newCount = $total - $existingCount;
            @endphp

            <div class="mt-6 border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex flex-wrap items-center gap-3 justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-900">Preview Hari Libur Tahun {{ $selectedYear }}</h3>
                        <p class="text-sm text-gray-600">Sumber: https://libur.deno.dev/api?year={{ $selectedYear }}</p>
                    </div>
                    <div class="flex items-center gap-2 text-xs">
                        <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-800">Total: {{ $total }}</span>
                        <span class="px-2 py-1 rounded-full bg-green-100 text-green-800">Baru: {{ $newCount }}</span>
                        <span class="px-2 py-1 rounded-full bg-yellow-100 text-yellow-800">Sudah ada: {{ $existingCount }}</span>
                    </div>
                </div>

                <div class="overflow-x-auto overflow-y-auto max-h-[26rem]" style="-webkit-overflow-scrolling: touch;">
                    <table class="min-w-[760px] w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-white sticky top-0">
                            <tr>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Tanggal</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Nama</th>
                                <th class="px-4 py-2 text-left font-semibold text-gray-700">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($previewHolidays as $holiday)
                                <tr class="{{ $holiday['already_exists'] ? 'bg-yellow-50' : 'bg-white' }}">
                                    <td class="px-4 py-2 text-gray-700">{{ \Carbon\Carbon::parse($holiday['date'])->translatedFormat('d M Y') }}</td>
                                    <td class="px-4 py-2 text-gray-900">{{ $holiday['name'] }}</td>
                                    <td class="px-4 py-2">
                                        @if($holiday['already_exists'])
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-minus-circle mr-1"></i>Sudah ada
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-green-100 text-green-800">
                                                <i class="fas fa-plus-circle mr-1"></i>Akan disimpan
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-gray-500">Tidak ada data libur untuk tahun ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Data dengan tanggal yang sudah ada di database akan dilewati saat proses simpan.
                        </p>
                    </div>
                </div>
            </div>

            <form action="{{ route('admin.holidays.auto-generate.store') }}" method="POST" class="mt-4 flex flex-col md:flex-row gap-3">
                @csrf
                <input type="hidden" name="year" value="{{ $selectedYear }}">
                <button type="submit" class="md:flex-1 bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md font-medium">
                    <i class="fas fa-save mr-2"></i>
                    Simpan {{ $newCount }} Data Baru ke Database
                </button>
                <a href="{{ route('admin.holidays.index') }}" class="md:flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg text-center transition duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
            </form>
        @else
            <div class="mt-6 flex gap-3">
                <a href="{{ route('admin.holidays.index') }}" class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg text-center transition duration-200 font-medium">
                    <i class="fas fa-times mr-2"></i>
                    Kembali
                </a>
            </div>
        @endif
    </div>
</div>
@endsection
