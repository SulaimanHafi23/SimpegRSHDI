@extends('layouts.admin')

@section('title', 'Perjalanan Dinas Saya')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Perjalanan Dinas Saya</h1>
            <p class="text-gray-600 mt-1">Daftar pengajuan perjalanan dinas</p>
        </div>
        <a href="{{ route('employee.business-trips.create') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">Ajukan Perjalanan</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">Tujuan</th>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($businessTrips as $trip)
                    <tr>
                        <td class="px-6 py-4">{{ $trip->destination }}</td>
                        <td class="px-6 py-4">{{ $trip->start_date->format('d M Y') }} - {{ $trip->end_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ ucfirst($trip->status) }}</td>
                        <td class="px-6 py-4 text-right">
                            <a href="{{ route('employee.business-trips.show', $trip->id) }}" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="px-6 py-12 text-center">Belum ada pengajuan perjalanan dinas.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($businessTrips->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">{{ $businessTrips->links() }}</div>
        @endif
    </div>
</div>
@endsection