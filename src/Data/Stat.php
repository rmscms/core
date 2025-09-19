<?php

declare(strict_types=1);

namespace RMS\Core\Data;

use Illuminate\Database\Query\Builder;

/**
 * Class for handling statistical calculations.
 */
class Stat
{
    /**
     * The query builder instance.
     *
     * @var Builder
     */
    public Builder $builder;

    /**
     * The label for the statistic.
     *
     * @var string
     */
    public string $label;

    /**
     * The column to calculate statistics on.
     *
     * @var string
     */
    public string $column;

    /**
     * The calculated value.
     *
     * @var mixed
     */
    public mixed $value = null;

    /**
     * The type of statistic.
     *
     * @var string|null
     */
    public ?string $type = null;

    /**
     * Stat constructor.
     *
     * @param Builder $builder
     * @param string $label
     * @param string $column
     */
    public function __construct(Builder $builder, string $label, string $column)
    {
        $this->builder = $builder;
        $this->label = $label;
        $this->column = $column;
    }

    /**
     * Calculate count statistic.
     *
     * @return $this
     */
    public function count(): self
    {
        $this->value = $this->builder->count();
        $this->type = 'count';
        return $this;
    }

    /**
     * Calculate sum statistic.
     *
     * @return $this
     */
    public function sum(): self
    {
        $this->value = $this->builder->sum($this->column);
        $this->type = 'sum';
        return $this;
    }

    /**
     * Calculate average statistic.
     *
     * @return $this
     */
    public function average(): self
    {
        $this->value = $this->builder->avg($this->column);
        $this->type = 'average';
        return $this;
    }

    /**
     * Calculate minimum statistic.
     *
     * @return $this
     */
    public function min(): self
    {
        $this->value = $this->builder->min($this->column);
        $this->type = 'min';
        return $this;
    }

    /**
     * Calculate maximum statistic.
     *
     * @return $this
     */
    public function max(): self
    {
        $this->value = $this->builder->max($this->column);
        $this->type = 'max';
        return $this;
    }

    /**
     * Calculate grouped statistics.
     *
     * @param string $groupColumn
     * @param string $operation
     * @return $this
     */
    public function grouped(string $groupColumn, string $operation = 'count'): self
    {
        $query = $this->builder->groupBy($groupColumn);
        
        switch ($operation) {
            case 'sum':
                $this->value = $query->selectRaw("{$groupColumn}, SUM({$this->column}) as value")->pluck('value', $groupColumn)->toArray();
                break;
            case 'avg':
            case 'average':
                $this->value = $query->selectRaw("{$groupColumn}, AVG({$this->column}) as value")->pluck('value', $groupColumn)->toArray();
                break;
            case 'min':
                $this->value = $query->selectRaw("{$groupColumn}, MIN({$this->column}) as value")->pluck('value', $groupColumn)->toArray();
                break;
            case 'max':
                $this->value = $query->selectRaw("{$groupColumn}, MAX({$this->column}) as value")->pluck('value', $groupColumn)->toArray();
                break;
            case 'count':
            default:
                $this->value = $query->selectRaw("{$groupColumn}, COUNT(*) as value")->pluck('value', $groupColumn)->toArray();
                break;
        }
        
        $this->type = "grouped_{$operation}";
        return $this;
    }

    /**
     * Get the statistic value.
     *
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * Get the statistic as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'value' => $this->value,
            'type' => $this->type,
            'column' => $this->column
        ];
    }
}
