@extends('layouts.admin')

@section('title', 'Data Agama')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Data Agama</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Daftar data master agama</p>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Nama Agama
                        </th>
                        <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">
                            Keterangan
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($religions as $index => $religion)
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <td class="px-3 sm:px-6 py-4">
                            <div class="flex items-center">
                                <div class="h-8 w-8 sm:h-10 sm:w-10 flex-shrink-0 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-mosque text-green-600 text-xs sm:text-sm"></i>
                                </div>
                                <div class="ml-3 sm:ml-4">
                                    <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $religion->name }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Data Master
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="2" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Tidak ada data</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        </div>

        <!-- Pagination -->
        @if($religions->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $religions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
