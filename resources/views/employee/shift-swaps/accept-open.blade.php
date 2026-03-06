@extends('layouts.employee')

@section('title', 'Terima Open Request')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 rounded-lg shadow-lg p-6 mb-6">
        <div class="flex items-center">
            <a href="{{ route('employee.shift-swaps.index') }}" class="mr-4 text-white hover:text-blue-200">
                <i class="fas fa-arrow-left text-xl"></i>
            </a>
            <div class="text-white">
                <h1 class="text-2xl sm:text-3xl font-bold flex items-center">
                    <i class="fas fa-hand-paper mr-3"></i>
                    Terima Open Request
                </h1>
                <p class="mt-2 text-blue-100">Pilih shift Anda untuk ditukar</p>
            </div>
        </div>
    </div>

    <!-- Request Info -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
            Detail Permintaan
        </h3>

        <div class="grid md:grid-cols-2 gap-6">
            <!-- Requester Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-2">Peminta</p>
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-blue-600 text-lg"></i>
                    </div>
                    <div>
                        <p class="font-semibold text-gray-900">{{ $swapRequest->requester->name }}</p>
                        <p class="text-sm text-gray-600">{{ $swapRequest->requester->department->name ?? '-' }}</p>
                    </div>
                </div>
            </div>

            <!-- Requester Shift Info -->
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-500 mb-2">Shift yang Ditawarkan</p>
                @php
                    $reqShift = $swapRequest->requesterShift?->shift;
                @endphp
                <div class="space-y-2">
                    <div class="flex items-center text-gray-800">
                        <i class="fas fa-clock text-gray-400 mr-2 w-5"></i>
                        <span class="font-medium">{{ $reqShift->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center text-gray-600 text-sm">
                        <i class="fas fa-calendar text-gray-400 mr-2 w-5"></i>
                        @if($swapRequest->swap_type === 'single_date' && $swapRequest->swap_date)
                            {{ $swapRequest->swap_date->format('d M Y') }}
                        @elseif($swapRequest->swap_type === 'date_range' && $swapRequest->swap_start_date && $swapRequest->swap_end_date)
                            {{ $swapRequest->swap_start_date->format('d M Y') }} s/d {{ $swapRequest->swap_end_date->format('d M Y') }}
                        @elseif($swapRequest->swap_type === 'recurring' && $swapRequest->swap_dates)
                            {{ collect($swapRequest->swap_dates)->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M Y'))->join(', ') }}
                        @else
                            N/A
                        @endif
                    </div>
                    <div class="flex items-center text-gray-600 text-sm">
                        <i class="fas fa-history text-gray-400 mr-2 w-5"></i>
                        {{ $reqShift->start_time ?? '' }} - {{ $reqShift->end_time ?? '' }}
                    </div>
                </div>
            </div>
        </div>

        @if($swapRequest->reason)
        <div class="mt-4 p-4 bg-yellow-50 border border-yellow-100 rounded-lg">
            <p class="text-sm text-gray-500 mb-1">Alasan Permintaan</p>
            <p class="text-gray-700">{{ $swapRequest->reason }}</p>
        </div>
        @endif
    </div>

    <!-- Select Your Shift Form -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-exchange-alt text-green-600 mr-2"></i>
            Pilih Shift Anda untuk Ditukar
        </h3>

        @if($workerShifts->isEmpty())
            <div class="text-center py-8">
                <i class="fas fa-calendar-times text-gray-300 text-4xl mb-3"></i>
                <p class="text-gray-500">Anda tidak memiliki shift yang aktif untuk ditukar.</p>
                <a href="{{ route('employee.shift-swaps.index') }}" class="mt-4 inline-flex items-center text-blue-600 hover:text-blue-700">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('employee.shift-swaps.accept-open.store', $swapRequest->id) }}">
                @csrf

                <div class="space-y-3 mb-6">
                    @foreach($workerShifts as $shift)
                    <label class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition has-[:checked]:border-blue-600 has-[:checked]:bg-blue-50">
                        <input type="radio" name="target_shift_id" value="{{ $shift->id }}" class="w-4 h-4 text-blue-600" required>
                        <div class="ml-4 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="font-medium text-gray-900">{{ $shift->shift->name }}</span>
                                <span class="text-sm text-gray-500">
                                    {{ $shift->shift->start_time }} - {{ $shift->shift->end_time }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="fas fa-calendar mr-1"></i>
                                Berlaku: {{ $shift->effective_from?->format('d M Y') ?? 'Tidak ditentukan' }}
                                @if($shift->effective_until)
                                    - {{ $shift->effective_until->format('d M Y') }}
                                @endif
                            </p>
                        </div>
                    </label>
                    @endforeach
                </div>

                @error('target_shift_id')
                    <p class="text-red-500 text-sm mb-4">{{ $message }}</p>
                @enderror

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 mr-3"></i>
                        <div class="text-sm text-yellow-800">
                            <p class="font-medium">Perhatian!</p>
                            <p class="mt-1">Setelah Anda menerima request ini, permintaan akan diteruskan ke Manager untuk persetujuan.</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <a href="{{ route('employee.shift-swaps.index') }}" class="px-6 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition font-medium">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        <i class="fas fa-check mr-2"></i>
                        Terima Request
                    </button>
                </div>
            </form>
        @endif
    </div>
</div>
@endsection
