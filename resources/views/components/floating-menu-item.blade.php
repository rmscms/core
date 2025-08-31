@props([
    'href' => '#',
    'image' => null,
    'label' => '',
])

<div class="floating-item">
    <div class="floating-icon bg-success d-flex align-items-center justify-content-center">
        <a href="{{ $href }}">
            {!! $image !!}
        </a>
    </div>
    <span class="floating-label">{{ $label }}</span>
</div>
