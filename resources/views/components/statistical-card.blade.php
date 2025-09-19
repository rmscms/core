{{--
    RMS Statistical Card Component
    
    کامپوننت کارت آماری قابل استفاده مجدد
    سازگار با قالب Limitless و پشتیبانی کامل از Dark Theme + RTL
    
    @param string $title - عنوان کارت (مثل "مجموع هزینه‌ها")
    @param string $value - مقدار عددی (مثل "۹۷,۲۰۰,۰۰۰")
    @param string $unit - واحد (مثل "تومان")
    @param string $icon - نام آیکون PhosphorIcons (مثل "currency-dollar")
    @param string $color - رنگ کارت (danger, success, warning, info, primary, secondary)
    @param string $colSize - سایز ستون Bootstrap (مثل "col-xl-4 col-md-6")
    @param string $description - توضیح اضافی (اختیاری)
    @param bool $showBorder - نمایش border (پیش‌فرض true)
    
    مثال استفاده:
    @component('cms::components.statistical-card', [
        'title' => 'مجموع هزینه‌ها',
        'value' => '۹۷,۲۰۰,۰۰۰',
        'unit' => 'تومان',
        'icon' => 'currency-dollar',
        'color' => 'danger',
        'colSize' => 'col-xl-4 col-md-6'
    ])
    @endcomponent
--}}

@php
    // Support both StatCard objects and array props
    if (isset($statCard) && is_object($statCard)) {
        // StatCard object passed
        $cardData = [
            'title' => $statCard->title,
            'value' => $statCard->value,
            'unit' => $statCard->unit,
            'icon' => $statCard->icon,
            'color' => $statCard->color,
            'colSize' => $statCard->colSize,
            'description' => $statCard->description,
            'showBorder' => $statCard->showBorder,
        ];
    } else {
        // Array props (backward compatibility)
        $cardData = [
            'title' => $title ?? 'عنوان کارت',
            'value' => $value ?? '0',
            'unit' => $unit ?? '',
            'icon' => $icon ?? 'chart-bar',
            'color' => $color ?? 'primary',
            'colSize' => $colSize ?? 'col-xl-4 col-md-6',
            'description' => $description ?? null,
            'showBorder' => $showBorder ?? true,
        ];
    }
@endphp

<div class="{{ $cardData['colSize'] }}">
    <div class="card bg-{{ $cardData['color'] }} bg-opacity-10 {{ $cardData['showBorder'] ? 'border-' . $cardData['color'] : '' }}">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <!-- آیکون -->
                <div class="flex-shrink-0">
                    <div class="bg-{{ $cardData['color'] }} bg-opacity-10 text-{{ $cardData['color'] }} lh-1 rounded-pill p-3">
                        <i class="ph-{{ $cardData['icon'] }} ph-2x"></i>
                    </div>
                </div>
                
                <!-- محتوای کارت -->
                <div class="flex-grow-1 ms-3">
                    <!-- مقدار عددی -->
                    <h4 class="mb-1 fw-bold text-{{ $cardData['color'] }}">
                        {{ $cardData['value'] }}{{ $cardData['unit'] ? ' ' . $cardData['unit'] : '' }}
                    </h4>
                    
                    <!-- عنوان -->
                    <p class="mb-0 text-muted">{{ $cardData['title'] }}</p>
                    
                    <!-- توضیح اضافی (اختیاری) -->
                    @if($cardData['description'])
                        <small class="text-muted">{{ $cardData['description'] }}</small>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
