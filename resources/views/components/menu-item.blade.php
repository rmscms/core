{{--
    Simple Menu Item Component
    
    Usage:
    <x-cms::admin.components.menu-item 
        title="Dashboard"
        url="/admin"
        icon="ph-house"
        :badge="5"
        description="Main dashboard"
        :routes="['admin.dashboard', 'admin.index']"
    />
--}}

@php
    // Determine if this menu item is active
    $isActive = false;
    
    // Check by URL match
    if (isset($url) && request()->is(ltrim($url, '/'))) {
        $isActive = true;
    }
    
    // Check by route names if provided
    if (isset($routes) && is_array($routes)) {
        foreach ($routes as $route) {
            if (request()->routeIs($route)) {
                $isActive = true;
                break;
            }
        }
    }
    
    // Check by URL pattern if provided
    if (isset($urlPattern)) {
        if (request()->is($urlPattern)) {
            $isActive = true;
        }
    }
    
    // Default values
    $url = $url ?? '#';
    $title = $title ?? 'Menu Item';
    $icon = $icon ?? 'ph-circle';
    $iconColor = $iconColor ?? null;
    $badge = $badge ?? null;
    $badgeColor = $badgeColor ?? 'bg-primary';
    $description = $description ?? null;
@endphp

<li class="nav-item">
    <a href="{{ $url }}" class="nav-link {{ $isActive ? 'active' : '' }}">
        @if($icon)
            <i class="{{ $icon }} {{ $iconColor ? 'text-' . $iconColor : '' }}"></i>
        @endif
        <span>
            {{ $title }}
            @if($description)
                <span class="d-block fw-normal opacity-50">{{ $description }}</span>
            @endif
        </span>
        @if($badge)
            <span class="badge {{ $badgeColor }} align-self-center rounded-pill ms-auto">{{ $badge }}</span>
        @endif
    </a>
</li>