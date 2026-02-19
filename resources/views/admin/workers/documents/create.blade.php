@extends('layouts.admin')

@section('title', 'Unggah Dokumen Pegawai')

@section('content')
<div class="doc-upload-page">
    <div class="doc-upload-hero">
        <div class="doc-upload-hero__content">
            <p class="doc-upload-eyebrow">Dokumen Pegawai</p>
            <h1 class="doc-upload-title">Unggah Dokumen Pegawai</h1>
            <p class="doc-upload-subtitle">Atur dokumen pegawai dengan cepat, rapi, dan mudah ditelusuri.</p>
        </div>
        <div class="doc-upload-hero__chip">Administrasi</div>
    </div>

    <div class="doc-upload-shell">
        <form action="{{ route('admin.worker-documents.store') }}" method="POST" enctype="multipart/form-data" class="doc-upload-card">
            @csrf

            @if(auth()->check() && auth()->user()->hasRole('Employee'))
                <input type="hidden" id="worker_id" name="worker_id" value="{{ auth()->user()->worker?->id }}">
            @else
                <div class="doc-upload-section">
                    <div class="doc-upload-section__title">Data Pegawai</div>
                    <label class="doc-upload-label">Pilih Pegawai <span class="text-red-500">*</span></label>
                    {{-- Hidden native select --}}
                    <select name="worker_id" id="worker_id" class="csel-native" tabindex="-1" aria-hidden="true">
                        <option value="">Pilih pegawai</option>
                        @foreach($workers as $w)
                            <option value="{{ $w->id }}" data-dept="{{ $w->department->name ?? '' }}" data-nip="{{ $w->nip ?? '-' }}" {{ old('worker_id', request('worker_id')) == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                    {{-- Custom dropdown --}}
                    <div class="csel" data-target="worker_id">
                        <button type="button" class="csel-trigger">
                            <span class="csel-value">Pilih pegawai</span>
                            <svg class="csel-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                        </button>
                        <div class="csel-panel">
                            <div class="csel-search-box">
                                <svg class="csel-search-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
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
                    @error('worker_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endif

            <div class="doc-upload-section">
                <div class="doc-upload-section__title">Detail Dokumen</div>
                <label class="doc-upload-label">Tipe Dokumen <span class="text-red-500">*</span></label>
                {{-- Hidden native select --}}
                <select name="document_type_id" id="document_type_id" class="csel-native" tabindex="-1" aria-hidden="true">
                    @if($documentTypes->isEmpty())
                        <option value="">Pilih pegawai terlebih dahulu</option>
                    @else
                        <option value="">Pilih tipe dokumen</option>
                        @foreach($documentTypes as $dt)
                            <option value="{{ $dt->id }}" data-desc="{{ $dt->description ?? '' }}" {{ old('document_type_id', request('document_type_id')) == $dt->id ? 'selected' : '' }}>{{ $dt->name }}</option>
                        @endforeach
                    @endif
                </select>
                {{-- Custom dropdown --}}
                <div class="csel {{ $documentTypes->isEmpty() ? 'csel--disabled' : '' }}" data-target="document_type_id" id="csel-doctype">
                    <button type="button" class="csel-trigger" {{ $documentTypes->isEmpty() ? 'disabled' : '' }}>
                        <span class="csel-value">{{ $documentTypes->isEmpty() ? 'Pilih pegawai terlebih dahulu' : 'Pilih tipe dokumen' }}</span>
                        <svg class="csel-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>
                    <div class="csel-panel">
                        <div class="csel-search-box">
                            <svg class="csel-search-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                            <input type="text" class="csel-search" placeholder="Cari tipe dokumen..." autocomplete="off">
                        </div>
                        <ul class="csel-list" id="csel-doctype-list">
                            @foreach($documentTypes as $dt)
                                <li class="csel-item" data-val="{{ $dt->id }}">
                                    <span class="csel-item__name">{{ $dt->name }}</span>
                                    @if($dt->description)
                                        <span class="csel-item__sub">{{ Str::limit($dt->description, 60) }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                        <div class="csel-no-result" style="display:none">Tidak ditemukan</div>
                    </div>
                </div>
                @error('document_type_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                <p class="doc-upload-help">Tipe dokumen akan muncul sesuai departemen pegawai.</p>
            </div>

            <div class="doc-upload-section">
                <label for="file" class="doc-upload-label">File (pdf, jpg, png) <span class="text-red-500">*</span></label>
                <input type="file" name="file" id="file" class="doc-upload-file" accept=".pdf,.jpg,.jpeg,.png" required>
                @error('file') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="doc-upload-grid">
                <div>
                    <label for="expired_date" class="doc-upload-label">Tanggal Kadaluarsa</label>
                    <input type="date" name="expired_date" id="expired_date" value="{{ old('expired_date') }}" class="doc-upload-input">
                    @error('expired_date') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="notes" class="doc-upload-label">Catatan</label>
                    <textarea name="notes" id="notes" rows="2" class="doc-upload-textarea" placeholder="Opsional">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="doc-upload-actions">
                <button type="submit" class="doc-upload-primary">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                    Unggah
                </button>
                <a href="{{ route('admin.worker-documents.index') }}" class="doc-upload-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />
<style>
    .doc-upload-page { display:flex; flex-direction:column; gap:1.5rem; }

    .doc-upload-hero {
        display:flex; justify-content:space-between; gap:1.5rem;
        padding:1.75rem 2rem; border-radius:1.25rem;
        background:linear-gradient(120deg,#0f766e,#14b8a6 45%,#fef3c7 110%);
        color:#0f172a; position:relative; overflow:hidden;
    }
    .doc-upload-hero::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at top right,rgba(255,255,255,.35),transparent 55%); pointer-events:none; }
    .doc-upload-hero__content { position:relative; z-index:1; font-family:'Space Grotesk',sans-serif; }
    .doc-upload-eyebrow { font-size:.75rem; letter-spacing:.2em; text-transform:uppercase; margin-bottom:.25rem; color:rgba(15,23,42,.7); }
    .doc-upload-title { font-size:1.75rem; font-weight:700; margin-bottom:.25rem; }
    .doc-upload-subtitle { font-size:.95rem; color:rgba(15,23,42,.75); }
    .doc-upload-hero__chip { align-self:flex-start; padding:.35rem .9rem; border-radius:999px; background:rgba(15,23,42,.15); font-size:.8rem; font-weight:600; position:relative; z-index:1; }

    .doc-upload-shell { display:grid; gap:1.5rem; }
    .doc-upload-card {
        background:#fff; border-radius:1.25rem; padding:1.75rem; padding-bottom:3rem;
        box-shadow:0 20px 45px rgba(15,23,42,.08); border:1px solid rgba(15,23,42,.06);
        display:flex; flex-direction:column; gap:1.25rem;
    }
    .doc-upload-section { display:flex; flex-direction:column; gap:.5rem; }
    .doc-upload-section__title { font-size:.85rem; text-transform:uppercase; letter-spacing:.2em; color:#0f766e; font-weight:600; }
    .doc-upload-label { font-size:.9rem; font-weight:600; color:#0f172a; }
    .doc-upload-input, .doc-upload-textarea, .doc-upload-file {
        width:100%; border-radius:.85rem; border:1px solid rgba(15,23,42,.15);
        padding:.7rem .85rem; font-size:.95rem; background:#f8fafc;
        transition:border-color .2s,box-shadow .2s;
    }
    .doc-upload-input:focus, .doc-upload-textarea:focus, .doc-upload-file:focus { outline:none; border-color:#14b8a6; box-shadow:0 0 0 3px rgba(20,184,166,.2); background:#fff; }
    .doc-upload-textarea { resize:vertical; min-height:70px; }
    .doc-upload-help { font-size:.8rem; color:#64748b; }
    .doc-upload-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:1rem; }
    .doc-upload-actions { display:flex; flex-wrap:wrap; gap:.75rem; }
    .doc-upload-primary { padding:.75rem 1.6rem; border-radius:999px; border:none; background:#0f766e; color:#fff; font-weight:600; box-shadow:0 14px 30px rgba(15,118,110,.25); cursor:pointer; display:inline-flex; align-items:center; }
    .doc-upload-primary:hover { background:#0d6b63; }
    .doc-upload-secondary { padding:.75rem 1.6rem; border-radius:999px; border:1px solid rgba(15,23,42,.2); color:#0f172a; font-weight:600; background:#fff; text-decoration:none; }

    /* ===== Custom Select (csel) ===== */
    .csel-native { position:absolute!important; width:1px!important; height:1px!important; overflow:hidden!important; clip:rect(0,0,0,0)!important; border:0!important; padding:0!important; margin:-1px!important; }
    .csel { position:relative; width:100%; }
    .csel--disabled { opacity:.55; pointer-events:none; }
    .csel-trigger {
        display:flex; align-items:center; justify-content:space-between; width:100%;
        padding:.75rem 1rem; border-radius:.85rem; border:1px solid rgba(15,23,42,.15);
        background:#f8fafc; cursor:pointer; font-size:.95rem; color:#0f172a;
        transition:border-color .2s,box-shadow .2s; text-align:left;
    }
    .csel-trigger:hover { border-color:#14b8a6; }
    .csel-trigger:focus, .csel.csel--open .csel-trigger { outline:none; border-color:#14b8a6; box-shadow:0 0 0 3px rgba(20,184,166,.2); background:#fff; }
    .csel-value { flex:1; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .csel-value--placeholder { color:#94a3b8; }
    .csel-arrow { width:1.25rem; height:1.25rem; flex-shrink:0; color:#64748b; transition:transform .2s; margin-left:.5rem; }
    .csel--open .csel-arrow { transform:rotate(180deg); }

    .csel-panel {
        display:none; position:absolute; z-index:50; left:0; right:0; top:calc(100% + 6px);
        background:#fff; border-radius:.85rem; border:1px solid rgba(15,23,42,.12);
        box-shadow:0 16px 48px rgba(15,23,42,.15); overflow:hidden;
    }
    .csel--open .csel-panel { display:block; }

    .csel-search-box { display:flex; align-items:center; padding:.6rem .85rem; border-bottom:1px solid rgba(15,23,42,.08); gap:.5rem; }
    .csel-search-icon { width:1rem; height:1rem; color:#94a3b8; flex-shrink:0; }
    .csel-search { border:none; outline:none; width:100%; font-size:.9rem; background:transparent; color:#0f172a; }
    .csel-search::placeholder { color:#94a3b8; }

    .csel-list { list-style:none; margin:0; padding:.35rem 0; max-height:260px; overflow-y:auto; }
    .csel-item {
        display:flex; flex-direction:column; padding:.6rem 1rem; cursor:pointer;
        transition:background .15s;
    }
    .csel-item:hover, .csel-item--active { background:rgba(20,184,166,.08); }
    .csel-item--selected { background:rgba(20,184,166,.15); font-weight:600; }
    .csel-item__name { font-size:.9rem; color:#0f172a; line-height:1.35; }
    .csel-item__sub { font-size:.75rem; color:#64748b; margin-top:1px; }
    .csel-no-result { padding:.85rem 1rem; text-align:center; font-size:.85rem; color:#94a3b8; }
    .csel-loading { padding:.85rem 1rem; text-align:center; font-size:.85rem; color:#64748b; }

    @media(max-width:768px) {
        .doc-upload-hero { flex-direction:column; padding:1.5rem; }
        .doc-upload-title { font-size:1.5rem; }
        .doc-upload-card { padding:1.25rem; }
        .csel-panel { position:fixed; left:.75rem; right:.75rem; top:auto; bottom:0; max-height:55vh; border-radius:1rem 1rem 0 0; }
        .csel-list { max-height:calc(55vh - 56px); }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    class CSelect {
        constructor(el) {
            this.root = el;
            this.targetId = el.dataset.target;
            this.native = document.getElementById(this.targetId);
            this.trigger = el.querySelector('.csel-trigger');
            this.valueEl = el.querySelector('.csel-value');
            this.panel = el.querySelector('.csel-panel');
            this.search = el.querySelector('.csel-search');
            this.list = el.querySelector('.csel-list');
            this.noResult = el.querySelector('.csel-no-result');
            this.isOpen = false;
            this.selectedVal = '';
            this._bind();
            this._syncFromNative();
        }

        _bind() {
            this.trigger.addEventListener('click', () => this.toggle());
            this.search?.addEventListener('input', () => this._filter());
            document.addEventListener('click', (e) => { if (!this.root.contains(e.target)) this.close(); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.close(); });
            this.list.addEventListener('click', (e) => {
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
        open() {
            if (this.trigger.disabled) return;
            this.isOpen = true;
            this.root.classList.add('csel--open');
            this.search?.focus();
        }
        close() {
            this.isOpen = false;
            this.root.classList.remove('csel--open');
            if (this.search) this.search.value = '';
            this._filter();
        }

        select(val, label) {
            this.selectedVal = val;
            this.valueEl.textContent = label || val;
            this.valueEl.classList.remove('csel-value--placeholder');
            if (this.native) { this.native.value = val; this.native.dispatchEvent(new Event('change', {bubbles:true})); }
            this._markSelected();
            this.close();
        }

        _markSelected() {
            this.list.querySelectorAll('.csel-item').forEach(li => {
                li.classList.toggle('csel-item--selected', li.dataset.val === this.selectedVal);
            });
        }

        _filter() {
            const q = (this.search?.value || '').toLowerCase();
            let visible = 0;
            this.list.querySelectorAll('.csel-item').forEach(li => {
                const txt = li.textContent.toLowerCase();
                const show = txt.includes(q);
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
                li.innerHTML = '<span class="csel-item__name">' + this._esc(item.name) + '</span>' + (item.description ? '<span class="csel-item__sub">' + this._esc(item.description.substring(0,60)) + '</span>' : '');
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

        setDisabled(disabled) {
            this.trigger.disabled = disabled;
            this.root.classList.toggle('csel--disabled', disabled);
            if (disabled) this.close();
        }

        setLoading(msg) {
            this.list.innerHTML = '<li class="csel-loading">' + msg + '</li>';
        }

        _esc(str) { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
    }

    const allCsels = {};
    document.querySelectorAll('.csel[data-target]').forEach(el => {
        allCsels[el.dataset.target] = new CSelect(el);
    });

    const workerNative = document.getElementById('worker_id');
    const doctypeCsel = allCsels['document_type_id'];
    const fetchUrl = "{{ route('admin.worker-documents.document-types-for-worker') }}";

    if (workerNative && doctypeCsel) {
        workerNative.addEventListener('change', async function() {
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
            if (existingItems.length === 0) {
                workerNative.dispatchEvent(new Event('change', {bubbles: true}));
            }
        }
    }

    const hiddenWorker = document.querySelector('input[type="hidden"]#worker_id');
    if (hiddenWorker && doctypeCsel && !allCsels['worker_id']) {
        const existingItems = doctypeCsel.list.querySelectorAll('.csel-item');
        if (existingItems.length === 0 && hiddenWorker.value) {
            (async function() {
                doctypeCsel.setLoading('Memuat tipe dokumen...');
                try {
                    const resp = await fetch(fetchUrl + '?worker_id=' + encodeURIComponent(hiddenWorker.value), {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    const json = await resp.json();
                    const data = json.data || [];
                    doctypeCsel.replaceItems(data, data.length ? 'Pilih tipe dokumen' : 'Tidak ada tipe dokumen');
                    doctypeCsel.setDisabled(data.length === 0);
                } catch (err) {
                    console.error(err);
                }
            })();
        }
    }
})();
</script>
@endpush
