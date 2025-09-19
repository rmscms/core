{{--
    Menu Header Component
    
    Usage:
    <x-cms::admin.components.menu-header title="Main Navigation" />
--}}

@php
    $title = $title ?? 'Section';
@endphp

<li class="nav-item-header {{ $loop->first ?? false ? 'pt-0' : '' }}">
    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ $title }}</div>
    <i class="ph-dots-three sidebar-resize-show"></i>
</li>