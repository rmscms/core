<?php

namespace RMS\Core\Data;

use Illuminate\Database\Query\Builder;
use RMS\Core\Controllers\Features\Filter\Column;

/**
 * Class for managing database queries for list generation.
 */
class Database
{
    /**
     * The Query Builder instance.
     *
     * @var Builder
     */
    public $sql;

    /**
     * Array of fields for the query.
     *
     * @var array
     */
    protected $fields;

    /**
     * Selected columns for the query.
     *
     * @var array
     */
    protected $select = [];

    /**
     * The database table name.
     *
     * @var string
     */
    protected $table;

    /**
     * Database constructor.
     *
     * @param array $fields
     * @param string $table
     */
    public function __construct(array $fields, string $table)
    {
        $this->fields = $fields;
        $this->table = $table;
        $this->generateSql();
    }

    /**
     * Add array of filters to the query.
     *
     * @param array $filters
     * @return void
     */
    public function withFilters(array $filters): void
    {
        foreach ($filters as $filter) {
            $this->filter($filter);
        }
    }

    /**
     * Apply a single filter to the query.
     *
     * @param Column $filter
     * @return void
     */
    protected function filter(Column $filter): void
    {
        $this->sql->where($filter->column, $filter->operator, $filter->value);
    }

    /**
     * Retrieve paginated data from the database.
     *
     * @param int $per_page
     * @param bool $simple
     * @return \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Pagination\Paginator
     */
    public function get(int $per_page, bool $simple = false): \Illuminate\Pagination\LengthAwarePaginator|\Illuminate\Pagination\Paginator
    {
        return $simple ? $this->sql->simplePaginate($per_page) : $this->sql->paginate($per_page);
    }

    /**
     * Generate the Query Builder instance.
     *
     * @return Builder
     */
    protected function generateSql(): Builder
    {
        foreach ($this->fields as $field) {
            $this->addFieldToSql($field);
        }
        $this->sql = \DB::table($this->table . ' AS a')->select($this->select);
        return $this->sql;
    }

    /**
     * Add a field to the SQL select clause.
     *
     * @param Field $field
     * @return void
     */
    protected function addFieldToSql(Field $field): void
    {
        if ($field->database_key) {
            if ($field->method_sql) {
                $this->select[] = \DB::raw($field->database_key);
            } else {
                $this->select[] = $field->database_key . ' as ' . $field->key;
            }
        } else {
            $this->select[] = $field->key;
        }
    }

    /**
     * Apply sorting to the query.
     *
     * @param string $order_by
     * @param string $order_way
     * @return void
     */
    public function sort(string $order_by, string $order_way): void
    {
        $this->sql->orderBy($order_by, $order_way);
    }
}
