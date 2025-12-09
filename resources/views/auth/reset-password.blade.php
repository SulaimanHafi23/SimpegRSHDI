{{-- filepath: resources/views/auth/reset-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'Reset Password')

@section('content')
<div class="space-y-6">
    <!-- Icon Header -->
    <div class="text-center">
        <div class="inline-flex items-center justify-center h-16 w-16 bg-green-100 rounded-full mb-4">
            <i class="fas fa-shield-alt text-green-600 text-2xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">
            Reset Your Password
        </h3>
        <p class="mt-2 text-sm text-gray-600">
            Choose a strong password for your account
        </p>
    </div>

    <!-- Form -->
    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" x-data="{ loading: false, show: false, showConfirm: false }" @submit="loading = true">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <!-- Email (Read-only) -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                Email Address
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400"></i>
                </div>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email', $email ?? '') }}"
                    readonly
                    class="input-field pl-11 bg-gray-50 cursor-not-allowed"
                >
            </div>
        </div>

        <!-- New Password -->
        <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                New Password
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400 group-focus-within:text-primary-600 transition duration-200"></i>
                </div>
                <input
                    :type="show ? 'text' : 'password'"
                    name="password"
                    id="password"
                    placeholder="••••••••"
                    required
                    autofocus
                    class="input-field pl-11 pr-11 {{ $errors->has('password') ? 'input-error' : '' }}"
                >
                <button
                    type="button"
                    @click="show = !show"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <i :class="show ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
            </div>
            <p class="mt-1.5 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Must be at least 8 characters with letters, numbers & symbols
            </p>
            @error('password')
                <p class="mt-1.5 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                Confirm New Password
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-lock text-gray-400 group-focus-within:text-primary-600 transition duration-200"></i>
                </div>
                <input
                    :type="showConfirm ? 'text' : 'password'"
                    name="password_confirmation"
                    id="password_confirmation"
                    placeholder="••••••••"
                    required
                    class="input-field pl-11 pr-11"
                >
                <button
                    type="button"
                    @click="showConfirm = !showConfirm"
                    class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-gray-600"
                >
                    <i :class="showConfirm ? 'fas fa-eye-slash' : 'fas fa-eye'"></i>
                </button>
            </div>
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary"
        >
            <span x-show="!loading" class="flex items-center justify-center">
                <i class="fas fa-check-circle mr-2"></i>
                Reset Password
            </span>
            <span x-show="loading" class="flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Resetting...
            </span>
        </button>
    </form>

    <!-- Back to Login -->
    <div class="text-center">
        <a href="{{ route('login') }}" class="inline-flex items-center text-sm text-primary-600 hover:text-primary-700 font-medium transition duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Back to Login
        </a>
    </div>
</div>
@endsection
