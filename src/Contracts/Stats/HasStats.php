<?php

namespace RMS\Core\Contracts\Stats;

use Illuminate\Database\Query\Builder;
use RMS\Core\Data\StatCard;

/**
 * Interface for classes that provide statistics.
 */
interface HasStats
{
    /**
     * Get the statistics data.
     * 
     * @param Builder|null $query Optional query builder with applied filters
     * @return StatCard[] Array of StatCard instances
     */
    public function getStats(?Builder $query = null): array;

    /**
     * Get a summary of statistics.
     * 
     * @param Builder|null $query Optional query builder with applied filters
     * @return array Raw summary data for API/calculations
     */
    public function getStatSummary(?Builder $query = null): array;
}
