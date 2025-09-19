{{--
    Form Statistics Section for Form Templates
    
    نمایش کارت‌های آماری برای فرم‌هایی که از HasFormStats استفاده می‌کنند
    
    Variables:
    - $form_stats: آرایه از StatCard objects
    - $is_edit_mode: آیا در حالت ویرایش هستیم
    - $model: مدل در حال ویرایش (در صورت وجود)
    
    Dependencies:
    - statistical-card کامپوننت
    - HasFormStats interface
--}}

@if($form_stats && !empty($form_stats) && $is_edit_mode)
<div class="card mb-3">
    <div class="card-header d-flex flex-wrap justify-content-between">
        <h6 class="mb-0">
            <a class="text-body" data-card-action="collapse">
                <i class="ph-chart-line me-2"></i>
                اطلاعات و آمار
                @if($model && method_exists($model, 'getKey'))
                    <span class="badge bg-primary bg-opacity-20 text-primary ms-2">
                        <i class="ph-hash me-1"></i>
                        ID: {{ $model->getKey() }}
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
    <div class="card-body">
        <div class="row g-3">
            @foreach($form_stats as $statCard)
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
        
        {{-- نکته برای کاربر --}}
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-info bg-info bg-opacity-10 border-info d-flex align-items-center">
                    <i class="ph-info me-2"></i>
                    <small class="mb-0 text-info">
                        آمار بالا مربوط به رکورد فعلی است و هنگام ذخیره تغییرات به‌روزرسانی خواهد شد.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endif