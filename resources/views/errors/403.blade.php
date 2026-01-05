@extends('layouts.guest')

@section('title', '403 - Akses Ditolak')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-2xl w-full bg-white rounded-lg shadow p-8 text-center">
        <div class="text-6xl text-red-500 mb-4">
            <i class="fas fa-lock"></i>
        </div>
        <h1 class="text-2xl font-bold mb-2">Akses Ditolak (403)</h1>
        <p class="text-gray-600 mb-4">Anda tidak memiliki otorisasi untuk melihat halaman ini.</p>

        @if(request()->is('approvals/*') || request()->is('approvals'))
            <p class="mb-4">Jika Anda berniat melihat pengajuan Anda sendiri, kunjungi halaman Perjalanan Dinas pribadi Anda.</p>
            <a href="{{ route('employee.business-trips.index') }}" class="px-4 py-2 bg-green-600 text-white rounded-lg">Perjalanan Dinas Saya</a>
        @else
            <a href="{{ url()->previous() ?? route('admin.dashboard') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg">Kembali</a>
        @endif
    </div>
</div>
@endsection