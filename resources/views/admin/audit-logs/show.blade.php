@extends('layouts.admin')

@section('title', 'Detail Audit Log')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Detail Audit Log"
        description="Detail lengkap perubahan data"
        icon="fas fa-history">
        <x-slot:actions>
            <x-button
                variant="secondary"
                icon="fas fa-arrow-left"
                onclick="window.location.href='{{ route('admin.audit-logs.index') }}'">
                Kembali
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Info --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Event Summary --}}
            <x-card>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-info-circle text-green-600 mr-2"></i>
                        Informasi Event
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Aksi</label>
                            @php $badge = $log->action_badge; @endphp
                            <div class="mt-1">
                                <x-badge :variant="$badge['variant']">
                                    <i class="{{ $badge['icon'] }} mr-1"></i>
                                    {{ $badge['label'] }}
                                </x-badge>
                            </div>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tipe Data</label>
                            <p class="mt-1 text-sm text-gray-900 font-medium">{{ $log->model_name }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">ID Data</label>
                            <p class="mt-1 text-sm text-gray-600 font-mono">{{ $log->auditable_id ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Waktu</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $log->created_at->format('d M Y, H:i:s') }}</p>
                            <p class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    @if($log->description)
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Deskripsi</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $log->description }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Changes --}}
            @if($log->action === 'updated' && $log->old_values && $log->new_values)
                <x-card>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-exchange-alt text-yellow-600 mr-2"></i>
                            Perubahan Data
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Lama</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai Baru</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($log->new_values as $field => $newValue)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ str_replace('_', ' ', ucfirst($field)) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-red-50 text-red-700 font-mono text-xs">
                                                    {{ is_array($log->old_values[$field] ?? null) ? json_encode($log->old_values[$field]) : ($log->old_values[$field] ?? '-') }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="inline-flex items-center px-2 py-1 rounded bg-green-50 text-green-700 font-mono text-xs">
                                                    {{ is_array($newValue) ? json_encode($newValue) : ($newValue ?? '-') }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-card>
            @endif

            {{-- Created Values --}}
            @if($log->action === 'created' && $log->new_values)
                <x-card>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                            Data yang Dibuat
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($log->new_values as $field => $value)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ str_replace('_', ' ', ucfirst($field)) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                                {{ is_array($value) ? json_encode($value) : ($value ?? '-') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-card>
            @endif

            {{-- Deleted Values --}}
            @if($log->action === 'deleted' && $log->old_values)
                <x-card>
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-trash text-red-600 mr-2"></i>
                            Data yang Dihapus
                        </h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($log->old_values as $field => $value)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-medium text-gray-900">
                                                {{ str_replace('_', ' ', ucfirst($field)) }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">
                                                {{ is_array($value) ? json_encode($value) : ($value ?? '-') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        {{-- Sidebar Info --}}
        <div class="space-y-6">
            {{-- User Info --}}
            <x-card>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-user text-green-600 mr-2"></i>
                        Pengguna
                    </h3>

                    <div class="flex items-center mb-4">
                        <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
                            <span class="text-green-700 text-lg font-bold">
                                {{ substr($log->user_name ?? 'S', 0, 1) }}
                            </span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-semibold text-gray-900">{{ $log->user_name ?? 'System' }}</p>
                            @if($log->user)
                                <p class="text-xs text-gray-500">{{ $log->user->email }}</p>
                            @endif
                        </div>
                    </div>

                    @if($log->user)
                        <div class="text-xs text-gray-500">
                            <p>Role: {{ $log->user->getRoleNames()->first() ?? '-' }}</p>
                        </div>
                    @endif
                </div>
            </x-card>

            {{-- Technical Info --}}
            <x-card>
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        <i class="fas fa-server text-green-600 mr-2"></i>
                        Info Teknis
                    </h3>

                    <div class="space-y-3">
                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">ID Log</label>
                            <p class="text-xs text-gray-600 font-mono break-all">{{ $log->id }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">IP Address</label>
                            <p class="text-sm text-gray-900 font-mono">{{ $log->ip_address ?? '-' }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">URL</label>
                            <p class="text-xs text-gray-600 break-all">{{ $log->url ?? '-' }}</p>
                        </div>

                        @if($log->user_agent)
                            <div>
                                <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">User Agent</label>
                                <p class="text-xs text-gray-500 break-all">{{ Str::limit($log->user_agent, 100) }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </x-card>
        </div>
    </div>
</div>
@endsection
