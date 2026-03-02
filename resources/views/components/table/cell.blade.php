@props([
    'header' => false,
])

@if($header)
    <th {{ $attributes->merge(['class' => 'px-3 sm:px-6 py-2.5 sm:py-3 text-left text-[11px] sm:text-xs font-medium text-gray-500 uppercase tracking-wider']) }}>
        {{ $slot }}
    </th>
@else
    <td {{ $attributes->merge(['class' => 'px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-900']) }}>
        {{ $slot }}
    </td>
@endif
