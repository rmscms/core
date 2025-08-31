<?php

namespace RMS\Core\Contracts\Filter;

/**
 * Interface for classes that support sorting.
 */
interface HasSort
{
    /**
     * Get the column for sorting.
     *
     * @return string|null
     */
    public function orderBy(): ?string;

    /**
     * Get the sort direction (ASC or DESC).
     *
     * @return string
     */
    public function orderWay(): string;

    /**
     * Get the ordered field.
     *
     * @return string|null
     */
    public function fieldOrdered(): ?string;
}
