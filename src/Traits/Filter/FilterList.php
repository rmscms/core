<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Filter;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Units\FilterOptions;

/**
 * Trait for handling list filtering functionality.
 * 
 * @package RMS\Core\Traits\Filter
 */
trait FilterList
{
    /**
     * Process filter request and redirect with applied filters.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function filter(Request $request): RedirectResponse
    {
        $controller = $this->controller($request);

        // Handle filter reset
        if ($request->has('resetFilter')) {
            $this->clearFilters($controller);
            return redirect($this->getFilterRedirectUrl($controller));
        }

        if ($controller instanceof UseDatabase) {
            $filters = $this->processFilters($request, $controller->getListFields());
            
            if (count($filters) > 0) {
                $this->cacheFilters($controller, $filters);
                return redirect($this->getFilterRedirectUrl($controller));
            }
            
            return redirect($this->getFilterRedirectUrl($controller))
                ->withErrors(trans('admin.filter_is_empty'));
        }

        return redirect($this->getFilterRedirectUrl($controller));
    }

    /**
     * Clear filters and redirect to index.
     *
     * @return RedirectResponse
     */
    public function clearFilter(): RedirectResponse
    {
        $this->clearFilters($this);
        return redirect($this->getFilterRedirectUrl($this));
    }

    /**
     * Get cached filters for the current controller.
     *
     * @return array
     */
    public function getCachedFilterData(): array
    {
        return $this->getCachedFilters($this, !$this->cacheFilter());
    }

    /**
     * Get cached dynamic filters for the current controller.
     * These are user-applied filters that have been cached.
     *
     * @return array Array of FilterDatabase objects
     */
    public function getFilters(): array
    {
        return $this->getCachedFilterData();
    }

    /**
     * Get static filters that are always applied.
     * Override in child classes to provide custom static filters.
     *
     * @return array Array of FilterDatabase objects
     */
    public function getStaticFilters(): array
    {
        return [];
    }

    /**
     * Get all filters combined (static + dynamic).
     * Static filters are always applied, dynamic filters come from cache.
     *
     * @return array Array of FilterDatabase objects
     */
    public function getAllFilters(): array
    {
        $staticFilters = $this->getStaticFilters();
        $dynamicFilters = $this->getFilters();
        
        return array_merge($staticFilters, $dynamicFilters);
    }

    /**
     * Process filter parameters from request.
     *
     * @param Request $request
     * @param array $fields
     * @return array
     */
    protected function processFilters(Request $request, array $fields): array
    {
        return FilterOptions::processFilters($request, $fields);
    }

    /**
     * Get cached filters for a specific controller.
     *
     * @param object $controller
     * @param bool $oneTime
     * @return array
     */
    protected function getCachedFilters(object $controller, bool $oneTime = false): array
    {
        $key = $this->getFilterCacheKey($controller);
        
        if ($oneTime) {
            $filters = Cache::get($key, []);
            Cache::forget($key);
            return $filters;
        }
        
        return Cache::get($key, []);
    }

    /**
     * Cache filters for a specific controller.
     *
     * @param object $controller
     * @param array $filters
     * @return void
     */
    protected function cacheFilters(object $controller, array $filters): void
    {
        $key = $this->getFilterCacheKey($controller);
        Cache::forever($key, $filters);
    }

    /**
     * Clear cached filters for a specific controller.
     *
     * @param object $controller
     * @return void
     */
    protected function clearFilters(object $controller): void
    {
        $key = $this->getFilterCacheKey($controller);
        Cache::forget($key);
    }

    /**
     * Get cache key for controller filters.
     *
     * @param object $controller
     * @return string
     */
    protected function getFilterCacheKey(object $controller): string
    {
        $baseKey = 'filter' . get_class($controller);
        
        if ($controller instanceof AdminController) {
            // Handle console environment where admin might be null
            if (property_exists($this, 'admin') && $this->admin && isset($this->admin->id)) {
                return $baseKey . $this->admin->id;
            }
            // Fallback for console/testing environment
            return $baseKey . '_console_test';
        }
        
        // Handle regular auth guard
        if (auth()->guard()->check()) {
            return $baseKey . auth()->guard()->user()->id;
        }
        
        // Fallback for testing/console environment
        return $baseKey . '_guest';
    }

    /**
     * Determine if controller should cache filters.
     *
     * @return bool
     */
    public function cacheFilter(): bool
    {
        return true;
    }

    /**
     * Get redirect URL after filter processing.
     *
     * @param object $controller
     * @return string
     */
    protected function getFilterRedirectUrl(object $controller): string
    {
        return route($controller->prefix_route . $controller->baseRoute() . '.index');
    }

    /**
     * Get dynamic filters for advanced filtering.
     * Override in child classes for custom dynamic filters.
     *
     * @return array
     */
    public function getDynamicFilters(): array
    {
        return [];
    }

    /**
     * Check if current controller should filter.
     *
     * @return bool
     */
    protected function shouldFilter(): bool
    {
        return $this instanceof ShouldFilter;
    }
}
