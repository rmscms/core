<?php

namespace RMS\Core\Contracts\Filter;

/**
 * Interface for classes that support filtering.
 */
interface ShouldFilter
{
    /**
     * Determine if filters should be cached.
     *
     * @return bool
     */
    public function cacheFilter(): bool;

    /**
     * Get the filter data.
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Get dynamic filters for advanced filtering.
     *
     * @return array
     */
    public function getDynamicFilters(): array;
}
