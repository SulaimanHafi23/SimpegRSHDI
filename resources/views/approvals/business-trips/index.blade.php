@extends('layouts.admin')

@section('title', 'Approval Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Approval Perjalanan Dinas</h1>
            <p class="text-gray-600 mt-1">Kelola pengajuan perjalanan dinas</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left">Pegawai</th>
                        <th class="px-6 py-3 text-left">Tujuan</th>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($trips as $trip)
                    <tr>
                        <td class="px-6 py-4">{{ $trip->worker->name }}</td>
                        <td class="px-6 py-4">{{ $trip->destination }}</td>
                        <td class="px-6 py-4">{{ $trip->start_date->format('d M Y') }} - {{ $trip->end_date->format('d M Y') }}</td>
                        <td class="px-6 py-4">{{ ucfirst($trip->status) }}</td>
                        <td class="px-6 py-4 text-right"><a href="{{ route('approvals.business-trips.show', $trip->id) }}" class="px-3 py-2 bg-blue-600 text-white rounded text-sm">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-6 py-12 text-center">Tidak ada pengajuan perjalanan dinas yang harus di-approval.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($trips->hasPages())
        <div class="bg-white px-6 py-4 border-t border-gray-200">{{ $trips->links() }}</div>
        @endif
    </div>
</div>
@endsection