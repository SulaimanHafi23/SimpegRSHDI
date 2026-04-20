@extends('layouts.employee')

@section('title', 'Upload Dokumen')

@section('content')
<div class="space-y-6">
    <div class="employee-doc-hero">
        <div>
            <p class="employee-doc-eyebrow">Portal Pegawai</p>
            <h1 class="employee-doc-title">Upload Dokumen</h1>
            <p class="employee-doc-subtitle">Semua dokumen dalam satu tempat, mudah dipantau dan diperbarui.</p>
        </div>
        <div class="employee-doc-hero__badge">Aman & Terverifikasi</div>
        @if(auth()->user()->worker?->department)
            <div class="employee-doc-dept">
                <i class="fas fa-building mr-2"></i>
                {{ auth()->user()->worker->department->name }}
            </div>
        @endif
    </div>


    <!-- Form -->
    <div class="employee-doc-card">
        @if($documentTypes->isNotEmpty())
            <!-- Document Summary Info -->
            <div class="employee-doc-summary">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fas fa-info-circle text-blue-500 text-xl mt-0.5"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-semibold text-gray-800 mb-2">Informasi Dokumen Posisi Anda:</h3>
                        @php
                            $requiredDocs = $documentTypes->where('is_required', true);
                            $optionalDocs = $documentTypes->where('is_required', false);
                            $uploadedCount = count($uploadedDocTypes ?? []);
                        @endphp

                        <div class="mb-3 p-2 bg-white rounded border border-blue-200">
                            <div class="grid grid-cols-3 gap-2 text-xs">
                                <div class="text-center">
                                    <div class="font-bold text-lg text-blue-600">{{ $documentTypes->count() }}</div>
                                    <div class="text-gray-600">Total Dokumen</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-lg text-green-600">{{ $uploadedCount }}</div>
                                    <div class="text-gray-600">Sudah Upload</div>
                                </div>
                                <div class="text-center">
                                    <div class="font-bold text-lg text-orange-600">{{ $documentTypes->count() - $uploadedCount }}</div>
                                    <div class="text-gray-600">Belum Upload</div>
                                </div>
                            </div>
                        </div>

                        @if($requiredDocs->isNotEmpty())
                            <div class="mb-2">
                                <span class="text-sm font-medium text-red-700">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Dokumen Wajib ({{ $requiredDocs->count() }}):
                                </span>
                                <ul class="text-xs text-gray-700 ml-4 mt-1 list-disc">
                                    @foreach($requiredDocs->take(3) as $doc)
                                        <li>
                                            {{ $doc->name }}
                                            @if(in_array($doc->id, $uploadedDocTypes ?? []))
                                                <span class="text-green-600 font-semibold"></span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if($requiredDocs->count() > 3)
                                        <li class="text-gray-500">... dan {{ $requiredDocs->count() - 3 }} lainnya</li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                        @if($optionalDocs->isNotEmpty())
                            <div class="mb-2">
                                <span class="text-sm font-medium text-blue-700">
                                    <i class="fas fa-file-alt mr-1"></i>
                                    Dokumen Opsional ({{ $optionalDocs->count() }}):
                                </span>
                                <ul class="text-xs text-gray-700 ml-4 mt-1 list-disc">
                                    @foreach($optionalDocs->take(3) as $doc)
                                        <li>
                                            {{ $doc->name }}
                                            @if(in_array($doc->id, $uploadedDocTypes ?? []))
                                                <span class="text-green-600 font-semibold"></span>
                                            @endif
                                        </li>
                                    @endforeach
                                    @if($optionalDocs->count() > 3)
                                        <li class="text-gray-500">... dan {{ $optionalDocs->count() - 3 }} lainnya</li>
                                    @endif
                                </ul>
                            </div>
                        @endif

                        <div class="mt-3 pt-3 border-t border-blue-200">
                            <p class="text-xs text-gray-700">
                                <i class="fas fa-lightbulb text-yellow-500 mr-1"></i>
                                <strong>Tip:</strong> Dokumen dengan tanda  sudah pernah diupload. Anda tetap bisa mengupload ulang untuk pembaruan atau perpanjangan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <form action="{{ route('employee.documents.store') }}" method="POST" enctype="multipart/form-data" class="employee-doc-form">
            @csrf

            <!-- Document Type -->
            <div class="employee-doc-section">
                <label class="employee-doc-label">
                    Jenis Dokumen <span class="text-red-500">*</span>
                </label>
                {{-- Hidden native select --}}
                <select name="document_type_id"
                        id="document_type_id"
                        required
                        class="csel-native" tabindex="-1" aria-hidden="true">
                    <option value="">Pilih Jenis Dokumen</option>
                    @forelse($documentTypes as $type)
                        @php
                            $isUploaded = in_array($type->id, $uploadedDocTypes ?? []);
                            $stats = $documentStats[$type->id] ?? null;
                        @endphp
                        <option value="{{ $type->id }}"
                                data-formats="{{ $type->file_format ?? 'pdf,jpg,jpeg,png' }}"
                                data-max-size="{{ $type->max_file_size ?? 5120 }}"
                                data-required="{{ $type->is_required ? 'true' : 'false' }}"
                                data-uploaded="{{ $isUploaded ? 'true' : 'false' }}"
                                data-stats="{{ $stats ? json_encode($stats) : '' }}"
                                {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @empty
                        <option value="" disabled>Tidak ada dokumen tersedia</option>
                    @endforelse
                </select>
                {{-- Custom dropdown --}}
                <div class="csel {{ $documentTypes->isEmpty() ? 'csel--disabled' : '' }}" data-target="document_type_id" id="csel-doctype">
                    <button type="button" class="csel-trigger" {{ $documentTypes->isEmpty() ? 'disabled' : '' }}>
                        <span class="csel-value csel-value--placeholder">Pilih Jenis Dokumen</span>
                        <svg class="csel-arrow" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd"/></svg>
                    </button>
                    <div class="csel-panel">
                        <div class="csel-search-box">
                            <svg class="csel-search-icon" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 3.5a5.5 5.5 0 100 11 5.5 5.5 0 000-11zM2 9a7 7 0 1112.452 4.391l3.328 3.329a.75.75 0 11-1.06 1.06l-3.329-3.328A7 7 0 012 9z" clip-rule="evenodd"/></svg>
                            <input type="text" class="csel-search" placeholder="Cari jenis dokumen..." autocomplete="off">
                        </div>
                        <ul class="csel-list" id="csel-doctype-list">
                            @forelse($documentTypes as $type)
                                @php
                                    $isUploaded2 = in_array($type->id, $uploadedDocTypes ?? []);
                                    $stats2 = $documentStats[$type->id] ?? null;
                                @endphp
                                <li class="csel-item {{ $isUploaded2 ? 'csel-item--uploaded' : '' }}" data-val="{{ $type->id }}">
                                    <span class="csel-item__name">
                                        @if($isUploaded2)<span class="csel-check"></span>@endif
                                        {{ $type->name }}
                                        @if($stats2) <span class="csel-stats">({{ $stats2['approved'] ?? 0 }}/{{ $stats2['total'] ?? 0 }})</span>@endif
                                    </span>
                                    @if($type->description)
                                        <span class="csel-item__sub">{{ \Illuminate\Support\Str::limit($type->description, 60) }}</span>
                                    @endif
                                    @if($stats2 && (($stats2['expired'] ?? 0) > 0))
                                        <span class="csel-item__sub">
                                            <span class="csel-badge csel-badge--exp">Kadaluarsa</span>
                                            <span class="ml-1 text-red-600">{{ $stats2['expired'] }} dokumen</span>
                                        </span>
                                    @endif
                                    <span class="csel-item__sub">
                                        @if($type->is_required)<span class="csel-badge csel-badge--req">Wajib</span>@else<span class="csel-badge csel-badge--opt">Opsional</span>@endif
                                    </span>
                                </li>
                            @empty
                                <li class="csel-loading">Tidak ada dokumen tersedia</li>
                            @endforelse
                        </ul>
                        <div class="csel-no-result" style="display:none">Tidak ditemukan</div>
                    </div>
                </div>
                @error('document_type_id')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror

                <!-- Document Status Info -->
                <div id="document-status-info" class="mt-2 hidden">
                    <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Status Dokumen Ini:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs" id="status-details"></ul>
                                <p class="mt-2 text-xs italic">
                                    <i class="fas fa-upload mr-1"></i>
                                    Anda dapat mengupload dokumen baru untuk pembaruan atau perpanjangan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                @if(auth()->user()->worker?->department_id)
                    <p class="mt-2 text-xs text-gray-500">
                        <i class="fas fa-info-circle"></i>
                        Menampilkan {{ $documentTypes->count() }} jenis dokumen untuk posisi {{ auth()->user()->worker->department->name ?? '-' }}
                    </p>
                    <p class="text-xs text-gray-500">
                        <i class="fas fa-check text-green-600"></i>
                        Tanda centang () menunjukkan dokumen yang sudah pernah diupload
                    </p>
                @endif
                @if($documentTypes->isEmpty())
                    <div class="mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <p class="text-sm text-yellow-800">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            @if(auth()->user()->worker?->department)
                                Tidak ada dokumen yang diperlukan untuk posisi Anda.
                            @else
                                Anda belum terdaftar di departemen mana pun. Hubungi HR untuk informasi lebih lanjut.
                            @endif
                        </p>
                    </div>
                @endif
            </div>

            <!-- Expiry Date -->
            <div class="employee-doc-section">
                <label for="expired_date" class="employee-doc-label">
                    Tanggal Kadaluarsa (Opsional)
                </label>
                <input type="date"
                       name="expired_date"
                       id="expired_date"
                       value="{{ old('expired_date') }}"
                       class="employee-doc-input @error('expired_date') border-red-500 @enderror">
                @error('expired_date')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Kosongkan jika dokumen tidak memiliki masa berlaku</p>
            </div>

            <!-- File -->
            <div class="employee-doc-section">
                <label for="file" class="employee-doc-label">
                    File Dokumen <span class="text-red-500">*</span>
                </label>
                <input type="file"
                       name="file"
                       id="file"
                       required
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="employee-doc-input @error('file') border-red-500 @enderror">
                @error('file')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500" id="file-info">
                    Format: PDF, JPG, JPEG, PNG. Maksimal 5MB
                </p>
                <div id="file-preview" class="mt-2 hidden">
                    <div class="flex items-center p-2 bg-gray-50 rounded border">
                        <i class="fas fa-file text-gray-400 mr-2"></i>
                        <span class="text-sm text-gray-600" id="file-name"></span>
                        <span class="text-xs text-gray-500 ml-2" id="file-size"></span>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="employee-doc-section">
                <label for="notes" class="employee-doc-label">
                    Catatan (Opsional)
                </label>
                <textarea name="notes"
                          id="notes"
                          rows="3"
                          class="employee-doc-textarea @error('notes') border-red-500 @enderror"
                          placeholder="Tambahkan catatan jika diperlukan">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Buttons -->
            <div class="employee-doc-actions">
                <button type="submit" class="employee-doc-primary">
                    <svg class="w-5 h-5 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Upload Dokumen
                </button>
                <a href="{{ route('employee.documents.index') }}" class="employee-doc-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://fonts.bunny.net/css?family=space-grotesk:400,500,600,700&display=swap" rel="stylesheet" />
<style>
    .employee-doc-upload { max-width:880px; margin:0 auto; padding:1.5rem; display:flex; flex-direction:column; gap:1.5rem; }

    .employee-doc-hero {
        display:flex; align-items:flex-start; justify-content:space-between; gap:1.5rem;
        padding:1.75rem 2rem; border-radius:1.5rem;
        background:linear-gradient(120deg,#1e293b,#0f766e 55%,#fde68a 140%);
        color:#f8fafc; position:relative; overflow:hidden;
    }
    .employee-doc-hero::after { content:''; position:absolute; inset:0; background:radial-gradient(circle at top right,rgba(255,255,255,.2),transparent 55%); pointer-events:none; }
    .employee-doc-eyebrow { font-size:.75rem; letter-spacing:.22em; text-transform:uppercase; font-family:'Space Grotesk',sans-serif; margin-bottom:.3rem; color:rgba(248,250,252,.7); }
    .employee-doc-title { font-size:1.85rem; font-weight:700; font-family:'Space Grotesk',sans-serif; margin-bottom:.25rem; }
    .employee-doc-subtitle { font-size:.95rem; color:rgba(248,250,252,.75); }
    .employee-doc-hero__badge { padding:.4rem 1rem; border-radius:999px; background:rgba(255,255,255,.15); font-size:.85rem; font-weight:600; align-self:flex-start; position:relative; z-index:1; }
    .employee-doc-dept { margin-top:1rem; display:inline-flex; align-items:center; padding:.35rem .9rem; border-radius:999px; background:rgba(255,255,255,.18); font-size:.85rem; color:#f8fafc; }

    .employee-doc-card { background:#fff; border-radius:1.5rem; padding:1.75rem; box-shadow:0 20px 45px rgba(15,23,42,.12); border:1px solid rgba(15,23,42,.08); }
    .employee-doc-summary { margin-bottom:1.5rem; padding:1rem 1.25rem; border-radius:1rem; background:linear-gradient(120deg,rgba(14,116,144,.12),rgba(253,230,138,.28)); border:1px solid rgba(14,116,144,.25); }
    .employee-doc-form { display:flex; flex-direction:column; gap:1.25rem; }
    .employee-doc-section { display:flex; flex-direction:column; gap:.5rem; }
    .employee-doc-label { font-size:.95rem; font-weight:600; color:#0f172a; }
    .employee-doc-input, .employee-doc-textarea {
        width:100%; border-radius:.9rem; border:1px solid rgba(15,23,42,.15);
        padding:.75rem .9rem; font-size:.95rem; background:#f8fafc;
        transition:border-color .2s,box-shadow .2s;
    }
    .employee-doc-input:focus, .employee-doc-textarea:focus { outline:none; border-color:#14b8a6; box-shadow:0 0 0 3px rgba(20,184,166,.2); background:#fff; }
    .employee-doc-textarea { resize:vertical; min-height:120px; }
    .employee-doc-actions { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:.75rem; }
    .employee-doc-primary { display:inline-flex; align-items:center; justify-content:center; padding:.85rem 1.5rem; border-radius:999px; background:#0f766e; color:#fff; font-weight:600; border:none; box-shadow:0 16px 30px rgba(15,118,110,.25); cursor:pointer; }
    .employee-doc-primary:hover { background:#0d6b63; }
    .employee-doc-secondary { display:inline-flex; align-items:center; justify-content:center; padding:.85rem 1.5rem; border-radius:999px; background:#fff; border:1px solid rgba(15,23,42,.2); color:#0f172a; font-weight:600; text-decoration:none; }

    /* ===== Custom Select (csel) ===== */
    .csel-native { position:absolute!important; width:1px!important; height:1px!important; overflow:hidden!important; clip:rect(0,0,0,0)!important; border:0!important; padding:0!important; margin:-1px!important; }
    .csel { position:relative; width:100%; }
    .csel--disabled { opacity:.55; pointer-events:none; }
    .csel-trigger {
        display:flex; align-items:center; justify-content:space-between; width:100%;
        padding:.75rem 1rem; border-radius:.9rem; border:1px solid rgba(15,23,42,.15);
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
        background:#fff; border-radius:.9rem; border:1px solid rgba(15,23,42,.12);
        box-shadow:0 16px 48px rgba(15,23,42,.15); overflow:hidden;
    }
    .csel--open .csel-panel { display:block; }

    .csel-search-box { display:flex; align-items:center; padding:.6rem .85rem; border-bottom:1px solid rgba(15,23,42,.08); gap:.5rem; }
    .csel-search-icon { width:1rem; height:1rem; color:#94a3b8; flex-shrink:0; }
    .csel-search { border:none; outline:none; width:100%; font-size:.9rem; background:transparent; color:#0f172a; }
    .csel-search::placeholder { color:#94a3b8; }

    .csel-list { list-style:none; margin:0; padding:.35rem 0; max-height:300px; overflow-y:auto; }
    .csel-item { display:flex; flex-direction:column; padding:.65rem 1rem; cursor:pointer; transition:background .15s; gap:2px; }
    .csel-item:hover, .csel-item--active { background:rgba(20,184,166,.08); }
    .csel-item--selected { background:rgba(20,184,166,.15); }
    .csel-item--uploaded { border-left:3px solid #22c55e; }
    .csel-item__name { font-size:.9rem; color:#0f172a; line-height:1.35; display:flex; align-items:center; gap:.35rem; }
    .csel-item__sub { font-size:.75rem; color:#64748b; }
    .csel-check { color:#22c55e; font-weight:700; }
    .csel-stats { font-size:.75rem; color:#64748b; font-weight:400; }
    .csel-badge { font-size:.65rem; padding:.1rem .45rem; border-radius:999px; font-weight:600; display:inline-block; }
    .csel-badge--req { background:rgba(239,68,68,.12); color:#dc2626; }
    .csel-badge--opt { background:rgba(59,130,246,.1); color:#2563eb; }
    .csel-badge--exp { background:rgba(239,68,68,.12); color:#dc2626; }
    .csel-no-result { padding:.85rem 1rem; text-align:center; font-size:.85rem; color:#94a3b8; }
    .csel-loading { padding:.85rem 1rem; text-align:center; font-size:.85rem; color:#64748b; list-style:none; }

    @media(max-width:768px) {
        .employee-doc-upload { padding:1rem; }
        .employee-doc-hero { flex-direction:column; padding:1.5rem; }
        .employee-doc-title { font-size:1.5rem; }
        .csel-panel { position:fixed; left:.75rem; right:.75rem; top:auto; bottom:0; max-height:60vh; border-radius:1rem 1rem 0 0; }
        .csel-list { max-height:calc(60vh - 56px); }
    }
</style>
@endpush

@push('scripts')
<script>
(function() {
    /* ===== Custom Select Engine ===== */
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
            if (this.native) { this.native.value = val; this.native.dispatchEvent(new window.Event('change', {bubbles:true})); }
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

        _esc(str) { const d = document.createElement('div'); d.textContent = str; return d.innerHTML; }
    }

    // Init custom selects
    document.querySelectorAll('.csel[data-target]').forEach(el => new CSelect(el));

    /* ===== Existing JS: status info, file validation ===== */
    const documentTypeSelect = document.getElementById('document_type_id');
    const fileInput = document.getElementById('file');
    const fileInfo = document.getElementById('file-info');
    const filePreview = document.getElementById('file-preview');
    const fileName = document.getElementById('file-name');
    const fileSizeEl = document.getElementById('file-size');

    documentTypeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (!selectedOption || !selectedOption.value) return;

        const formats = selectedOption.getAttribute('data-formats') || 'pdf,jpg,jpeg,png';
        const maxSize = selectedOption.getAttribute('data-max-size') || 5120;
        const isUploaded = selectedOption.getAttribute('data-uploaded') === 'true';
        const statsJson = selectedOption.getAttribute('data-stats');

        const acceptFormats = formats.split(',').map(f => '.' + f.trim()).join(',');
        fileInput.setAttribute('accept', acceptFormats);

        const maxSizeMB = Math.round(maxSize / 1024);
        fileInfo.textContent = 'Format: ' + formats.toUpperCase().replace(/,/g, ', ') + '. Maksimal ' + maxSizeMB + 'MB';

        const statusInfo = document.getElementById('document-status-info');
        const statusDetails = document.getElementById('status-details');

        if (isUploaded && statsJson) {
            try {
                const stats = JSON.parse(statsJson);
                statusDetails.innerHTML = '';
                if (stats.total) statusDetails.innerHTML += '<li>Total dokumen diupload: <strong>' + stats.total + '</strong></li>';
                if (stats.approved > 0) statusDetails.innerHTML += '<li class="text-green-700"> Terverifikasi: <strong>' + stats.approved + '</strong></li>';
                if (stats.pending > 0) statusDetails.innerHTML += '<li class="text-yellow-700"> Menunggu verifikasi: <strong>' + stats.pending + '</strong></li>';
                if (stats.rejected > 0) statusDetails.innerHTML += '<li class="text-red-700"> Ditolak: <strong>' + stats.rejected + '</strong></li>';
                if (stats.expired > 0) statusDetails.innerHTML += '<li class="text-red-700"><span class="csel-badge csel-badge--exp">Kadaluarsa</span> Dokumen ini sudah kadaluarsa (<strong>' + stats.expired + '</strong>).</li>';
                if (stats.latest_expired_date) statusDetails.innerHTML += '<li>Tanggal kadaluarsa terakhir: <strong>' + stats.latest_expired_date + '</strong></li>';
                if (stats.latest_status) {
                    const sm = {verified:' Terverifikasi', approved:' Terverifikasi', pending:' Menunggu', rejected:' Ditolak'};
                    statusDetails.innerHTML += '<li>Status terakhir: <strong>' + (sm[stats.latest_status] || stats.latest_status) + '</strong></li>';
                }
                statusInfo.classList.remove('hidden');
            } catch(e) { statusInfo.classList.add('hidden'); }
        } else {
            statusInfo.classList.add('hidden');
        }

        if (fileInput.files.length > 0) {
            const ext = fileInput.files[0].name.split('.').pop().toLowerCase();
            if (!formats.split(',').map(f => f.trim()).includes(ext)) {
                fileInput.value = '';
                filePreview.classList.add('hidden');
            }
        }
    });

    fileInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const selectedOption = documentTypeSelect.options[documentTypeSelect.selectedIndex];
            const allowedFormats = (selectedOption.getAttribute('data-formats') || 'pdf,jpg,jpeg,png').split(',');
            const maxSize = parseInt(selectedOption.getAttribute('data-max-size') || 5120) * 1024;
            const ext = file.name.split('.').pop().toLowerCase();
            if (!allowedFormats.map(f => f.trim()).includes(ext)) {
                window.showWarningAlert('Validasi', 'Format file tidak sesuai dengan jenis dokumen yang dipilih!');
                this.value = '';
                filePreview.classList.add('hidden');
                return;
            }
            if (file.size > maxSize) {
                window.showWarningAlert('Validasi', 'Ukuran file terlalu besar! Maksimal ' + Math.round(maxSize / 1024 / 1024) + 'MB.');
                this.value = '';
                filePreview.classList.add('hidden');
                return;
            }
            fileName.textContent = file.name;
            fileSizeEl.textContent = '(' + (file.size/1024/1024).toFixed(2) + ' MB)';
            filePreview.classList.remove('hidden');
        } else {
            filePreview.classList.add('hidden');
        }
    });

    document.querySelector('form').addEventListener('submit', function(e) {
        if (documentTypeSelect.value === '') { e.preventDefault(); window.showWarningAlert('Validasi', 'Pilih jenis dokumen terlebih dahulu!'); return; }
        if (fileInput.files.length === 0) { e.preventDefault(); window.showWarningAlert('Validasi', 'Pilih file dokumen yang akan diupload!'); return; }
    });
})();
</script>
@endpush
