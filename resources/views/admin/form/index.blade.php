@extends('cms::admin.layout.index')
@section('content')

<div class="d-flex flex-column" style="min-height: 100vh;">

    {{-- Form Statistics Section --}}
    @include('cms::admin.form.form-stats')

    <!-- Dynamic Form Card -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ $title ?? 'فرم' }}</h5>
        </div>

        <div class="card-body">

            {{-- Display Validation Errors --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h6><i class="ph-warning-circle me-2"></i>خطاهای اعتبارسنجی:</h6>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Dynamic Form --}}
            <form action="{{ $form_url }}" method="POST"
                  class="{{ $form_config['class'] ?? 'form-horizontal' }}"
                  @if(isset($form_config['enctype'])) enctype="{{ $form_config['enctype'] }}" @endif
                  @if(isset($form_config['autocomplete'])) autocomplete="{{ $form_config['autocomplete'] }}" @endif>

                @csrf
                {{-- Auto-detect PUT method: if URL contains a number at the end, it's edit mode --}}
                @if(preg_match('/\/\d+$/', $form_url))
                    @method('PUT')
                @endif

                {{-- Dynamic Fields Generation --}}
                @foreach($fields as $field)
                    <div class="row mb-3">
                        <label class="col-form-label col-lg-3">
                            {{ $field->title }}
                            @if($field->required)
                                <span class="text-danger">*</span>
                            @endif
                        </label>
                        <div class="col-lg-9">

                            {{-- Generate Field Based on Type --}}
                            @switch($field->type)

                                {{-- STRING Input --}}
                                @case(\RMS\Core\Data\Field::STRING)
                                    <input type="text"
                                           name="{{ $field->key }}"
                                           class="form-control @error($field->key) is-invalid @enderror"
                                           placeholder="{{ $field->placeholder ?? '' }}"
                                           value="{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif
                                           @if($field->readonly) readonly @endif>
                                @break

                                {{-- PASSWORD Input --}}
                                @case(\RMS\Core\Data\Field::PASSWORD)
                                    <input type="password"
                                           name="{{ $field->key }}"
                                           class="form-control @error($field->key) is-invalid @enderror"
                                           placeholder="{{ $field->placeholder ?? '' }}"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif>
                                @break

                                {{-- SELECT Dropdown --}}
                                @case(\RMS\Core\Data\Field::SELECT)
                                    @php
                                        $selectedValue = old($field->key, $form_values[$field->key] ?? $field->default_value);

                                        // Determine select classes
                                        $selectClasses = 'form-select';

                                        // Check if this select needs advanced functionality
                                        if (isset($field->advanced) && $field->advanced === true) {
                                            $selectClasses .= ' enhanced-select';
                                        }

                                        // Data attributes for enhanced select
                                        $dataAttributes = '';
                                        if (isset($field->advanced) && $field->advanced === true) {
                                            if (isset($field->ajax_url)) {
                                                $dataAttributes .= ' data-ajax="' . $field->ajax_url . '"';
                                            }
                                            if (isset($field->searchable) && $field->searchable) {
                                                $dataAttributes .= ' data-search="true"';
                                            }
                                            if (isset($field->creatable) && $field->creatable) {
                                                $dataAttributes .= ' data-create="true"';
                                            }
                                            if (isset($field->placeholder)) {
                                                $dataAttributes .= ' data-placeholder="' . $field->placeholder . '"';
                                            }
                                        }
                                    @endphp
                                    <select name="{{ $field->key }}"
                                            class="{{ $selectClasses }}"
                                            @if($field->required) required @endif
                                            @if($field->disabled) disabled @endif
                                            @if($field->multiple) multiple @endif
                                            {!! $dataAttributes !!}>
                                        @if($field->select_data && $field->select_data->count() > 0)
                                            @foreach($field->select_data as $option)
                                                @php
                                                    $optionValue = $option[$field->select_id ?? 'id'];
                                                    $optionLabel = $option[$field->select_title ?? 'name'];
                                                    // Convert both values to string for comparison
                                                    $isSelected = (string)$selectedValue === (string)$optionValue;
                                                @endphp
                                                <option value="{{ $optionValue }}"
                                                    @if($isSelected) selected @endif>
                                                    {{ $optionLabel }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                @break

                                {{-- BOOL (Radio or Checkbox) --}}
                                @case(\RMS\Core\Data\Field::BOOL)
                                    @if($field->options)
                                        @foreach($field->options as $value => $label)
                                            <div class="form-check">
                                                <input class="form-check-input @error($field->key) is-invalid @enderror"
                                                       type="radio"
                                                       name="{{ $field->key }}"
                                                       value="{{ $value }}"
                                                       id="{{ $field->key }}_{{ $value }}"
                                                       @if(old($field->key, $form_values[$field->key] ?? $field->default_value) == $value) checked @endif
                                                       @if($field->disabled) disabled @endif>
                                                <label class="form-check-label" for="{{ $field->key }}_{{ $value }}">
                                                    {{ $label }}
                                                </label>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="form-check">
                                            <input class="form-check-input @error($field->key) is-invalid @enderror"
                                                   type="checkbox"
                                                   name="{{ $field->key }}"
                                                   value="1"
                                                   id="{{ $field->key }}"
                                                   @if(old($field->key, $form_values[$field->key] ?? $field->default_value)) checked @endif
                                                   @if($field->disabled) disabled @endif>
                                            <label class="form-check-label" for="{{ $field->key }}">
                                                {{ $field->title }}
                                            </label>
                                        </div>
                                    @endif
                                @break

                                {{-- FILE Input --}}
                                @case(\RMS\Core\Data\Field::FILE)
                                    <input type="file"
                                           name="{{ $field->key }}"
                                           class="form-control @error($field->key) is-invalid @enderror"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif
                                           @if($field->multiple) multiple @endif>
                                @break

                                {{-- IMAGE Upload with Preview --}}
                                @case(\RMS\Core\Data\Field::IMAGE)
                                    @php
                                        $imageInputClass = 'image-uploader';
                                        $imageInputAttributes = '';

                                        // Build data attributes from field attributes
                                        foreach ($field->attributes as $attr => $value) {
                                            if (str_starts_with($attr, 'data-')) {
                                                $imageInputAttributes .= ' ' . $attr . '="' . $value . '"';
                                            } elseif ($attr === 'accept') {
                                                $imageInputAttributes .= ' accept="' . $value . '"';
                                            } elseif ($attr === 'multiple' && $value) {
                                                $imageInputAttributes .= ' multiple';
                                            }
                                        }

                                        // Default attributes if not set
                                        if (!isset($field->attributes['accept'])) {
                                            $imageInputAttributes .= ' accept=".jpg,.jpeg,.png,.gif,.webp"';
                                        }
                                        if (!isset($field->attributes['data-max-size'])) {
                                            $imageInputAttributes .= ' data-max-size="2MB"';
                                        }
                                        if (!isset($field->attributes['data-preview'])) {
                                            $imageInputAttributes .= ' data-preview="true"';
                                        }
                                        if (!isset($field->attributes['data-drag-drop'])) {
                                            $imageInputAttributes .= ' data-drag-drop="true"';
                                        }
                                    @endphp

                                    <div class="{{ $imageInputClass }}">
                                        <input type="file"
                                               name="{{ $field->key }}"
                                               class="form-control @error($field->key) is-invalid @enderror"
                                               @if($field->required) required @endif
                                               @if($field->disabled) disabled @endif
                                               {!! $imageInputAttributes !!}>
                                    </div>
                                @break

                                {{-- COMMENT/TEXTAREA --}}
                                @case(\RMS\Core\Data\Field::COMMENT)
                                @case(\RMS\Core\Data\Field::TEXTAREA)
                                    @php
                                        // Determine rows from attributes or field properties
                                        $rows = $field->attributes['rows'] ?? ($field->large_size ? 8 : 4);
                                    @endphp
                                    <textarea name="{{ $field->key }}"
                                              class="form-control @error($field->key) is-invalid @enderror"
                                              rows="{{ $rows }}"
                                              placeholder="{{ $field->placeholder ?? '' }}"
                                              @if($field->required) required @endif
                                              @if($field->disabled) disabled @endif
                                              @if($field->readonly) readonly @endif>{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}</textarea>
                                @break

                                {{-- DATE Input --}}
                                @case(\RMS\Core\Data\Field::DATE)
                                    @php
                                        $dateClasses = 'form-control persian-datepicker @error($field->key) is-invalid @enderror';
                                        if ($field->attributes && isset($field->attributes['class'])) {
                                            $dateClasses .= ' ' . $field->attributes['class'];
                                        }
                                    @endphp
                                    <input type="text"
                                           name="{{ $field->key }}"
                                           class="{{ $dateClasses }}"
                                           placeholder="{{ $field->placeholder ?? 'YYYY/MM/DD' }}"
                                           value="{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif
                                           @if($field->readonly) readonly @endif
                                           autocomplete="off">
                                @break

                                {{-- DATETIME Input --}}
                                @case(\RMS\Core\Data\Field::DATE_TIME)
                                    @php
                                        $datetimeClasses = 'form-control persian-datepicker ';
                                        if ($field->attributes && isset($field->attributes['class'])) {
                                            $datetimeClasses .= ' ' . $field->attributes['class'];
                                        }
                                    @endphp
                                    <input type="text"
                                           name="{{ $field->key }}"
                                           class="{{ $datetimeClasses }}"
                                           placeholder="{{ $field->placeholder ?? 'YYYY/MM/DD HH:mm' }}"
                                           value="{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}"
                                           data-format="YYYY/MM/DD HH:mm"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif
                                           @if($field->readonly) readonly @endif
                                           autocomplete="off">
                                @break

                                {{-- NUMBER Input --}}
                                @case(\RMS\Core\Data\Field::NUMBER)
                                @case(\RMS\Core\Data\Field::INTEGER)
                                    <input type="number"
                                           name="{{ $field->key }}"
                                           class="form-control @error($field->key) is-invalid @enderror"
                                           placeholder="{{ $field->placeholder ?? '' }}"
                                           value="{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif
                                           @if($field->readonly) readonly @endif>
                                @break

                                {{-- HIDDEN Input --}}
                                @case(\RMS\Core\Data\Field::HIDDEN)
                                    <input type="hidden"
                                           name="{{ $field->key }}"
                                           value="{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}">
                                @break

                                {{-- DEFAULT: Text Input --}}
                                @default
                                    <input type="text"
                                           name="{{ $field->key }}"
                                           class="form-control @error($field->key) is-invalid @enderror"
                                           placeholder="{{ $field->placeholder ?? '' }}"
                                           value="{{ old($field->key, $form_values[$field->key] ?? $field->default_value) }}"
                                           @if($field->required) required @endif
                                           @if($field->disabled) disabled @endif
                                           @if($field->readonly) readonly @endif>
                            @endswitch

                            {{-- Field Hint --}}
                            @if($field->hint)
                                <div class="form-text">{{ $field->hint }}</div>
                            @endif

                            {{-- Validation Error --}}
                            @error($field->key)
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                        </div>
                    </div>
                @endforeach

                {{-- Form Buttons --}}
                <div class="row">
                    <div class="col-lg-9 offset-lg-3">
                        {{-- Main Submit Button --}}
                        <button type="submit" name="submit" class="btn btn-primary">
                            <i class="ph-paper-plane-tilt me-2"></i>
                            {{ isset($form_values) && !empty($form_values) ? 'به‌روزرسانی' : 'ذخیره' }}
                        </button>

                        {{-- Stay in Form Button --}}
                        @if(isset($form_config['show_stay_button']) && $form_config['show_stay_button'])
                            <button type="submit" name="stay_in_form" value="1" class="btn btn-success ms-2">
                                <i class="ph-floppy-disk me-2"></i>
                                {{ isset($form_values) && !empty($form_values) ? 'به‌روزرسانی و ماندن' : 'ذخیره و ماندن' }}
                            </button>
                        @endif

                        <a href="{{ url()->previous() }}" class="btn btn-light ms-2">
                            <i class="ph-arrow-left me-2"></i>
                            بازگشت
                        </a>
                        <button type="reset" class="btn btn-outline-secondary ms-2">
                            <i class="ph-arrow-counter-clockwise me-2"></i>
                            بازنشانی
                        </button>
                    </div>
                </div>

            </form>

        </div>
    </div>

</div>

@endsection

@push('styles')
<style>
/* Custom form styles */
.required::after {
    content: ' *';
    color: #dc3545;
}

/* Form validation styles */
.is-invalid {
    border-color: #dc3545;
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

/* Responsive form adjustments */
@media (max-width: 768px) {
    .row .col-lg-3,
    .row .col-lg-9 {
        flex: 0 0 100%;
        max-width: 100%;
    }

    .row .col-lg-9.offset-lg-3 {
        margin-right: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation and interaction scripts
    console.log('Dynamic form loaded successfully');

    // Auto-focus first input
    const firstInput = document.querySelector('.form-control:not([readonly]):not([disabled])');
    if (firstInput) {
        firstInput.focus();
    }
});
</script>
@endpush
