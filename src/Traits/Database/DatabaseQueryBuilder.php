<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Database;

use RMS\Core\Data\Column;

/**
 * Trait for basic database query building operations.
 * 
 * @package RMS\Core\Traits\Database
 */
trait DatabaseQueryBuilder
{
    /**
     * Add array of filters to the query with validation.
     *
     * @param array $filters
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function withFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            $this->filter($filter);
        }
        return $this;
    }

    /**
     * Apply a single filter to the query with security validation.
     *
     * @param Column $filter
     * @return $this
     * @throws \InvalidArgumentException
     */
    protected function filter(Column $filter): self
    {
        $this->validateFilter($filter);
        
        // Store applied filter for debugging/logging
        $this->appliedFilters[] = [
            'column' => $filter->column,
            'operator' => $filter->operator,
            'value' => $filter->value
        ];
        
        $this->sql->where($filter->column, $filter->operator, $filter->value);
        return $this;
    }

    /**
     * Add WHERE condition with validation.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function where(string $column, string $operator, mixed $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);
        
        $this->sql->where($column, $operator, $value);
        return $this;
    }

    /**
     * Add WHERE IN condition.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereIn(string $column, array $values): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereIn($column, $values);
        return $this;
    }

    /**
     * Add WHERE NOT IN condition.
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function whereNotIn(string $column, array $values): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereNotIn($column, $values);
        return $this;
    }

    /**
     * Add WHERE NULL condition.
     *
     * @param string $column
     * @return $this
     */
    public function whereNull(string $column): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereNull($column);
        return $this;
    }

    /**
     * Add WHERE NOT NULL condition.
     *
     * @param string $column
     * @return $this
     */
    public function whereNotNull(string $column): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereNotNull($column);
        return $this;
    }

    /**
     * Add date range filter.
     *
     * @param string $column
     * @param string $startDate
     * @param string $endDate
     * @return $this
     */
    public function whereDateBetween(string $column, string $startDate, string $endDate): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereBetween($column, [$startDate, $endDate]);
        return $this;
    }

    /**
     * Add WHERE BETWEEN condition.
     *
     * @param string $column
     * @param mixed $min
     * @param mixed $max
     * @return $this
     */
    public function whereBetween(string $column, mixed $min, mixed $max): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereBetween($column, [$min, $max]);
        return $this;
    }

    /**
     * Add WHERE NOT BETWEEN condition.
     *
     * @param string $column
     * @param mixed $min
     * @param mixed $max
     * @return $this
     */
    public function whereNotBetween(string $column, mixed $min, mixed $max): self
    {
        $this->validateColumn($column);
        
        $this->sql->whereNotBetween($column, [$min, $max]);
        return $this;
    }

    /**
     * Add search across multiple columns.
     *
     * @param string $term
     * @param array $columns
     * @return $this
     */
    public function search(string $term, array $columns): self
    {
        $term = $this->sanitizeSearchTerm($term);
        
        if (empty($term)) {
            return $this;
        }
        
        $this->sql->where(function($query) use ($term, $columns) {
            foreach ($columns as $i => $column) {
                $this->validateColumn($column);
                
                if ($i === 0) {
                    $query->where($column, 'LIKE', "%{$term}%");
                } else {
                    $query->orWhere($column, 'LIKE', "%{$term}%");
                }
            }
        });
        
        return $this;
    }

    /**
     * Add OR WHERE condition.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);
        
        $this->sql->orWhere($column, $operator, $value);
        return $this;
    }

    /**
     * Add WHERE LIKE condition.
     *
     * @param string $column
     * @param string $value
     * @param bool $exact
     * @return $this
     */
    public function whereLike(string $column, string $value, bool $exact = false): self
    {
        $this->validateColumn($column);
        
        $searchValue = $exact ? $value : "%{$value}%";
        $this->sql->where($column, 'LIKE', $searchValue);
        
        return $this;
    }
}
