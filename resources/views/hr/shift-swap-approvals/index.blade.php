@extends('layouts.admin')

@section('title', 'Persetujuan Tukar Shift - HR')

@section('content')
<div class="space-y-6">
    {{-- Page Header --}}
    <x-page-header
        title="Persetujuan Tukar Shift (Tahap HR)"
        description="Setujui atau tolak permintaan pertukaran shift yang sudah diverifikasi manager"
        icon="fas fa-exchange-alt">
    </x-page-header>

    {{-- Statistics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <x-stats-card
            title="Verifikasi Manager"
            :value="$statistics['manager_verified'] ?? 0"
            icon="fas fa-user-check"
            color="info" />

        <x-stats-card
            title="Disetujui HR"
            :value="$statistics['approved'] ?? 0"
            icon="fas fa-check-circle"
            color="green" />

        <x-stats-card
            title="Ditolak"
            :value="$statistics['rejected'] ?? 0"
            icon="fas fa-times-circle"
            color="red" />

        <x-stats-card
            title="Total Data"
            :value="$statistics['total'] ?? 0"
            icon="fas fa-file-alt"
            color="blue" />
    </div>

    {{-- Filter Section --}}
    <x-filter-section action="{{ route('hr.shift-swap-approvals.index') }}">
        <x-form.select
            name="status"
            label="Status"
            :selected="$filters['original_status'] ?? ''"
            placeholder="Semua Status">
            <option value="manager_verified">Terverifikasi Manager</option>
            <option value="approved">Disetujui HR</option>
            <option value="executed">Dieksekusi</option>
            <option value="rejected">Ditolak</option>
            <option value="cancelled">Dibatalkan</option>
        </x-form.select>

        <x-form.input
            type="date"
            name="date_from"
            label="Dari Tanggal"
            :value="request('date_from')" />

        <x-form.input
            type="date"
            name="date_to"
            label="Sampai Tanggal"
            :value="request('date_to')" />

        <x-form.input
            type="number"
            name="per_page"
            label="Per Halaman"
            value="{{ request('per_page', 15) }}" />
    </x-filter-section>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-100 border-b border-gray-300">
                    <tr class="text-left text-sm font-semibold text-gray-700">
                        <th class="px-6 py-4 whitespace-nowrap">Pemohon</th>
                        <th class="px-6 py-4 whitespace-nowrap">Tukar Dengan</th>
                        <th class="px-6 py-4 whitespace-nowrap">Tanggal</th>
                        <th class="px-6 py-4 whitespace-nowrap">Diverifikasi</th>
                        <th class="px-6 py-4 whitespace-nowrap">Manager</th>
                        <th class="px-6 py-4 whitespace-nowrap">Status</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($items as $swap)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $swap->requester->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $swap->requester->department->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $swap->targetWorker->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $swap->targetWorker->department->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                @if($swap->swap_date)
                                    {{ $swap->swap_date->format('d M Y') }}
                                @else
                                    {{ $swap->swap_start_date?->format('d M Y') ?? '-' }}
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-700 whitespace-nowrap">
                                {{ $swap->manager_verified_at?->format('d M Y H:i') ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <div class="font-medium text-gray-900">{{ $swap->manager?->name ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    {{ ucfirst(str_replace('_', ' ', $swap->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right">
                                <a href="{{ route('hr.shift-swap-approvals.show', $swap->id) }}"
                                   class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-blue-700 rounded-lg hover:bg-blue-50 transition text-xs font-medium">
                                    <i class="fas fa-eye mr-1"></i> Lihat
                                </a>
                                @if($swap->status === 'cancelled')
                                    <form action="{{ route('hr.shift-swap-approvals.destroy', $swap->id) }}" method="POST" class="inline ml-1">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-red-700 rounded-lg hover:bg-red-50 transition text-xs font-medium"
                                                onclick="return confirm('Hapus data pengajuan ini?')">
                                            <i class="fas fa-trash-alt mr-1"></i> Hapus
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-3xl mb-2 opacity-50"></i>
                                <p>Tidak ada permintaan tukar shift yang menunggu persetujuan</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $items->links() }}
    </div>
</div>
@endsection
