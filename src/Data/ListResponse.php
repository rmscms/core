<?php

namespace RMS\Core\Data;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use RMS\Core\View\HelperList\Generator;

/**
 * Class for wrapping list response data.
 */
class ListResponse
{
    /**
     * The paginated data rows.
     *
     * @var LengthAwarePaginator|Paginator
     */
    public $rows;

    /**
     * The Generator instance.
     *
     * @var Generator
     */
    public $feature;

    /**
     * The list fields.
     *
     * @var array
     */
    public $fields;

    /**
     * Additional metadata for the response.
     *
     * @var array
     */
    public array $meta = [];

    /**
     * ListResponse constructor.
     *
     * @param LengthAwarePaginator|Paginator $rows
     * @param Generator $feature
     * @param array $fields
     */
    public function __construct($rows, Generator $feature, array $fields)
    {
        if (!$rows instanceof Paginator) {
            throw new \InvalidArgumentException('Rows must be a paginator instance');
        }
        $this->rows = $rows;
        $this->feature = $feature;
        $this->fields = $fields;
    }

    /**
     * Get the paginated rows.
     *
     * @return LengthAwarePaginator|Paginator
     */
    public function getRows(): LengthAwarePaginator|Paginator
    {
        return $this->rows;
    }

    /**
     * Get the Generator instance.
     *
     * @return Generator
     */
    public function getFeature(): Generator
    {
        return $this->feature;
    }

    /**
     * Get the list fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Add metadata to the response.
     *
     * @param array $meta
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'rows' => $this->rows->toArray(),
            'fields' => $this->fields,
            'feature' => [
                'create' => $this->feature->create,
                'base_route' => $this->feature->base_route,
                'per_page' => $this->feature->per_page,
                'simple_pagination' => $this->feature->simple_pagination,
            ],
            'meta' => $this->meta,
        ];
    }
}
