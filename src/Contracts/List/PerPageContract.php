<?php

namespace RMS\Core\Contracts\List;

/**
 * Interface for managing per-page pagination settings.
 */
interface PerPageContract
{
    /**
     * Get the number of items per page.
     *
     * @return int
     */
    public function getPerPage(): int;
}
