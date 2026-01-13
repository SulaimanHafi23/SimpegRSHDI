@extends('layouts.admin')

@section('title', 'Page Expired')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full">
        <div class="text-center">
            {{-- Error Icon --}}
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-orange-100 rounded-full">
                    <i class="fas fa-clock text-5xl text-orange-600"></i>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-8xl font-bold text-gray-900 mb-4">419</h1>
            
            {{-- Error Title --}}
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Halaman Kadaluarsa
            </h2>
            
            {{-- Error Message --}}
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Sesi Anda telah habis. Silakan muat ulang halaman dan coba lagi.
            </p>

            {{-- Info Box --}}
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-8 max-w-md mx-auto">
                <p class="text-sm text-orange-700">
                    <i class="fas fa-info-circle mr-2"></i>
                    Ini terjadi karena Anda tidak aktif terlalu lama atau token keamanan telah kadaluarsa.
                </p>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                <x-button 
                    variant="primary" 
                    icon="fas fa-redo"
                    onclick="window.location.reload()">
                    Muat Ulang Halaman
                </x-button>

                @auth
                    @php
                        $dashboardUrl = '/';
                        if (auth()->user()->hasRole('Employee')) {
                            $dashboardUrl = route('employee.dashboard');
                        } elseif (auth()->user()->hasRole('HR')) {
                            $dashboardUrl = route('hr.dashboard');
                        } elseif (auth()->user()->hasRole('Manager')) {
                            $dashboardUrl = route('manager.dashboard');
                        } elseif (auth()->user()->hasRole('Super Admin')) {
                            $dashboardUrl = route('admin.dashboard');
                        }
                    @endphp
                    <x-button 
                        variant="secondary" 
                        icon="fas fa-home"
                        onclick="window.location.href='{{ $dashboardUrl }}'">
                        Ke Dashboard
                    </x-button>
                @else
                    <x-button 
                        variant="secondary" 
                        icon="fas fa-sign-in-alt"
                        onclick="window.location.href='{{ route('login') }}'">
                        Login
                    </x-button>
                @endauth
            </div>

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
            </div>
        </div>

        {{-- Animated Background Elements --}}
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-orange-100 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-yellow-100 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse animation-delay-2000"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .animation-delay-2000 {
        animation-delay: 2s;
    }
</style>
@endpush
@endsection
