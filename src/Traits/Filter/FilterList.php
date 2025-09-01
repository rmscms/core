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
     * Get cached filters for the current controller.
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->getCachedFilters($this, !$this->cacheFilter());
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
            return $baseKey . $this->admin->id;
        }
        
        return $baseKey . auth()->guard()->user()->id;
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
     * Check if current controller should filter.
     *
     * @return bool
     */
    protected function shouldFilter(): bool
    {
        return $this instanceof ShouldFilter;
    }
}
