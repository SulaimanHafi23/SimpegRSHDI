@extends('layouts.admin')

@section('title', 'Ajukan Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('employee.business-trips.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tujuan</label>
                    <input type="text" name="destination" value="{{ old('destination') }}" class="w-full rounded-lg border-gray-300 p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" class="w-full rounded-lg border-gray-300 p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tanggal Selesai</label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" class="w-full rounded-lg border-gray-300 p-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Estimasi Biaya</label>
                    <input type="number" step="0.01" name="estimated_cost" value="{{ old('estimated_cost') }}" class="w-full rounded-lg border-gray-300 p-2">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Tujuan Perjalanan</label>
                    <textarea name="purpose" rows="4" class="w-full rounded-lg border-gray-300 p-2" required>{{ old('purpose') }}</textarea>
                </div>
            </div>

            <div class="mt-4 flex justify-end gap-2">
                <a href="{{ route('employee.business-trips.index') }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Ajukan</button>
            </div>
        </form>
    </div>
</div>
@endsection