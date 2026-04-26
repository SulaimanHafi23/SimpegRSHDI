@extends('layouts.admin')

@section('title', 'Manajemen Jadwal Pegawai')

@section('content')
<div class="container mx-auto px-4 py-6" x-data="{ activeTab: '{{ request('tab', 'list') }}' }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Manajemen Jadwal Pegawai</h1>
            <p class="text-gray-600 mt-1">Kelola jadwal shift pegawai</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.worker-shifts.generate') }}"
               class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v6h6M20 20v-6h-6M5 19a9 9 0 0114-7M19 5a9 9 0 00-14 7"/>
                </svg>
                Generate Rotasi
            </a>
            <a href="{{ route('admin.worker-shifts.create') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg shadow-md transition duration-150">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jadwal
            </a>
        </div>
    </div>



    <!-- Tabs -->
    <div class="mb-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8" aria-label="Tabs">
                <button @click="activeTab = 'list'; window.history.pushState({}, '', '?tab=list')"
                        :class="activeTab === 'list' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-list mr-2"></i>
                    Daftar Jadwal
                </button>
                <button @click="activeTab = 'calendar'; window.history.pushState({}, '', '?tab=calendar')"
                        :class="activeTab === 'calendar' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm transition-colors">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Kalender Shift
                </button>
            </nav>
        </div>
    </div>

    <!-- Tab Content: List View -->
    <div x-show="activeTab === 'list'" x-cloak>
    <!-- Filter Section -->
    <div x-data="{ showFilters: false }" class="mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            {{-- Filter Header --}}
            <button @click="showFilters = !showFilters"
                    class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-filter text-indigo-600"></i>
                    <span class="font-semibold text-gray-900">Filter & Pencarian</span>
                </div>
                <i class="fas fa-chevron-down transform transition-transform"
                   :class="{ 'rotate-180': showFilters }"></i>
            </button>

            {{-- Filter Form --}}
            <div x-show="showFilters"
                 x-collapse
                 class="border-t border-gray-200">
                <form method="GET" action="{{ route('admin.worker-shifts.index') }}" class="p-6">
                    <input type="hidden" name="tab" value="list">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Cari Pegawai</label>
                            <input type="text"
                                   name="search"
                                   value="{{ $filters['search'] ?? '' }}"
                                   placeholder="Nama atau NIP"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                            <select name="worker_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Pegawai</option>
                                @foreach($workers as $worker)
                                    <option value="{{ $worker->id }}" {{ ($filters['worker_id'] ?? '') == $worker->id ? 'selected' : '' }}>
                                        {{ $worker->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Shift</label>
                            <select name="shift_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Shift</option>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ ($filters['shift_id'] ?? '') == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="is_active" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Semua Status</option>
                                <option value="1" {{ ($filters['is_active'] ?? '') === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ ($filters['is_active'] ?? '') === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                            </select>
                        </div>

                        <div class="flex items-end gap-2">
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg shadow-md transition duration-150 flex items-center">
                                <i class="fas fa-search mr-2"></i>
                                Filter
                            </button>
                            <a href="{{ route('admin.worker-shifts.index', ['tab' => 'list']) }}"
                               class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-lg shadow-md transition duration-150 flex items-center">
                                <i class="fas fa-redo mr-2"></i>
                                Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Worker Shifts Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <x-table>
            <x-slot:thead>
                <x-table.row>
                    <x-table.cell header>Pegawai</x-table.cell>
                    <x-table.cell header class="hidden md:table-cell">Shift</x-table.cell>
                    <x-table.cell header class="hidden lg:table-cell">Jam Kerja</x-table.cell>
                    <x-table.cell header class="hidden lg:table-cell">Pola</x-table.cell>
                    <x-table.cell header class="hidden md:table-cell">Periode</x-table.cell>
                    <x-table.cell header>Status</x-table.cell>
                    <x-table.cell header class="text-right">Actions</x-table.cell>
                </x-table.row>
            </x-slot:thead>
            @forelse($workersWithShifts as $worker)
                <x-table.row class="{{ !$worker->latestShift ? 'bg-yellow-50' : '' }}">
                    <x-table.cell>
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if(($worker->photo_url ?? false) && Storage::disk('public')->exists($worker->photo_url))
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ Storage::url($worker->photo_url) }}"
                                             alt="{{ $worker->name }}">
                                    @elseif(($worker->photo ?? false) && Storage::disk('public')->exists($worker->photo))
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ Storage::url($worker->photo) }}"
                                             alt="{{ $worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-green-500 flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($worker->name ?? ($worker->employee_number ?? '-'), 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $worker->employee_number ?? '-' }}</div>
                                </div>
                            </div>
                        <x-table.cell class="hidden md:table-cell">
                            @if($worker->latestShift)
                                <div class="text-sm font-medium text-gray-900">{{ $worker->latestShift->shift->name }}</div>
                                <div class="text-sm text-gray-500">{{ $worker->latestShift->shift->code ?? '-' }}</div>
                            @else
                                <div class="text-sm font-medium text-gray-400 italic">Belum ada shift</div>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="hidden lg:table-cell">
                            @if($worker->latestShift)
                                <div class="text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($worker->latestShift->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($worker->latestShift->shift->end_time)->format('H:i') }}
                                </div>
                            @else
                                <div class="text-sm text-gray-400 italic">-</div>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="hidden lg:table-cell">
                            @if($worker->latestShift)
                                @if($worker->hasRotation ?? false)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                        Rotasi
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        Tetap
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-400 italic">
                                    -
                                </span>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="hidden md:table-cell">
                            @if($worker->latestShift)
                                <div class="text-sm text-gray-900">{{ $worker->latestShift->effective_from->format('d M Y') }}</div>
                                <div class="text-sm text-gray-500">
                                    s/d {{ $worker->latestShift->effective_until ? $worker->latestShift->effective_until->format('d M Y') : 'Selamanya' }}
                                </div>
                            @else
                                <div class="text-sm text-gray-400 italic">-</div>
                            @endif
                        </x-table.cell>
                        <x-table.cell>
                            @if($worker->latestShift)
                                @if($worker->latestShift->is_active)
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                @else
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Tidak Aktif
                                    </span>
                                @endif
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Belum Ada Shift
                                </span>
                            @endif
                        </x-table.cell>
                        <x-table.cell class="text-right">
                            @if($worker->latestShift)
                                <div class="flex justify-end space-x-2">
                                    <a href="{{ route('admin.worker-shifts.show', $worker->latestShift->id) }}#off-day-management"
                                       class="text-amber-600 hover:text-amber-900" title="Kelola Libur">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.worker-shifts.show', $worker->latestShift->id) }}"
                                       class="text-blue-600 hover:text-blue-900" title="View">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('admin.worker-shifts.edit', $worker->latestShift->id) }}"
                                       class="text-indigo-600 hover:text-indigo-900" title="Edit">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form action="{{ route('admin.worker-shifts.destroy', $worker->latestShift->id) }}"
                                          method="POST"
                                          class="inline-block"
                                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal shift ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900" title="Delete">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            @else
                                <a href="{{ route('admin.worker-shifts.create', ['worker_id' => $worker->id]) }}"
                                   class="inline-flex items-center px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-xs font-semibold rounded-lg shadow-sm transition duration-150"
                                   title="Tambah Shift">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Tambah Shift
                                </a>
                            @endif
                        </x-table.cell>
                </x-table.row>
                @empty
                <x-table.row>
                    <x-table.cell colspan="7" class="text-center text-gray-500">
                        <div class="flex flex-col items-center justify-center py-8">
                            <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <p class="text-lg font-medium text-gray-700 mb-1">Tidak ada data pegawai</p>
                            <p class="text-sm text-gray-500">Silakan tambahkan pegawai terlebih dahulu</p>
                        </div>
                    </x-table.cell>
                </x-table.row>
                @endforelse
        </x-table>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $workersWithShifts->links() }}
        </div>
    </div>

    <!-- Legend Info -->
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-600 text-xl"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Informasi</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc list-inside space-y-1">
                        <li>Baris dengan <span class="px-2 py-0.5 bg-yellow-100 text-yellow-800 rounded font-semibold">latar kuning</span> menunjukkan pegawai yang <strong>belum memiliki shift</strong></li>
                        <li>Klik tombol <span class="px-2 py-0.5 bg-green-600 text-white rounded font-semibold">Tambah Shift</span> untuk menambahkan jadwal shift untuk pegawai tersebut</li>
                        <li>Pegawai yang sudah memiliki shift akan menampilkan shift yang aktif saat ini</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </div>{{-- End Tab List --}}

    <!-- Tab Content: Calendar View -->
    <div x-show="activeTab === 'calendar'" x-cloak>
        @include('admin.schedules.partials.calendar')
    </div>

</div>
@endsection
