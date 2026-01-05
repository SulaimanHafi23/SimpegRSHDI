@extends('layouts.employee')

@section('title', 'Ajukan Perjalanan Dinas')

@section('content')
<div class="space-y-6">
    <!-- Breadcrumb -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li>
                <a href="{{ route('employee.business-trips.index') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-briefcase mr-2"></i>Perjalanan Dinas
                </a>
            </li>
            <li>
                <span class="mx-2 text-gray-400">/</span>
            </li>
            <li class="text-gray-500">Ajukan Baru</li>
        </ol>
    </nav>

    <!-- Page Header -->
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Ajukan Perjalanan Dinas</h1>
        <p class="text-gray-600 mt-1">Isi formulir untuk mengajukan perjalanan dinas</p>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('employee.business-trips.store') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Destination -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tujuan <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="destination" value="{{ old('destination') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 @error('destination') border-red-500 @enderror"
                           placeholder="Kota/Lokasi tujuan" required>
                    @error('destination')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Start Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Mulai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="start_date" value="{{ old('start_date') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 @error('start_date') border-red-500 @enderror"
                           required>
                    @error('start_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal Selesai <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="end_date" value="{{ old('end_date') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 @error('end_date') border-red-500 @enderror"
                           required>
                    @error('end_date')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Estimated Cost -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Estimasi Biaya (Rp)
                    </label>
                    <input type="number" step="0.01" name="estimated_cost" value="{{ old('estimated_cost') }}" 
                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 @error('estimated_cost') border-red-500 @enderror"
                           placeholder="0.00">
                    @error('estimated_cost')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Purpose -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Tujuan Perjalanan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="purpose" rows="4" 
                              class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 @error('purpose') border-red-500 @enderror"
                              placeholder="Jelaskan tujuan dan keperluan perjalanan dinas..." required>{{ old('purpose') }}</textarea>
                    @error('purpose')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('employee.business-trips.index') }}" 
                   class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-paper-plane mr-2"></i>Ajukan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection