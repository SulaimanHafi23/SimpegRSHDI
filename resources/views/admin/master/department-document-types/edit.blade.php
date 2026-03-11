@extends('layouts.admin')

@section('title', 'Edit Relasi Departemen - Tipe Dokumen')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold">Edit Relasi Departemen - Tipe Dokumen</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        @if(session('error'))
            <div class="mb-4 text-red-600">{{ session('error') }}</div>
        @endif

        <form action="{{ route('admin.master.department-document-types.update', ['department_document_type' => $department->id ?? 'universal']) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-gray-700">Departemen <span class="text-red-500">*</span></label>
                <select name="department_id" class="w-full mt-1 rounded border px-3 py-2">
                    <option value="">Pilih departemen</option>
                    <option value="universal" {{ (old('department_id') ?? $department->id) == 'universal' ? 'selected' : '' }}>Universal (Semua Departemen)</option>
                    @foreach($departments as $d)
                        <option value="{{ $d->id }}" {{ (old('department_id') ?? $department->id) == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                    @endforeach
                </select>
                @error('department_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Tipe Dokumen <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-64 overflow-y-auto border rounded p-2">
                    @foreach($documentTypes as $dt)
                        @php
                            $checked = in_array($dt->id, old('document_type_ids', $selected ?? []));
                        @endphp
                        <label class="flex items-start space-x-2">
                            <input type="checkbox" name="document_type_ids[]" value="{{ $dt->id }}" data-description="{{ htmlentities($dt->description ?? '') }}" data-is-universal="{{ $dt->is_universal ? '1' : '0' }}" {{ $checked ? 'checked' : '' }} class="mt-1">
                            <div>
                                <div class="font-medium">{{ $dt->name }}</div>
                                <div class="text-xs text-gray-500">{{ \Illuminate\Support\Str::limit($dt->description ?? '-', 80) }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>
                @error('document_type_ids') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

                <div id="document-description" class="mt-2 text-sm text-gray-600">
                    <em>Deskripsi akan muncul di sini saat Anda memilih tipe dokumen.</em>
                </div>
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
            syncUniversalState();
            updateDesc();
        })();
    </script>
@endpush
