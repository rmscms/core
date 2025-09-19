{{--
    Submenu Item Component
    
    Usage:
    <x-cms::admin.components.submenu-item 
        title="Settings"
        icon="ph-gear"
        :badge="3"
        :children="[
            [
                'title' => 'General Settings',
                'url' => '/admin/settings',
                'routes' => ['admin.settings.index'],
            ],
            [
                'title' => 'Admins',
                'url' => '/admin/admins',
                'routes' => ['admin.admins.*'],
                'icon' => 'ph-users-three',
                'iconColor' => 'danger',
                'badge' => \$totalAdmins ?? null,
            ],
            ['divider' => true],
            [
                'title' => 'Cache Management',
                'url' => '/admin/settings/cache',
            ]
        ]"
    />
--}}

@php
    // Check if any child is active to determine parent state
    $hasActiveChild = false;
    $activeChildUrl = '';
    
    if (isset($children) && is_array($children)) {
        foreach ($children as $child) {
            // Skip dividers
            if (isset($child['divider']) && $child['divider']) {
                continue;
            }
            
            $childIsActive = false;
            
            // Check by URL match
            if (isset($child['url']) && request()->is(ltrim($child['url'], '/') . '*')) {
                $childIsActive = true;
            }
            
            // Check by route names if provided
            if (isset($child['routes']) && is_array($child['routes'])) {
                foreach ($child['routes'] as $route) {
                    if (request()->routeIs($route)) {
                        $childIsActive = true;
                        break;
                    }
                }
            }
            
            // Check by URL pattern if provided
            if (isset($child['urlPattern'])) {
                if (request()->is($child['urlPattern'])) {
                    $childIsActive = true;
                }
            }
            
            if ($childIsActive) {
                $hasActiveChild = true;
                $activeChildUrl = $child['url'] ?? '';
                break;
            }
        }
    }
    
    // Default values
    $title = $title ?? 'Menu Group';
    $icon = $icon ?? 'ph-folder';
    $iconColor = $iconColor ?? null;
    $badge = $badge ?? null;
    $badgeColor = $badgeColor ?? 'bg-primary';
    $children = $children ?? [];
@endphp

<li class="nav-item nav-item-submenu {{ $hasActiveChild ? 'nav-item-expanded nav-item-open' : '' }}">
    <a href="#" class="nav-link {{ $hasActiveChild ? 'active' : '' }}">
        @if($icon)
            <i class="{{ $icon }} {{ $iconColor ? 'text-' . $iconColor : '' }}"></i>
        @endif
        <span>{{ $title }}</span>
        @if($badge)
            <span class="badge {{ $badgeColor }} align-self-center rounded-pill ms-auto">{{ $badge }}</span>
        @endif
    </a>
    
    <ul class="nav-group-sub collapse {{ $hasActiveChild ? 'show' : '' }}">
        @foreach($children as $child)
            @if(isset($child['divider']) && $child['divider'])
                <li class="nav-item-divider"></li>
            @else
                @php
                    // Determine if this child is active
                    $childIsActive = false;
                    
                    // Check by URL match
                    if (isset($child['url']) && request()->is(ltrim($child['url'], '/') . '*')) {
                        $childIsActive = true;
                    }
                    
                    // Check by route names if provided
                    if (isset($child['routes']) && is_array($child['routes'])) {
                        foreach ($child['routes'] as $route) {
                            if (request()->routeIs($route)) {
                                $childIsActive = true;
                                break;
                            }
                        }
                    }
                    
                    // Check by URL pattern if provided
                    if (isset($child['urlPattern'])) {
                        if (request()->is($child['urlPattern'])) {
                            $childIsActive = true;
                        }
                    }
                    
                    // Child defaults
                    $childUrl = $child['url'] ?? '#';
                    $childTitle = $child['title'] ?? 'Child Item';
                    $childIcon = $child['icon'] ?? null;
                    $childIconColor = $child['iconColor'] ?? null;
                    $childBadge = $child['badge'] ?? null;
                    $childBadgeColor = $child['badgeColor'] ?? 'text-muted';
                    $childDescription = $child['description'] ?? null;
                @endphp
                
                <li class="nav-item">
                    <a href="{{ $childUrl }}" class="nav-link {{ $childIsActive ? 'active' : '' }}">
                        @if($childIcon)
                            <i class="{{ $childIcon }} {{ $childIconColor ? 'text-' . $childIconColor : '' }}"></i>
                        @endif
                        <span>
                            {{ $childTitle }}
                            @if($childDescription)
                                <span class="d-block fw-normal opacity-50">{{ $childDescription }}</span>
                            @endif
                        </span>
                        @if($childBadge)
                            <span class="{{ $childBadgeColor }} ms-auto">{{ $childBadge }}</span>
                        @endif
                    </a>
                </li>
            @endif
        @endforeach
    </ul>
</li>