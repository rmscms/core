<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Database;

use InvalidArgumentException;

/**
 * Trait for database sorting operations.
 * 
 * @package RMS\Core\Traits\Database
 */
trait DatabaseSorting
{
    /**
     * Apply sorting to the query.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function sort(string $column, string $direction = 'ASC'): self
    {
        $this->validateColumn($column);
        $this->validateSortDirection($direction);
        
        $this->appliedSorting = [
            'column' => $column,
            'direction' => strtolower($direction)
        ];
        
        $this->sql->orderBy($column, $direction);
        return $this;
    }

    /**
     * Apply multiple sorting rules.
     *
     * @param array $sortRules
     * @return $this
     */
    public function multiSort(array $sortRules): self
    {
        if (empty($sortRules)) {
            return $this;
        }
        
        foreach ($sortRules as $rule) {
            if (!is_array($rule) || count($rule) < 2) {
                continue; // Skip invalid rules
            }
            
            $column = $rule[0];
            $direction = $rule[1] ?? 'ASC';
            
            try {
                $this->validateColumn($column);
                $this->validateSortDirection($direction);
                
                $this->sql->orderBy($column, $direction);
            } catch (InvalidArgumentException $e) {
                // Skip invalid rules gracefully
                continue;
            }
        }
        
        return $this;
    }

    /**
     * Order by raw SQL expression.
     *
     * @param string $sql
     * @param array $bindings
     * @return $this
     */
    public function orderByRaw(string $sql, array $bindings = []): self
    {
        $this->sql->orderByRaw($sql, $bindings);
        return $this;
    }

    /**
     * Order by descending.
     *
     * @param string $column
     * @return $this
     */
    public function orderByDesc(string $column): self
    {
        return $this->sort($column, 'DESC');
    }

    /**
     * Order by ascending.
     *
     * @param string $column
     * @return $this
     */
    public function orderByAsc(string $column): self
    {
        return $this->sort($column, 'ASC');
    }

    /**
     * Add latest ordering (ORDER BY created_at DESC).
     *
     * @param string $column
     * @return $this
     */
    public function latest(string $column = 'created_at'): self
    {
        return $this->orderByDesc($column);
    }

    /**
     * Add oldest ordering (ORDER BY created_at ASC).
     *
     * @param string $column
     * @return $this
     */
    public function oldest(string $column = 'created_at'): self
    {
        return $this->orderByAsc($column);
    }

    /**
     * Clear all ordering from the query.
     *
     * @return $this
     */
    public function clearOrdering(): self
    {
        $this->sql->reorder();
        $this->appliedSorting = null;
        return $this;
    }

    /**
     * Get applied sorting information.
     *
     * @return array|null
     */
    public function getAppliedSorting(): ?array
    {
        return $this->appliedSorting;
    }

    /**
     * Check if sorting is applied.
     *
     * @return bool
     */
    public function hasSorting(): bool
    {
        return $this->appliedSorting !== null;
    }

    /**
     * Apply random ordering.
     *
     * @return $this
     */
    public function inRandomOrder(): self
    {
        $this->sql->inRandomOrder();
        $this->appliedSorting = [
            'column' => 'RANDOM',
            'direction' => 'random'
        ];
        return $this;
    }
}
