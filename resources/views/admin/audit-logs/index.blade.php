@extends('layouts.admin')

@section('title', 'Audit Log')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Audit Log"
        description="Riwayat aktivitas dan perubahan data dalam sistem"
        icon="fas fa-history">
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <x-stats-card
            title="Total Log"
            :value="$stats['total']"
            icon="fas fa-list"
            color="blue" />

        <x-stats-card
            title="Hari Ini"
            :value="$stats['today']"
            icon="fas fa-calendar-day"
            color="green" />

        <x-stats-card
            title="Minggu Ini"
            :value="$stats['this_week']"
            icon="fas fa-calendar-week"
            color="purple" />

        <x-stats-card
            title="Dibuat"
            :value="$stats['creates']"
            icon="fas fa-plus-circle"
            color="green" />

        <x-stats-card
            title="Diubah"
            :value="$stats['updates']"
            icon="fas fa-edit"
            color="yellow" />

        <x-stats-card
            title="Dihapus"
            :value="$stats['deletes']"
            icon="fas fa-trash"
            color="red" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('admin.audit-logs.index') }}">
        <x-form.input
            name="search"
            label="Pencarian"
            placeholder="Cari deskripsi, user, IP..."
            :value="$filters['search'] ?? ''" />

        <x-form.select
            name="action"
            label="Aksi"
            :selected="$filters['action'] ?? ''"
            placeholder="Semua Aksi">
            @foreach($actions as $action)
                <option value="{{ $action }}">{{ ucfirst($action) }}</option>
            @endforeach
        </x-form.select>

        <x-form.select
            name="model_type"
            label="Tipe Data"
            :selected="$filters['model_type'] ?? ''"
            placeholder="Semua Tipe">
            @foreach($modelTypes as $type)
                <option value="{{ $type }}">{{ $type }}</option>
            @endforeach
        </x-form.select>

        <x-form.input
            name="date_from"
            label="Dari Tanggal"
            type="date"
            :value="$filters['date_from'] ?? ''" />

        <x-form.input
            name="date_to"
            label="Sampai Tanggal"
            type="date"
            :value="$filters['date_to'] ?? ''" />
    </x-filter-section>

    {{-- Audit Log Table --}}
    <x-card>
        @if($logs->isEmpty())
            <x-empty-state
                icon="fas fa-history"
                title="Belum ada log aktivitas"
                description="Log aktivitas akan muncul saat ada perubahan data dalam sistem." />
        @else
            {{-- Mobile Card Layout --}}
            <div class="md:hidden space-y-4">
                @foreach($logs as $log)
                    <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-sm">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex items-center gap-2">
                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-green-700 text-xs font-bold">
                                        {{ substr($log->user_name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $log->user_name ?? 'System' }}</div>
                                    <div class="text-xs text-gray-500">{{ $log->created_at->format('d/m/Y H:i:s') }}</div>
                                </div>
                            </div>
                            @php $mobileBadge = $log->action_badge; @endphp
                            <x-badge :variant="$mobileBadge['variant']">
                                <i class="{{ $mobileBadge['icon'] }} mr-1"></i>
                                {{ $mobileBadge['label'] }}
                            </x-badge>
                        </div>

                        <div class="space-y-2 text-sm">
                            <div>
                                <span class="font-medium text-gray-500">Tipe Data:</span>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $log->model_name }}
                                </span>
                            </div>
                            @if($log->description)
                                <div>
                                    <span class="font-medium text-gray-500">Deskripsi:</span>
                                    <span class="text-gray-600">{{ Str::limit($log->description, 80) }}</span>
                                </div>
                            @endif
                            <div>
                                <span class="font-medium text-gray-500">IP:</span>
                                <span class="text-xs text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</span>
                            </div>
                        </div>

                        <div class="flex justify-end mt-3 pt-3 border-t border-gray-100">
                            <a href="{{ route('admin.audit-logs.show', $log->id) }}"
                               class="text-blue-600 hover:text-blue-900 text-sm font-medium">
                                <i class="fas fa-eye mr-1"></i> Detail
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Desktop Table --}}
            <div class="hidden md:block">
            <x-table>
                <x-slot:thead>
                    <x-table.row>
                        <x-table.cell header>Waktu</x-table.cell>
                        <x-table.cell header>User</x-table.cell>
                        <x-table.cell header>Aksi</x-table.cell>
                        <x-table.cell header>Tipe Data</x-table.cell>
                        <x-table.cell header>Deskripsi</x-table.cell>
                        <x-table.cell header>IP Address</x-table.cell>
                        <x-table.cell header>Detail</x-table.cell>
                    </x-table.row>
                </x-slot:thead>

                @foreach($logs as $log)
                    <x-table.row>
                        {{-- Waktu --}}
                        <x-table.cell>
                            <div class="text-sm text-gray-900">{{ $log->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                        </x-table.cell>

                        {{-- User --}}
                        <x-table.cell>
                            <div class="flex items-center">
                                <div class="h-8 w-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                                    <span class="text-green-700 text-xs font-bold">
                                        {{ substr($log->user_name ?? 'S', 0, 1) }}
                                    </span>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $log->user_name ?? 'System' }}</div>
                                </div>
                            </div>
                        </x-table.cell>

                        {{-- Aksi --}}
                        <x-table.cell>
                            @php $badge = $log->action_badge; @endphp
                            <x-badge :variant="$badge['variant']">
                                <i class="{{ $badge['icon'] }} mr-1"></i>
                                {{ $badge['label'] }}
                            </x-badge>
                        </x-table.cell>

                        {{-- Tipe Data --}}
                        <x-table.cell>
                            <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $log->model_name }}
                            </span>
                        </x-table.cell>

                        {{-- Deskripsi --}}
                        <x-table.cell>
                            <p class="text-sm text-gray-600 max-w-xs truncate" title="{{ $log->description }}">
                                {{ $log->description ?? '-' }}
                            </p>
                        </x-table.cell>

                        {{-- IP Address --}}
                        <x-table.cell>
                            <span class="text-xs text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</span>
                        </x-table.cell>

                        {{-- Detail --}}
                        <x-table.cell>
                            <a href="{{ route('admin.audit-logs.show', $log->id) }}"
                               class="text-blue-600 hover:text-blue-900 transition"
                               title="Lihat Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </x-table.cell>
                    </x-table.row>
                @endforeach
            </x-table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                <x-pagination :paginator="$logs" />
            </div>
        @endif
    </x-card>
</div>
@endsection
