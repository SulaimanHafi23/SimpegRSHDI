@props([
    'label' => null,
    'name',
    'accept' => null,
    'required' => false,
    'disabled' => false,
    'help' => null,
    'error' => null,
    'preview' => false,
    'currentFile' => null,
])

<div {{ $attributes->only('class') }}>
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    @if($currentFile && $preview)
        <div class="mb-2">
            @if(Str::contains($accept ?? '', 'image'))
                <img src="{{ $currentFile }}" alt="Current file" class="h-32 w-32 object-cover rounded-lg border">
            @else
                <a href="{{ $currentFile }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm">
                    <i class="fas fa-file mr-1"></i> Lihat file saat ini
                </a>
            @endif
        </div>
    @endif
    
    <input 
        type="file"
        name="{{ $name }}"
        id="{{ $name }}"
        accept="{{ $accept }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->except(['class', 'label', 'name', 'accept', 'required', 'disabled', 'help', 'error', 'preview', 'currentFile'])->merge([
            'class' => 'block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 ' .
                      ($error || $errors->has($name) ? 'border-red-500' : '') .
                      ($disabled ? ' opacity-50 cursor-not-allowed' : '')
        ]) }}
        @if($preview) onchange="previewFile(this, '{{ $name }}_preview')" @endif
    >
    
    @if($preview)
        <div id="{{ $name }}_preview" class="mt-2 hidden">
            <img src="" alt="Preview" class="h-32 w-32 object-cover rounded-lg border">
        </div>
    @endif
    
    @if($help)
        <p class="mt-1 text-sm text-gray-500">{{ $help }}</p>
    @endif
    
    @if($error)
        <p class="mt-1 text-sm text-red-600">{{ $error }}</p>
    @elseif($errors->has($name))
        <p class="mt-1 text-sm text-red-600">{{ $errors->first($name) }}</p>
    @endif
</div>

@once
@push('scripts')
<script>
function previewFile(input, previewId) {
    const preview = document.getElementById(previewId);
    const file = input.files[0];
    
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.querySelector('img').src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
    }
}
</script>
@endpush
@endonce
