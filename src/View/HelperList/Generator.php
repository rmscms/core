<?php

namespace RMS\Core\View\HelperList;

use Illuminate\Support\Str;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Data\Database;
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Batch\HasBatch;
use RMS\Core\Data\ListResponse;

/**
 * Trait Generator
 * @package RMS\Core\View\HelperList
 */
class Generator
{
    use Actions;

    /**
     * Determine create button is active or not
     * @var bool $create
     */
    public $create = true;

    /**
     * Identifier key in each row
     * @var string $identifier
     */
    public $identifier = 'id';

    /**
     * If set to false, loop from foreach begins from 1
     * @var bool $view_id
     */
    public $view_id = true;

    /**
     * Base route parameter
     * @var string|null $route_parameter
     */
    public $route_parameter;

    /**
     * Display rows per page
     * @var int $per_page
     */
    public $per_page = 20;

    /**
     * Simple pagination
     * @var bool $simple_pagination
     */
    public $simple_pagination = false;

    /**
     * Fields
     * @var array $fields
     */
    protected $fields = [];

    /**
     * @var HasList $list
     */
    protected $list;

    /**
     * @var string $base_route
     */
    public $base_route;

    /**
     * @var array $links
     */
    public $links = [];

    /**
     * @var bool $batch_destroy
     */
    public $batch_destroy = true;

    /**
     * @var bool $batch_active
     */
    public $batch_active = false;

    /**
     * Generator constructor.
     * @param HasList $list
     */
    public function __construct(HasList $list)
    {
        $this->list = $list;
        $this->route_parameter = $list->routeParameter();
        $this->base_route = $list->baseRoute();
        $this->fields = $this->list->getListFields();

        // Load list configuration if available
        if (method_exists($list, 'getListConfig')) {
            $config = $list->getListConfig();

            // Apply configuration settings
            if (isset($config['show_create'])) {
                $this->create = $config['show_create'];
            }
            if (isset($config['per_page'])) {
                $this->per_page = $config['per_page'];
            }
            if (isset($config['simple_pagination'])) {
                $this->simple_pagination = $config['simple_pagination'];
            }
        }

        // Load batch actions if controller implements HasBatch
        if ($this->list instanceof HasBatch) {
            $this->batch_destroy = true; // Enable batch actions
            $this->loadBatchActionsFromController();
        }
    }

    /**
     * Generate the list response.
     *
     * @return ListResponse
     */
    public function generate(): ListResponse
    {
        if ($this->list instanceof UseDatabase) {
            // Get current page from request or default to 1
            $currentPage = request()->get('page', 1);

            // Build the query with all filters, sorting, etc.
            $database = $this->builder();

            // Get paginated results
            $paginatedData = $database->get($this->perPage(), (int) $currentPage, $this->simple_pagination);

            // Create list response with enhanced metadata
            $response = new ListResponse($paginatedData, $this, $this->fields);

            // Get stats data if controller supports HasStats
            $statsData = null;
            if ($this->list instanceof HasStats) {
                // Pass the query builder with filters applied to stats method
                $statsData = $this->list->getStats($database->sql);
            }

            // Add metadata for Blade rendering
            return $response->withMeta([
                'controller' => $this->list,
                'config' => $this->getBladeRenderingConfig(),
                'filters' => $this->getActiveFilters(),
                'actions' => $this->actions,
                'batch_actions' => $this->batches,
                'route_info' => $this->getRouteInfo(),
                'stats' => $statsData
            ]);
        }
        throw new \Exception('This class should implement: ' . UseDatabase::class);
    }

    /**
     * Build the database query.
     *
     * @return Database
     */
    public function builder(): Database
    {
        $database = new Database($this->fields, $this->list->table());
        $this->list->query($database->sql);
        if ($this->list instanceof ShouldFilter) {
            // Apply cached filters from user input, not static filter definitions
            $cachedFilters = method_exists($this->list, 'getCachedFilterData') ? $this->list->getCachedFilterData() : [];
            if (!empty($cachedFilters)) {
                $this->applyFilterDatabaseObjects($database, $cachedFilters);
            }
        }
        if ($this->list instanceof HasSort && $column = $this->list->orderBy()) {
            $database->sort($this->list->orderBy(), $this->list->orderWay());
        }
        return $database;
    }

