{{-- filepath: resources/views/components/status-badge.blade.php --}}
@props(['status'])

@php
$statusConfig = [
    'pending' => [
        'color' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'icon' => 'fas fa-clock',
        'label' => 'Pending'
    ],
    'approved' => [
        'color' => 'bg-green-100 text-green-800 border-green-200',
        'icon' => 'fas fa-check-circle',
        'label' => 'Approved'
    ],
    'rejected' => [
        'color' => 'bg-red-100 text-red-800 border-red-200',
        'icon' => 'fas fa-times-circle',
        'label' => 'Rejected'
    ],
    'verified' => [
        'color' => 'bg-blue-100 text-blue-800 border-blue-200',
        'icon' => 'fas fa-check-double',
        'label' => 'Verified'
    ],
];

$config = $statusConfig[strtolower($status)] ?? [
    'color' => 'bg-gray-100 text-gray-800 border-gray-200',
    'icon' => 'fas fa-circle',
    'label' => ucfirst($status)
];
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold border {{ $config['color'] }}">
    <i class="{{ $config['icon'] }} mr-1.5"></i>
    {{ $config['label'] }}
</span>
