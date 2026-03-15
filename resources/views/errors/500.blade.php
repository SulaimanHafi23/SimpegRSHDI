@extends('layouts.admin')

@section('title', 'Server Error')

@section('content')
<x-errors.page-shell>
            {{-- Error Icon --}}
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-[#fff4d9] border border-[#f5a623]/25 rounded-full">
                    <i class="fas fa-exclamation-triangle text-5xl text-[#c2410c]"></i>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-8xl font-bold text-[#0a3d1f] mb-4">500</h1>

            {{-- Error Title --}}
            <h2 class="text-3xl font-bold text-[#155a2e] mb-4">
                Terjadi Kesalahan Server
            </h2>

            {{-- Error Message --}}
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
                Maaf, terjadi kesalahan pada server kami.
                Tim kami sedang bekerja untuk memperbaikinya.
            </p>

            @if(config('app.debug') && isset($exception))
                <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-8 max-w-lg mx-auto text-left">
                    <p class="text-xs font-mono text-red-800 break-all">
                        {{ $exception->getMessage() }}
                    </p>
                </div>
            @endif

            {{-- Action Buttons --}}
            @include('errors.partials.action-buttons', [
                'primaryLabel' => 'Muat Ulang',
                'primaryIcon' => 'fas fa-redo',
                'primaryOnclick' => 'window.location.reload()'
            ])

            {{-- Help Text --}}
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">
                    Jika masalah berlanjut, silakan hubungi administrator
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="mailto:sidia.rshdi@gmail.com" class="text-sm text-[#155a2e] hover:text-[#0f4d27]">
                        <i class="fas fa-envelope mr-1"></i>
                        sidia.rshdi@gmail.com
                    </a>
                    <a href="{{ route('public.help') }}" class="text-sm text-amber-600 hover:text-amber-700">
                        <i class="fas fa-life-ring mr-1"></i>
                        Help Center
                    </a>
                    <a href="{{ route('public.terms') }}" class="text-sm text-sky-600 hover:text-sky-700">
                        <i class="fas fa-file-contract mr-1"></i>
                        Terms
                    </a>
                </div>
                @include('errors.partials.quick-links', [
                    'containerClass' => 'mt-5 flex flex-wrap justify-center gap-3 text-sm',
                    'showHelp' => false,
                    'showHome' => true
                ])
            </div>
</x-errors.page-shell>
@endsection