    /**
     * Calculate per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        $cached_per_page = $this->list->getPerPage();
        if ($cached_per_page > 0) {
            $this->per_page = $cached_per_page;
        }
        return $this->per_page;
    }

    /**
     * Add a link to the list.
     *
     * @param Link $link
     * @return $this
     */
    public function link(Link $link)
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * Apply cached filters to database query using FilterDatabase objects.
     *
     * @param Database $database
     * @param array $cachedFilters Array of FilterDatabase objects
     * @return void
     */
    protected function applyFilterDatabaseObjects(Database $database, array $cachedFilters): void
    {
        foreach ($cachedFilters as $filterName => $filterObject) {
            // If it's a FilterDatabase object, apply it directly to the query
            if (is_object($filterObject) && method_exists($filterObject, 'applyToQuery')) {
                $filterObject->applyToQuery($database->sql);
            }
            // Legacy support: if it's still the old format, use fallback
            elseif (!is_object($filterObject) && !empty($filterObject)) {
                $this->applyLegacyFilter($database, $filterName, $filterObject);
            }
        }
    }

    /**
     * Legacy fallback for old-style filter values.
     *
     * @param Database $database
     * @param string $key
     * @param mixed $value
     * @return void
     */
    protected function applyLegacyFilter(Database $database, string $key, mixed $value): void
    {
        // Handle specific filter types (legacy support)
        switch ($key) {
            case 'email_verified':
                if ($value === 'verified') {
                    $database->whereNotNull('email_verified_at');
                } elseif ($value === 'not_verified') {
                    $database->whereNull('email_verified_at');
                }
                break;

            case 'search':
                $database->search($value, ['name', 'email']);
                break;

            case 'created_at_from':
                $database->where('created_at', '>=', $value);
                break;

            case 'created_at_to':
                $database->where('created_at', '<=', $value . ' 23:59:59');
                break;

            default:
                // Generic filter handling
                if (is_array($value)) {
                    $database->whereIn($key, $value);
                } else {
                    $database->where($key, '=', $value);
                }
                break;
        }
    }

    /**
     * Get configuration data for Blade rendering.
     *
     * @return array
     */
    public function getBladeRenderingConfig(): array
    {
        return [
            'create_button' => $this->create,
            'view_id' => $this->view_id,
            'per_page' => $this->per_page,
            'simple_pagination' => $this->simple_pagination,
            'batch_destroy' => $this->batch_destroy,
            'batch_active' => $this->batch_active,
            'identifier' => $this->identifier,
            'base_route' => $this->base_route,
            'route_parameter' => $this->route_parameter,
        ];
    }

    /**
     * Get active filters for Blade rendering.
     *
     * @return array
     */
    public function getActiveFilters(): array
    {
        if (!($this->list instanceof ShouldFilter)) {
            return [];
        }

        $activeFilters = method_exists($this->list, 'getCachedFilterData')
            ? $this->list->getCachedFilterData()
            : [];

        // Convert FilterDatabase objects to simple key-value format for template
        $bladeFilters = [];
        foreach ($activeFilters as $name => $filter) {
            if (is_object($filter) && property_exists($filter, 'value')) {
                // Extract the field name from filter name (remove 'filter_' prefix)
                $fieldName = str_replace('filter_', '', $name);

                // Clean up the value for display (remove SQL wildcards)
                $displayValue = $filter->value;
                if (is_string($displayValue) && $filter->operator === 'LIKE') {
                    // Remove % wildcards from LIKE queries for display
                    $displayValue = trim($displayValue, '%');
                }

                $bladeFilters[$fieldName] = $displayValue;
            } else {
                $bladeFilters[$name] = $filter;
            }
        }

        return $bladeFilters;
    }

    /**
     * Get route information for Blade rendering.
     *
     * @return array
     */
    public function getRouteInfo(): array
    {
        // Get the full route prefix from controller if available
        $routePrefix = '';
        if (property_exists($this->list, 'prefix_route')) {
            $routePrefix = rtrim($this->list->prefix_route, '.') . '.';
        }

        // Construct full route names
        $baseRouteName = $routePrefix . $this->base_route;

        return [
            'index' => $baseRouteName . '.index',
            'create' => $baseRouteName . '.create',
            'show' => $baseRouteName . '.show',
            'edit' => $baseRouteName . '.edit',
            'destroy' => $baseRouteName . '.destroy',
            'filter' => $baseRouteName . '.filter',
            'clear_filter' => $baseRouteName . '.clear_filter',
            'export' => $baseRouteName . '.export',
            'sort' => $baseRouteName . '.sort',
            'toggle_active' => $baseRouteName . '.toggle_active',
            'batch' => [
                'activate' => $baseRouteName . '.batch.activate',
                'deactivate' => $baseRouteName . '.batch.deactivate',
                'delete' => $baseRouteName . '.batch.delete',
            ],
            'parameter' => $this->route_parameter
        ];
    }

