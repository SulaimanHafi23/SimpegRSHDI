@extends('layouts.admin')

@section('title', 'Manajemen Absensi')

@section('content')
<div x-data="{
    showFilters: false,
    activeTab: '{{ request('tab', 'today') }}',
    todaySummary: {
        label: '{{ request('attendance_date') ? \Carbon\Carbon::parse(request('attendance_date'))->translatedFormat('d F Y') : now()->translatedFormat('d F Y') }}',
        total: {{ $summary['total_workers'] }},
        present: {{ $summary['present'] }},
        perfect: {{ $summary['perfect'] }},
        late: {{ $summary['late'] }},
        early_leave: {{ $summary['early_leave'] }},
        on_leave: {{ $summary['on_leave'] ?? 0 }},
        absent: {{ $summary['absent'] }}
    },
    historySummary: {
        label: '{{ $historySummary['period_label'] }}',
        total: {{ $historySummary['total_records'] }},
        present: {{ $historySummary['present'] }},
        perfect: {{ $historySummary['perfect'] }},
        late: {{ $historySummary['late'] }},
        early_leave: {{ $historySummary['early_leave'] }},
        on_leave: {{ $historySummary['on_leave'] }},
        absent: {{ $historySummary['absent'] }}
    },
    get currentSummary() { return this.activeTab === 'history' ? this.historySummary : this.todaySummary; }
}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6 gap-4">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-user-check mr-3 text-blue-600"></i>
                Manajemen Absensi
            </h1>
            <p class="mt-1 text-sm text-gray-600">Kelola data absensi pegawai dan monitoring kehadiran</p>
            <div id="real-time-clock" class="mt-2 text-sm text-blue-600 font-semibold"></div>
        </div>
        <div class="flex gap-2">
            <!-- Export Dropdown -->
            <!-- <div class="relative inline-block text-left" x-data="{ open: false }">
                <button @click="open = !open" type="button" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition duration-200 shadow-md">
                    <i class="fas fa-download mr-2"></i>
                    Export Data
                    <i class="fas fa-chevron-down ml-2 text-xs"></i>
                </button>
