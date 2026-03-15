@extends('layouts.admin')

@section('title', 'Buat Pengajuan Kenaikan Pangkat')

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3 sm:gap-4">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <i class="fas fa-arrow-up text-white text-lg sm:text-xl"></i>
        </div>
        <div class="min-w-0 flex-1">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Buat Pengajuan Kenaikan Pangkat</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Isi form untuk mengajukan kenaikan pangkat / promosi pegawai</p>
        </div>
        <a href="{{ route('admin.promotions.index') }}"
           class="inline-flex items-center justify-center gap-2 px-3.5 py-2.5 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition shrink-0">
            <i class="fas fa-arrow-left text-xs"></i>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl">
            <ul class="list-disc list-inside text-sm text-red-700">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="flex items-start gap-3 px-4 py-3.5 bg-blue-50 rounded-xl border border-blue-200">
        <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
        <div class="min-w-0">
            <p class="text-sm font-semibold text-blue-800 mb-1">Informasi Penting</p>
            <p class="text-xs sm:text-sm text-blue-700">Usulan pangkat, golongan, dan gaji pokok baru akan dipakai pada proses approval dan pembaruan data pegawai.</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6 space-y-5">
        <form method="POST" action="{{ route('admin.promotions.store') }}">
            @csrf

            <div class="space-y-4">
                {{-- Worker Select --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Pegawai <span class="text-red-500">*</span>
                    </label>
                    <select name="worker_id" id="workerSelect"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500 @error('worker_id') border-red-300 bg-red-50 @enderror">
                        <option value="">-- Pilih Pegawai --</option>
                        @foreach($workers as $worker)
                            <option value="{{ $worker->id }}"
                                    data-rank="{{ $worker->rank }}"
                                    data-rank-level="{{ $worker->rank_level }}"
                                    data-salary="{{ $worker->base_salary }}"
                                    @selected(old('worker_id') === $worker->id)>
                                {{ $worker->nip }} - {{ $worker->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('worker_id')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Current Info (auto-filled) --}}
                <div class="p-4 bg-gray-50 border border-gray-200 rounded-xl text-sm" id="currentInfo" style="display:none">
                    <p class="font-medium text-gray-700 mb-2">Informasi Saat Ini:</p>
                    <div class="grid grid-cols-2 gap-2 text-gray-600">
                        <div>Pangkat: <span id="currentRank" class="font-medium text-gray-900">-</span></div>
                        <div>Tingkat: <span id="currentLevel" class="font-medium text-gray-900">-</span></div>
                        <div>Gaji Pokok: <span id="currentSalary" class="font-medium text-gray-900">-</span></div>
                    </div>
                </div>

                {{-- Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kenaikan <span class="text-red-500">*</span></label>
                    <select name="promotion_type" class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                        <option value="kenaikan_pangkat" @selected(old('promotion_type', 'kenaikan_pangkat') === 'kenaikan_pangkat')>Kenaikan Pangkat</option>
                        <option value="kenaikan_jabatan" @selected(old('promotion_type') === 'kenaikan_jabatan')>Kenaikan Jabatan</option>
                        <option value="penyesuaian_gaji" @selected(old('promotion_type') === 'penyesuaian_gaji')>Penyesuaian Gaji</option>
                    </select>
                </div>

                {{-- Proposed Rank --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pangkat Baru <span class="text-red-500">*</span></label>
                        <input type="text" name="proposed_rank" value="{{ old('proposed_rank') }}"
                               placeholder="contoh: Penata Muda Tk.I"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                        @error('proposed_rank')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tingkat Baru</label>
                        <input type="text" name="proposed_rank_level" value="{{ old('proposed_rank_level') }}"
                               placeholder="contoh: III/b"
                               class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                {{-- Proposed Salary --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Gaji Pokok Baru (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="proposed_base_salary" value="{{ old('proposed_base_salary') }}"
                           min="0" step="1000"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                    @error('proposed_base_salary')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Effective Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Efektif <span class="text-red-500">*</span></label>
                    <input type="date" name="effective_date" value="{{ old('effective_date', now()->format('Y-m-d')) }}"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500">
                    @error('effective_date')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>

                {{-- Reason --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alasan / Keterangan</label>
                    <textarea name="reason" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white text-sm focus:ring-2 focus:ring-blue-500"
                              placeholder="Catatan alasan kenaikan pangkat...">{{ old('reason') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex flex-col-reverse sm:flex-row gap-3">
                <button type="submit"
                        class="w-full sm:flex-1 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl font-semibold text-sm shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-paper-plane mr-2"></i>Ajukan Kenaikan Pangkat
                </button>
                <a href="{{ route('admin.promotions.index') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 rounded-xl text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('workerSelect').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    if (!opt.value) { document.getElementById('currentInfo').style.display = 'none'; return; }

    document.getElementById('currentRank').textContent  = opt.dataset.rank || '-';
    document.getElementById('currentLevel').textContent = opt.dataset.rankLevel || '-';
    document.getElementById('currentSalary').textContent = opt.dataset.salary
        ? 'Rp ' + parseInt(opt.dataset.salary).toLocaleString('id-ID')
        : '-';
    document.getElementById('currentInfo').style.display = 'block';
});
</script>
@endsection