    /**
     * Get field rendering data for Blade.
     *
     * @return array
     */
    public function getFieldsForBlade(): array
    {
        $bladeFields = [];

        foreach ($this->fields as $field) {
            $fieldData = [
                'key' => $field->key,
                'title' => $field->title,
                'type' => $this->convertFieldTypeToString($field->type),
                'database_key' => $field->database_key,
                'method' => $field->method,
                'width' => $field->width ?? 'auto',
                'class' => $field->class ?? '',
                'sortable' => isset($field->sort) ? $field->sort : false,
                'searchable' => property_exists($field, 'search') ? $field->search : false,
                'filterable' => (isset($field->attributes['filterable']) && $field->attributes['filterable']) || (property_exists($field, 'filter') && $field->filter),
                'filter_type' => $this->convertFieldTypeToString(property_exists($field, 'filter_type') ? $field->filter_type : $field->type),
                'advanced' => property_exists($field, 'advanced') ? $field->advanced : false, // Enhanced select support
            ];

            // Add filter options if available
            if (property_exists($field, 'select_data') && !empty($field->select_data)) {
                // Convert select_data Collection to array format for blade
                $options = [];
                $idKey = $field->select_id ?? 'id';
                $nameKey = $field->select_title ?? 'name';

                foreach ($field->select_data as $item) {
                    $options[$item[$idKey]] = $item[$nameKey];
                }

                $fieldData['filter_options'] = $options;
            } elseif (property_exists($field, 'options') && !empty($field->options)) {
                $fieldData['filter_options'] = $field->options;
            }

            $bladeFields[] = $fieldData;
        }

        return $bladeFields;
    }

    /**
     * Convert field type constant to string.
     *
     * @param int $type
     * @return string
     */
    protected function convertFieldTypeToString(int $type): string
    {
        return match ($type) {
            1 => 'string',
            2 => 'date',
            3 => 'integer',
            4 => 'bool',
            5 => 'select',
            6 => 'price',
            7 => 'date_time',
            8 => 'time',
            9 => 'password',
            10 => 'hidden',
            11 => 'comment',
            12 => 'file',
            13 => 'editor',
            14 => 'label',
            15 => 'color',
            16 => 'number',
            17 => 'range',
            default => 'string'
        };
    }

    /**
     * Render field value for display.
     *
     * @param object $row Database row
     * @param Field|array $field Field definition
     * @return string
     */
    public function renderFieldValue(object $row, Field|array $field): string
    {
        // Handle array format for Blade compatibility
        if (is_array($field)) {
            $fieldObj = (object) $field;
        } else {
            $fieldObj = $field;
        }

        // If field has a custom method, use controller method
        if (($fieldObj->method ?? false) && $fieldObj->method !== false) {
            if (method_exists($this->list, $fieldObj->method)) {
                return $this->list->{$fieldObj->method}($row,$fieldObj->key);
            }
        }

        // Handle identifier field (row number)
        if (($fieldObj->key ?? '') === ($this->identifier ?? 'id') && !($this->view_id ?? false)) {
            // Calculate current index for pagination
            static $rowIndex = 0;
            $rowIndex++;
            $currentPage = request('page', 1);
            $perPage = $this->per_page ?? 15;
            return (string) ($rowIndex + ($perPage * ($currentPage - 1)));
        }

        // Get the raw value
        $fieldKey = $fieldObj->key ?? $fieldObj->database_key ?? '';
        $value = $row->{$fieldKey} ?? '';

        // Format based on field type
        return $this->formatFieldValue($value, $fieldObj);
    }

    /**
     * Format field value based on its type.
     *
     * @param mixed $value
     * @param object $field
     * @return string
     */
    protected function formatFieldValue(mixed $value, object $field): string
    {
        if ($value === null || $value === '') {
            return '<span class="text-muted">-</span>';
        }

        // Get field type (handle both object property and array key)
        $fieldType = $field->type ?? ($field['type'] ?? 'string');

        return match ($fieldType) {
            'bool', 'boolean' => $this->formatBoolField($value, $field),
            'date' => $this->formatDateField($value),
            'date_time', 'datetime' => $this->formatDateTimeField($value),
            'time' => $this->formatTimeField($value),
            'price', 'money' => $this->formatPriceField($value),
            'number', 'integer', 'int' => $this->formatNumberField($value),
            'image', 'avatar' => $this->formatImageField($value),
            'email' => $this->formatEmailField($value),
            'url', 'link' => $this->formatUrlField($value),
            'badge', 'status' => $this->formatBadgeField($value, $field),
            default => $this->formatStringField($value)
        };
    }

