@extends('layouts.admin')

@section('title', 'Detail Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Detail Perjalanan Dinas</h1>
            <p class="text-gray-600">{{ $trip->destination }}</p>
        </div>
        <div>
            @if($trip->status === 'pending')
            <form action="{{ route('employee.business-trips.cancel', $trip->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan?')">
                @csrf
                @method('DELETE')
                <button class="px-4 py-2 bg-red-600 text-white rounded">Batalkan</button>
            </form>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500">Tujuan</p>
                <p class="text-lg font-semibold">{{ $trip->destination }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tanggal</p>
                <p class="text-lg font-semibold">{{ $trip->start_date->format('d M Y') }} - {{ $trip->end_date->format('d M Y') }}</p>
            </div>
            <div class="md:col-span-2">
                <p class="text-sm text-gray-500">Tujuan Perjalanan</p>
                <p class="text-gray-800">{{ $trip->purpose }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Estimasi Biaya</p>
                <p class="text-lg font-semibold">{{ number_format($trip->estimated_cost, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <p class="text-lg font-semibold">{{ ucfirst($trip->status) }}</p>
            </div>
        </div>
    </div>
</div>
@endsection