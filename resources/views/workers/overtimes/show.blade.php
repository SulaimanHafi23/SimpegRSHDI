@extends('layouts.workers')

@section('title', 'Detail Lembur')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Detail Lembur</h1>
            <p class="mt-2 text-sm text-gray-600">Informasi lengkap pengajuan lembur</p>
        </div>
        <a href="{{ route('workers.overtimes.index') }}"
           class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition inline-flex items-center">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Status Badge -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-gray-800 mb-2">Pengajuan Lembur</h2>
                <p class="text-sm text-gray-600">Diajukan pada: {{ $overtime->created_at->format('d F Y, H:i') ?? now()->format('d F Y, H:i') }}</p>
            </div>
            <div>
                @if(($overtime->status ?? 'pending') === 'pending')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                        <i class="fas fa-clock mr-1"></i>Menunggu Persetujuan
                    </span>
                @elseif(($overtime->status ?? 'pending') === 'approved')
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-green-100 text-green-800">
                        <i class="fas fa-check-circle mr-1"></i>Disetujui
                    </span>
                @else
                    <span class="px-4 py-2 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                        <i class="fas fa-times-circle mr-1"></i>Ditolak
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Overtime Details -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-bold text-gray-800 mb-4">
            <i class="fas fa-info-circle text-green-600 mr-2"></i>Informasi Lembur
        </h3>
        
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-600 mb-1">Tanggal Lembur</p>
                <p class="text-base font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($overtime->overtime_date ?? now())->format('d F Y') }}
                </p>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Jam Mulai</p>
                    <p class="text-base font-semibold text-gray-900">{{ $overtime->start_time ?? '17:00' }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 mb-1">Jam Selesai</p>
                    <p class="text-base font-semibold text-gray-900">{{ $overtime->end_time ?? '20:00' }}</p>
                </div>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Total Jam Lembur</p>
                <p class="text-base font-semibold text-gray-900">
                    <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full">
                        {{ $overtime->total_hours ?? 3 }} jam
                    </span>
                </p>
            </div>

            <div>
                <p class="text-sm text-gray-600 mb-1">Deskripsi Pekerjaan</p>
                <p class="text-base text-gray-900">{{ $overtime->description ?? 'Tidak ada deskripsi' }}</p>
            </div>

            @if(isset($overtime->status) && $overtime->status !== 'pending')
            <div class="pt-4 border-t border-gray-200">
                <h4 class="text-md font-bold text-gray-800 mb-3">Informasi Persetujuan</h4>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Disetujui/Ditolak Oleh</p>
                        <p class="text-base font-semibold text-gray-900">{{ $overtime->approved_by_name ?? 'Admin' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Tanggal</p>
                        <p class="text-base font-semibold text-gray-900">
                            {{ $overtime->approved_at ? \Carbon\Carbon::parse($overtime->approved_at)->format('d F Y, H:i') : '-' }}
                        </p>
                    </div>
                </div>
                @if(isset($overtime->approval_notes) && $overtime->approval_notes)
                <div class="mt-3">
                    <p class="text-sm text-gray-600 mb-1">Catatan</p>
                    <p class="text-base text-gray-900">{{ $overtime->approval_notes }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        @if(isset($overtime->status) && $overtime->status === 'pending')
        <div class="mt-6 pt-6 border-t border-gray-200 flex justify-end space-x-3">
            <a href="{{ route('workers.overtimes.edit', $overtime->id) }}" 
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                <i class="fas fa-edit mr-2"></i>Edit Pengajuan
            </a>
            <form action="{{ route('workers.overtimes.destroy', $overtime->id) }}" method="POST" class="inline"
                  onsubmit="return confirm('Yakin ingin membatalkan pengajuan lembur ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    <i class="fas fa-trash mr-2"></i>Batalkan Pengajuan
                </button>
            </form>
        </div>
        @endif
    </div>
</div>
@endsection
