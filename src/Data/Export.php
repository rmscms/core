<?php

namespace RMS\Core\Data;

/**
 * Trait for managing field export properties.
 */
trait Export
{
    /**
     * Whether the field is hidden in export.
     *
     * @var bool
     */
    public bool $hidden_in_export = false;

    /**
     * Hide the field in export.
     *
     * @return $this
     */
    public function hiddenInExport(): self
    {
        $this->hidden_in_export = true;
        return $this;
    }
}
