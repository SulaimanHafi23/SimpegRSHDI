{{-- filepath: resources/views/auth/forgot-password.blade.php --}}
@extends('layouts.guest')

@section('title', 'Forgot Password')

@section('content')
<div class="space-y-6">
    <!-- Icon Header -->
    <div class="text-center">
        <div class="inline-flex items-center justify-center h-16 w-16 bg-primary-100 rounded-full mb-4">
            <i class="fas fa-key text-primary-600 text-2xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-gray-900">
            Forgot Password?
        </h3>
        <p class="mt-2 text-sm text-gray-600">
            No worries! Enter your email and we'll send you reset instructions.
        </p>
    </div>

    <!-- Success Message -->
    @if (session('status'))
        <div class="p-4 bg-green-50 border-l-4 border-green-500 rounded-lg animate-fade-in">
            <div class="flex items-center">
                <i class="fas fa-check-circle text-green-500 text-lg mr-3"></i>
                <p class="text-sm text-green-800 font-medium">{{ session('status') }}</p>
            </div>
        </div>
    @endif

    <!-- Form -->
    <form method="POST" action="{{ route('password.email') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        <!-- Email Input -->
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                Email Address
            </label>
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i class="fas fa-envelope text-gray-400 group-focus-within:text-primary-600 transition duration-200"></i>
                </div>
                <input
                    type="email"
                    name="email"
                    id="email"
                    value="{{ old('email') }}"
                    placeholder="you@example.com"
                    required
                    autofocus
                    class="input-field pl-11 {{ $errors->has('email') ? 'input-error' : '' }}"
                >
            </div>
            @error('email')
                <p class="mt-1.5 text-sm text-red-600 flex items-center">
                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Submit Button -->
        <button
            type="submit"
            :disabled="loading"
            class="w-full btn-primary"
        >
            <span x-show="!loading" class="flex items-center justify-center">
                <i class="fas fa-paper-plane mr-2"></i>
                Send Reset Link
            </span>
            <span x-show="loading" class="flex items-center justify-center">
                <i class="fas fa-spinner fa-spin mr-2"></i>
                Sending...
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
