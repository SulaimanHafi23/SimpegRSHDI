@php
    $containerClass = $containerClass ?? 'mt-8 flex flex-wrap items-center justify-center gap-3 text-sm';
    $showHelp = $showHelp ?? true;
    $showPrivacy = $showPrivacy ?? true;
    $showTerms = $showTerms ?? true;
    $showHome = $showHome ?? false;
    $showLogin = $showLogin ?? false;
@endphp

<div class="{{ $containerClass }}">
    @if($showHelp)
        <a href="{{ route('public.help') }}" class="px-4 py-2 rounded-xl bg-amber-50 text-amber-700 hover:bg-amber-100 transition">
            <i class="fas fa-life-ring mr-1"></i> Help Center
        </a>
    @endif

    @if($showPrivacy)
        <a href="{{ route('public.privacy') }}" class="px-4 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">
            <i class="fas fa-shield-halved mr-1"></i> Privacy
        </a>
    @endif

    @if($showTerms)
        <a href="{{ route('public.terms') }}" class="px-4 py-2 rounded-xl bg-sky-50 text-sky-700 hover:bg-sky-100 transition">
            <i class="fas fa-file-contract mr-1"></i> Terms
        </a>
    @endif

    @if($showHome)
        <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl bg-green-50 text-green-700 hover:bg-green-100 transition">
            <i class="fas fa-house mr-1"></i> Beranda
        </a>
    @endif

    @if($showLogin)
        <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 transition">
            <i class="fas fa-right-to-bracket mr-1"></i> Login Ulang
        </a>
    @endif
</div>
