@extends('layouts.admin')

@section('title', 'Komponen Gaji Pegawai')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.workers.show', $worker->id) }}"
               class="p-2 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded-lg">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h1 class="text-xl font-bold text-gray-900">Komponen Gaji</h1>
                <p class="text-sm text-gray-600">{{ $worker->name }} &bull; {{ $worker->nip }}</p>
            </div>
        </div>

        {{-- Apply Default Button --}}
        @if($worker->payroll_category)
            <form method="POST" action="{{ route('admin.workers.salary-components.apply-default', $worker->id) }}"
                  onsubmit="return confirm('Terapkan komponen default untuk kategori {{ strtoupper($worker->payroll_category) }}? Komponen yang sudah ada akan diganti.')">
                @csrf
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-sync-alt mr-2"></i>Terapkan Default ({{ strtoupper($worker->payroll_category) }})
                </button>
            </form>
        @endif
    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded text-sm text-green-700">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded text-sm text-red-700">{{ session('error') }}</div>
    @endif

    {{-- Last Sync Info --}}
    @if($lastSyncLog)
        <div class="bg-blue-50 border border-blue-100 rounded-lg p-4 text-sm text-blue-700">
            <i class="fas fa-info-circle mr-2"></i>
            Sinkronisasi terakhir:
            <strong>{{ optional($lastSyncLog->created_at)->format('d/m/Y H:i') }}</strong>
            — {{ $lastSyncLog->description }}
        </div>
    @endif

    {{-- Info Gaji Pokok --}}
    <div class="bg-white rounded-lg shadow p-5">
        <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Informasi Dasar Penggajian</p>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Gaji Pokok</p>
                <p class="font-bold text-lg text-gray-900">
                    Rp {{ $worker->base_salary ? number_format($worker->base_salary, 0, ',', '.') : '—' }}
                </p>
            </div>
            <div>
                <p class="text-gray-500">Kategori</p>
                <p class="font-semibold text-gray-800 uppercase">{{ $worker->payroll_category ?? '—' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Golongan / Pangkat</p>
                <p class="font-semibold text-gray-800">{{ $worker->rank ?? '—' }}{{ $worker->rank_level ? ' / ' . $worker->rank_level : '' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Total Komponen Aktif</p>
                <p class="font-bold text-lg text-blue-700">{{ $assignments->where('is_active', true)->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Component Form --}}
    <form method="POST" action="{{ route('admin.workers.salary-components.update', $worker->id) }}">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex justify-between items-center">
                <p class="font-semibold text-gray-800">Komponen Gaji</p>
                <p class="text-xs text-gray-500">Centang untuk mengaktifkan komponen</p>
            </div>

            @php
                $earningComponents  = $allComponents->where('type', 'earning');
                $deductionComponents = $allComponents->where('type', 'deduction');
                $assignmentMap = $assignments->keyBy('salary_component_id');
            @endphp

            {{-- Tunjangan / Earning --}}
            @if($earningComponents->isNotEmpty())
                <div class="px-6 py-3 bg-green-50 border-b">
                    <p class="text-xs font-semibold text-green-700 uppercase tracking-wider">
                        <i class="fas fa-plus-circle mr-1"></i>Tunjangan / Penghasilan Tambahan
                    </p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($earningComponents as $comp)
                        @php $assignment = $assignmentMap->get($comp->id); @endphp
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <input type="checkbox"
                                       name="components[{{ $comp->id }}][enabled]"
                                       value="1"
                                       id="comp_{{ $comp->id }}"
                                       class="w-4 h-4 rounded text-blue-600 focus:ring-blue-500"
                                       {{ $assignment && $assignment->is_active ? 'checked' : '' }}>
                                <label for="comp_{{ $comp->id }}" class="cursor-pointer">
                                    <span class="font-medium text-gray-900 text-sm">{{ $comp->name }}</span>
                                    <span class="text-xs text-gray-400 ml-2 font-mono">({{ $comp->code }})</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-2 ml-7 sm:ml-0">
                                <select name="components[{{ $comp->id }}][calculation_type]"
                                        class="px-3 py-1.5 border rounded text-xs focus:ring-2 focus:ring-blue-400">
                                    <option value="fixed"      {{ (!$assignment || $assignment->calculation_type === 'fixed') ? 'selected' : '' }}>Nominal (Rp)</option>
                                    <option value="percentage" {{ ($assignment && $assignment->calculation_type === 'percentage') ? 'selected' : '' }}>% dari Gaji Pokok</option>
                                </select>
                                <input type="number"
                                       name="components[{{ $comp->id }}][amount]"
                                       value="{{ $assignment ? $assignment->amount : 0 }}"
                                       min="0" step="0.01"
                                       class="w-28 px-3 py-1.5 border rounded text-xs text-right focus:ring-2 focus:ring-blue-400"
                                       placeholder="0">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Potongan / Deduction --}}
            @if($deductionComponents->isNotEmpty())
                <div class="px-6 py-3 bg-red-50 border-b border-t">
                    <p class="text-xs font-semibold text-red-700 uppercase tracking-wider">
                        <i class="fas fa-minus-circle mr-1"></i>Potongan
                    </p>
                </div>
                <div class="divide-y divide-gray-100">
                    @foreach($deductionComponents as $comp)
                        @php $assignment = $assignmentMap->get($comp->id); @endphp
                        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <input type="checkbox"
                                       name="components[{{ $comp->id }}][enabled]"
                                       value="1"
                                       id="comp_{{ $comp->id }}"
                                       class="w-4 h-4 rounded text-red-600 focus:ring-red-500"
                                       {{ $assignment && $assignment->is_active ? 'checked' : '' }}>
                                <label for="comp_{{ $comp->id }}" class="cursor-pointer">
                                    <span class="font-medium text-gray-900 text-sm">{{ $comp->name }}</span>
                                    <span class="text-xs text-gray-400 ml-2 font-mono">({{ $comp->code }})</span>
                                </label>
                            </div>
                            <div class="flex items-center gap-2 ml-7 sm:ml-0">
                                <select name="components[{{ $comp->id }}][calculation_type]"
                                        class="px-3 py-1.5 border rounded text-xs focus:ring-2 focus:ring-red-400">
                                    <option value="fixed"      {{ (!$assignment || $assignment->calculation_type === 'fixed') ? 'selected' : '' }}>Nominal (Rp)</option>
                                    <option value="percentage" {{ ($assignment && $assignment->calculation_type === 'percentage') ? 'selected' : '' }}>% dari Gaji Pokok</option>
                                </select>
                                <input type="number"
                                       name="components[{{ $comp->id }}][amount]"
                                       value="{{ $assignment ? $assignment->amount : 0 }}"
                                       min="0" step="0.01"
                                       class="w-28 px-3 py-1.5 border rounded text-xs text-right focus:ring-2 focus:ring-red-400"
                                       placeholder="0">
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Save Button --}}
            <div class="px-6 py-4 border-t bg-gray-50 flex gap-3 justify-end">
                <a href="{{ route('admin.workers.show', $worker->id) }}"
                   class="px-5 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm">
                    Kembali
                </a>
                <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium">
                    <i class="fas fa-save mr-2"></i>Simpan Komponen Gaji
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
