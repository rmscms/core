<?php

namespace RMS\Core\Contracts\Batch;

/**
 * Interface for classes that support batch actions.
 */
interface HasBatch
{
    /**
     * Get the batch actions.
     *
     * @return array
     */
    public function getBatchActions(): array;

    /**
     * Check if a batch action can be performed.
     *
     * @param string $action
     * @return bool
     */
    public function canPerformBatchAction(string $action): bool;
}
