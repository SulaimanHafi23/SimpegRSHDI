@props([
    'active' => false,
    'activeLabel' => 'Aktif',
    'inactiveLabel' => 'Tidak Aktif',
    'size' => 'sm',
])

@php
    $sizeClass = $size === 'xs' ? 'px-2 py-0.5 text-xs' : 'px-2.5 py-1 text-xs';
    $stateClass = $active
        ? 'bg-green-100 text-green-800'
        : 'bg-red-100 text-red-800';
    $iconClass = $active ? 'fas fa-check-circle' : 'fas fa-times-circle';
    $label = $active ? $activeLabel : $inactiveLabel;
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center font-medium rounded-full ' . $sizeClass . ' ' . $stateClass]) }}>
    <i class="{{ $iconClass }} mr-1"></i>
    {{ $label }}
</span>
