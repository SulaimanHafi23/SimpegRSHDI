{{-- filepath: resources/views/components/forms/file-upload.blade.php --}}
@props([
    'name',
    'label' => null,
    'accept' => null,
    'required' => false,
    'preview' => false,
])

<div class="mb-4">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-semibold text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <div class="flex items-center justify-center w-full">
        <label for="{{ $name }}" class="flex flex-col items-center justify-center w-full h-32 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition duration-200">
            <div class="flex flex-col items-center justify-center pt-5 pb-6" id="{{ $name }}_preview_container">
                <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-3"></i>
                <p class="mb-2 text-sm text-gray-500">
                    <span class="font-semibold">Click to upload</span> or drag and drop
                </p>
                <p class="text-xs text-gray-500">{{ $accept ?? 'All files allowed' }}</p>
            </div>
            <input
                id="{{ $name }}"
                name="{{ $name }}"
                type="file"
                class="hidden"
                accept="{{ $accept }}"
                {{ $required ? 'required' : '' }}
                @if($preview)
                    onchange="previewFile(this, '{{ $name }}_preview_container')"
                @endif
            />
        </label>
    </div>

    @error($name)
        <p class="mt-1 text-sm text-red-600">
            <i class="fas fa-exclamation-circle"></i> {{ $message }}
        </p>
    @enderror
</div>

@if($preview)
@push('scripts')
<script>
function previewFile(input, containerId) {
    const container = document.getElementById(containerId);
    const file = input.files[0];

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            container.innerHTML = `
                <img src="${e.target.result}" class="max-h-24 rounded-lg" alt="Preview">
                <p class="text-xs text-gray-600 mt-2">${file.name}</p>
            `;
        };
        reader.readAsDataURL(file);
    }
}
</script>
@endpush
@endif
