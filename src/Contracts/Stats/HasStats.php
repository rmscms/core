<?php

namespace RMS\Core\Contracts\Stats;

/**
 * Interface for classes that provide statistics.
 */
interface HasStats
{
    /**
     * Get the statistics data.
     *
     * @return array
     */
    public function getStats(): array;

    /**
     * Get a summary of statistics.
     *
     * @return array
     */
    public function getStatSummary(): array;
}
