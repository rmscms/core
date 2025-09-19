{{--
    Statistics Section for List Template
    
    نمایش کارت‌های آماری برای لیست‌هایی که از HasStats استفاده می‌کنند
    
    Variables:
    - $listData: داده‌های لیست شامل meta و stats
    
    Dependencies:
    - statistical-card کامپوننت
    - HasStats interface
--}}

@php
    $controllerInstance = $listData['meta']['controller'] ?? null;
    $supportsStats = $controllerInstance && $controllerInstance instanceof \RMS\Core\Contracts\Stats\HasStats;
    $statsData = $listData['meta']['stats'] ?? null;
    $hasActiveFilters = $listData['has_filters'] ?? false;
    
    // تعیین وضعیت پیش‌فرض کارت آمار (باز یا بسته)
    $statsExpanded = true; // پیش‌فرض: باز
    
    // اگر controller متد getStatsCardExpanded دارد، از آن استفاده کن
    if ($controllerInstance && method_exists($controllerInstance, 'getStatsCardExpanded')) {
        $statsExpanded = $controllerInstance->getStatsCardExpanded();
    }
    // یا اگر filter فعال باشد، خودکار باز باشد
    elseif ($hasActiveFilters) {
        $statsExpanded = true; // با فیلتر فعال، همیشه باز
    }
@endphp

@if($supportsStats && $statsData && !empty($statsData))
<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between">
        <h6 class="mb-0">
            <a class="text-body" data-card-action="collapse">
                <i class="ph-chart-bar me-2"></i>
                آمار کلی
                @if($hasActiveFilters)
                    <span class="badge bg-info bg-opacity-20 text-info ms-2">
                        <i class="ph-funnel me-1"></i>
                        فیلتر شده
                    </span>
                @endif
            </a>
        </h6>
        <div>
            <a class="text-body mx-2" data-card-action="reload" title="بروزرسانی آمار">
                <i class="ph-arrows-clockwise"></i>
            </a>
            <a class="text-body" data-card-action="collapse">
                <i class="ph-caret-down"></i>
            </a>
        </div>
    </div>
    <div class="collapse{{ $statsExpanded ? ' show' : '' }}" id="collapsible_card_stats">
        <div class="card-body">
        <div class="row g-3">
            @foreach($statsData as $statCard)
                @if(is_object($statCard))
                    {{-- StatCard object --}}
                    @component('cms::components.statistical-card', ['statCard' => $statCard])
                    @endcomponent
                @else
                    {{-- Legacy array format --}}
                    @component('cms::components.statistical-card', $statCard)
                    @endcomponent
                @endif
            @endforeach
        </div>
        
        {{-- اگر فیلتر فعال باشد، نکته‌ای در مورد آمار نمایش بده --}}
        @if($hasActiveFilters)
            <div class="row mt-3">
                <div class="col-12">
                    <div class="alert alert-info bg-info bg-opacity-10 border-info d-flex align-items-center">
                        <i class="ph-info me-2"></i>
                        <small class="mb-0 text-info">
                            آمار بالا بر اساس فیلترهای فعال محاسبه شده است.
                            <a href="{{ request()->url() }}" class="alert-link">نمایش آمار کل</a>
                        </small>
                    </div>
                </div>
            </div>
        @endif
        </div>
    </div>
</div>
@endif
