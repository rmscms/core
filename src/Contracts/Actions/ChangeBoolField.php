<?php

namespace RMS\Core\Contracts\Actions;

/**
 * Interface for classes that manage boolean field changes.
 */
interface ChangeBoolField
{
    /**
     * Get the boolean fields.
     *
     * @return array
     */
    public function boolFields(): array;

    /**
     * Get the URL for changing a boolean field.
     *
     * @param mixed $id
     * @param string $key
     * @return string
     */
    public function boolFieldUrl($id, string $key): string;
}
