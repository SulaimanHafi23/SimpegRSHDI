@extends('layouts.admin')

@section('title', 'Edit Payroll')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Payroll</h1>
            <p class="text-gray-600 mt-1">Ubah payroll untuk periode {{ $payroll->period ?? '' }}</p>
        </div>
    </div>

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('admin.payroll.update', $payroll) }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Basic Salary</label>
                <input type="number" name="basic_salary" value="{{ old('basic_salary', $payroll->basic_salary ?? 0) }}" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500" required>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Komponen Gaji</label>
                <div class="mt-2 space-y-2">
                    @foreach($components as $component)
                        @php
                            $detail = $payroll->details->firstWhere('salary_component_id', $component->id);
                            $amount = old('components.' . $component->id . '.amount', $detail->amount ?? 0);
                        @endphp
                        <div class="flex items-center gap-3">
                            <div class="flex-1">
                                <div class="text-sm font-medium">{{ $component->name }} <span class="text-xs text-gray-400">({{ ucfirst($component->type) }})</span></div>
                                <div class="text-xs text-gray-500">{{ $component->description }}</div>
                            </div>
                            <div class="w-48">
                                <input type="hidden" name="components[{{ $component->id }}][salary_component_id]" value="{{ $component->id }}">
                                <input type="number" name="components[{{ $component->id }}][amount]" value="{{ number_format((float)$amount, 2, '.', '') }}" step="0.01" min="0" class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('admin.payroll.show', $payroll) }}" class="px-4 py-2 bg-gray-200 rounded-lg">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg">Simpan</button>
            </div>
        </div>
    </form>
</div>
@endsection