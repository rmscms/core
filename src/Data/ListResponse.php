<?php

namespace RMS\Core\Data;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use RMS\Core\View\HelperList\Generator;

/**
 * Class for wrapping list response data.
 */
class ListResponse
{
    /**
     * The paginated data rows.
     *
     * @var LengthAwarePaginator|Paginator
     */
    public $rows;

    /**
     * The Generator instance.
     *
     * @var Generator
     */
    public $feature;

    /**
     * The list fields.
     *
     * @var array
     */
    public $fields;

    /**
     * Additional metadata for the response.
     *
     * @var array
     */
    public array $meta = [];

    /**
     * ListResponse constructor.
     *
     * @param LengthAwarePaginator|Paginator $rows
     * @param Generator $feature
     * @param array $fields
     */
    public function __construct($rows, Generator $feature, array $fields)
    {
        if (!$rows instanceof Paginator) {
            throw new \InvalidArgumentException('Rows must be a paginator instance');
        }
        $this->rows = $rows;
        $this->feature = $feature;
        $this->fields = $fields;
    }

    /**
     * Get the paginated rows.
     *
     * @return LengthAwarePaginator|Paginator
     */
    public function getRows(): LengthAwarePaginator|Paginator
    {
        return $this->rows;
    }

    /**
     * Get the Generator instance.
     *
     * @return Generator
     */
    public function getFeature(): Generator
    {
        return $this->feature;
    }

    /**
     * Get the list fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Add metadata to the response.
     *
     * @param array $meta
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'rows' => $this->rows->toArray(),
            'fields' => $this->fields,
            'feature' => [
                'create' => $this->feature->create,
                'base_route' => $this->feature->base_route,
                'per_page' => $this->feature->per_page,
                'simple_pagination' => $this->feature->simple_pagination,
            ],
            'meta' => $this->meta,
        ];
    }

    /**
     * Get data optimized for Blade rendering.
     *
     * @return array
     */
    public function toBladeData(): array
    {
        // Get active filters from the generator
        $activeFilters = method_exists($this->feature, 'getActiveFilters') 
            ? $this->feature->getActiveFilters() 
            : [];

        // Get route information
        $routeInfo = method_exists($this->feature, 'getRouteInfo') 
            ? $this->feature->getRouteInfo() 
            : [];

        // Get configuration data
        $config = method_exists($this->feature, 'getBladeRenderingConfig') 
            ? $this->feature->getBladeRenderingConfig() 
            : [];

        // Get fields formatted for Blade
        $bladeFields = method_exists($this->feature, 'getFieldsForBlade') 
            ? $this->feature->getFieldsForBlade() 
            : $this->fields;

        return [
            'rows' => $this->rows,
            'pagination' => [
                'current_page' => $this->rows->currentPage(),
                'last_page' => method_exists($this->rows, 'lastPage') ? $this->rows->lastPage() : 1,
                'per_page' => $this->rows->perPage(),
                'total' => method_exists($this->rows, 'total') ? $this->rows->total() : $this->rows->count(),
                'from' => method_exists($this->rows, 'firstItem') ? $this->rows->firstItem() : 1,
                'to' => method_exists($this->rows, 'lastItem') ? $this->rows->lastItem() : $this->rows->count(),
                'has_more_pages' => $this->rows->hasMorePages(),
                'links' => method_exists($this->rows, 'links') ? $this->rows->links() : '',
            ],
            'fields' => $bladeFields,
            'config' => array_merge([
                'create_button' => $this->feature->create ?? false,
                'base_route' => $this->feature->base_route ?? '',
                'route_parameter' => $this->feature->route_parameter ?? 'id',
                'per_page' => $this->feature->per_page ?? 15,
                'simple_pagination' => $this->feature->simple_pagination ?? false,
                'batch_destroy' => $this->feature->batch_destroy ?? false,
                'batch_active' => $this->feature->batch_active ?? false,
                'identifier' => $this->feature->identifier ?? 'id',
                'view_id' => $this->feature->view_id ?? null,
            ], $config),
            'routes' => $routeInfo,
            'active_filters' => $activeFilters,
            'has_filters' => !empty($activeFilters),
            'has_pagination' => $this->hasPagination(),
            'meta' => $this->meta,
            'actions' => [
                'row_actions' => $this->feature->actions ?? [],
                'batch_actions' => $this->feature->batches ?? [],
                'has_row_actions' => !empty($this->feature->actions ?? []),
                'has_batch_actions' => !empty($this->feature->batches ?? []),
            ],
        ];
    }

    /**
     * Check if pagination is needed.
     *
     * @return bool
     */
    public function hasPagination(): bool
    {
        if (method_exists($this->rows, 'lastPage')) {
            return $this->rows->lastPage() > 1;
        }
        
        return $this->rows->hasMorePages();
    }

    /**
     * Render field value using the generator.
     *
     * @param object $row Database row
     * @param array|object $field Field definition
     * @return string
     */
    public function renderField(object $row, array|object $field): string
    {
        if (method_exists($this->feature, 'renderFieldValue')) {
            return $this->feature->renderFieldValue($row, $field);
        }
        
        // Fallback to simple field rendering
        $fieldKey = is_array($field) ? ($field['key'] ?? $field['database_key'] ?? '') : $field->key;
        $value = $row->{$fieldKey} ?? '';
        
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
