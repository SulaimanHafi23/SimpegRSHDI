@extends('layouts.admin')

@section('title', 'Server Error')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center px-4">
    <div class="max-w-2xl w-full">
        <div class="text-center">
            {{-- Error Icon --}}
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-red-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-5xl text-red-600"></i>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-8xl font-bold text-gray-900 mb-4">500</h1>
            
            {{-- Error Title --}}
            <h2 class="text-3xl font-bold text-gray-800 mb-4">
                Terjadi Kesalahan Server
            </h2>
            
            {{-- Error Message --}}
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Maaf, terjadi kesalahan pada server kami. 
                Tim kami sedang bekerja untuk memperbaikinya.
            </p>

            @if(config('app.debug') && isset($exception))
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-8 max-w-lg mx-auto text-left">
                    <p class="text-xs font-mono text-red-800 break-all">
                        {{ $exception->getMessage() }}
                    </p>
                </div>
            @endif

            {{-- Action Buttons --}}
            <div class="flex flex-col sm:flex-row gap-3 justify-center items-center">
                <x-button 
                    variant="primary" 
                    icon="fas fa-redo"
                    onclick="window.location.reload()">
                    Muat Ulang
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

            {{-- Help Text --}}
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">
                    Jika masalah berlanjut, silakan hubungi administrator
                </p>
                <a href="mailto:admin@simpegrshdi.com" class="text-sm text-blue-600 hover:text-blue-700">
                    <i class="fas fa-envelope mr-1"></i>
                    admin@simpegrshdi.com
                </a>
            </div>
        </div>

        {{-- Animated Background Elements --}}
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-red-100 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-orange-100 rounded-full mix-blend-multiply filter blur-xl opacity-30 animate-pulse animation-delay-2000"></div>
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
