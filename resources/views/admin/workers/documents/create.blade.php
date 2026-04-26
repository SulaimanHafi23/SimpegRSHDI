@extends('layouts.admin')

@section('title', 'Unggah Dokumen Pegawai')

@section('content')
<div class="w-full">

    {{-- Page Header --}}
    <div class="flex items-center gap-3 sm:gap-4 mb-6">
        <div class="w-12 h-12 sm:w-14 sm:h-14 bg-gradient-to-br from-teal-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg shrink-0">
            <svg class="w-6 h-6 sm:w-7 sm:h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800">Unggah Dokumen Pegawai</h1>
            <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Atur dokumen pegawai dengan cepat, rapi, dan mudah ditelusuri</p>
        </div>
    </div>

    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="flex items-start gap-3 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-5">
            <svg class="w-5 h-5 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div>
                <p class="text-sm font-semibold mb-1">Terdapat {{ $errors->count() }} kesalahan pada form:</p>
                <ul class="text-sm space-y-0.5 list-disc list-inside">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        </div>
    @endif

    <form action="{{ route('admin.worker-documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 sm:space-y-5">
        @csrf

        {{-- Section 1: Data Pegawai (admin only) --}}
        @if(auth()->check() && !auth()->user()->can('dashboard.employee'))
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Data Pegawai</h2>
            </div>

            {{-- Hidden native select (for form submission and JS events) --}}
            <select name="worker_id" id="worker_id" class="csel-native" tabindex="-1" aria-hidden="true">
                <option value="">Pilih pegawai</option>
                @foreach($workers as $w)
                    <option value="{{ $w->id }}"
                            data-dept="{{ $w->department->name ?? '' }}"
                            data-nip="{{ $w->nip ?? '-' }}"
                            {{ old('worker_id', request('worker_id')) == $w->id ? 'selected' : '' }}>
                        {{ $w->name }}
                    </option>
                @endforeach
            </select>

            {{-- Custom searchable dropdown --}}
            <div class="csel" data-target="worker_id">
                <button type="button" class="csel-trigger">
                    <span class="csel-value csel-value--placeholder">Pilih pegawai</span>
                    <svg class="csel-arrow" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                    </svg>
                </button>
                <div class="csel-panel">
                    <div class="csel-search-box">
                        <svg class="csel-search-icon" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                        </svg>
                        <input type="text" class="csel-search" placeholder="Cari pegawai..." autocomplete="off">
                    </div>
                    <ul class="csel-list">
                        @foreach($workers as $w)
                            <li class="csel-item" data-val="{{ $w->id }}">
                                <span class="csel-item__name">{{ $w->name }}</span>
                                <span class="csel-item__sub">{{ $w->nip ?? '-' }} &middot; {{ $w->department->name ?? '-' }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="csel-no-result" style="display:none">Tidak ditemukan</div>
                </div>
            </div>
            @error('worker_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
        </div>
        @else
            <input type="hidden" id="worker_id" name="worker_id" value="{{ auth()->user()->worker?->id }}">
        @endif

        {{-- Section 2: Detail Dokumen --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 sm:p-6">
            <div class="flex items-center gap-2.5 mb-5">
                <div class="w-8 h-8 bg-teal-100 rounded-lg flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </div>
                <h2 class="text-sm sm:text-base font-semibold text-gray-800">Detail Dokumen</h2>
            </div>

            <div class="space-y-4">
                {{-- Tipe Dokumen --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Tipe Dokumen <span class="text-red-500">*</span>
                    </label>
                    <select name="document_type_id" id="document_type_id" class="csel-native" tabindex="-1" aria-hidden="true">
                        @if($documentTypes->isEmpty())
                            <option value="">Pilih pegawai terlebih dahulu</option>
                        @else
                            <option value="">Pilih tipe dokumen</option>
                            @foreach($documentTypes as $dt)
                                <option value="{{ $dt->id }}" {{ old('document_type_id', request('document_type_id')) == $dt->id ? 'selected' : '' }}>{{ $dt->name }}</option>
                            @endforeach
                        @endif
                    </select>
                    <div class="csel {{ $documentTypes->isEmpty() ? 'csel--disabled' : '' }}" data-target="document_type_id" id="csel-doctype">
                        <button type="button" class="csel-trigger" {{ $documentTypes->isEmpty() ? 'disabled' : '' }}>
                            <span class="csel-value {{ $documentTypes->isEmpty() ? 'csel-value--placeholder' : '' }}">
                                {{ $documentTypes->isEmpty() ? 'Pilih pegawai terlebih dahulu' : 'Pilih tipe dokumen' }}
                            </span>
                            <svg class="csel-arrow" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div class="csel-panel">
                            <div class="csel-search-box">
                                <svg class="csel-search-icon" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/>
                                </svg>
                                <input type="text" class="csel-search" placeholder="Cari tipe dokumen..." autocomplete="off">
                            </div>
                            <ul class="csel-list" id="csel-doctype-list">
                                @foreach($documentTypes as $dt)
                                    <li class="csel-item" data-val="{{ $dt->id }}">
                                        <span class="csel-item__name">{{ $dt->name }}</span>
                                        @if($dt->description)
                                            <span class="csel-item__sub">{{ \Illuminate\Support\Str::limit($dt->description, 60) }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                            <div class="csel-no-result" style="display:none">Tidak ditemukan</div>
                        </div>
                    </div>
                    @error('document_type_id') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    <p class="mt-1 text-xs text-gray-400">Tipe dokumen akan muncul sesuai departemen pegawai.</p>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        File Dokumen <span class="text-red-500">*</span>
                    </label>
                    <label for="file" class="relative flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer bg-gray-50 hover:bg-teal-50 hover:border-teal-400 transition-all group">
                        <div id="fileUploadText" class="flex flex-col items-center pointer-events-none">
                            <svg class="w-7 h-7 text-gray-300 group-hover:text-teal-400 mb-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            <p class="text-xs sm:text-sm text-gray-500"><span class="font-semibold text-teal-600">Pilih file</span> atau seret ke sini</p>
                            <p class="text-xs text-gray-400 mt-0.5">PDF, JPG, PNG (Maks. 10MB)</p>
                        </div>
                        <div id="fileUploadSelected" class="hidden items-center gap-2 pointer-events-none">
                            <svg class="w-5 h-5 text-teal-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span id="fileUploadName" class="text-sm font-medium text-teal-700 truncate max-w-xs"></span>
                        </div>
                        <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required
                               class="absolute inset-0 opacity-0 cursor-pointer"
                               onchange="handleDocFileSelect(this)">
                    </label>
                    @error('file') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                </div>

                {{-- Expired date & Notes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                    <div>
                        <label for="expired_date" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Tanggal Kadaluarsa
                            <span class="text-xs font-normal text-gray-400 ml-1">(Opsional)</span>
                        </label>
                        <input type="date" name="expired_date" id="expired_date" value="{{ old('expired_date') }}"
                               class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent transition text-sm sm:text-base @error('expired_date') border-red-400 bg-red-50 @enderror">
                        @error('expired_date') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1.5">
                            Catatan
                            <span class="text-xs font-normal text-gray-400 ml-1">(Opsional)</span>
                        </label>
                        <textarea name="notes" id="notes" rows="3" placeholder="Tambahkan catatan dokumen..."
                                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 border border-gray-200 rounded-xl bg-gray-50 focus:bg-white focus:ring-2 focus:ring-teal-500 focus:border-transparent transition resize-none text-sm sm:text-base @error('notes') border-red-400 bg-red-50 @enderror">{{ old('notes') }}</textarea>
                        @error('notes') <p class="mt-1.5 text-sm text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex flex-col-reverse sm:flex-row gap-3 pt-1 pb-2">
            <a href="{{ route('admin.worker-documents.index') }}"
               class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-3 border border-gray-300 rounded-xl text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Batal
            </a>
            <button type="submit"
                    class="w-full sm:w-auto sm:flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 bg-gradient-to-r from-teal-600 to-emerald-600 hover:from-teal-700 hover:to-emerald-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-[0.98]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Unggah Dokumen
            </button>
        </div>
    </form>
</div>
@endsection

@push('styles')
<style>
    /* CSelect — custom searchable dropdown component */
    .csel-native { position:absolute!important; width:1px!important; height:1px!important; overflow:hidden!important; clip:rect(0,0,0,0)!important; border:0!important; padding:0!important; margin:-1px!important; }
    .csel { position:relative; width:100%; }
    .csel--disabled { opacity:.55; pointer-events:none; }
    .csel-trigger {
        display:flex; align-items:center; justify-content:space-between; width:100%;
        padding:.65rem .875rem; border-radius:.75rem; border:1px solid #e5e7eb;
        background:#f9fafb; cursor:pointer; font-size:.9375rem; color:#111827;
        transition:border-color .2s, box-shadow .2s; text-align:left;
    }
    .csel-trigger:hover { border-color:#6ee7b7; }
    .csel-trigger:focus, .csel.csel--open .csel-trigger { outline:none; border-color:transparent; box-shadow:0 0 0 2px #14b8a6; background:#fff; }
    .csel-value { flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .csel-value--placeholder { color:#9ca3af; }
    .csel-arrow { width:1.125rem; height:1.125rem; flex-shrink:0; color:#9ca3af; transition:transform .2s; margin-left:.5rem; }
    .csel--open .csel-arrow { transform:rotate(180deg); }
    .csel-panel {
        display:none; position:absolute; z-index:50; left:0; right:0; top:calc(100% + 4px);
        background:#fff; border-radius:.75rem; border:1px solid #e5e7eb;
        box-shadow:0 10px 25px -5px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.07); overflow:hidden;
    }
    .csel--open .csel-panel { display:block; }
    .csel-search-box { display:flex; align-items:center; padding:.5rem .75rem; border-bottom:1px solid #f3f4f6; gap:.5rem; }
    .csel-search-icon { width:.875rem; height:.875rem; color:#9ca3af; flex-shrink:0; }
    .csel-search { border:none; outline:none; width:100%; font-size:.875rem; background:transparent; color:#111827; }
    .csel-search::placeholder { color:#9ca3af; }
    .csel-list { list-style:none; margin:0; padding:.25rem 0; max-height:220px; overflow-y:auto; }
    .csel-item { display:flex; flex-direction:column; padding:.5rem .75rem; cursor:pointer; transition:background .12s; }
    .csel-item:hover, .csel-item--active { background:#f0fdf4; }
    .csel-item--selected { background:#ccfbf1; font-weight:600; }
    .csel-item__name { font-size:.875rem; color:#111827; line-height:1.35; }
    .csel-item__sub { font-size:.75rem; color:#6b7280; margin-top:1px; }
    .csel-no-result, .csel-loading { padding:.75rem 1rem; text-align:center; font-size:.8125rem; color:#9ca3af; }
    @media(max-width:640px) {
        .csel-panel { position:fixed; left:.75rem; right:.75rem; top:auto; bottom:0; max-height:55vh; border-radius:1rem 1rem 0 0; }
        .csel-list { max-height:calc(55vh - 52px); }
    }
</style>
@endpush

@push('scripts')
<script>
(function () {
    class CSelect {
        constructor(el) {
            this.root      = el;
            this.targetId  = el.dataset.target;
            this.native    = document.getElementById(this.targetId);
            this.trigger   = el.querySelector('.csel-trigger');
            this.valueEl   = el.querySelector('.csel-value');
            this.panel     = el.querySelector('.csel-panel');
            this.search    = el.querySelector('.csel-search');
            this.list      = el.querySelector('.csel-list');
            this.noResult  = el.querySelector('.csel-no-result');
            this.isOpen    = false;
            this.selectedVal = '';
            this._bind();
            this._syncFromNative();
        }

        _bind() {
            this.trigger.addEventListener('click', () => this.toggle());
            this.search?.addEventListener('input', () => this._filter());
            document.addEventListener('click', e => { if (!this.root.contains(e.target)) this.close(); });
            document.addEventListener('keydown', e => { if (e.key === 'Escape') this.close(); });
            this.list.addEventListener('click', e => {
                const item = e.target.closest('.csel-item');
                if (item) this.select(item.dataset.val, item.querySelector('.csel-item__name')?.textContent?.trim());
            });
        }

        _syncFromNative() {
            if (!this.native) return;
            const opt = this.native.options[this.native.selectedIndex];
            if (opt && opt.value) {
                this.selectedVal = opt.value;
                this.valueEl.textContent = opt.textContent.trim();
                this.valueEl.classList.remove('csel-value--placeholder');
                this._markSelected();
            } else {
                this.valueEl.classList.add('csel-value--placeholder');
            }
        }

        toggle() { this.isOpen ? this.close() : this.open(); }
        open()   { if (this.trigger.disabled) return; this.isOpen = true; this.root.classList.add('csel--open'); this.search?.focus(); }
        close()  { this.isOpen = false; this.root.classList.remove('csel--open'); if (this.search) this.search.value = ''; this._filter(); }

        select(val, label) {
            this.selectedVal = val;
            this.valueEl.textContent = label || val;
            this.valueEl.classList.remove('csel-value--placeholder');
            if (this.native) { this.native.value = val; this.native.dispatchEvent(new window.Event('change', { bubbles: true })); }
            this._markSelected();
            this.close();
        }

        _markSelected() {
            this.list.querySelectorAll('.csel-item').forEach(li =>
                li.classList.toggle('csel-item--selected', li.dataset.val === this.selectedVal)
            );
        }

        _filter() {
            const q = (this.search?.value || '').toLowerCase();
            let visible = 0;
            this.list.querySelectorAll('.csel-item').forEach(li => {
                const show = li.textContent.toLowerCase().includes(q);
                li.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (this.noResult) this.noResult.style.display = visible === 0 ? '' : 'none';
        }

        replaceItems(items, placeholder) {
            this.list.innerHTML = '';
            items.forEach(item => {
                const li = document.createElement('li');
                li.className = 'csel-item';
                li.dataset.val = item.id;
                li.innerHTML = '<span class="csel-item__name">' + this._esc(item.name) + '</span>' +
                    (item.description ? '<span class="csel-item__sub">' + this._esc(item.description.substring(0, 60)) + '</span>' : '');
                this.list.appendChild(li);
            });
            if (this.native) {
                this.native.innerHTML = '<option value="">' + (placeholder || 'Pilih') + '</option>';
                items.forEach(item => {
                    const opt = document.createElement('option');
                    opt.value = item.id;
                    opt.textContent = item.name;
                    this.native.appendChild(opt);
                });
            }
            this.selectedVal = '';
            this.valueEl.textContent = placeholder || 'Pilih';
            this.valueEl.classList.add('csel-value--placeholder');
        }

        setDisabled(disabled) { this.trigger.disabled = disabled; this.root.classList.toggle('csel--disabled', disabled); if (disabled) this.close(); }
        setLoading(msg)       { this.list.innerHTML = '<li class="csel-loading">' + msg + '</li>'; }
        _esc(str)             { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
    }

    const allCsels = {};
    document.querySelectorAll('.csel[data-target]').forEach(el => { allCsels[el.dataset.target] = new CSelect(el); });

    const workerNative = document.getElementById('worker_id');
    const doctypeCsel  = allCsels['document_type_id'];
    const fetchUrl     = "{{ route('admin.worker-documents.document-types-for-worker') }}";

    if (workerNative && doctypeCsel) {
        workerNative.addEventListener('change', async function () {
            const wid = this.value;
            if (!wid) {
                doctypeCsel.setDisabled(true);
                doctypeCsel.replaceItems([], 'Pilih pegawai terlebih dahulu');
                return;
            }
            doctypeCsel.setDisabled(false);
            doctypeCsel.setLoading('Memuat tipe dokumen...');
            try {
                const resp = await fetch(fetchUrl + '?worker_id=' + encodeURIComponent(wid), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const json = await resp.json();
                const data = json.data || [];
                if (data.length === 0) {
                    doctypeCsel.replaceItems([], 'Tidak ada tipe dokumen tersedia');
                    doctypeCsel.setDisabled(true);
                } else {
                    doctypeCsel.replaceItems(data, 'Pilih tipe dokumen');
                }
            } catch (err) {
                console.error('Gagal memuat tipe dokumen:', err);
                doctypeCsel.replaceItems([], 'Gagal memuat tipe dokumen');
            }
        });

        if (workerNative.value) {
            const existingItems = doctypeCsel.list.querySelectorAll('.csel-item');
            if (existingItems.length === 0) workerNative.dispatchEvent(new window.Event('change', { bubbles: true }));
        }
    }

    // Employee role: auto-load doc types from hidden worker input
    const hiddenWorker = document.querySelector('input[type="hidden"]#worker_id');
    if (hiddenWorker && doctypeCsel && !allCsels['worker_id']) {
        const existingItems = doctypeCsel.list.querySelectorAll('.csel-item');
        if (existingItems.length === 0 && hiddenWorker.value) {
            (async function () {
                doctypeCsel.setLoading('Memuat tipe dokumen...');
                try {
                    const resp = await fetch(fetchUrl + '?worker_id=' + encodeURIComponent(hiddenWorker.value), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    const data = json.data || [];
                    doctypeCsel.replaceItems(data, data.length ? 'Pilih tipe dokumen' : 'Tidak ada tipe dokumen');
                    doctypeCsel.setDisabled(data.length === 0);
                } catch (err) { console.error(err); }
            })();
        }
    }

    function handleDocFileSelect(input) {
        const text     = document.getElementById('fileUploadText');
        const selected = document.getElementById('fileUploadSelected');
        const name     = document.getElementById('fileUploadName');
        if (input.files && input.files[0]) {
            text.classList.add('hidden');
            selected.classList.remove('hidden');
            selected.classList.add('flex');
            name.textContent = input.files[0].name;
        } else {
            text.classList.remove('hidden');
            selected.classList.add('hidden');
            selected.classList.remove('flex');
        }
    }
    window.handleDocFileSelect = handleDocFileSelect;
})();
</script>
@endpush
