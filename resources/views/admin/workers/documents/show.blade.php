@extends('layouts.admin')

@section('title', 'Detail Dokumen Pegawai')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <div class="flex flex-col gap-4 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div>
            <h1 class="text-xl font-bold text-gray-900 sm:text-2xl">Detail Dokumen</h1>
            <p class="mt-1 text-sm text-gray-500">Tinjau file, status verifikasi, dan informasi dokumen pegawai.</p>
        </div>
        <a href="{{ route('admin.worker-documents.download', $document->id) }}" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700">Download</a>
    </div>

    <div class="bg-white rounded-lg shadow p-4 sm:p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2">
                {{-- Preview area --}}
                @php
                    $filePath = $document->file_path ?? null;
                    /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
                    $disk = \Illuminate\Support\Facades\Storage::disk('public');
                    $fileExists = $filePath ? $disk->exists($filePath) : false;
                    $ext = $filePath ? pathinfo($filePath, PATHINFO_EXTENSION) : null;
                @endphp

                @if($fileExists)
                    @if(in_array(strtolower($ext), ['pdf']))
                        <div class="w-full border rounded overflow-hidden bg-white" style="min-height: 600px;">
                            <iframe
                                src="{{ $disk->url($filePath) }}#toolbar=1&navpanes=0&scrollbar=1&zoom=page-width"
                                class="w-full"
                                style="height: 800px; border: none;"
                                frameborder="0"
                                loading="lazy">
                            </iframe>
                        </div>
                    @elseif(in_array(strtolower($ext), ['jpg','jpeg','png','gif']))
                        <img src="{{ $disk->url($filePath) }}" alt="{{ $document->file_name }}" class="w-full rounded">
                    @else
                        <p class="text-sm text-gray-600">Preview tidak tersedia untuk tipe file ini.</p>
                        <p class="mt-2"><a href="{{ route('admin.worker-documents.download', $document->id) }}" class="px-3 py-2 bg-blue-600 text-white rounded">Download</a></p>
                    @endif
                @else
                    <div class="p-4 border rounded bg-yellow-50">
                        <p class="text-sm text-yellow-800">File tidak ditemukan di penyimpanan.</p>
                        <p class="text-sm text-gray-600 mt-2">Periksa kembali apakah `storage:link` sudah dijalankan dan file ada di disk public.</p>
                    </div>
                @endif
            </div>

            <div>
                <h3 class="text-lg font-semibold mb-3">Informasi Dokumen</h3>
                <div class="space-y-3 text-sm text-gray-700">
                    <div>
                        <p class="text-xs text-gray-500">Pegawai</p>
                        <p class="font-medium">{{ $document->worker->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Tipe Dokumen</p>
                        <p class="font-medium">{{ $document->documentType->name ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Nama File</p>
                        <p>{{ $document->file_name }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Ukuran File</p>
                        <p>{{ $document->file_size_human ?? number_format($document->file_size) . ' bytes' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst($document->status) }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Tanggal Kadaluarsa</p>
                        <p>{{ $document->expired_date?->format('d F Y') ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Diupload Pada</p>
                        <p>{{ $document->created_at?->format('d F Y, H:i') }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Catatan</p>
                        <p>{{ $document->notes ?? '-' }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500">Diverifikasi Oleh</p>
                        <p>{{ $document->verifier?->name ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-4">
                    @if($document->status === 'pending' && (auth()->user()->hasRole('Super Admin') || auth()->user()->can('verify-worker-documents')))
                        <form action="{{ route('admin.worker-documents.verify', $document->id) }}" method="POST" class="inline-block mr-2">
                            @csrf
                            <button class="px-4 py-2 bg-green-600 text-white rounded">Verifikasi</button>
                        </form>

                        <button onclick="document.getElementById('reject-modal').classList.remove('hidden')" class="px-4 py-2 bg-red-600 text-white rounded">Tolak</button>

                        <div id="reject-modal" class="hidden fixed inset-0 z-50" onclick="if(event.target === this) document.getElementById('reject-modal').classList.add('hidden')">
                            <div class="absolute inset-0 bg-black/30"></div>
                            <div class="relative flex min-h-screen items-center justify-center p-4">
                                <div class="w-full max-w-md rounded-xl border border-gray-200 bg-white shadow-xl" onclick="event.stopPropagation()">
                                    <form action="{{ route('admin.worker-documents.reject', $document->id) }}" method="POST">
                                        @csrf
                                        <div class="border-b border-gray-200 px-5 py-4">
                                            <h3 class="text-base font-semibold text-gray-900">Tolak Dokumen</h3>
                                            <p class="mt-1 text-sm text-gray-500">Isi alasan penolakan untuk dikirim ke pegawai.</p>
                                        </div>
                                        <div class="px-5 py-4">
                                            <label class="block text-sm font-medium text-gray-700">Alasan penolakan <span class="text-red-500">*</span></label>
                                            <textarea name="notes" class="mt-2 w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-100" rows="4" required placeholder="Tuliskan alasan penolakan..."></textarea>
                                        </div>
                                        <div class="flex justify-end gap-2 border-t border-gray-200 px-5 py-4">
                                            <button type="button" onclick="document.getElementById('reject-modal').classList.add('hidden')" class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Batal</button>
                                            <button type="submit" class="rounded-lg border border-red-700 bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700">Tolak Dokumen</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
