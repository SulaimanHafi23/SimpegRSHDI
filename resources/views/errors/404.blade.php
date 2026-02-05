@extends('layouts.admin')

@section('title', 'Page Not Found')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full">
        <div class="text-center">
            {{-- Error Icon --}}
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-yellow-100 rounded-full">
                    <i class="fas fa-search text-5xl text-yellow-600"></i>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-8xl font-bold text-gray-900 mb-4">404</h1>
            
            {{-- Error Title --}}
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Halaman Tidak Ditemukan
            </h2>
            
            {{-- Error Message --}}
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Maaf, halaman yang Anda cari tidak dapat ditemukan. 
                Halaman mungkin telah dipindahkan atau tidak pernah ada.
            </p>

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                <x-button 
                    variant="primary" 
                    icon="fas fa-arrow-left"
                    onclick="window.history.back()">
                    Kembali
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

            {{-- Popular Links --}}
            @auth
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">
                    Mungkin Anda mencari:
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ $dashboardUrl }}" class="text-sm text-blue-600 hover:text-blue-700 px-4 py-2 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
                    </a>
                    @can('worker.view')
                        <a href="{{ route('employee.profile.show') }}" class="text-sm text-blue-600 hover:text-blue-700 px-4 py-2 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                            <i class="fas fa-user mr-1"></i> Profil Saya
                        </a>
                    @endcan
                    @can('attendance.checkin')
                        @if(Route::has('employee.attendance.checkin'))
                            <a href="{{ route('employee.attendance.checkin') }}" class="text-sm text-blue-600 hover:text-blue-700 px-4 py-2 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                                <i class="fas fa-clock mr-1"></i> Absensi
                            </a>
                        @endif
                    @endcan
                </div>
            </div>
            @endauth
        </div>

        {{-- Animated Background Elements --}}
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-yellow-100 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-blue-100 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse animation-delay-2000"></div>
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
