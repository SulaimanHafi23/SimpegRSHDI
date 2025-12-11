@extends('layouts.workers')

@section('title', 'Dokumen Saya')

@section('content')
<div class="space-y-4 sm:space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">Dokumen Saya</h1>
            <p class="mt-2 text-sm text-gray-600">Kelola dokumen pribadi dan dokumen kenaikan pangkat</p>
        </div>
        <button onclick="document.getElementById('uploadModal').classList.remove('hidden')" 
                class="w-full sm:w-auto px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition inline-flex items-center justify-center">
            <i class="fas fa-plus mr-2"></i>Upload Dokumen
        </button>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-white rounded-lg shadow-md p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Total Dokumen</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800">{{ $totalDocuments }}</p>
                </div>
                <div class="p-2 sm:p-3 bg-blue-100 rounded-full">
                    <i class="fas fa-file-alt text-lg sm:text-xl text-blue-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Disetujui</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-green-600">{{ $approvedDocuments }}</p>
                </div>
                <div class="p-2 sm:p-3 bg-green-100 rounded-full">
                    <i class="fas fa-check-circle text-lg sm:text-xl text-green-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Pending</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-yellow-600">{{ $pendingDocuments }}</p>
                </div>
                <div class="p-2 sm:p-3 bg-yellow-100 rounded-full">
                    <i class="fas fa-clock text-lg sm:text-xl text-yellow-600"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs sm:text-sm text-gray-600 mb-1">Expired</p>
                    <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-red-600">{{ $expiredDocuments }}</p>
                </div>
                <div class="p-2 sm:p-3 bg-red-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-lg sm:text-xl text-red-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents List -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="p-4 sm:p-6 border-b border-gray-200">
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Daftar Dokumen</h3>
        </div>

        @if($documents->isEmpty())
        <div class="p-8 text-center text-gray-500">
            <i class="fas fa-inbox text-4xl mb-3"></i>
            <p>Belum ada dokumen</p>
            <p class="text-sm mt-2">Upload dokumen pertama Anda</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Dokumen</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden sm:table-cell">Tanggal Upload</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Expired</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($documents as $document)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 sm:px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($document->file_type == 'application/pdf')
                                    <i class="fas fa-file-pdf text-2xl text-red-600"></i>
                                    @else
                                    <i class="fas fa-file-image text-2xl text-blue-600"></i>
                                    @endif
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-gray-900">{{ $document->fileRequirement->documentType->name ?? 'Dokumen' }}</p>
                                    <p class="text-xs text-gray-500">{{ $document->file_name }}</p>
                                    <p class="text-xs text-gray-400 sm:hidden">{{ $document->created_at->format('d/m/Y') }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden sm:table-cell">
                            {{ $document->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 hidden md:table-cell">
                            @if($document->expired_date)
                                <span class="{{ $document->is_expired ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ \Carbon\Carbon::parse($document->expired_date)->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap">
                            @if($document->status == 'Approved')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>Disetujui
                            </span>
                            @elseif($document->status == 'Rejected')
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                <i class="fas fa-times-circle mr-1"></i>Ditolak
                            </span>
                            @else
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>Pending
                            </span>
                            @endif
                        </td>
                        <td class="px-4 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('workers.documents.show', $document->id) }}" 
                                   class="text-blue-600 hover:text-blue-900" title="Lihat">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('workers.documents.download', $document->id) }}" 
                                   class="text-green-600 hover:text-green-900" title="Download">
                                    <i class="fas fa-download"></i>
                                </a>
                                @if($document->status != 'Approved')
                                <form action="{{ route('workers.documents.destroy', $document->id) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('Yakin ingin menghapus dokumen ini?')" 
                                      class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full max-h-[90vh] overflow-y-auto">
        <div class="p-4 sm:p-6">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900">Upload Dokumen Baru</h3>
                <button onclick="document.getElementById('uploadModal').classList.add('hidden')" 
                        class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <form action="{{ route('workers.documents.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        Jenis Dokumen <span class="text-red-500">*</span>
                    </label>
                    <select name="file_requirement_id" required 
                            class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Pilih Jenis Dokumen</option>
                        @foreach($fileRequirements as $requirement)
                        <option value="{{ $requirement->id }}">{{ $requirement->documentType->name ?? 'Dokumen' }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        Tanggal Expired (Jika Ada)
                    </label>
                    <input type="date" name="expired_date" 
                           class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        File Dokumen <span class="text-red-500">*</span>
                    </label>
                    <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    <p class="mt-1 text-xs text-gray-500">Format: PDF, JPG, PNG (Max: 5MB)</p>
                </div>

                <div>
                    <label class="block text-xs sm:text-sm font-medium text-gray-700 mb-2">
                        Catatan
                    </label>
                    <textarea name="notes" rows="3"
                              class="w-full px-3 sm:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              placeholder="Catatan tambahan (opsional)"></textarea>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-4">
                    <button type="button" 
                            onclick="document.getElementById('uploadModal').classList.add('hidden')"
                            class="w-full sm:w-auto px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                        Batal
                    </button>
                    <button type="submit" 
                            class="w-full sm:w-auto px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-upload mr-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Document Guide -->
<div class="bg-blue-50 rounded-lg p-4 sm:p-6 border border-blue-200">
    <h4 class="text-sm font-semibold text-blue-900 mb-2">
        <i class="fas fa-info-circle mr-2"></i>Contoh Dokumen yang Diperlukan:
    </h4>
    <div class="grid sm:grid-cols-2 gap-2 text-xs sm:text-sm text-blue-800">
        <ul class="list-disc ml-5 space-y-1">
            <li>STRP (Surat Tanda Registrasi Profesi)</li>
            <li>KTP (Kartu Tanda Penduduk)</li>
            <li>Ijazah Terakhir</li>
            <li>Sertifikat Kompetensi</li>
        </ul>
        <ul class="list-disc ml-5 space-y-1">
            <li>NPWP (Nomor Pokok Wajib Pajak)</li>
            <li>Kartu BPJS Kesehatan</li>
            <li>Kartu BPJS Ketenagakerjaan</li>
            <li>Dokumen Kenaikan Pangkat</li>
        </ul>
    </div>
</div>
@endsection
