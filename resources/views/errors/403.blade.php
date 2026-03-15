@extends('layouts.guest')

@section('title', '403 - Akses Ditolak')

@section('content')
<x-errors.page-shell>
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-[#fff4d9] border border-[#f5a623]/25 rounded-full">
                    <i class="fas fa-lock text-5xl text-[#c2410c]"></i>
                </div>
            </div>

            <h1 class="text-8xl font-bold text-[#0a3d1f] mb-4">403</h1>
            <h2 class="text-3xl font-bold mb-4 text-[#155a2e]">Akses Ditolak</h2>
            <p class="text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">Anda tidak memiliki otorisasi untuk melihat halaman ini.</p>

            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                @if(request()->is('approvals/*') || request()->is('approvals'))
                    <a href="{{ route('employee.business-trips.index') }}" class="inline-flex items-center justify-center w-full sm:w-auto sm:min-w-[190px] px-4 py-2.5 bg-[#155a2e] hover:bg-[#0f4d27] text-white rounded-xl transition">Perjalanan Dinas Saya</a>
                @else
                    <a href="{{ url()->previous() ?? route('admin.dashboard') }}" class="inline-flex items-center justify-center w-full sm:w-auto sm:min-w-[190px] px-4 py-2.5 bg-[#155a2e] hover:bg-[#0f4d27] text-white rounded-xl transition">Kembali</a>
                @endif
                <a href="{{ route('public.help') }}" class="inline-flex items-center justify-center w-full sm:w-auto sm:min-w-[190px] px-4 py-2.5 bg-amber-50 text-amber-700 hover:bg-amber-100 rounded-xl transition">Help Center</a>
            </div>

            @auth
            <div class="bg-yellow-50 border border-yellow-200 rounded-2xl p-4 mt-8 text-left">
                <p class="text-sm"><strong>DEBUG INFO:</strong></p>
                <p class="text-xs">User: {{ auth()->user()->email }}</p>
                <p class="text-xs">Role: {{ auth()->user()->roles->pluck('name')->implode(', ') }}</p>
                <p class="text-xs">Timestamp: {{ now()->format('Y-m-d H:i:s') }}</p>
                @if(isset($exception))
                <p class="text-xs text-red-600">Message: {{ $exception->getMessage() }}</p>
                @endif
            </div>
            @endauth

            <div class="mt-8 pt-6 border-t border-gray-200">
                @include('errors.partials.quick-links', ['showHome' => true])
                <p class="mt-5 text-xs text-gray-500">&copy; {{ date('Y') }} Muhammad Sulaiman Hafi &amp; Muhammad Hafidl Badali x RSUD HDI. All rights reserved.</p>
            </div>
</x-errors.page-shell>
@endsection
