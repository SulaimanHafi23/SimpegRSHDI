@props([
    'headers' => [],
    'responsive' => true,
    'striped' => true,
    'hover' => true,
])

<div {{ $attributes->merge(['class' => $responsive ? 'overflow-x-auto' : '']) }}>
    <div data-responsive-table class="space-y-4">
        <div data-responsive-table-mobile class="hidden md:hidden space-y-3"></div>

        <table data-responsive-table-table class="w-full min-w-[760px] md:min-w-0 divide-y divide-gray-200">
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
</div>

@include('components.table-responsive-script')
