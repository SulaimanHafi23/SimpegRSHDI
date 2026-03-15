@props([
    'maxWidth' => 'max-w-3xl',
    'contentClass' => 'text-center rounded-3xl border border-[#f5a623]/20 bg-white/95 shadow-2xl p-8 md:p-10 relative overflow-hidden',
])

<div class="min-h-[72vh] flex items-center justify-center px-4 py-8 bg-linear-to-br from-[#f6f9f7] via-[#edf7ef] to-[#fff9ee] rounded-3xl">
    <div class="{{ $maxWidth }} w-full relative">
        <div class="absolute -top-16 -right-16 h-44 w-44 rounded-full bg-[#f5a623]/15 blur-3xl"></div>
        <div class="absolute -bottom-16 -left-16 h-44 w-44 rounded-full bg-[#28a04f]/15 blur-3xl"></div>

        <div class="{{ $contentClass }}">
            {{ $slot }}
        </div>

        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-[#f5a623]/20 rounded-full mix-blend-multiply filter blur-xl opacity-40 animate-pulse"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-[#28a04f]/20 rounded-full mix-blend-multiply filter blur-xl opacity-40 animate-pulse" style="animation-delay: 2s;"></div>
        </div>
    </div>
</div>
