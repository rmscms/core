<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Filter;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Data\Field;

/**
 * Trait for handling list sorting functionality.
 * 
 * @package RMS\Core\Traits\Filter
 */
trait Sortable
{
    /**
     * Valid sort directions.
     */
    protected array $validSortDirections = ['asc', 'desc'];

    /**
     * Default sort direction.
     */
    protected string $defaultSortDirection = 'asc';

    /**
     * Handle sorting request.
     *
     * @param string $orderBy
     * @param string $orderWay
     * @return RedirectResponse
     */
    public function sort(string $orderBy, string $orderWay): RedirectResponse
    {
        try {
            $this->validateSortRequest($orderBy, $orderWay);
            
            $sortData = $this->prepareSortData($orderBy, $orderWay);
            
            if ($sortData) {
                $this->cacheSortData($sortData);
            }
            
            return back()->with('success', 'Sort applied successfully!');
            
        } catch (\InvalidArgumentException $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /**
     * Validate sort request parameters.
     *
     * @param string $orderBy
     * @param string $orderWay
     * @return void
     * @throws \InvalidArgumentException
     */
    protected function validateSortRequest(string $orderBy, string $orderWay): void
    {
        // Validate sort direction
        if (!in_array(strtolower($orderWay), $this->validSortDirections)) {
            throw new \InvalidArgumentException('Invalid sort direction');
        }

        // Validate sort column
        if (!$this->isValidSortColumn($orderBy)) {
            throw new \InvalidArgumentException('Invalid sort column');
        }
    }

    /**
     * Check if the column is valid for sorting.
     *
     * @param string $column
     * @return bool
     */
    protected function isValidSortColumn(string $column): bool
    {
        if (!($this instanceof HasList)) {
            return false;
        }

        $fields = $this->getListFields();
        
        foreach ($fields as $field) {
            if ($field instanceof Field && $field->sort && $field->key === $column) {
                return true;
            }
            
            // Handle array format
            if (is_array($field) && 
                ($field['sort'] ?? false) && 
                ($field['key'] ?? '') === $column) {
                return true;
            }
        }

        return false;
    }

    /**
     * Prepare sort data for caching.
     *
     * @param string $orderBy
     * @param string $orderWay
     * @return array|null
     */
    protected function prepareSortData(string $orderBy, string $orderWay): ?array
    {
        if (!($this instanceof HasList)) {
            return null;
        }

        $fields = $this->getListFields();
        $column = null;
        $fieldKey = null;

        foreach ($fields as $field) {
            if ($field instanceof Field) {
                if ($field->sort && $field->key === $orderBy) {
                    $column = $field->filter_key ?: ($field->database_key ?: $field->key);
                    $fieldKey = $field->key;
                    break;
                }
            } elseif (is_array($field)) {
                if (($field['sort'] ?? false) && ($field['key'] ?? '') === $orderBy) {
                    $column = $field['filter_key'] ?? ($field['database_key'] ?? $field['key']);
                    $fieldKey = $field['key'];
                    break;
                }
            }
        }

        if (!$column) {
            return null;
        }

        return [
            'order_by' => $column,
            'order_way' => strtolower($orderWay),
            'field_key' => $fieldKey
        ];
    }

    /**
     * Cache sort data.
     *
     * @param array $sortData
     * @return void
     */
    protected function cacheSortData(array $sortData): void
    {
        $cacheKeyPrefix = $this->getSortCacheKey();
        
        Cache::forever($cacheKeyPrefix . 'order_by', $sortData['order_by']);
        Cache::forever($cacheKeyPrefix . 'order_way', $sortData['order_way']);
        Cache::forever($cacheKeyPrefix . 'field_ordered', $sortData['field_key']);
    }

    /**
     * Get current sort column.
     *
     * @return string|null
     */
    public function orderBy(): ?string
    {
        return Cache::get($this->getSortCacheKey() . 'order_by');
    }

    /**
     * Get current sort direction.
     *
     * @return string
     */
    public function orderWay(): string
    {
        return Cache::get($this->getSortCacheKey() . 'order_way', $this->defaultSortDirection);
    }

    /**
     * Get currently ordered field key.
     *
     * @return string|null
     */
    public function fieldOrdered(): ?string
    {
        return Cache::get($this->getSortCacheKey() . 'field_ordered');
    }

    /**
     * Clear sort settings.
     *
     * @return $this
     */
    public function clearSort(): self
    {
        $cacheKeyPrefix = $this->getSortCacheKey();
        
        Cache::forget($cacheKeyPrefix . 'order_by');
        Cache::forget($cacheKeyPrefix . 'order_way');
        Cache::forget($cacheKeyPrefix . 'field_ordered');
        
        return $this;
    }

    /**
     * Get sort cache key prefix.
     *
     * @return string
     */
    protected function getSortCacheKey(): string
    {
        $baseKey = 'sort' . class_basename($this);
        
        if ($this instanceof AdminController) {
            return $baseKey . (auth('admin')->user()->id ?? 'guest');
        }
        
        return $baseKey . (auth()->user()->id ?? 'guest');
    }

    /**
     * Get sort configuration.
     *
     * @return array
     */
    public function getSortConfig(): array
    {
        return [
            'order_by' => $this->orderBy(),
            'order_way' => $this->orderWay(),
            'field_ordered' => $this->fieldOrdered(),
            'valid_directions' => $this->validSortDirections,
            'default_direction' => $this->defaultSortDirection
        ];
    }

    /**
     * Set default sort direction.
     *
     * @param string $direction
     * @return $this
     */
    public function setDefaultSortDirection(string $direction): self
    {
        if (in_array(strtolower($direction), $this->validSortDirections)) {
            $this->defaultSortDirection = strtolower($direction);
        }
        
        return $this;
    }

    /**
     * Check if a field is currently being sorted.
     *
     * @param string $fieldKey
     * @return bool
     */
    public function isFieldSorted(string $fieldKey): bool
    {
        return $this->fieldOrdered() === $fieldKey;
    }

    /**
     * Get sort URL for a specific field.
     *
     * @param string $fieldKey
     * @param string|null $direction
     * @return string
     */
    public function getSortUrl(string $fieldKey, ?string $direction = null): string
    {
        if (!$direction) {
            // If field is currently sorted ascending, make it descending, otherwise ascending
            $direction = ($this->isFieldSorted($fieldKey) && $this->orderWay() === 'asc') ? 'desc' : 'asc';
        }
        
        return route($this->prefix_route . $this->baseRoute() . '.sort', [
            'order_by' => $fieldKey,
            'order_way' => $direction
        ]);
    }

    /**
     * Get sort icon class for a field.
     *
     * @param string $fieldKey
     * @return string
     */
    public function getSortIcon(string $fieldKey): string
    {
        if (!$this->isFieldSorted($fieldKey)) {
            return 'fa-sort';
        }
        
        return $this->orderWay() === 'asc' ? 'fa-sort-up' : 'fa-sort-down';
    }
}
