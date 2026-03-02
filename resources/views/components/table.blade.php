@props([
    'headers' => [],
    'responsive' => true,
    'striped' => true,
    'hover' => true,
])

<div {{ $attributes->merge(['class' => $responsive ? 'overflow-x-auto' : '']) }}>
    <table class="min-w-[760px] md:min-w-full divide-y divide-gray-200">
        @if(isset($thead))
            <thead class="bg-gray-50">
                {{ $thead }}
            </thead>
        @elseif(!empty($headers))
            <thead class="bg-gray-50">
                <tr>
                    @foreach($headers as $header)
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ $header }}
                        </th>
                    @endforeach
                </tr>
            </thead>
        @endif
        
        <tbody class="{{ $striped ? 'bg-white divide-y divide-gray-200' : 'bg-white' }}">
            {{ $slot }}
        </tbody>
        
        @if(isset($tfoot))
            <tfoot class="bg-gray-50">
                {{ $tfoot }}
            </tfoot>
        @endif
    </table>
</div>
