<form method="post" action="{{ route($listData['routes']['filter'] ?? ($listData['routes']['index'] ?? '#')) }}" enctype="multipart/form-data">
    @csrf
    @php $controllerInstance = $listData['meta']['controller'] ?? null; @endphp
    @if($controllerInstance)
        <input type="hidden" name="key" value="{{ encrypt(get_class($controllerInstance)) }}">
    @endif
    
    <div class="card-body">
        <div class="row">
            @php $colCount = 0; @endphp
            @foreach($listData['fields'] as $field)
                @if($field['filterable'] ?? false)
                    @php $colCount++; @endphp
                    <div class="col-lg-4">
                        <div class="row mb-3">
                            <label for="filter_{{ $field['key'] }}" class="col-form-label col-lg-3">{{ $field['title'] }}</label>
                            <div class="col-lg-9">
                                @if($field['filter_type'] === 'date' || $field['filter_type'] === 'date_time')
                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="ph-calendar"></i>
                                                        </span>
                                        <input type="text" name="filter_{{ $field['key'] }}_from"
                                               value="{{ $listData['active_filters'][$field['key'] . '_from'] ?? '' }}"
                                               class="form-control persian-datepicker" autocomplete="off"
                                               placeholder="{{ $field['title'] }} از...">
                                    </div>
                                    <br>
                                    <div class="input-group">
                                                        <span class="input-group-text">
                                                            <i class="ph-calendar"></i>
                                                        </span>
                                        <input type="text" name="filter_{{ $field['key'] }}_to"
                                               value="{{ $listData['active_filters'][$field['key'] . '_to'] ?? '' }}"
                                               class="form-control persian-datepicker" autocomplete="off"
                                               placeholder="{{ $field['title'] }} تا...">
                                    </div>
                                @elseif($field['filter_type'] === 'bool')
                                    @php
                                        // Use array_key_exists to properly detect 0 values
                                        $boolValue = array_key_exists($field['key'], $listData['active_filters']) 
                                                   ? $listData['active_filters'][$field['key']] 
                                                   : null;
                                        // Convert boolean to proper string representation
                                        if ($boolValue === null) {
                                            $boolValueStr = '';
                                        } elseif ($boolValue === true || $boolValue === 1 || $boolValue === '1') {
                                            $boolValueStr = '1';
                                        } else {
                                            $boolValueStr = '0'; // for false, 0, '0'
                                        }
                                    @endphp
                                    <select name="filter_{{ $field['key'] }}" class="form-select" id="filter_{{ $field['key'] }}">
                                        <option value="" {{ $boolValue === null ? 'selected' : '' }}>همه</option>
                                        <option value="1" {{ $boolValueStr === '1' ? 'selected' : '' }}>بله</option>
                                        <option value="0" {{ $boolValueStr === '0' ? 'selected' : '' }}>خیر</option>
                                    </select>
                                @elseif(isset($field['filter_options']) && !empty($field['filter_options']))
                                    @php
                                        // Determine if this select should use enhanced functionality
                                        $selectClasses = 'form-select';
                                        $needsEnhanced = false;
                                        
                                        // Check if field has advanced property set to true
                                        if (isset($field['advanced']) && $field['advanced'] === true) {
                                            $needsEnhanced = true;
                                        } 
                                        // Auto-detect if many options (more than 10)
                                        elseif (count($field['filter_options']) > 10) {
                                            $needsEnhanced = true;
                                        }
                                        // Check for complex options structure
                                        elseif (collect($field['filter_options'])->contains(function($value) {
                                            return is_array($value) || (is_string($value) && strlen($value) > 50);
                                        })) {
                                            $needsEnhanced = true;
                                        }
                                        
                                        if ($needsEnhanced) {
                                            $selectClasses .= ' enhanced-select';
                                        }
                                    @endphp
                                    <select name="filter_{{ $field['key'] }}" class="{{ $selectClasses }}" id="filter_{{ $field['key'] }}">
                                        <option value="">همه</option>
                                        @foreach($field['filter_options'] as $value => $label)
                                            <option value="{{ $value }}" {{ ($listData['active_filters'][$field['key']] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    @php
                                        // Determine input class based on field type
                                        $inputClass = 'form-control';
                                        $inputType = 'text';
                                        
                                        // Check if this is a price/amount field
                                        if (isset($field['type']) && $field['type'] === 'price') {
                                            $inputClass .= ' amount-field';
                                        }
                                        // Also check by field key name
                                        elseif (str_contains($field['key'], 'amount') || str_contains($field['key'], 'price')) {
                                            $inputClass .= ' amount-field';
                                        }
                                    @endphp
                                    <input autocomplete="off" class="{{ $inputClass }}" type="{{ $inputType }}"
                                           id="filter_{{ $field['key'] }}" name="filter_{{ $field['key'] }}"
                                           value="{{ $listData['active_filters'][$field['key']] ?? '' }}">
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($colCount % 3 === 0)
        </div><div class="row">
            @endif
            @endif
            @endforeach
        </div>
    </div>
    <div class="card-footer">
        <div class="row">
            <div class="col-lg-4">
                <button type="submit" name="filter" class="btn btn-success">
                    <i class="ph-magnifying-glass"></i> جستجو
                </button>
                @if($listData['has_filters'] ?? false)
                    @php
                        // Debug routes
                        // dd($listData['routes']);
                        $clearRoute = $listData['routes']['clear_filter'] ?? ($listData['routes']['index'] ?? '#');
                    @endphp
                    <a href="{{ route($clearRoute) }}" class="btn btn-outline-secondary">
                        <i class="ph-x"></i> پاک کردن فیلتر
                    </a>
                @endif
            </div>
        </div>
    </div>
</form>
