@props([
    'href' => '#',
    'icon' => null,
    'image' => null,
    'label' => '',
    'subItems' => null,
    'badge' => null,
    'active' => false,
    'disabled' => false,
])

<li class="nav-item {{ $subItems ? 'nav-item-submenu' : '' }}">
    <a href="{{ $href }}" class="nav-link {{ $active ? 'active' : '' }} {{ $disabled ? 'disabled' : '' }}">
        @if($icon)
            <i class="{{ $icon }}"></i>
        @endif
        @if($image)
            {!! $image !!}
        @endif
        <span>
            {{ $label }}
            @if($badge)
                <span class="badge bg-primary align-self-center rounded-pill ms-auto">{{ $badge }}</span>
            @endif
            @if(!$subItems && $slot->isNotEmpty())
                <span class="d-block fw-normal opacity-50">{{ $slot }}</span>
            @endif
        </span>
    </a>
    @if($subItems)
        <ul class="nav-group-sub collapse">
            {{ $subItems }}
        </ul>
    @endif
</li>
