{{-- filepath: resources/views/components/ui/loading.blade.php --}}
@props(['size' => 'md']) {{-- sm, md, lg --}}

@php
$sizes = [
    'sm' => 'h-4 w-4',
    'md' => 'h-8 w-8',
    'lg' => 'h-12 w-12',
];
@endphp

<div class="flex justify-center items-center">
    <div class="{{ $sizes[$size] }} border-4 border-primary-200 border-t-primary-600 rounded-full animate-spin"></div>
</div>
