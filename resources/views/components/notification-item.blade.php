@props([
    'image' => null,
    'icon' => null,
    'iconBg' => 'bg-primary',
    'content' => '',
    'actions' => null,
    'time' => '',
])

<div class="d-flex align-items-start mb-3">
    <div class="me-3">
        @if($image)
            <a href="#" class="status-indicator-container">
                {!! $image !!}
                @if(str_contains($iconBg, 'bg-'))
                    <span class="status-indicator {{ $iconBg }}"></span>
                @endif
            </a>
        @elseif($icon)
            <div class="{{ $iconBg }} rounded-pill">
                <i class="{{ $icon }} p-2"></i>
            </div>
        @endif
    </div>
    
    <div class="flex-fill">
        <div class="notification-content">
            {!! $content !!}
        </div>
        
        @if($actions)
            <div class="my-2">
                {!! $actions !!}
            </div>
        @endif
        
        @if($time)
            <div class="fs-sm text-muted mt-1">{!! $time !!}</div>
        @endif
    </div>
</div>