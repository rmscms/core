@extends('cms::admin.layout.index')
@section('content')

    <div class="d-flex flex-column" style="min-height: 100vh;">
        {{-- Dynamic Filter Section --}}
        @php
            $hasFilterableFields = collect($listData['fields'] ?? [])->contains(function($field) {
                return $field['filterable'] ?? false;
            });
            // Get controller from listData meta
            $controllerInstance = $listData['meta']['controller'] ?? null;
            $supportsFilter = $controllerInstance && $controllerInstance instanceof \RMS\Core\Contracts\Filter\ShouldFilter;

        @endphp

        {{-- Statistics Section --}}
        @include('cms::admin.list.stats')

        @if($hasFilterableFields && $supportsFilter)
            <div class="card mb-3">
                <div class="card-header d-flex flex-wrap justify-content-between">
                    <h6 class="mb-0">
                        <a class="text-body" data-card-action="collapse">
                            فیلتر
                            @if($listData['has_filters'] ?? false)
                                <span class="badge bg-info ms-2">{{ count($listData['active_filters']) }}</span>
                            @endif
                        </a>
                    </h6>
                    <div>
                        <a class="text-body mx-2" data-card-action="reload">
                            <i class="ph-arrows-clockwise"></i>
                        </a>
                        <a class="text-body" data-card-action="collapse">
                            <i class="ph-caret-down"></i>
                        </a>
                    </div>
                </div>
                <div class="collapse{{ ($listData['has_filters'] ?? false) ? ' show' : '' }}" id="collapsible_card_filter">
                    @include('cms::admin.list.filter')
                </div>
            </div>
        @endif

        <div class="card flex-grow-1 d-flex flex-column">
            <div class="card-header d-flex flex-wrap align-items-center justify-content-between">
                <h6 class="mb-0">{{ $title ?? 'فهرست داده‌ها' }}</h6>

                <div class="d-flex align-items-center gap-2">
                    @if(isset($listData['pagination']['total']) && $listData['pagination']['total'] !== null && $listData['pagination']['total'] > 0)
                        <div class="table-list-stat-cont">
                            <div class="table-list-stat-label">{{ number_format($listData['pagination']['total']) }} تعداد</div>
                        </div>
                    @elseif(isset($listData['config']['simple_pagination']) && $listData['config']['simple_pagination'])
                        <div class="table-list-stat-cont">
                            <div class="table-list-stat-label">صفحه {{ $listData['pagination']['current_page'] ?? 1 }}</div>
                        </div>
                    @endif

                    @if($listData['config']['create_button'] ?? false)
                        <a href="{{ route($listData['routes']['create'] ?? '#') }}" class="btn btn-success btn-sm">
                            <i class="ph-plus me-1"></i> افزودن
                        </a>
                    @endif

                    {{-- Export button if controller supports export --}}
                    @php
                        $controllerInstance = $listData['meta']['controller'] ?? null;
                        $supportsExport = $controllerInstance && $controllerInstance instanceof \RMS\Core\Contracts\Export\ShouldExport;
                        $exportUrl = $supportsExport && isset($listData['routes']['export']) ? route($listData['routes']['export']) : null;
                    @endphp
                    @if($supportsExport && $exportUrl)
                        <a href="{{ $exportUrl }}" class="btn btn-info btn-sm" title="دریافت فایل Excel">
                            <i class="ph-microsoft-excel-logo me-1"></i> دریافت Excel
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body p-0 flex-grow-1">
                <div class="table-responsive overflow-x-auto">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="table-light">
                        <tr>
                            {{-- Batch Selection Column --}}
                            @if(isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false))
                                <th class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <input type="checkbox" class="form-check-input" id="select_all" name="select_all">
                                        <span>انتخاب</span>
                                    </div>
                                </th>
                            @endif

                            {{-- ID Column (if view_id is enabled and batch is not active) --}}
                            @if(($listData['config']['view_id'] ?? false) && !(isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false)))
                                <th class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <span>ردیف</span>
                                    </div>
                                </th>
                            @endif

                            {{-- Dynamic Field Headers --}}
                            @foreach($listData['fields'] as $field)
                                <th class="{{ (!empty($field['class']) ? $field['class'] : 'text-center') }}" @if(isset($field['width']) && $field['width'] !== 'auto') width="{{ $field['width'] }}" @endif>
                                    @if(($field['sortable'] ?? false) && isset($controller) && $controller instanceof \RMS\Core\Contracts\Filter\HasSort && isset($listData['routes']['sort']))
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            <span>{{ $field['title'] }}</span>
                                            <div class="d-flex flex-column" style="gap: 1px;">
                                                @php
                                                    $isCurrentFieldSorted = $controller->fieldOrdered() === $field['key'];
                                                    $currentSortDirection = $isCurrentFieldSorted ? $controller->orderWay() : null;
                                                @endphp

                                                <a href="{{ route($listData['routes']['sort'], ['by' => $field['key'], 'way' => 'asc']) }}"
                                                   class="btn btn-xs p-0 border-0{{ ($isCurrentFieldSorted && $currentSortDirection === 'asc') ? ' text-primary' : ' text-muted' }}"
                                                   style="font-size: 0.65rem; line-height: 1; width: 12px; height: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ph-caret-up"></i>
                                                </a>
                                                <a href="{{ route($listData['routes']['sort'], ['by' => $field['key'], 'way' => 'desc']) }}"
                                                   class="btn btn-xs p-0 border-0{{ ($isCurrentFieldSorted && $currentSortDirection === 'desc') ? ' text-primary' : ' text-muted' }}"
                                                   style="font-size: 0.65rem; line-height: 1; width: 12px; height: 10px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="ph-caret-down"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        {{ $field['title'] }}
                                    @endif
                                </th>
                            @endforeach

                            {{-- Actions Column --}}
                            @if($listData['actions']['has_row_actions'] ?? false)
                                <th class="text-center">عملیات</th>
                            @endif
                        </tr>
                        </thead>
                        @if(isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false))
                            <form id="table_form" method="post" action="{{ isset($listData['routes']['batch']['delete']) ? route($listData['routes']['batch']['delete']) : '#' }}">
                                @csrf
                            </form>
                        @endif
                        <tbody>
                        @forelse($listData['rows'] as $row)
                            @php
                                $rowId = $row->{$listData['config']['identifier']} ?? $row->id ?? $loop->iteration;
                                $currentIndexId = $loop->iteration + ($listData['config']['per_page'] ?? 15) * (($listData['pagination']['current_page'] ?? 1) - 1);
                            @endphp
                            <tr id="row-{{ $rowId }}" data-id="{{ $rowId }}">
                                {{-- Batch Selection Column --}}
                                @if(isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false))
                                    <td class="text-center align-middle">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <input type="checkbox" class="form-check-input row-selector"
                                                   id="id_{{ $rowId }}" name="ids[]" value="{{ $rowId }}" form="table_form">
                                            <label for="id_{{ $rowId }}" class="mb-0">{{ $currentIndexId }}</label>
                                        </div>
                                    </td>
                                @endif

                                {{-- ID Column (if view_id is enabled and batch is not active) --}}
                                @if(($listData['config']['view_id'] ?? false) && !(isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false)))
                                    <td class="text-center align-middle">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <span>{{ $rowId }}</span>
                                        </div>
                                    </td>
                                @endif

                                {{-- Dynamic Field Columns --}}
                                @foreach($listData['fields'] as $field)
                                    <td class="{{ (!empty($field['class']) ? $field['class'] : 'text-center') }} align-middle {{ $field['key'] }}">
                                        @if(($field['type'] ?? '') === 'bool' && !($field['method'] ?? false) && isset($controller) && $controller instanceof \RMS\Core\Contracts\Actions\ChangeBoolField)
                                            {{-- Boolean Toggle Field --}}
                                            @php
                                                $fieldKey = $field['key'] ?? $field['database_key'] ?? '';
                                                $fieldValue = $row->{$fieldKey} ?? false;
                                                $isActive = (bool) $fieldValue;
                                                $rowId = $row->{$listData['config']['identifier']} ?? $row->id ?? $loop->iteration;
                                                $toggleUrl = $controller->boolFieldUrl($rowId, $fieldKey);
                                            @endphp

                                            {{-- Boolean Toggle as AJAX link (global style) --}}
                                            <a href="{{ $toggleUrl }}" class="ajax-bool" title="{{ $isActive ? 'کلیک برای غیرفعال کردن' : 'کلیک برای فعال کردن' }}">
                                                <button type="button" class="btn {{ $isActive ? 'btn-success' : 'btn-danger' }} btn-sm">
                                                    <i class="ph-{{ $isActive ? 'check' : 'x' }}"></i>
                                                </button>
                                            </a>
                                        @elseif(in_array($field['type'] ?? '', ['date', 'date_time']))
                                            {{-- Date/DateTime Field with Persian Formatting --}}
                                            @php
                                                $fieldKey = $field['key'] ?? $field['database_key'] ?? '';
                                                $fieldValue = $row->{$fieldKey} ?? null;
                                            @endphp
                                            @if($fieldValue && $fieldValue !== '0000-00-00 00:00:00')
                                                @if(function_exists('\RMS\Helper\persian_date'))
                                                    @php
                                                        $format = ($field['type'] === 'date_time') ? 'Y/m/d H:i' : 'Y/m/d';
                                                        try {
                                                            $persianDate = \RMS\Helper\persian_date($fieldValue, $format);
                                                        } catch (Exception $e) {
                                                            $persianDate = $fieldValue; // Fallback to original value if conversion fails
                                                        }
                                                    @endphp
                                                    <span class="text-nowrap" title="{{ $fieldValue }}">{{ $persianDate }}</span>
                                                @else
                                                    {{-- Fallback if helper function not available --}}
                                                    <span class="text-nowrap">{{ $fieldValue }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        @else
                                            {{-- Regular Field Rendering --}}
                                            {!! $list->renderField($row, $field) !!}
                                        @endif
                                    </td>
                                @endforeach

                                {{-- Actions Column --}}
                                @if($listData['actions']['has_row_actions'] ?? false)
                                    <td class="text-center align-middle">
                                        <div class="d-flex align-self-center dropdown">
                                            <a href="#" class="text-body d-inline-flex align-items-center" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="ph-list"></i>
                                            </a>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                @foreach($listData['actions']['row_actions'] as $action)
                                                    @php
                                                        // Check if this action should be skipped for current row
                                                        $shouldSkip = !empty($action->skip) && in_array($rowId, $action->skip);
                                                    @endphp
                                                    @if(!$shouldSkip)
                                                        @if($action->confirm ?? false)
                                                            <a href="#" class="dropdown-item{{ isset($action->class_name) && str_contains($action->class_name, 'danger') ? ' text-danger' : '' }}"
                                                               data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                               data-action-url="{{ route($action->route, [$listData['routes']['parameter'] ?? 'id' => $rowId]) }}"
                                                               data-action-method="{{ $action->method ?? 'GET' }}"
                                                               data-action-title="{{ $action->title }}">
                                                                {!! $action->icon ?? '<i class="ph-gear"></i>' !!}
                                                                {{ $action->title }}
                                                            </a>
                                                        @else
                                                            <a href="{{ route($action->route, [$listData['routes']['parameter'] ?? 'id' => $rowId]) }}"
                                                               class="dropdown-item{{ isset($action->class_name) && str_contains($action->class_name, 'danger') ? ' text-danger' : '' }}">
                                                                {!! $action->icon ?? '<i class="ph-gear"></i>' !!}
                                                                {{ $action->title }}
                                                            </a>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            </div>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                @php
                                    $hasBatchActions = isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false);
                                    $colspanCount = count($listData['fields']) + ($hasBatchActions ? 1 : 0) + (($listData['config']['view_id'] ?? false) && !$hasBatchActions ? 1 : 0) + ($listData['actions']['has_row_actions'] ? 1 : 0);
                                @endphp
                                <td class="text-center align-middle" colspan="{{ $colspanCount }}">
                                    <div class="py-4">
                                        <i class="ph-inbox ph-3x d-block text-muted mb-3"></i>
                                        <span class="text-muted">هیچ داده‌ای یافت نشد</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if((isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false)) || ($listData['pagination']['total'] ?? 0) > ($listData['config']['per_page'] ?? 15) || ($listData['pagination']['total'] ?? 0) > 10 || ($listData['config']['simple_pagination'] ?? false))
                <div class="card-footer mt-auto">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        {{-- Left side: Batch Actions --}}
                        <div class="d-flex align-items-center gap-2">
                            @if(isset($controller) && $controller instanceof \RMS\Core\Contracts\Batch\HasBatch && ($listData['actions']['has_batch_actions'] ?? false))
                                <div class="btn-group">
                                    <button type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                            data-bs-toggle="dropdown" id="batch-actions-btn" disabled>
                                        <i class="ph-check-square-offset me-1"></i>
                                        عملیات انتخابها
                                    </button>
                                    <ul class="dropdown-menu">
                                        @foreach($listData['actions']['batch_actions'] as $batchAction)
                                            <li>
                                                <a class="dropdown-item{{ isset($batchAction->class_name) && str_contains($batchAction->class_name, 'danger') ? ' text-danger' : '' }}"
                                                   href="#" onclick="submitBatchAction('{{ $batchAction->url }}')">
                                                    {!! $batchAction->icon ?? '<i class="ph-gear"></i>' !!}
                                                    {{ $batchAction->title }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            {{-- Per Page Selector --}}
                            @if(($listData['pagination']['total'] ?? 0) > 10 || ($listData['config']['simple_pagination'] ?? false))
                                <div class="d-flex align-items-center gap-2">
                                    <label for="per_page" class="form-label mb-0 text-nowrap">نمایش:</label>
                                    <select name="perPage" id="per_page" class="form-select form-select-sm"
                                            onchange="changePerPage(this.value)" style="min-width: 80px;">
                                        @foreach([10, 20, 30, 50, 100] as $perPageOption)
                                            <option value="{{ $perPageOption }}"
                                                {{ ($listData['pagination']['per_page'] ?? 15) == $perPageOption ? 'selected' : '' }}>
                                                {{ $perPageOption }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        {{-- Right side: Pagination --}}
                        @if($listData['has_pagination'] ?? false)
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                {{-- Pagination Info --}}
                                <div class="text-muted">
                                    @if(isset($listData['pagination']['total']) && $listData['pagination']['total'] !== null)
                                        {{-- Regular pagination with total count --}}
                                        نمایش {{ $listData['pagination']['from'] ?? 1 }} تا {{ $listData['pagination']['to'] ?? $listData['pagination']['per_page'] }} از {{ number_format($listData['pagination']['total']) }} مورد
                                    @else
                                        {{-- Simple pagination without total count --}}
                                        نمایش {{ $listData['pagination']['from'] ?? 1 }} تا {{ $listData['pagination']['to'] ?? $listData['pagination']['per_page'] }}
                                        (صفحه {{ $listData['pagination']['current_page'] ?? 1 }})
                                    @endif
                                </div>

                                {{-- Pagination Links --}}
                                @php
                                    $hasLastPage = isset($listData['pagination']['last_page']) && $listData['pagination']['last_page'] !== null;
                                    $isSimplePagination = !$hasLastPage;
                                @endphp

                                @if($isSimplePagination || ($listData['pagination']['last_page'] ?? 1) > 1)
                                    <nav aria-label="صفحه‌بندی">
                                        <ul class="pagination pagination-sm mb-0">
                                            {{-- Previous Page --}}
                                            @if($listData['pagination']['current_page'] > 1)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $listData['pagination']['current_page'] - 1]) }}">
                                                        <i class="ph-caret-left"></i> قبلی
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                <span class="page-link">
                                                    <i class="ph-caret-left"></i> قبلی
                                                </span>
                                                </li>
                                            @endif

                                            @if($isSimplePagination)
                                                {{-- Simple pagination: only show current page --}}
                                                <li class="page-item active">
                                                    <span class="page-link">{{ $listData['pagination']['current_page'] ?? 1 }}</span>
                                                </li>
                                            @else
                                                {{-- Regular pagination: show page numbers --}}
                                                @php
                                                    $currentPage = $listData['pagination']['current_page'] ?? 1;
                                                    $lastPage = $listData['pagination']['last_page'] ?? 1;
                                                    $startPage = max(1, $currentPage - 2);
                                                    $endPage = min($lastPage, $currentPage + 2);
                                                @endphp

                                                @if($startPage > 1)
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => 1]) }}">1</a>
                                                    </li>
                                                    @if($startPage > 2)
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    @endif
                                                @endif

                                                @for($i = $startPage; $i <= $endPage; $i++)
                                                    <li class="page-item {{ $i === $currentPage ? 'active' : '' }}">
                                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $i]) }}">{{ $i }}</a>
                                                    </li>
                                                @endfor

                                                @if($endPage < $lastPage)
                                                    @if($endPage < $lastPage - 1)
                                                        <li class="page-item disabled">
                                                            <span class="page-link">...</span>
                                                        </li>
                                                    @endif
                                                    <li class="page-item">
                                                        <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => $lastPage]) }}">{{ $lastPage }}</a>
                                                    </li>
                                                @endif
                                            @endif

                                            {{-- Next Page --}}
                                            @if($listData['pagination']['has_more_pages'] ?? false)
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ request()->fullUrlWithQuery(['page' => ($listData['pagination']['current_page'] ?? 1) + 1]) }}">
                                                        بعدی <i class="ph-caret-right"></i>
                                                    </a>
                                                </li>
                                            @else
                                                <li class="page-item disabled">
                                                <span class="page-link">
                                                    بعدی <i class="ph-caret-right"></i>
                                                </span>
                                                </li>
                                            @endif
                                        </ul>
                                    </nav>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Confirmation Modal --}}
    @if($listData['actions']['has_row_actions'] ?? false)
        <div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">تأیید عملیات</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p id="confirm-message">آیا از انجام این عملیات مطمئن هستید؟</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <form id="confirmForm" method="POST" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger">تأیید</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('styles')
    <style>
        /* Fix large icons in empty state */
        .table .ph-3x {
            font-size: 2.5rem !important;
            opacity: 0.3;
        }

        /* Fix table header sort buttons */
        .table thead th .btn {
            background: none !important;
            border: none !important;
            padding: 0 !important;
            box-shadow: none !important;
        }

        .table thead th .btn:hover {
            background: none !important;
            transform: scale(1.1);
        }

        .table thead th .btn:focus {
            box-shadow: none !important;
        }

        /* Action dropdown icons */
        .dropdown-menu .dropdown-item i {
            width: 1.2rem;
            text-align: center;
            margin-left: 0.5rem;
            font-size: 0.875rem;
        }

        /* Custom pagination styles to override default Laravel pagination */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            border-radius: 0.375rem;
            margin-bottom: 0 !important;
            flex-wrap: wrap;
            gap: 2px;
        }

        .pagination .page-item {
            position: relative;
            display: block;
            margin: 0;
        }

        .pagination .page-link {
            position: relative;
            display: block;
            padding: 0.375rem 0.75rem;
            margin-left: 0;
            line-height: 1.25;
            color: #6c757d;
            text-decoration: none;
            background-color: #fff;
            border: 1px solid #dee2e6;
            font-size: 0.875rem;
            border-radius: 0.375rem;
            transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out;
        }

        .pagination .page-link:hover {
            z-index: 2;
            color: #0051a0;
            background-color: #e9ecef;
            border-color: #dee2e6;
        }

        .pagination .page-link:focus {
            z-index: 3;
            color: #0051a0;
            background-color: #e9ecef;
            border-color: #86b7fe;
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }

        .pagination .page-item.active .page-link {
            z-index: 3;
            color: #fff;
            background-color: #0d6efd;
            border-color: #0d6efd;
        }

        .pagination .page-item.disabled .page-link {
            color: #6c757d;
            pointer-events: none;
            background-color: #fff;
            border-color: #dee2e6;
        }

        /* Fix icons in pagination */
        .pagination .page-link i {
            font-size: 0.75rem;
            vertical-align: middle;
        }

        /* Small pagination for card footer */
        .card-footer .pagination {
            --bs-pagination-padding-x: 0.5rem;
            --bs-pagination-padding-y: 0.25rem;
            --bs-pagination-font-size: 0.75rem;
        }

        .card-footer .pagination .page-link {
            padding: var(--bs-pagination-padding-y) var(--bs-pagination-padding-x);
            font-size: var(--bs-pagination-font-size);
        }

        /* Responsive pagination */
        @media (max-width: 576px) {
            .pagination {
                justify-content: center;
                font-size: 0.75rem;
            }

            .pagination .page-link {
                padding: 0.25rem 0.5rem;
                font-size: 0.75rem;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>

    </script>
@endpush
