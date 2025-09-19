<?php

namespace RMS\Core\Contracts\Export;

/**
 * Interface for classes that support exporting data.
 */
interface ShouldExport
{
    /**
     * Export the data in the specified format.
     *
     * @param string $format
     * @return mixed
     */
    public function export(string $format = 'csv'): mixed;
}
