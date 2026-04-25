{{-- filepath: resources/views/components/ui/table.blade.php --}}
@props(['headers' => []])

<div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <div data-responsive-table class="space-y-4">
        <div data-responsive-table-mobile class="hidden md:hidden space-y-3"></div>

        <table data-responsive-table-table class="min-w-[760px] md:min-w-full divide-y divide-gray-200">
            @if(!empty($headers))
                <thead class="bg-gray-50">
                    <tr>
                        @foreach($headers as $header)
                            <th scope="col" class="px-6 py-3 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                {{ $header }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
            @endif
            <tbody class="bg-white divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

@include('components.table-responsive-script')
