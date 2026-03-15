@extends('layouts.admin')

@section('title', 'Page Expired')

@section('content')
<x-errors.page-shell>
            {{-- Error Icon --}}
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-[#fff4d9] border border-[#f5a623]/25 rounded-full">
                    <i class="fas fa-clock text-5xl text-[#c2410c]"></i>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-8xl font-bold text-[#0a3d1f] mb-4">419</h1>

            {{-- Error Title --}}
            <h2 class="text-3xl font-bold text-[#155a2e] mb-4">
                Halaman Kadaluarsa
            </h2>

            {{-- Error Message --}}
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
                Sesi Anda telah habis. Silakan muat ulang halaman dan coba lagi.
            </p>

            {{-- Info Box --}}
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-8 max-w-md mx-auto">
                <p class="text-sm text-amber-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    Ini terjadi karena Anda tidak aktif terlalu lama atau token keamanan telah kadaluarsa.
                </p>
            </div>

            {{-- Action Buttons --}}
            @include('errors.partials.action-buttons', [
                'primaryLabel' => 'Muat Ulang Halaman',
                'primaryIcon' => 'fas fa-redo',
                'primaryOnclick' => 'window.location.reload()'
            ])

            {{-- Tips --}}
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm font-semibold text-gray-700 mb-3">
                    Tips untuk menghindari masalah ini:
                </p>
                <ul class="text-sm text-gray-600 space-y-2 max-w-md mx-auto text-left">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span>Jangan biarkan halaman terbuka terlalu lama tanpa aktivitas</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span>Simpan pekerjaan Anda secara berkala</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-1 mr-2"></i>
                        <span>Hindari membuka banyak tab aplikasi yang sama</span>
                    </li>
                </ul>
                @include('errors.partials.quick-links', [
                    'containerClass' => 'mt-6 flex flex-wrap justify-center gap-3 text-sm',
                    'showTerms' => false,
                    'showLogin' => true
                ])
            </div>
</x-errors.page-shell>
@endsection
