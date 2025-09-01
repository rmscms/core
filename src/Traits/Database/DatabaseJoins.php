<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Database;

/**
 * Trait for database join operations.
 * 
 * @package RMS\Core\Traits\Database
 */
trait DatabaseJoins
{
    /**
     * Add JOIN to the query.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @param string $type
     * @return $this
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): self
    {
        $this->validateTable($table);
        $this->validateColumn($first);
        $this->validateColumn($second);
        
        $this->sql->join($table, $first, $operator, $second, $type);
        return $this;
    }

    /**
     * Add LEFT JOIN to the query.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * Add RIGHT JOIN to the query.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * Add INNER JOIN to the query.
     *
     * @param string $table
     * @param string $first
     * @param string $operator
     * @param string $second
     * @return $this
     */
    public function innerJoin(string $table, string $first, string $operator, string $second): self
    {
        return $this->join($table, $first, $operator, $second, 'inner');
    }

    /**
     * Add GROUP BY to the query.
     *
     * @param string|array $columns
     * @return $this
     */
    public function groupBy(string|array $columns): self
    {
        if (is_string($columns)) {
            $columns = [$columns];
        }

        foreach ($columns as $column) {
            $this->validateColumn($column);
        }

        $this->sql->groupBy($columns);
        return $this;
    }

    /**
     * Add HAVING condition to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function having(string $column, string $operator, mixed $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);
        
        $this->sql->having($column, $operator, $value);
        return $this;
    }

    /**
     * Add HAVING RAW condition to the query.
     *
     * @param string $sql
     * @param array $bindings
     * @return $this
     */
    public function havingRaw(string $sql, array $bindings = []): self
    {
        $this->sql->havingRaw($sql, $bindings);
        return $this;
    }

    /**
     * Add complex join with closure.
     *
     * @param string $table
     * @param \Closure $callback
     * @param string $type
     * @return $this
     */
    public function joinAdvanced(string $table, \Closure $callback, string $type = 'inner'): self
    {
        $this->validateTable($table);
        
        $this->sql->join($table, $callback, null, null, $type);
        return $this;
    }

    /**
     * Add LEFT JOIN with closure.
     *
     * @param string $table
     * @param \Closure $callback
     * @return $this
     */
    public function leftJoinAdvanced(string $table, \Closure $callback): self
    {
        return $this->joinAdvanced($table, $callback, 'left');
    }
}
