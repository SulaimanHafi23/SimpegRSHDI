@extends('layouts.admin')

@section('title', 'Page Not Found')

@section('content')
<x-errors.page-shell>
            {{-- Error Icon --}}
            <div class="mb-8">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-[#fff4d9] border border-[#f5a623]/25 rounded-full">
                    <i class="fas fa-search text-5xl text-[#b45309]"></i>
                </div>
            </div>

            {{-- Error Code --}}
            <h1 class="text-8xl font-bold text-[#0a3d1f] mb-4">404</h1>

            {{-- Error Title --}}
            <h2 class="text-3xl font-bold text-[#155a2e] mb-4">
                Halaman Tidak Ditemukan
            </h2>

            {{-- Error Message --}}
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto leading-relaxed">
                Maaf, halaman yang Anda cari tidak dapat ditemukan.
                Halaman mungkin telah dipindahkan atau tidak pernah ada.
            </p>

            {{-- Action Buttons --}}
            @include('errors.partials.action-buttons', [
                'primaryLabel' => 'Kembali',
                'primaryIcon' => 'fas fa-arrow-left',
                'primaryOnclick' => 'window.history.back()'
            ])

            {{-- Popular Links --}}
            @auth
            <div class="mt-12 pt-8 border-t border-gray-200">
                <p class="text-sm text-gray-500 mb-4">
                    Mungkin Anda mencari:
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    @php
                        $dashboardUrl = '/';
                        if (auth()->user()->can('dashboard.employee')) {
                            $dashboardUrl = route('employee.dashboard');
                        } elseif (auth()->user()->can('dashboard.hr')) {
                            $dashboardUrl = route('hr.dashboard');
                        } elseif (auth()->user()->can('dashboard.manager')) {
                            $dashboardUrl = route('manager.dashboard');
                        } elseif (auth()->user()->can('dashboard.admin')) {
                            $dashboardUrl = route('admin.dashboard');
                        }
                    @endphp
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

            @include('errors.partials.quick-links', ['showHome' => true])
</x-errors.page-shell>
@endsection
