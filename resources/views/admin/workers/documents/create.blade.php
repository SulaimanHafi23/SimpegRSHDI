@extends('layouts.admin')

@section('title', 'Unggah Dokumen Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-xl sm:text-2xl font-bold">Unggah Dokumen Pegawai</h1>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <form action="{{ route('admin.worker-documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf

            @if(auth()->check() && auth()->user()->hasRole('Employee'))
                <input type="hidden" id="worker_id" name="worker_id" value="{{ auth()->user()->worker?->id }}">
            @else
                <div>
                    <label for="worker_id" class="block text-sm font-medium text-gray-700">Pilih Pegawai <span class="text-red-500">*</span></label>
                    <select name="worker_id" id="worker_id" class="w-full mt-1 rounded border px-3 py-2">
                        <option value="">Pilih pegawai</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}" {{ old('worker_id') == $w->id ? 'selected' : '' }}>{{ $w->name }} ({{ $w->nip ?? '-' }})</option>
                        @endforeach
                    </select>
                    @error('worker_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label for="document_type_id" class="block text-sm font-medium text-gray-700">Tipe Dokumen <span class="text-red-500">*</span></label>
                <select name="document_type_id" id="document_type_id" class="w-full mt-1 rounded border px-3 py-2">
                    <option value="">Pilih tipe dokumen</option>
                </select>
                @error('document_type_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-500 mt-1">Tipe dokumen akan muncul sesuai departemen pegawai.</p>
            </div>

            <div>
                <label for="file" class="block text-sm font-medium text-gray-700">File (pdf, jpg, png) <span class="text-red-500">*</span></label>
                <input type="file" name="file" id="file" class="w-full mt-1" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('file') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="expired_date" class="block text-sm font-medium text-gray-700">Tanggal Kadaluarsa</label>
                    <input type="date" name="expired_date" id="expired_date" value="{{ old('expired_date') }}" class="w-full mt-1 rounded border px-3 py-2">
                    @error('expired_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                    <input type="text" name="notes_preview" id="notes_preview" value="" class="w-full mt-1 rounded border px-3 py-2" disabled placeholder="(Optional: tambahkan catatan di bagian Catatan di bawah)" />
                </div>
            </div>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700">Catatan</label>
                <textarea name="notes" id="notes" rows="3" class="w-full mt-1 rounded border px-3 py-2">{{ old('notes') }}</textarea>
            </div>

            <div class="flex space-x-2">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded">Unggah</button>
                <a href="{{ route('admin.worker-documents.index') }}" class="px-4 py-2 bg-gray-200 rounded">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

        @section('scripts')
            <script>
                (function() {
                    const workerSelect = document.getElementById('worker_id');
                    const docTypeSelect = document.getElementById('document_type_id');
                    const fetchUrl = "{{ route('admin.worker-documents.document-types-for-worker') }}";

                    async function fetchAndPopulate(workerId) {
                        if (!workerId) {
                            // if no worker selected, keep default options (first option)
                            docTypeSelect.innerHTML = '<option value="">Pilih tipe dokumen</option>';
                            return;
                        }

                        try {
                            const resp = await fetch(fetchUrl + '?worker_id=' + encodeURIComponent(workerId), {
                                headers: { 'X-Requested-With': 'XMLHttpRequest' }
                            });
                            const json = await resp.json();
                            const data = json.data || [];

                            let html = '<option value="">Pilih tipe dokumen</option>';
                            data.forEach(function(dt) {
                                // preserve old value if exists
                                const selected = (dt.id === '{{ old('document_type_id') }}') ? ' selected' : '';
                                html += `<option value="${dt.id}"${selected}>${dt.name}</option>`;
                            });

                            docTypeSelect.innerHTML = html;
                        } catch (err) {
                            console.error('Gagal memuat tipe dokumen:', err);
                        }
                    }

                    if (workerSelect) {
                        workerSelect.addEventListener('change', function(e) {
                            fetchAndPopulate(e.target.value);
                        });

                        // If the form was loaded with a preselected worker (e.g., employee) trigger population
                        const initial = workerSelect.value;
                        if (initial) {
                            fetchAndPopulate(initial);
                        }
                    }
                })();
            </script>
        @endsection