phpinfo() di browser
                <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 z-10 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                    <div class="py-1">
                        <a href="{{ route('admin.attendance.export', array_merge(request()->all(), ['format' => 'pdf'])) }}" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700">
                            <i class="fas fa-file-pdf mr-3 text-red-500 group-hover:text-red-700"></i>
                            <div>
                                <div class="font-medium">Export PDF</div>
                                <div class="text-xs text-gray-500">Format dokumen cetak</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.attendance.export', array_merge(request()->all(), ['format' => 'excel'])) }}" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                            <i class="fas fa-file-excel mr-3 text-green-500 group-hover:text-green-700"></i>
                            <div>
                                <div class="font-medium">Export Excel</div>
                                <div class="text-xs text-gray-500">Format spreadsheet</div>
                            </div>
                        </a>
                        <a href="{{ route('admin.attendance.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700">
                            <i class="fas fa-file-csv mr-3 text-blue-500 group-hover:text-blue-700"></i>
                            <div>
                                <div class="font-medium">Export CSV</div>
                                <div class="text-xs text-gray-500">Format comma separated</div>
                            </div>
                        </a>
                    </div>
                </div>
            </div> -->
        </div>
    </div>

    <!-- Summary Statistics - Dynamic berdasarkan tab aktif -->
    <div class="bg-white rounded-lg shadow-md mb-6 p-3">
        <div class="flex items-center gap-2 mb-2">
            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-blue-50 text-blue-700">
                <i class="fas fa-chart-pie text-xs"></i>
            </span>
            <div>
                <p class="text-xs text-gray-800 font-semibold" x-text="activeTab === 'history' ? 'Periode: ' + currentSummary.label : currentSummary.label"></p>
            </div>
            <span x-show="activeTab === 'history'" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                <i class="fas fa-history mr-1 text-xs"></i> Keseluruhan
            </span>
            <span x-show="activeTab !== 'history'" class="ml-1 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                <i class="fas fa-calendar-day mr-1 text-xs"></i> Hari Ini
            </span>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-7 gap-2">
            <div class="bg-gray-50 rounded p-2 border border-gray-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-gray-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-users text-gray-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-gray-500 truncate" x-text="activeTab === 'history' ? 'Total Record' : 'Total'"></p>
                        <p class="text-base font-bold text-gray-900" x-text="currentSummary.total"></p>
                    </div>
                </div>
            </div>
            <div class="bg-green-50 rounded p-2 border border-green-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-green-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-check-circle text-green-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-green-700 truncate">Masuk</p>
                        <p class="text-base font-bold text-green-600" x-text="currentSummary.present"></p>
                    </div>
                </div>
            </div>
            <div class="bg-emerald-50 rounded p-2 border border-emerald-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-emerald-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-star text-emerald-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-emerald-700 truncate">Sempurna</p>
                        <p class="text-base font-bold text-emerald-600" x-text="currentSummary.perfect"></p>
                    </div>
                </div>
            </div>
            <div class="bg-yellow-50 rounded p-2 border border-yellow-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-yellow-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-clock text-yellow-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-yellow-700 truncate">Terlambat</p>
                        <p class="text-base font-bold text-yellow-600" x-text="currentSummary.late"></p>
                    </div>
                </div>
            </div>
            <div class="bg-orange-50 rounded p-2 border border-orange-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-orange-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-running text-orange-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-orange-700 truncate">Pulang Awal</p>
                        <p class="text-base font-bold text-orange-600" x-text="currentSummary.early_leave"></p>
                    </div>
                </div>
            </div>
            <div class="bg-purple-50 rounded p-2 border border-purple-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-purple-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-calendar-day text-purple-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-purple-700 truncate">Cuti</p>
                        <p class="text-base font-bold text-purple-600" x-text="currentSummary.on_leave"></p>
                    </div>
                </div>
            </div>
            <div class="bg-red-50 rounded p-2 border border-red-100">
                <div class="flex items-center gap-1.5">
                    <div class="bg-red-100 p-1.5 rounded flex-shrink-0">
                        <i class="fas fa-times-circle text-red-600 text-sm"></i>
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs text-red-700 truncate">Absent</p>
                        <p class="text-base font-bold text-red-600" x-text="currentSummary.absent"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md mb-6">
        <button @click="showFilters = !showFilters" class="w-full px-6 py-4 flex items-center justify-between text-left hover:bg-gray-50 transition-colors">
            <div class="flex items-center space-x-3">
                <i class="fas fa-filter text-blue-600"></i>
                <span class="font-semibold text-gray-900">Filter & Pencarian</span>
            </div>
            <i class="fas fa-chevron-down transform transition-transform" :class="{ 'rotate-180': showFilters }"></i>
        </button>

        <div x-show="showFilters" x-collapse class="border-t border-gray-200">
            <form method="GET" action="{{ route('admin.attendance.index') }}" class="p-6" id="history-filter-form">
                <input type="hidden" name="tab" id="history-tab-input" value="{{ request('tab', 'today') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian</label>
                        <input type="text" name="search" value="{{ $historyFilters['search'] ?? '' }}"
                               placeholder="Nama, NIP, Email..."
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pegawai</label>
                        <select name="worker_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Pegawai</option>
                            @foreach($workers as $worker)
                                <option value="{{ $worker->id }}" {{ ($historyFilters['worker_id'] ?? '') == $worker->id ? 'selected' : '' }}>
                                    {{ $worker->name }} ({{ $worker->nip }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Semua Status</option>
                            <option value="present" {{ ($historyFilters['status'] ?? '') == 'present' ? 'selected' : '' }}>Hadir</option>
                            <option value="late" {{ ($historyFilters['status'] ?? '') == 'late' ? 'selected' : '' }}>Terlambat</option>
                            <option value="absent" {{ ($historyFilters['status'] ?? '') == 'absent' ? 'selected' : '' }}>Tidak Hadir</option>
                            <option value="sick" {{ ($historyFilters['status'] ?? '') == 'sick' ? 'selected' : '' }}>Sakit</option>
                            <option value="permission" {{ ($historyFilters['status'] ?? '') == 'permission' ? 'selected' : '' }}>Izin</option>
                            <option value="leave" {{ ($historyFilters['status'] ?? '') == 'leave' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Per Halaman</label>
                        <select name="per_page" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="15" {{ ($historyFilters['per_page'] ?? 15) == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ ($historyFilters['per_page'] ?? 15) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($historyFilters['per_page'] ?? 15) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($historyFilters['per_page'] ?? 15) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                        <input type="date" name="date_from" value="{{ $historyFilters['date_from'] ?? '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                        <input type="date" name="date_to" value="{{ $historyFilters['date_to'] ?? '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="flex gap-2 mt-4">
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition duration-200">
                        <i class="fas fa-search mr-2"></i>
                        Terapkan
                    </button>
                    <a href="{{ route('admin.attendance.index', ['tab' => request('tab', 'history')]) }}" class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg transition duration-200">
                        <i class="fas fa-redo mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Toggle View -->
    <div class="bg-white rounded-lg shadow-md mb-6 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h3 class="text-base sm:text-lg font-semibold text-gray-900">Tampilan Data</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 w-full sm:w-auto">
                <button id="btn-history-view" @click="activeTab = 'history'"
                    class="px-3 sm:px-4 py-2 rounded-lg transition duration-200 text-sm"
                    :class="activeTab === 'history' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                    Riwayat Absensi
                </button>
                <button id="btn-today-view" @click="activeTab = 'today'"
                    class="px-3 sm:px-4 py-2 rounded-lg transition duration-200 text-sm"
                    :class="activeTab !== 'history' ? 'bg-blue-600 text-white hover:bg-blue-700' : 'bg-gray-200 text-gray-700 hover:bg-gray-300'">
                    Absensi Hari Ini
                </button>
            </div>
        </div>
    </div>

    <!-- Table for Today's Attendance (Detail) - Default visible -->
    <div id="today-view" class="bg-white rounded-lg shadow-md overflow-hidden @if(request('tab') == 'history') hidden @endif">
        <div class="p-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <h3 class="text-lg font-semibold text-gray-900">
                    Absensi Tanggal:
                    <span id="selected-date-display" class="text-blue-600">{{ request('attendance_date') ? \Carbon\Carbon::parse(request('attendance_date'))->isoFormat('dddd, D MMMM Y') : now()->isoFormat('dddd, D MMMM Y') }}</span>
                </h3>
                <div class="flex items-center gap-2 flex-wrap w-full sm:w-auto">
                    <!-- Export Dropdown -->
                    <div class="relative inline-block text-left" x-data="{ openExport: false }">
                        <button @click="openExport = !openExport" type="button" class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm rounded-lg transition duration-200 shadow-sm">
                            <i class="fas fa-download mr-2"></i>
                            Export
                            <i class="fas fa-chevron-down ml-2 text-xs"></i>
                        </button>

                        <div x-show="openExport" @click.away="openExport = false" x-transition class="absolute right-0 z-10 mt-2 w-48 rounded-lg shadow-lg bg-white ring-1 ring-black ring-opacity-5">
                            <div class="py-1">
                                <a href="{{ route('admin.attendance.today.export', ['format' => 'pdf', 'attendance_date' => request('attendance_date', now()->format('Y-m-d'))]) }}"
                                   class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-700">
                                    <i class="fas fa-file-pdf mr-3 text-red-500 group-hover:text-red-700"></i>
                                    <div>
                                        <div class="font-medium">Export PDF</div>
                                        <div class="text-xs text-gray-500">Laporan detail</div>
                                    </div>
                                </a>
                                <a href="{{ route('admin.attendance.today.export', ['format' => 'excel', 'attendance_date' => request('attendance_date', now()->format('Y-m-d'))]) }}"
                                   class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700">
                                    <i class="fas fa-file-excel mr-3 text-green-500 group-hover:text-green-700"></i>
                                    <div>
                                        <div class="font-medium">Export Excel</div>
                                        <div class="text-xs text-gray-500">Format spreadsheet</div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Date Filter Form -->
                    <form method="GET" action="{{ route('admin.attendance.index') }}" class="flex flex-wrap sm:flex-nowrap items-center gap-2 w-full sm:w-auto" id="date-filter-form">
                        <input type="hidden" name="tab" id="tab-input" value="{{ request('tab', 'today') }}">
                        <label for="attendance_date" class="text-sm font-medium text-gray-700 whitespace-nowrap">
                            <i class="fas fa-calendar-alt mr-1 text-blue-600"></i>
                            Pilih Tanggal:
                        </label>
                        <input type="date"
                               id="attendance_date"
                               name="attendance_date"
                               value="{{ request('attendance_date', now()->format('Y-m-d')) }}"
                               max="{{ now()->format('Y-m-d') }}"
                               class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm"
                               onchange="this.form.submit()">
                        @if(request('attendance_date'))
                            <a href="{{ route('admin.attendance.index', ['tab' => request('tab', 'today')]) }}"
                               class="px-3 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm transition">
                                <i class="fas fa-redo mr-1"></i>
                                Reset
                            </a>
                        @endif
                    </form>
                </div>
            </div>
        </div>
        {{-- ══════════════ MOBILE CARDS (visible < md) ══════════════ --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($workersWithAttendance as $worker)
                @php
                    $shift = null; $activeWorkerShift = null;
                    $selectedDate = \Carbon\Carbon::parse(request('attendance_date', now()->format('Y-m-d')));
                    $override = $worker->shiftOverrides->filter(fn($o) => $o->override_date->format('Y-m-d') === $selectedDate->format('Y-m-d'))->first();
                    if ($override && $override->shift) { $shift = $override->shift; } else {
                        $activeWorkerShift = $worker->workerShifts->filter(fn($ws) => $ws->isActiveOnDate($selectedDate))->sortByDesc('effective_from')->first();
                        if ($activeWorkerShift && $activeWorkerShift->shift) { $shift = $activeWorkerShift->shift; } elseif ($worker->shift) { $shift = $worker->shift; }
                    }
                    $schedule = $shift ? $shift->getScheduleForDate($selectedDate) : null;
                    $statusConfig = [
                        'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir', 'icon' => 'fa-check'],
                        'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat', 'icon' => 'fa-clock'],
                        'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir', 'icon' => 'fa-times'],
                        'off_day' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'label' => 'Libur Kerja', 'icon' => 'fa-calendar-times'],
                        'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit', 'icon' => 'fa-medkit'],
                        'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin', 'icon' => 'fa-info-circle'],
                        'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti', 'icon' => 'fa-umbrella-beach'],
                        'not_checked_in' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Belum Absen', 'icon' => 'fa-clock'],
                    ];
                    $st = $statusConfig[$worker->attendance_status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Unknown', 'icon' => 'fa-question'];
                @endphp
                <div class="p-4 hover:bg-gray-50">
                    {{-- Header: avatar + name + badge --}}
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                                <img class="h-10 w-10 rounded-full object-cover flex-shrink-0" src="{{ asset('storage/' . $worker->photo_url) }}" alt="">
                            @else
                                <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ substr($worker->name, 0, 1) }}</div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $worker->name }}</p>
                                <p class="text-xs text-gray-500">{{ $worker->nip ?? '-' }} &middot; {{ $worker->department->name ?? '-' }}</p>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full whitespace-nowrap {{ $st['bg'] }} {{ $st['text'] }}">
                            <i class="fas {{ $st['icon'] }} mr-0.5"></i>{{ $st['label'] }}
                        </span>
                    </div>

                    @if($worker->leave_request || ($worker->is_off_day ?? false))
                        <p class="text-sm text-gray-600 italic">{{ $worker->is_off_day ? 'Libur Kerja' : ($worker->leave_request->leaveType->name ?? 'Cuti') }}</p>
                    @elseif(!$shift)
                        <p class="text-sm text-gray-500 italic">Belum ada jadwal shift</p>
                    @else
                        {{-- Grid info --}}
                        <div class="grid grid-cols-2 gap-2 text-xs">
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-gray-500 mb-0.5">Shift</p>
                                <p class="font-semibold text-gray-800">{{ $shift->name }}</p>
                                <p class="text-gray-500">{{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}</p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-gray-500 mb-0.5">Jam Masuk</p>
                                @if($worker->check_in_time)
                                    <p class="font-semibold text-gray-800">{{ $worker->check_in_time->format('H:i:s') }}</p>
                                    @if($worker->is_late)
                                        <p class="text-red-500"><i class="fas fa-clock mr-0.5"></i>Terlambat -{{ $worker->late_minutes }}m</p>
                                    @else
                                        <p class="text-green-500"><i class="fas fa-check mr-0.5"></i>Tepat waktu</p>
                                    @endif
                                @else
                                    <p class="text-gray-400 italic">Belum check-in</p>
                                @endif
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2">
                                <p class="text-gray-500 mb-0.5">Jam Keluar</p>
                                @if($worker->check_out_time)
                                    <p class="font-semibold text-gray-800">{{ $worker->check_out_time->format('H:i:s') }}</p>
                                    @if($worker->is_early_leave && $worker->early_leave_minutes > 0)
                                        <p class="text-orange-600"><i class="fas fa-exclamation-triangle mr-0.5"></i>Awal {{ $worker->early_leave_minutes }}m</p>
                                    @endif
                                @elseif($worker->check_in_time)
                                    <p class="text-yellow-600 italic"><i class="fas fa-clock mr-0.5"></i>Belum check-out</p>
                                @else
                                    <p class="text-gray-400">-</p>
                                @endif
                            </div>
                            <div class="bg-gray-50 rounded-lg p-2 flex items-center justify-center">
                                @if($worker->today_attendance)
                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.attendance.show', $worker->today_attendance->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg" title="Detail"><i class="fas fa-eye"></i></a>
                                        @if(!$worker->check_out_time)
                                            <button onclick="checkOutWorker('{{ $worker->today_attendance->id }}', '{{ $worker->name }}')" class="p-2 text-orange-600 hover:bg-orange-50 rounded-lg" title="Check Out"><i class="fas fa-sign-out-alt"></i></button>
                                        @endif
                                    </div>
                                @else
                                    <button onclick="checkInWorker('{{ $worker->id }}', '{{ $worker->name }}')" class="p-2 text-green-600 hover:bg-green-50 rounded-lg" title="Check In"><i class="fas fa-sign-in-alt"></i></button>
                                @endif
                            </div>
                        </div>
                        @if($worker->today_attendance)
                            <p class="text-xs text-gray-500 mt-2"><i class="fas fa-map-marker-alt mr-1"></i>{{ config('attendance.location.name', '-') }}</p>
                        @endif
                    @endif
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-users text-3xl mb-2"></i>
                    <p class="font-medium">Tidak ada data pegawai</p>
                </div>
            @endforelse
        </div>

        {{-- ══════════════ DESKTOP TABLE (visible >= md) ══════════════ --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jadwal Shift</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jam Masuk</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Jam Keluar</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Status & Keterangan</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($workersWithAttendance as $worker)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ asset('storage/' . $worker->photo_url) }}"
                                             alt="{{ $worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($worker->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $worker->nip ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $worker->department->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        @php
                            $shift = null;
                            $activeWorkerShift = null;
                            $selectedDate = \Carbon\Carbon::parse(request('attendance_date', now()->format('Y-m-d')));

                            // Cek shift override untuk tanggal ini (filter pakai format agar Carbon vs Carbon cocok)
                            $override = $worker->shiftOverrides->filter(fn($o) => $o->override_date->format('Y-m-d') === $selectedDate->format('Y-m-d'))->first();

                            // Cek apakah ada tukar shift yang sudah dieksekusi untuk tanggal ini
                            $swapInfo = null;
                            $dateStr = $selectedDate->format('Y-m-d');
                            $executedSwap = $worker->shiftSwapRequestsAsRequester
                                ->where('status', 'executed')
                                ->filter(function($swap) use ($dateStr) {
                                    return $swap->swap_type === 'single_date'
                                        ? optional($swap->swap_date)->format('Y-m-d') === $dateStr
                                        : ($swap->swap_type === 'date_range'
                                            ? optional($swap->swap_start_date)->format('Y-m-d') <= $dateStr && optional($swap->swap_end_date)->format('Y-m-d') >= $dateStr
                                            : ($swap->swap_type === 'recurring' && is_array($swap->swap_dates)
                                                ? in_array($dateStr, array_map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'), $swap->swap_dates))
                                                : false));
                                })->first();
                            if (!$executedSwap) {
                                $executedSwap = $worker->shiftSwapRequestsAsTarget
                                    ->where('status', 'executed')
                                    ->filter(function($swap) use ($dateStr) {
                                        return $swap->swap_type === 'single_date'
                                            ? optional($swap->swap_date)->format('Y-m-d') === $dateStr
                                            : ($swap->swap_type === 'date_range'
                                                ? optional($swap->swap_start_date)->format('Y-m-d') <= $dateStr && optional($swap->swap_end_date)->format('Y-m-d') >= $dateStr
                                                : ($swap->swap_type === 'recurring' && is_array($swap->swap_dates)
                                                    ? in_array($dateStr, array_map(fn($d) => \Carbon\Carbon::parse($d)->format('Y-m-d'), $swap->swap_dates))
                                                    : false));
                                    })->first();
                            }
                            if ($executedSwap) {
                                // Tentukan partner tukar shift
                                if ($executedSwap->requester_id === $worker->id) {
                                    $swapInfo = $executedSwap->targetWorker;
                                } else {
                                    $swapInfo = $executedSwap->requester;
                                }
                            }

                            if ($override && $override->shift) {
                                $shift = $override->shift;
                            } else {
                                // Cari shift aktif dari workerShifts menggunakan isActiveOnDate
                                $activeWorkerShift = $worker->workerShifts
                                    ->filter(function($ws) use ($selectedDate) {
                                        return $ws->isActiveOnDate($selectedDate);
                                    })
                                    ->sortByDesc('effective_from')
                                    ->first();

                                if ($activeWorkerShift && $activeWorkerShift->shift) {
                                    $shift = $activeWorkerShift->shift;
                                } elseif ($worker->shift) {
                                    // Fallback ke shift default worker
                                    $shift = $worker->shift;
                                }
                            }

                            $schedule = $shift ? $shift->getScheduleForDate($selectedDate) : null;
                        @endphp
                        @if($worker->leave_request || ($worker->is_off_day ?? false))
                            @php
                                $statusText = $worker->is_off_day
                                    ? 'Libur Kerja'
                                    : ($worker->leave_request->leaveType->name ?? 'Cuti');
                            @endphp
                            <td class="px-6 py-4 text-center" colspan="5">
                                <div class="text-sm font-semibold text-gray-800">
                                    {{ $statusText }}
                                </div>
                                @if($worker->leave_request)
                                    <div class="text-xs text-gray-600 mt-1">
                                        {{ $worker->leave_request->start_date->format('d/m/Y') }} - {{ $worker->leave_request->end_date->format('d/m/Y') }}
                                    </div>
                                    @if($worker->leave_request->reason)
                                        <div class="text-xs text-gray-500 mt-1">
                                            {{ Str::limit($worker->leave_request->reason, 80) }}
                                        </div>
                                    @endif
                                @endif
                            </td>
                        @elseif(!$shift)
                            <td class="px-6 py-4 text-center" colspan="5">
                                <div class="text-sm font-semibold text-gray-600">
                                    Belum ada jadwal shift
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    Perlu setting jadwal pegawai
                                </div>
                            </td>
                        @else
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900 font-medium">{{ $shift->name }}</div>
                                <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}</div>
                                @if($override)
                                    <div class="text-xs text-blue-600">
                                        <i class="fas fa-exchange-alt mr-1"></i>Shift Override
                                    </div>
                                @elseif($swapInfo)
                                    <div class="text-xs text-indigo-600">
                                        <i class="fas fa-people-arrows mr-1"></i>Tukar Shift
                                        <span class="text-indigo-500">({{ $swapInfo->name ?? '-' }})</span>
                                    </div>
                                @elseif($activeWorkerShift)
                                    <div class="text-xs text-green-600">
                                        <i class="fas fa-check mr-1"></i>Shift Aktif
                                    </div>
                                @else
                                    <div class="text-xs text-gray-600">
                                        <i class="fas fa-clock mr-1"></i>Shift Default
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($worker->check_in_time)
                                    <div class="text-sm text-gray-900 font-medium">{{ $worker->check_in_time->format('H:i:s') }}</div>
                                    @if($worker->is_late)
                                        <div class="text-xs text-red-500">
                                            <i class="fas fa-clock mr-1"></i>Terlambat {{ $worker->late_minutes }} menit
                                        </div>
                                    @else
                                        <div class="text-xs text-green-500">
                                            <i class="fas fa-check mr-1"></i>Tepat waktu
                                        </div>
                                    @endif
                                    @if($shift)
                                        <div class="text-xs text-gray-400">Target: {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}</div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-400">Belum check-in</div>
                                    @if($shift)
                                        <div class="text-xs text-gray-500">Target: {{ \Carbon\Carbon::parse($schedule['start_time'])->format('H:i') }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @if($worker->check_out_time)
                                    <div class="text-sm text-gray-900 font-medium">{{ $worker->check_out_time->format('H:i:s') }}</div>
                                    @if($worker->is_early_leave && $worker->early_leave_minutes > 0)
                                        <div class="text-xs text-orange-600">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>Pulang awal {{ $worker->early_leave_minutes }} menit
                                        </div>
                                    @elseif($shift)
                                        <div class="text-xs text-green-500">
                                            <i class="fas fa-check mr-1"></i>Sesuai jadwal
                                        </div>
                                    @endif
                                    @if($shift)
                                        <div class="text-xs text-gray-400">Target: {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}</div>
                                    @endif
                                @elseif($worker->check_in_time)
                                    <div class="text-sm text-yellow-600">
                                        <i class="fas fa-clock mr-1"></i>Belum check-out
                                    </div>
                                    @if($shift)
                                        <div class="text-xs text-gray-500">Target: {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}</div>
                                    @endif
                                @else
                                    <div class="text-sm text-gray-400">-</div>
                                    @if($shift)
                                        <div class="text-xs text-gray-500">Target: {{ \Carbon\Carbon::parse($schedule['end_time'])->format('H:i') }}</div>
                                    @endif
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusConfig = [
                                        'present' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Hadir', 'icon' => 'fa-check'],
                                        'late' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Terlambat', 'icon' => 'fa-clock'],
                                        'absent' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Tidak Hadir', 'icon' => 'fa-times'],
                                        'off_day' => ['bg' => 'bg-rose-100', 'text' => 'text-rose-800', 'label' => 'Libur Kerja', 'icon' => 'fa-calendar-times'],
                                        'sick' => ['bg' => 'bg-orange-100', 'text' => 'text-orange-800', 'label' => 'Sakit', 'icon' => 'fa-medkit'],
                                        'permission' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Izin', 'icon' => 'fa-info-circle'],
                                        'leave' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Cuti', 'icon' => 'fa-umbrella-beach'],
                                        'not_checked_in' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Belum Absen', 'icon' => 'fa-clock'],
                                    ];
                                    $status = $statusConfig[$worker->attendance_status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => 'Unknown', 'icon' => 'fa-question'];
                                @endphp
                                <div class="space-y-2">
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }}">
                                        <i class="fas {{ $status['icon'] }} mr-1"></i>
                                        {{ $status['label'] }}
                                    </span>

                                    @if($worker->today_attendance)
                                        @if($worker->today_attendance->notes)
                                            <div class="text-sm text-gray-600 mt-1">
                                                <i class="fas fa-sticky-note mr-1 text-gray-400"></i>
                                                {{ Str::limit($worker->today_attendance->notes, 50) }}
                                            </div>
                                        @endif

                                        @if($worker->is_late && $worker->late_minutes > 0)
                                            <div class="text-xs text-red-600">
                                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                                Keterlambatan: {{ $worker->late_minutes }} menit
                                            </div>
                                        @endif

                                        <div class="text-xs text-gray-500">
                                            <i class="fas fa-map-marker-alt mr-1"></i>
                                            {{ config('attendance.location.name', '-') }}
                                        </div>
                                    @elseif($worker->attendance_status == 'not_checked_in')
                                        <div class="text-xs text-gray-500 mt-1">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Belum melakukan absensi pada tanggal {{ \Carbon\Carbon::parse(request('attendance_date', now()->format('Y-m-d')))->isoFormat('D MMMM Y') }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center space-x-1">
                                    @if($worker->today_attendance)
                                        <a href="{{ route('admin.attendance.show', $worker->today_attendance->id) }}"
                                           class="p-2 text-blue-600 hover:text-blue-900 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                            <i class="fas fa-eye w-4 h-4"></i>
                                        </a>
                                        @if(!$worker->check_out_time)
                                            <button onclick="checkOutWorker('{{ $worker->today_attendance->id }}', '{{ $worker->name }}')"
                                                    class="p-2 text-orange-600 hover:text-orange-900 hover:bg-orange-50 rounded-lg transition-colors" title="Check Out">
                                                <i class="fas fa-sign-out-alt w-4 h-4"></i>
                                            </button>
                                        @endif
                                    @else
                                        <button onclick="checkInWorker('{{ $worker->id }}', '{{ $worker->name }}')"
                                                class="p-2 text-green-600 hover:text-green-900 hover:bg-green-50 rounded-lg transition-colors" title="Check In">
                                            <i class="fas fa-sign-in-alt w-4 h-4"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada data pegawai</p>
                                <p class="text-sm">Belum ada pegawai yang terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Table for Attendance History (Statistik) - Hidden by default -->
    <div id="history-view" class="bg-white rounded-lg shadow-md overflow-hidden @if(request('tab') != 'history') hidden @endif">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Absensi</h3>
                    <p class="text-sm text-gray-500 mt-1">
                        Statistik kehadiran
                        @if($statsFilters['period'] === 'year')
                            Tahun {{ $statsFilters['year'] }}
                        @elseif($statsFilters['period'] === 'custom')
                            {{ \Carbon\Carbon::parse($statsFilters['start_date'])->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($statsFilters['end_date'])->translatedFormat('d M Y') }}
                        @else
                            {{ \Carbon\Carbon::create($statsFilters['year'], $statsFilters['month'], 1)->translatedFormat('F Y') }}
                        @endif
                    </p>
                </div>
            </div>

            <!-- Filter Periode Statistik -->
            <div x-data="{ showStatsPeriodForm: false }" class="mb-4">
                <button @click="showStatsPeriodForm = !showStatsPeriodForm"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition duration-200">
                    <i class="fas fa-calendar-alt mr-2"></i>
                    Ubah Periode
                    <i class="fas fa-chevron-down ml-2 transform transition-transform" :class="{ 'rotate-180': showStatsPeriodForm }"></i>
                </button>

                <div x-show="showStatsPeriodForm" x-collapse class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    <form method="GET" action="{{ route('admin.attendance.index') }}" id="stats-period-form">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Periode</label>
                                <select name="stats_period" id="stats_period" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="month" {{ ($statsFilters['period'] ?? 'month') == 'month' ? 'selected' : '' }}>Per Bulan</option>
                                    <option value="year" {{ ($statsFilters['period'] ?? '') == 'year' ? 'selected' : '' }}>Per Tahun</option>
                                    <option value="custom" {{ ($statsFilters['period'] ?? '') == 'custom' ? 'selected' : '' }}>Custom (Pilih Tanggal)</option>
                                </select>
                            </div>

                            <div id="month-field" style="display: {{ ($statsFilters['period'] ?? 'month') == 'month' ? 'block' : 'none' }}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Bulan</label>
                                <select name="stats_month" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @for($m = 1; $m <= 12; $m++)
                                        <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" {{ ($statsFilters['month'] ?? now()->format('m')) == str_pad($m, 2, '0', STR_PAD_LEFT) ? 'selected' : '' }}>
                                            {{ \Carbon\Carbon::create(null, $m, 1)->translatedFormat('F') }}
                                        </option>
                                    @endfor
                                </select>
                            </div>

                            <div id="year-field" style="display: {{ in_array($statsFilters['period'] ?? 'month', ['month', 'year']) ? 'block' : 'none' }}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tahun</label>
                                <select name="stats_year" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                                        <option value="{{ $y }}" {{ ($statsFilters['year'] ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                            </div>

                            <div id="date-from-field" style="display: {{ ($statsFilters['period'] ?? '') == 'custom' ? 'block' : 'none' }}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                                <input type="date" name="stats_date_from" value="{{ $statsFilters['date_from'] ?? '' }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>

                            <div id="date-to-field" style="display: {{ ($statsFilters['period'] ?? '') == 'custom' ? 'block' : 'none' }}">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                                <input type="date" name="stats_date_to" value="{{ $statsFilters['date_to'] ?? '' }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                        </div>

                        <div class="flex gap-2 mt-4">
                            <button type="submit" class="px-6 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg transition duration-200">
                                <i class="fas fa-filter mr-2"></i>
                                Terapkan Filter
                            </button>
                            <a href="{{ route('admin.attendance.index') }}" class="px-6 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition duration-200">
                                <i class="fas fa-redo mr-2"></i>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- ══════════════ HISTORY MOBILE CARDS (visible < md) ══════════════ --}}
        <div class="md:hidden divide-y divide-gray-200">
            @forelse($workersWithAttendance as $worker)
                @php
                    $stats = $workerStats[$worker->id] ?? [
                        'total_present' => 0, 'total_late' => 0, 'total_late_minutes' => 0, 'avg_late_minutes' => 0,
                        'total_early_leave' => 0, 'total_early_leave_minutes' => 0, 'avg_early_leave_minutes' => 0,
                        'total_perfect' => 0, 'total_absent' => 0, 'total_sick' => 0, 'total_permission' => 0, 'total_leave' => 0
                    ];
                @endphp
                <div class="p-4 hover:bg-gray-50">
                    {{-- Header --}}
                    <div class="flex items-center gap-3 mb-3">
                        @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                            <img class="h-10 w-10 rounded-full object-cover flex-shrink-0" src="{{ asset('storage/' . $worker->photo_url) }}" alt="">
                        @else
                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">{{ substr($worker->name, 0, 1) }}</div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ $worker->name }}</p>
                            <p class="text-xs text-gray-500">{{ $worker->nip ?? '-' }} &middot; {{ $worker->department->name ?? '-' }}</p>
                        </div>
                        <a href="{{ route('admin.attendance.history', $worker->id) }}" class="p-2 text-purple-600 hover:bg-purple-50 rounded-lg flex-shrink-0" title="Statistik">
                            <i class="fas fa-chart-bar"></i>
                        </a>
                    </div>
                    {{-- Stats grid --}}
                    <div class="grid grid-cols-3 gap-2 text-xs">
                        <div class="bg-green-50 border border-green-200 rounded-lg p-2 text-center">
                            <p class="text-lg font-bold text-green-800">{{ $stats['total_present'] }}</p>
                            <p class="text-green-600">Hadir</p>
                        </div>
                        <div class="bg-emerald-50 border border-emerald-200 rounded-lg p-2 text-center">
                            <p class="text-lg font-bold text-emerald-800">{{ $stats['total_perfect'] }}</p>
                            <p class="text-emerald-600">Sempurna</p>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-lg p-2 text-center">
                            <p class="text-lg font-bold text-red-800">{{ $stats['total_absent'] }}</p>
                            <p class="text-red-600">Absen</p>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-2 text-center">
                            <p class="text-lg font-bold text-yellow-800">{{ $stats['total_late'] }}</p>
                            <p class="text-yellow-600">Terlambat</p>
                            @if($stats['total_late'] > 0)
                                <p class="text-yellow-500 mt-0.5">~{{ $stats['avg_late_minutes'] }}m</p>
                            @endif
                        </div>
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-2 text-center">
                            <p class="text-lg font-bold text-orange-800">{{ $stats['total_early_leave'] }}</p>
                            <p class="text-orange-600">Plg Cepat</p>
                            @if($stats['total_early_leave'] > 0)
                                <p class="text-orange-500 mt-0.5">~{{ $stats['avg_early_leave_minutes'] }}m</p>
                            @endif
                        </div>
                        @if($stats['total_sick'] > 0 || $stats['total_permission'] > 0 || $stats['total_leave'] > 0)
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-2 text-center">
                                @if($stats['total_sick'] > 0)<p class="text-gray-700">Sakit: {{ $stats['total_sick'] }}</p>@endif
                                @if($stats['total_permission'] > 0)<p class="text-gray-700">Izin: {{ $stats['total_permission'] }}</p>@endif
                                @if($stats['total_leave'] > 0)<p class="text-gray-700">Cuti: {{ $stats['total_leave'] }}</p>@endif
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-8 text-center text-gray-500">
                    <i class="fas fa-users text-3xl mb-2"></i>
                    <p class="font-medium">Tidak ada data pegawai</p>
                </div>
            @endforelse
        </div>

        {{-- ══════════════ HISTORY DESKTOP TABLE (visible >= md) ══════════════ --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gradient-to-r from-blue-600 to-blue-700 text-white">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider">Pegawai</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Total Hadir</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Sempurna</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Terlambat</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Pulang Cepat</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Tidak Hadir</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($workersWithAttendance as $worker)
                    @php
                        $stats = $workerStats[$worker->id] ?? [
                            'total_present' => 0,
                            'total_late' => 0,
                            'total_late_minutes' => 0,
                            'avg_late_minutes' => 0,
                            'total_early_leave' => 0,
                            'total_early_leave_minutes' => 0,
                            'avg_early_leave_minutes' => 0,
                            'total_perfect' => 0,
                            'total_absent' => 0,
                            'total_sick' => 0,
                            'total_permission' => 0,
                            'total_leave' => 0
                        ];
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if($worker->photo_url && Storage::disk('public')->exists($worker->photo_url))
                                        <img class="h-10 w-10 rounded-full object-cover"
                                             src="{{ asset('storage/' . $worker->photo_url) }}"
                                             alt="{{ $worker->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-bold text-sm">
                                            {{ substr($worker->name, 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $worker->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $worker->nip ?? '-' }}</div>
                                    <div class="text-xs text-gray-500">{{ $worker->department->name ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold text-green-800">{{ $stats['total_present'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">hari</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold text-emerald-800">{{ $stats['total_perfect'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">hari</span>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold text-yellow-800">{{ $stats['total_late'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">kali</span>
                                @if($stats['total_late'] > 0)
                                    <div class="mt-2 text-xs bg-yellow-50 px-2 py-1 rounded border border-yellow-200">
                                        <div class="text-yellow-700">Total: <span class="font-semibold">{{ $stats['total_late_minutes'] }}</span> menit</div>
                                        <div class="text-yellow-600 mt-0.5">Rata-rata: <span class="font-semibold">{{ $stats['avg_late_minutes'] }}</span> menit</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold text-orange-800">{{ $stats['total_early_leave'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">kali</span>
                                @if($stats['total_early_leave'] > 0)
                                    <div class="mt-2 text-xs bg-orange-50 px-2 py-1 rounded border border-orange-200">
                                        <div class="text-orange-700">Total: <span class="font-semibold">{{ $stats['total_early_leave_minutes'] }}</span> menit</div>
                                        <div class="text-orange-600 mt-0.5">Rata-rata: <span class="font-semibold">{{ $stats['avg_early_leave_minutes'] }}</span> menit</div>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold text-red-800">{{ $stats['total_absent'] }}</span>
                                <span class="text-xs text-gray-500 mt-1">hari</span>
                                @if($stats['total_absent'] > 0)
                                    <div class="mt-2 text-xs bg-red-50 px-2 py-1 rounded border border-red-200 space-y-0.5">
                                        @if($stats['total_sick'] > 0)
                                            <div class="text-red-700">Sakit: <span class="font-semibold">{{ $stats['total_sick'] }}</span></div>
                                        @endif
                                        @if($stats['total_permission'] > 0)
                                            <div class="text-red-700">Izin: <span class="font-semibold">{{ $stats['total_permission'] }}</span></div>
                                        @endif
                                        @if($stats['total_leave'] > 0)
                                            <div class="text-red-700">Cuti: <span class="font-semibold">{{ $stats['total_leave'] }}</span></div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center space-x-1">
                                <!-- Tombol Statistik Pegawai -->
                                <a href="{{ route('admin.attendance.history', $worker->id) }}"
                                   class="p-2 text-purple-600 hover:text-purple-900 hover:bg-purple-50 rounded-lg transition-colors" title="Lihat Statistik">
                                    <i class="fas fa-chart-bar w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="text-gray-500">
                                <i class="fas fa-users text-4xl mb-4"></i>
                                <p class="text-lg font-medium">Tidak ada data pegawai</p>
                                <p class="text-sm">Belum ada pegawai yang terdaftar</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Toggle between today view and history view
    document.addEventListener('DOMContentLoaded', function() {
        const btnTodayView = document.getElementById('btn-today-view');
        const btnHistoryView = document.getElementById('btn-history-view');
        const todayView = document.getElementById('today-view');
        const historyView = document.getElementById('history-view');
        const tabInput = document.getElementById('tab-input');
        const historyTabInput = document.getElementById('history-tab-input');
        const attendanceDateInput = document.getElementById('attendance_date');

        if (attendanceDateInput) {
            const localToday = new Date();
            const yyyy = localToday.getFullYear();
            const mm = String(localToday.getMonth() + 1).padStart(2, '0');
            const dd = String(localToday.getDate()).padStart(2, '0');
            attendanceDateInput.max = `${yyyy}-${mm}-${dd}`;
        }

        // Function to update URL parameter without refresh
        function updateUrlParameter(key, value) {
            const url = new URL(window.location);
            url.searchParams.set(key, value);
            window.history.pushState({}, '', url);
        }

        // Function to switch to history view
        function switchToHistoryView() {
            // Show history view (statistik)
            historyView.classList.remove('hidden');
            todayView.classList.add('hidden');

            // Update URL and hidden inputs
            updateUrlParameter('tab', 'history');
            if (tabInput) tabInput.value = 'history';
            if (historyTabInput) historyTabInput.value = 'history';
        }

        // Function to switch to today view
        function switchToTodayView() {
            // Show today view (detail)
            todayView.classList.remove('hidden');
            historyView.classList.add('hidden');

            // Update URL and hidden inputs
            updateUrlParameter('tab', 'today');
            if (tabInput) tabInput.value = 'today';
            if (historyTabInput) historyTabInput.value = 'today';
        }

        // Event listeners for tab buttons
        btnHistoryView.addEventListener('click', switchToHistoryView);
        btnTodayView.addEventListener('click', switchToTodayView);

        // Restore active tab from URL parameter on page load
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'today'; // Default to 'today'

        console.log('Current URL params:', window.location.search);
        console.log('Active tab from URL:', activeTab);

        if (activeTab === 'today') {
            console.log('Switching to TODAY view');
            switchToTodayView();
        } else if (activeTab === 'history') {
            console.log('Switching to HISTORY view');
            switchToHistoryView();
        }

        // Toggle fields based on stats period selection
        const statsPeriodSelect = document.getElementById('stats_period');
        if (statsPeriodSelect) {
            statsPeriodSelect.addEventListener('change', function() {
                const period = this.value;
                const monthField = document.getElementById('month-field');
                const yearField = document.getElementById('year-field');
                const dateFromField = document.getElementById('date-from-field');
                const dateToField = document.getElementById('date-to-field');

                if (period === 'month') {
                    monthField.style.display = 'block';
                    yearField.style.display = 'block';
                    dateFromField.style.display = 'none';
                    dateToField.style.display = 'none';
                } else if (period === 'year') {
                    monthField.style.display = 'none';
                    yearField.style.display = 'block';
                    dateFromField.style.display = 'none';
                    dateToField.style.display = 'none';
                } else if (period === 'custom') {
                    monthField.style.display = 'none';
                    yearField.style.display = 'none';
                    dateFromField.style.display = 'block';
                    dateToField.style.display = 'block';
                }
            });
        }
    });

    // Check in worker function - redirect to check-in form
    function checkInWorker(workerId, workerName) {
        // Redirect to check-in form page with worker data
        window.location.href = `/attendance/${workerId}/check-in`;
    }

    // Check out worker function - redirect to check-out form
    function checkOutWorker(attendanceId, workerName) {
        // Redirect to check-out form page using correct URL pattern
        window.location.href = `/attendance/${attendanceId}/check-out`;
    }

    // Auto refresh every 5 minutes for real-time data (only for today view)
    setInterval(function() {
        if (!document.getElementById('today-view').classList.contains('hidden')) {
            window.location.reload();
        }
    }, 300000);

    // Real-time clock
    function updateClock() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID');
        const dateString = now.toLocaleDateString('id-ID', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        // Update clock if element exists
        const clockElement = document.getElementById('real-time-clock');
        if (clockElement) {
            clockElement.innerHTML = `<i class="fas fa-clock mr-2"></i>${timeString} - ${dateString}`;
        }
    }

    // Update clock every second
    setInterval(updateClock, 1000);
    updateClock(); // Initial call
</script>
@endpush
@endsection
