@extends('layouts.workers')

@section('title', 'Ajukan Lembur')

@section('content')
<div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Ajukan Lembur</h1>
        <p class="mt-2 text-sm text-gray-600">Isi formulir untuk mengajukan lembur</p>
    </div>

    <div class="bg-white rounded-lg shadow-md p-6">
        <form action="{{ route('workers.overtimes.store') }}" method="POST" id="overtimeForm">
            @csrf

            <div class="space-y-6">
                <div>
                    <label for="overtime_date" class="block text-sm font-medium text-gray-700 mb-1">
                        Tanggal Lembur <span class="text-red-500">*</span>
                    </label>
                    <input type="date" name="overtime_date" id="overtime_date" 
                           value="{{ old('overtime_date') }}" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('overtime_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-1">
                            Jam Mulai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="start_time" id="start_time" 
                               value="{{ old('start_time') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               onchange="calculateHours()">
                        @error('start_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-1">
                            Jam Selesai <span class="text-red-500">*</span>
                        </label>
                        <input type="time" name="end_time" id="end_time" 
                               value="{{ old('end_time') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                               onchange="calculateHours()">
                        @error('end_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Total Jam Lembur
                    </label>
                    <div class="flex items-center">
                        <input type="number" name="total_hours" id="total_hours" step="0.5"
                               value="{{ old('total_hours', 0) }}" readonly
                               class="w-32 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50">
                        <span class="ml-3 text-sm text-gray-600">jam</span>
                    </div>
                    @error('total_hours')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                        Deskripsi Pekerjaan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description" id="description" rows="4" required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent"
                              placeholder="Jelaskan pekerjaan yang dilakukan saat lembur...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-between">
                <a href="{{ route('workers.overtimes.index') }}"
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition inline-flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <div class="flex space-x-3">
                    <button type="button" onclick="this.form.reset(); document.getElementById('total_hours').value = 0"
                            class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-paper-plane mr-2"></i>Ajukan Lembur
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function calculateHours() {
    const startTime = document.getElementById('start_time').value;
    const endTime = document.getElementById('end_time').value;
    
    if (startTime && endTime) {
        const start = new Date('2000-01-01 ' + startTime);
        const end = new Date('2000-01-01 ' + endTime);
        
        if (end > start) {
            const diffMs = end - start;
            const diffHrs = diffMs / (1000 * 60 * 60);
            document.getElementById('total_hours').value = diffHrs.toFixed(1);
        } else {
            document.getElementById('total_hours').value = 0;
            alert('Jam selesai harus lebih besar dari jam mulai');
        }
    }
}
</script>
@endsection
