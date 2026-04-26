@extends('layouts.admin')

@section('title', 'Manajemen Cuti')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Manajemen Cuti"
        description="Kelola pengajuan cuti pegawai"
        icon="fas fa-calendar-times">
        <x-slot:actions>
            {{-- Export Buttons --}}
            <x-export-buttons :route="route('admin.leave.export')" title="Export Cuti">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition">
                        <option value="">Semua Status</option>
                        <option value="pending">Menunggu</option>
                        <option value="approved">Disetujui</option>
                        <option value="rejected">Ditolak</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </x-export-buttons>

            @can('create-leave')
                <x-button
                    variant="success"
                    icon="fas fa-plus"
                    onclick="window.location.href='{{ route('admin.leave.create') }}'">
                    Ajukan Cuti
                </x-button>
            @endcan
        </x-slot:actions>
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <x-stats-card
            title="Total Pengajuan"
            :value="$statistics['total'] ?? 0"
            icon="fas fa-file-alt"
            color="blue" />

        <x-stats-card
            title="Menunggu"
            :value="$statistics['pending'] ?? 0"
            icon="fas fa-clock"
            color="yellow" />

        <x-stats-card
            title="Disetujui"
            :value="$statistics['approved'] ?? 0"
            icon="fas fa-check-circle"
            color="green" />

        <x-stats-card
            title="Ditolak"
            :value="$statistics['rejected'] ?? 0"
            icon="fas fa-times-circle"
            color="red" />

        <x-stats-card
            title="Dibatalkan"
            :value="$statistics['cancelled'] ?? 0"
            icon="fas fa-ban"
            color="gray" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.leave.index') }}">
        <x-form.select
            name="worker_id"
            label="Pegawai"
            :selected="$filters['worker_id'] ?? ''"
            placeholder="Semua Pegawai">
            @foreach($workers as $worker)
                <option value="{{ $worker->id }}">{{ $worker->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="leave_type_id"
            label="Jenis Cuti"
            :selected="$filters['leave_type_id'] ?? ''"
            placeholder="Semua Jenis">
            @foreach($leaveTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="status"
            label="Status"
            :options="[
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan'
            ]"
            :selected="$filters['status'] ?? ''"
            placeholder="Semua Status" />

        <x-form.select
            name="month"
            label="Bulan"
            :selected="$filters['month'] ?? ''"
            placeholder="Semua Bulan">
            @for($i = 1; $i <= 12; $i++)
                <option value="{{ $i }}">{{ DateTime::createFromFormat('!m', $i)->format('F') }}</option>
            @endfor
        </x-form.select>

        <x-form.select
            name="year"
            label="Tahun"
            :selected="$filters['year'] ?? ''"
            placeholder="Semua Tahun">
            @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                <option value="{{ $y }}">{{ $y }}</option>
            @endfor
        </x-form.select>
    </x-filter-section>

    {{-- Leave Requests Table --}}
    <x-card>
        @if(isset($leaves) && $leaves->isEmpty())
            <x-empty-state
                icon="fas fa-calendar-times"
                title="Tidak ada data pengajuan cuti"
                description="Pengajuan cuti akan ditampilkan di sini"
                actionText="Ajukan Cuti"
                :actionUrl="route('admin.leave.create')" />
        @elseif(isset($leaves))
            {{-- Mobile View --}}
            <div class="md:hidden space-y-4 p-4 bg-gray-50/50">
                @foreach($leaves as $index => $leave)
                    @php
                        $statusBadges = [
                            'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                            'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                        ];
                        $badge = $statusBadges[$leave->status] ?? ['variant' => 'secondary', 'label' => ucfirst($leave->status)];
                    @endphp
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-4 border-b border-gray-100 pb-3">
                            <div>
                                <span class="text-xs text-gray-500 font-medium">#{{ $leaves->firstItem() + $index }}</span>
                                <div class="text-xs text-gray-400 mt-0.5 font-medium">
                                    <i class="far fa-clock mr-1"></i>{{ $leave->created_at->format('d M Y, H:i') }}
                                </div>
                            </div>
                            <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-500 font-bold text-sm border border-gray-200">
                                    {{ substr($leave->worker->name ?? '?', 0, 1) }}
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-400 block mb-0.5">Pegawai</span>
                                    <span class="text-sm font-bold text-gray-900 leading-tight block">{{ $leave->worker->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500">{{ $leave->worker->nip ?? '-' }}</span>
                                </div>
                            </div>

                            <div class="bg-gray-50 rounded-xl p-3 grid grid-cols-2 gap-4 border border-gray-100">
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                        <i class="fas fa-tags text-indigo-400 mr-1"></i>Jenis Cuti
                                    </span>
                                    <span class="text-sm font-bold text-indigo-700 block">{{ $leave->leaveType->name ?? '-' }}</span>
                                    <span class="text-xs text-gray-500 font-medium">{{ $leave->total_days ?? 0 }} hari</span>
                                </div>
                                <div>
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-gray-500 block mb-1.5">
                                        <i class="fas fa-calendar-alt text-emerald-400 mr-1"></i>Periode
                                    </span>
                                    <span class="text-xs font-bold text-emerald-700 block">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M y') }} - {{ \Carbon\Carbon::parse($leave->end_date)->format('d M y') }}</span>
                                    <span class="text-[10px] text-gray-400 font-medium">Tanggal cuti</span>
                                </div>
                            </div>

                            @if($leave->reason)
                                <div class="bg-blue-50/50 rounded-lg p-3 border border-blue-100/50">
                                    <span class="text-[10px] uppercase tracking-wider font-bold text-blue-500 block mb-1">
                                        <i class="fas fa-comment-alt mr-1"></i>Alasan
                                    </span>
                                    <p class="text-xs text-gray-700 italic leading-relaxed">"{{ Str::limit($leave->reason, 100) }}"</p>
                                </div>
                            @endif
                        </div>

                        <div class="flex justify-end gap-3 mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ route('admin.leave.show', $leave->id) }}"
                               class="inline-flex items-center rounded-xl bg-blue-600 px-5 py-2.5 text-xs font-bold text-white hover:bg-blue-700 shadow-sm transition active:scale-95">
                                <i class="fas fa-search mr-1.5"></i> Periksa Pengajuan
                            </a>
                            <form action="{{ route('admin.leave.destroy', $leave->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center rounded-xl bg-red-50 px-4 py-2 text-xs font-bold text-red-700 hover:bg-red-100 transition active:scale-95"
                                        title="Hapus"
                                        onclick="event.preventDefault(); showDeleteConfirm(() => this.closest('form').submit());">
                                    <i class="fas fa-trash-alt mr-1.5"></i> Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop View --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">No</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Pegawai</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Jenis Cuti</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Tanggal</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Durasi</th>
                            <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                            <th class="px-6 py-3 text-right font-semibold text-gray-700">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 bg-white">
                        @foreach($leaves as $index => $leave)
                            <tr>
                                <td class="px-6 py-3">{{ $leaves->firstItem() + $index }}</td>
                                <td class="px-6 py-3">
                                    <div class="font-medium text-gray-900">{{ $leave->worker->name ?? '-' }}</div>
                                    <div class="text-sm text-gray-500">{{ $leave->worker->nip ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $leave->leaveType->name ?? '-' }}</td>
                                <td class="px-6 py-3">
                                    <div class="text-sm">{{ \Carbon\Carbon::parse($leave->start_date)->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-500">s/d {{ \Carbon\Carbon::parse($leave->end_date)->format('d M Y') }}</div>
                                </td>
                                <td class="px-6 py-3 text-gray-700">{{ $leave->total_days ?? 0 }} hari</td>
                                <td class="px-6 py-3">
                                    @php
                                        $statusBadges = [
                                            'pending' => ['variant' => 'warning', 'label' => 'Menunggu'],
                                            'approved' => ['variant' => 'success', 'label' => 'Disetujui'],
                                            'rejected' => ['variant' => 'danger', 'label' => 'Ditolak'],
                                            'cancelled' => ['variant' => 'secondary', 'label' => 'Dibatalkan'],
                                        ];
                                        $badge = $statusBadges[$leave->status] ?? ['variant' => 'secondary', 'label' => $leave->status];
                                    @endphp
                                    <x-badge :variant="$badge['variant']">{{ $badge['label'] }}</x-badge>
                                </td>
                                <td class="px-6 py-3 text-right">
                                    <div class="flex justify-end space-x-2">
                                        <a href="{{ route('admin.leave.show', $leave->id) }}" class="text-blue-600 hover:text-blue-900" title="Detail">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.leave.destroy', $leave->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus" onclick="event.preventDefault(); showDeleteConfirm(() => this.closest('form').submit());">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($leaves->hasPages())
                <div class="mt-4">
                    <x-pagination :paginator="$leaves" />
                </div>
            @endif
        @else
            <x-empty-state
                icon="fas fa-calendar-times"
                title="Tidak ada data pengajuan cuti"
                description="Gunakan filter di atas untuk melihat data" />
        @endif
    </x-card>
</div>
@endsection
