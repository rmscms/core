<?php

namespace RMS\Core\Data;

/**
 * Trait for managing field filters.
 */
trait Filter
{
    /**
     * Whether the field is sortable.
     *
     * @var bool
     */
    public bool $sort = false;

    /**
     * Custom filter key for cached filters.
     *
     * @var string|bool
     */
    public string|bool $filter_key = false;

    /**
     * Whether the field has filtering enabled.
     *
     * @var bool
     */
    public bool $filter = true;

    /**
     * The type of filter (e.g., string, integer, select).
     *
     * @var int
     */
    public int $filter_type = Field::STRING;

    /**
     * Whether autocomplete is enabled for the filter.
     *
     * @var bool
     */
    public bool $auto_complete = false;

    /**
     * Disable filtering for this field.
     *
     * @return $this
     */
    public function disableFilter(): self
    {
        $this->filter = false;
        return $this;
    }

    /**
     * Enable autocomplete for this filter.
     *
     * @return $this
     */
    public function enableAutoComplete(): self
    {
        $this->auto_complete = true;
        return $this;
    }

    /**
     * Set the filter type.
     *
     * @param int $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function filterType(int $type): self
    {
        if (!in_array($type, [
            Field::STRING, Field::DATE, Field::INTEGER, Field::BOOL, Field::SELECT,
            Field::PRICE, Field::DATE_TIME, Field::TIME, Field::PASSWORD, Field::HIDDEN,
            Field::COMMENT, Field::FILE, Field::EDITOR, Field::LABEL, Field::COLOR,
            Field::NUMBER, Field::RANGE
        ])) {
            throw new \InvalidArgumentException('Invalid filter type');
        }
        $this->filter_type = $type;
        return $this;
    }

    /**
     * Set the custom filter key.
     *
     * @param string $key
     * @return $this
     */
    public function filterKey(string $key): self
    {
        $this->filter_key = $key;
        return $this;
    }

    /**
     * Alias for filterKey method for consistency.
     *
     * @param string $key
     * @return $this
     */
    public function setFilterKey(string $key): self
    {
        return $this->filterKey($key);
    }

    /**
     * Enable sorting for this field.
     *
     * @return $this
     */
    public function sortable(): self
    {
        $this->sort = true;
        return $this;
    }
}
