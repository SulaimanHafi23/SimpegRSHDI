@extends('layouts.admin')

@section('title', 'Generate Penggajian')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3 sm:gap-4">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <i class="fas fa-calculator text-white text-lg sm:text-xl"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Generate Penggajian</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Buat periode gaji baru dan hitung slip semua pegawai aktif</p>
        </div>
        <a href="{{ route('admin.payrolls.index') }}"
           class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shrink-0">
            <i class="fas fa-arrow-left text-xs"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-start gap-3 px-4 py-3.5 bg-blue-50 rounded-xl border border-blue-200">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-blue-800 mb-1">Informasi Penting</p>
            <p class="text-xs sm:text-sm text-blue-700">Sistem akan menghasilkan slip untuk seluruh pegawai aktif berdasarkan komponen gaji yang telah dikonfigurasi.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 space-y-5">
        <form method="POST" action="{{ route('admin.payrolls.generate') }}">
            @csrf

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Nama Periode <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" value="{{ old('name', 'Penggajian ' . now()->isoFormat('MMMM YYYY')) }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500 @error('name') border-red-300 bg-red-50 @enderror"
                           placeholder="contoh: Penggajian Januari 2026">
                    @error('name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan <span class="text-red-500">*</span></label>
                        <select name="month" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                            @php
                                $months = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
                            @endphp
                            @foreach($months as $i => $m)
                                <option value="{{ $i + 1 }}" @selected(old('month', now()->month) == $i + 1)>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                        <input type="number" name="year" value="{{ old('year', now()->year) }}"
                               min="2020" max="{{ now()->year + 1 }}"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai <span class="text-red-500">*</span></label>
                        <input type="date" name="start_date" value="{{ old('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir <span class="text-red-500">*</span></label>
                        <input type="date" name="end_date" value="{{ old('end_date', now()->endOfMonth()->format('Y-m-d')) }}"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500"
                              placeholder="Catatan opsional...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-700">
                <i class="fas fa-info-circle mr-2"></i>
                Sistem akan menghitung gaji untuk <strong>semua pegawai aktif</strong> berdasarkan komponen gaji yang telah dikonfigurasi.
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                <button type="submit"
                        class="w-full sm:flex-1 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-calculator mr-2"></i>Generate Penggajian
                </button>
                <a href="{{ route('admin.payrolls.index') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