    /**
     * Format boolean field with toggle functionality.
     */
    protected function formatBoolField(mixed $value, object $field): string
    {
        $isActive = (bool) $value;
        $fieldKey = $field->key ?? $field['key'] ?? 'active';

        // Check if controller supports boolean field changes
        $canToggle = $this->list instanceof \RMS\Core\Controllers\Features\Actions\ChangeBoolField;

        if ($canToggle) {
            $toggleUrl = $this->list->boolFieldUrl($this->getCurrentRowId(), $fieldKey);
            $toggleClass = 'ajax_bool';
        } else {
            $toggleUrl = '#';
            $toggleClass = '';
        }

        $buttonClass = $isActive ? 'btn-success' : 'btn-secondary';
        $icon = $isActive ? 'ph-check' : 'ph-x';
        $text = $isActive ? 'فعال' : 'غیرفعال';

        return sprintf(
            '<a href="%s" class="%s"><button type="button" class="btn %s btn-sm"><i class="%s"></i></button></a>',
            $toggleUrl,
            $toggleClass,
            $buttonClass,
            $icon
        );
    }

    /**
     * Format date field.
     */
    protected function formatDateField(mixed $value): string
    {
        try {
            if ($value instanceof \DateTime) {
                return $value->format('Y/m/d');
            }
            return date('Y/m/d', strtotime((string) $value));
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    /**
     * Format datetime field.
     */
    protected function formatDateTimeField(mixed $value): string
    {
        try {
            if ($value instanceof \DateTime) {
                return $value->format('Y/m/d H:i');
            }
            return date('Y/m/d H:i', strtotime((string) $value));
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    /**
     * Format time field.
     */
    protected function formatTimeField(mixed $value): string
    {
        try {
            return date('H:i:s', strtotime((string) $value));
        } catch (\Exception $e) {
            return (string) $value;
        }
    }

    /**
     * Format price/money field.
     */
    protected function formatPriceField(mixed $value): string
    {
        if (is_null($value) || $value === '') {
            return '<span class="text-muted">-</span>';
        }

        $floatValue = (float) $value;

        // Get currency settings from config
        $currencySymbol = config('cms.currency.symbol', 'تومان');
        $currencyPosition = config('cms.currency.position', 'after');
        $decimalPlaces = config('cms.currency.decimal_places', 2);
        $thousandSeparator = config('cms.currency.thousand_separator', ',');
        $decimalSeparator = config('cms.currency.decimal_separator', '.');

        // Format with specified decimals, then remove trailing zeros if decimal_places > 0
        $formatted = number_format($floatValue, $decimalPlaces, $decimalSeparator, $thousandSeparator);

        // Remove trailing zeros only if we have decimal places
        if ($decimalPlaces > 0) {
            $formatted = rtrim(rtrim($formatted, '0'), $decimalSeparator);
        }

        // Add currency symbol based on position
        return $currencyPosition === 'before'
            ? $currencySymbol . ' ' . $formatted
            : $formatted . ' ' . $currencySymbol;
    }

    /**
     * Format number field.
     */
    protected function formatNumberField(mixed $value): string
    {
        return number_format((float) $value);
    }

    /**
     * Format image/avatar field.
     */
    protected function formatImageField(mixed $value): string
    {
        if (empty($value)) {
            return '<span class="text-muted">بدون تصویر</span>';
        }

        return sprintf(
            '<img src="%s" class="rounded-circle" width="40" height="40" alt="">',
            htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Format email field.
     */
    protected function formatEmailField(mixed $value): string
    {
        $email = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        return sprintf('<a href="mailto:%s">%s</a>', $email, $email);
    }

    /**
     * Format URL field.
     */
    protected function formatUrlField(mixed $value): string
    {
        $url = htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
        return sprintf('<a href="%s" target="_blank">%s <i class="ph-arrow-square-out"></i></a>', $url, $url);
    }

    /**
     * Format badge/status field.
     */
    protected function formatBadgeField(mixed $value, object $field): string
    {
        // You can customize badge colors based on value or field options
        $badgeClass = 'bg-primary';

        if (isset($field->options) && is_array($field->options)) {
            // If field has predefined options with colors
            foreach ($field->options as $option) {
                if (isset($option['value']) && $option['value'] == $value) {
                    $badgeClass = $option['class'] ?? 'bg-primary';
                    $value = $option['label'] ?? $value;
                    break;
                }
            }
        }

        return sprintf(
            '<span class="badge %s bg-opacity-20">%s</span>',
            $badgeClass,
            htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Format string field (default).
     */
    protected function formatStringField(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get current row ID (helper for boolean toggle).
     */
    protected function getCurrentRowId(): mixed
    {
        // This is a simple implementation - you might need to adjust based on your needs
        static $currentRow = null;
        return $currentRow ? $currentRow->{$this->identifier ?? 'id'} : null;
    }

    /**
     * Load batch actions from controller that implements HasBatch.
     *
     * @return void
     */
    protected function loadBatchActionsFromController(): void
    {
        if ($this->list instanceof HasBatch) {
            $batchActions = $this->list->getBatchActions();

            // Clear existing batch actions
            $this->batches = [];

            // Add batch actions from controller
            foreach ($batchActions as $batchAction) {
                $this->addBatchAction($batchAction);
            }
        }
    }
}
