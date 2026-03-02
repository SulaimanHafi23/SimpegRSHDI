@extends('layouts.admin')

@section('title', 'Data Jenis Kelamin')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Data Jenis Kelamin</h1>
            <p class="text-sm sm:text-base text-gray-600 mt-1">Daftar data master jenis kelamin</p>
        </div>
    </div>

    <div class="md:hidden space-y-3">
        @forelse($genders as $gender)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="h-9 w-9 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-venus-mars text-purple-600 text-sm"></i>
                    </div>
                    <div class="ml-3 min-w-0">
                        <p class="text-sm font-semibold text-gray-900 truncate">{{ $gender->name }}</p>
                        <span class="inline-flex mt-1 items-center px-2 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Data Master
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-lg shadow px-6 py-12 text-center">
                <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                <p class="text-gray-500 text-lg">Tidak ada data</p>
            </div>
        @endforelse
    </div>

    <div class="hidden md:block bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jenis Kelamin</th>
                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase hidden md:table-cell">Keterangan</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($genders as $index => $gender)
                <tr class="hover:bg-gray-50 transition duration-150">
                    <td class="px-3 sm:px-6 py-4">
                        <div class="flex items-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-venus-mars text-purple-600 text-xs sm:text-sm"></i>
                            </div>
                            <div class="ml-3 sm:ml-4">
                                <div class="text-xs sm:text-sm font-medium text-gray-900">{{ $gender->name }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-3 sm:px-6 py-4 text-xs sm:text-sm text-gray-500 hidden md:table-cell">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Data Master
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="px-6 py-12 text-center">
                        <i class="fas fa-inbox text-gray-400 text-5xl mb-4"></i>
                        <p class="text-gray-500 text-lg">Tidak ada data</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
        @if($genders->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t">{{ $genders->links() }}</div>
        @endif
    </div>

    @if($genders->hasPages())
    <div class="md:hidden bg-gray-50 px-4 py-3 rounded-lg border border-gray-200">
        {{ $genders->links() }}
    </div>
    @endif
</div>
@endsection
