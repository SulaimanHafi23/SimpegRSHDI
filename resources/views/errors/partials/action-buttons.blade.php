@php
    $containerClass = $containerClass ?? 'flex flex-col sm:flex-row gap-3 justify-center items-center';
    $primaryLabel = $primaryLabel ?? 'Kembali';
    $primaryIcon = $primaryIcon ?? 'fas fa-arrow-left';
    $primaryOnclick = $primaryOnclick ?? 'window.history.back()';
    $authLabel = $authLabel ?? 'Ke Dashboard';
    $guestLabel = $guestLabel ?? 'Login';
@endphp

<div class="{{ $containerClass }}">
    <x-button
        variant="primary"
        icon="{{ $primaryIcon }}"
        class="w-full sm:w-auto sm:min-w-[190px]"
        onclick="{{ $primaryOnclick }}">
        {{ $primaryLabel }}
    </x-button>

    @auth
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
        <x-button
            variant="secondary"
            icon="fas fa-home"
            class="w-full sm:w-auto sm:min-w-[190px]"
            onclick="window.location.href='{{ $dashboardUrl }}'">
            {{ $authLabel }}
        </x-button>
    @else
        <x-button
            variant="secondary"
            icon="fas fa-sign-in-alt"
            class="w-full sm:w-auto sm:min-w-[190px]"
            onclick="window.location.href='{{ route('login') }}'">
            {{ $guestLabel }}
        </x-button>
    @endauth
</div>
