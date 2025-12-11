@extends('layouts.workers')

@section('title', 'Jadwal Shift')

@section('content')
<div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Jadwal Shift Saya</h1>
        <p class="mt-2 text-sm text-gray-600">Lihat jadwal shift Anda</p>
    </div>

    <!-- Month Selector -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('workers.schedule') }}" class="flex items-center gap-4">
            <select name="month" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ ($month ?? date('n')) == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create()->month($m)->isoFormat('MMMM') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @for($y = date('Y') - 1; $y <= date('Y') + 1; $y++)
                    <option value="{{ $y }}" {{ ($year ?? date('Y')) == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                <i class="fas fa-search mr-2"></i>Lihat
            </button>
        </form>
    </div>

    <!-- Schedule List -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">
            Jadwal {{ \Carbon\Carbon::create()->month($month ?? date('n'))->isoFormat('MMMM Y') }}
        </h2>

        <div class="space-y-3">
            @forelse($schedules ?? [] as $schedule)
            <div class="flex items-center justify-between p-4 rounded-lg {{ \Carbon\Carbon::parse($schedule->schedule_date)->isToday() ? 'bg-green-50 border-l-4 border-green-500' : 'bg-gray-50' }}">
                <div class="flex items-center space-x-4">
                    <div class="text-center min-w-[60px]">
                        <p class="text-xs text-gray-600">{{ \Carbon\Carbon::parse($schedule->schedule_date)->isoFormat('ddd') }}</p>
                        <p class="text-2xl font-bold text-gray-800">{{ \Carbon\Carbon::parse($schedule->schedule_date)->format('d') }}</p>
                    </div>
                    <div class="w-px h-12 bg-gray-300"></div>
                    <div>
                        <p class="font-semibold text-gray-800">{{ $schedule->shift->name ?? 'Shift' }}</p>
                        <p class="text-sm text-gray-600">
                            <i class="far fa-clock mr-1"></i>
                            {{ $schedule->shift->start_time ?? '08:00' }} - {{ $schedule->shift->end_time ?? '16:00' }}
                        </p>
                    </div>
                </div>
                @if(\Carbon\Carbon::parse($schedule->schedule_date)->isToday())
                    <span class="px-3 py-1 text-xs font-bold text-white bg-green-600 rounded-full">
                        Hari Ini
                    </span>
                @endif
            </div>
            @empty
            <div class="py-8 text-center text-gray-500">
                <i class="fas fa-calendar-times text-4xl mb-3"></i>
                <p>Belum ada jadwal untuk bulan ini</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
