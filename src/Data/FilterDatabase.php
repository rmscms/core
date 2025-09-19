<?php

declare(strict_types=1);

namespace RMS\Core\Data;

/**
 * Class for representing database filter conditions.
 * 
 * This class encapsulates the information needed to apply a filter
 * to a database query, including the column, operator, value, and type.
 */
class FilterDatabase
{
    /**
     * The database column to filter on.
     *
     * @var string
     */
    public string $column;

    /**
     * The comparison operator (=, >, <, LIKE, etc.).
     *
     * @var string
     */
    public string $operator;

    /**
     * The value to filter by.
     *
     * @var mixed
     */
    public mixed $value;

    /**
     * The field type (from Field constants).
     *
     * @var int
     */
    public int $type;

    /**
     * FilterDatabase constructor.
     *
     * @param string $column The database column name
     * @param string $operator The comparison operator
     * @param mixed $value The filter value
     * @param int $type The field type
     */
    public function __construct(string $column, string $operator, mixed $value, int $type)
    {
        $this->column = $column;
        $this->operator = $operator;
        $this->value = $value;
        $this->type = $type;
    }

    /**
     * Apply this filter to a query builder.
     *
     * @param \Illuminate\Database\Query\Builder|\Illuminate\Database\Eloquent\Builder $query
     * @return void
     */
    public function applyToQuery($query): void
    {
        switch ($this->operator) {
            case 'LIKE':
                $query->where($this->column, 'LIKE', $this->value);
                break;
            case '=':
                $query->where($this->column, '=', $this->value);
                break;
            case '>=':
                $query->where($this->column, '>=', $this->value);
                break;
            case '<=':
                $query->where($this->column, '<=', $this->value);
                break;
            case '<':
                $query->where($this->column, '<', $this->value);
                break;
            case '>':
                $query->where($this->column, '>', $this->value);
                break;
            case '!=':
            case '<>':
                $query->where($this->column, '!=', $this->value);
                break;
            case 'IS NOT NULL':
                $query->whereNotNull($this->column);
                break;
            case 'IS NULL':
                $query->whereNull($this->column);
                break;
            default:
                $query->where($this->column, $this->operator, $this->value);
                break;
        }
    }

    /**
     * Get the SQL representation of this filter.
     *
     * @return array [column, operator, value]
     */
    public function toSql(): array
    {
        return [$this->column, $this->operator, $this->value];
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'column' => $this->column,
            'operator' => $this->operator,
            'value' => $this->value,
            'type' => $this->type
        ];
    }

    /**
     * String representation for debugging.
     *
     * @return string
     */
    public function __toString(): string
    {
        return "{$this->column} {$this->operator} " . 
               (is_string($this->value) ? "'{$this->value}'" : $this->value);
    }
}
