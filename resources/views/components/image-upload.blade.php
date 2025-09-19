{{--
    RMS Image Upload Component
    
    Professional image upload component with preview
    
    @param string $name Field name
    @param string $label Field label 
    @param string|null $value Current value
    @param string $accept Accepted file types (default: .jpg,.jpeg,.png,.gif,.webp)
    @param string $maxSize Max file size (default: 2MB)
    @param bool $multiple Allow multiple files (default: false)
    @param bool $preview Show preview (default: true)
    @param bool $dragDrop Enable drag & drop (default: true)
    @param bool $required Field is required (default: false)
    @param string|null $hint Help text
    @param array $resize Resize options
    @param array $thumbnail Thumbnail options
    
    @author RMS Core Team
    @version 1.0.0
--}}

@props([
    'name' => 'image',
    'label' => 'تصویر',
    'value' => null,
    'accept' => '.jpg,.jpeg,.png,.gif,.webp',
    'maxSize' => '2MB',
    'multiple' => false,
    'preview' => true,
    'dragDrop' => true,
    'required' => false,
    'hint' => null,
    'resize' => ['width' => 800, 'height' => 600],
    'thumbnail' => ['width' => 150, 'height' => 150]
])

@php
    // Build attributes
    $inputAttributes = [
        'type' => 'file',
        'name' => $name,
        'accept' => $accept,
        'data-max-size' => $maxSize,
        'data-preview' => $preview ? 'true' : 'false',
        'data-drag-drop' => $dragDrop ? 'true' : 'false',
        'data-resize' => json_encode($resize),
        'data-thumbnail' => json_encode($thumbnail)
    ];
    
    if ($multiple) {
        $inputAttributes['multiple'] = true;
    }
    
    if ($required) {
        $inputAttributes['required'] = true;
    }
    
    $fieldId = 'image_' . str_replace(['[', ']'], ['_', ''], $name);
@endphp

@push('css')
    <link rel="stylesheet" href="{{ asset('admin/css/image-uploader.css') }}">
@endpush

@push('js')
    <script src="{{ asset('admin/js/image-uploader.js') }}"></script>
@endpush

<div class="mb-3">
    {{-- Label --}}
    @if($label)
        <label for="{{ $fieldId }}" class="form-label">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif
    
    {{-- Image Upload Container --}}
    <div class="image-uploader">
        <input 
            id="{{ $fieldId }}"
            class="form-control @error($name) is-invalid @enderror"
            @foreach($inputAttributes as $attr => $attrValue)
                @if(is_bool($attrValue))
                    {{ $attr }}
                @else
                    {{ $attr }}="{{ $attrValue }}"
                @endif
            @endforeach
        >
        
        {{-- Show current image if editing --}}
        @if($value)
            <div class="current-image mt-2">
                <div class="d-flex align-items-center p-3 border rounded bg-light">
                    <div class="current-thumbnail me-3">
                        <img src="{{ Storage::disk('public')->url($value) }}" 
                             class="rounded border" 
                             style="width: {{ $thumbnail['width'] }}px; height: {{ $thumbnail['height'] }}px; object-fit: cover;"
                             alt="تصویر فعلی">
                    </div>
                    <div class="current-info flex-grow-1">
                        <div class="fw-bold text-success">
                            <i class="ph-check-circle me-1"></i>
                            تصویر فعلی
                        </div>
                        <div class="text-muted small">{{ basename($value) }}</div>
                    </div>
                    <div class="current-actions">
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove-current">
                            <i class="ph-trash me-1"></i>
                            حذف
                        </button>
                        <input type="hidden" name="{{ $name }}_remove" value="0" class="remove-current-input">
                    </div>
                </div>
            </div>
        @endif
    </div>
    
    {{-- Hint Text --}}
    @if($hint)
        <div class="form-text">{{ $hint }}</div>
    @endif
    
    {{-- Validation Error --}}
    @error($name)
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle remove current image
    const removeCurrentBtns = document.querySelectorAll('.btn-remove-current');
    removeCurrentBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const currentImageDiv = this.closest('.current-image');
            const removeInput = currentImageDiv.querySelector('.remove-current-input');
            
            // Mark for removal
            removeInput.value = '1';
            
            // Hide current image
            currentImageDiv.style.display = 'none';
            
            // Show removed message
            const removedMsg = document.createElement('div');
            removedMsg.className = 'alert alert-warning alert-dismissible fade show mt-2';
            removedMsg.innerHTML = `
                <i class="ph-info-circle me-2"></i>
                تصویر فعلی حذف خواهد شد. برای لغو، صفحه را رفرش کنید.
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            currentImageDiv.parentNode.insertBefore(removedMsg, currentImageDiv.nextSibling);
        });
    });
});
</script>
@endpush