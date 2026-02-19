@extends('layouts.admin')

@section('title', 'Tambah Relasi Departemen - Tipe Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold">Tambah Relasi Departemen - Tipe Dokumen</h1>
            <p class="text-sm text-gray-600">Pilih departemen dan tipe dokumen yang ingin ditetapkan.</p>
        </div>
        <a href="{{ route('admin.master.department-document-types.index') }}" class="inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        @if(session('error'))
            <div class="mb-4 text-red-600">{{ session('error') }}</div>
        @endif

        @php
            $selectedDepartment = old('department_id', request('department_id'));
        @endphp

        <form action="{{ route('admin.master.department-document-types.store') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700">Departemen <span class="text-red-500">*</span></label>
                    <select name="department_id" class="w-full mt-1 rounded border px-3 py-2">
                    <option value="">Pilih departemen</option>
                    <option value="universal" {{ $selectedDepartment == 'universal' ? 'selected' : '' }}>Universal (Semua Departemen)</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ $selectedDepartment == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">Pilih "Universal" agar dokumen bisa diunggah oleh semua pegawai.</p>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tipe Dokumen <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 max-h-[420px] overflow-y-auto border rounded-lg p-3">
                    @foreach($documentTypes as $dt)
                        @php
                            $checked = in_array($dt->id, old('document_type_ids', []));
                        @endphp
                        <label class="flex items-start space-x-2 rounded-lg border border-gray-200 p-2 hover:bg-gray-50">
                            <input type="checkbox" name="document_type_ids[]" value="{{ $dt->id }}" data-description="{{ htmlentities($dt->description ?? '') }}" data-is-universal="{{ $dt->is_universal ? '1' : '0' }}" {{ $checked ? 'checked' : '' }} class="mt-1">
                            <div>
                                <div class="font-medium">
                                    {{ $dt->name }}
                                    @if($dt->is_universal)
                                        <span class="ml-2 inline-block px-2 py-0.5 text-xs bg-green-50 text-green-700 rounded">Universal</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($dt->description ?? '-', 80) }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('document_type_ids') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <div id="document-description" class="mt-2 text-sm text-gray-600">
                    <em>Pilih satu atau beberapa tipe dokumen; deskripsi akan muncul di sini jika dipilih.</em>
                </div>
                <p class="text-xs text-gray-500 mt-2">Tipe dokumen berlabel "Universal" sudah berlaku untuk semua departemen dan tidak perlu dipilih lagi.</p>
            </div>

            <div class="flex space-x-2">
                <button class="px-4 py-2 bg-green-600 text-white rounded">Simpan</button>
                <a href="{{ route('admin.master.department-document-types.index') }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        (function(){
            const departmentSelect = document.querySelector('select[name="department_id"]');
            const checkboxes = Array.from(document.querySelectorAll('input[name="document_type_ids[]"]'));
            const desc = document.getElementById('document-description');

            function syncUniversalState() {
                const isUniversalDept = departmentSelect?.value === 'universal';

                checkboxes.forEach(cb => {
                    const isUniversalType = cb.dataset.isUniversal === '1';

                    if (isUniversalDept) {
                        cb.disabled = false;
                        return;
                    }

                    if (isUniversalType) {
                        cb.checked = false;
                        cb.disabled = true;
                    } else {
                        cb.disabled = false;
                    }
                });
            }

            function updateDesc() {
                const checked = checkboxes.filter(cb => cb.checked);
                if (checked.length === 0) {
                    desc.innerHTML = '<em>Pilih satu atau beberapa tipe dokumen; deskripsi akan muncul di sini jika dipilih.</em>';
                    return;
                }

                const parts = checked.map(cb => {
                    const name = cb.closest('label')?.querySelector('div > .font-medium')?.textContent || '';
                    const raw = cb.dataset.description || '';
                    const tmp = document.createElement('div'); tmp.innerHTML = raw; const decoded = tmp.textContent || tmp.innerText || '';
                    return `<div class="mb-2"><strong>${name}</strong><div class="text-sm text-gray-700">${decoded.replace(/\n/g,'<br>')}</div></div>`;
                });

                desc.innerHTML = parts.join('<hr class="my-2">');
            }

            checkboxes.forEach(cb => cb.addEventListener('change', updateDesc));
            if (departmentSelect) {
                departmentSelect.addEventListener('change', function () {
                    syncUniversalState();
                    updateDesc();
                });
            }

            // initialize (in case of old values)
            syncUniversalState();
            updateDesc();
        })();
    </script>
@endpush
