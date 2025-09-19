<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Stats;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use RMS\Core\Data\Stat;
use RMS\Helper\Helper;

/**
 * Trait for handling statistics functionality.
 * 
 * @package RMS\Core\Traits\Stats
 */
trait Statable
{
    /**
     * Statistics collection.
     *
     * @var array<string, Stat>
     */
    protected array $stats = [];

    /**
     * Add a statistic to the collection.
     *
     * @param Stat $stat
     * @param string|null $key
     * @return $this
     */
    public function withStat(Stat $stat, ?string $key = null): self
    {
        if ($key === null) {
            $this->stats[] = $stat;
        } else {
            $this->stats[$key] = $stat;
        }
        
        return $this;
    }

    /**
     * Add multiple statistics to the collection.
     *
     * @param array<string, Stat> $stats
     * @return $this
     */
    public function withStats(array $stats): self
    {
        foreach ($stats as $key => $stat) {
            $this->withStat($stat, is_string($key) ? $key : null);
        }
        
        return $this;
    }

    /**
     * Remove a statistic from the collection.
     *
     * @param string $key
     * @return $this
     */
    public function removeStat(string $key): self
    {
        unset($this->stats[$key]);
        return $this;
    }

    /**
     * Get a specific statistic.
     *
     * @param string $key
     * @return Stat|null
     */
    public function getStat(string $key): ?Stat
    {
        return $this->stats[$key] ?? null;
    }

    /**
     * Get all statistics.
     *
     * @return array<string, Stat>
     */
    public function getStats(): array
    {
        return $this->stats;
    }

    /**
     * Clear all statistics.
     *
     * @return $this
     */
    public function clearStats(): self
    {
        $this->stats = [];
        return $this;
    }

    /**
     * Pass statistics to template variables.
     *
     * @return $this
     */
    public function statsToTpl(): self
    {
        $this->view->withVariables(['stats' => $this->stats]);
        return $this;
    }

    /**
     * Add count statistic.
     *
     * @param Builder $builder
     * @param string|null $key
     * @param string $label
     * @return $this
     */
    public function statsCount(Builder $builder, ?string $key = 'total', ?string $label = null): self
    {
        $label = $label ?? trans('admin.count');
        $stat = (new Stat($builder, $label, '*'))->count();
        
        return $this->withStat($stat, $key);
    }

    /**
     * Add sum statistic.
     *
     * @param Builder $builder
     * @param string $column
     * @param bool $formatAsAmount
     * @param string|null $key
     * @param string|null $label
     * @return $this
     */
    public function statsSum(
        Builder $builder,
        string $column,
        bool $formatAsAmount = false,
        ?string $key = null,
        ?string $label = null
    ): self {
        $label = $label ?? trans('admin.sum');
        $stat = (new Stat($builder, $label, $column))->sum();

        if ($formatAsAmount) {
            $stat->value = Helper::displayAmount($stat->value);
        }

        $key = $key ?? "sum_{$column}";
        
        return $this->withStat($stat, $key);
    }

    /**
     * Add average statistic.
     *
     * @param Builder $builder
     * @param string $column
     * @param bool $formatAsAmount
     * @param string|null $key
     * @param string|null $label
     * @return $this
     */
    public function statsAverage(
        Builder $builder,
        string $column,
        bool $formatAsAmount = false,
        ?string $key = null,
        ?string $label = null
    ): self {
        $label = $label ?? trans('admin.average');
        $stat = (new Stat($builder, $label, $column))->average();

        if ($formatAsAmount) {
            $stat->value = Helper::displayAmount($stat->value);
        }

        $key = $key ?? "avg_{$column}";
        
        return $this->withStat($stat, $key);
    }

    /**
     * Add minimum value statistic.
     *
     * @param Builder $builder
     * @param string $column
     * @param bool $formatAsAmount
     * @param string|null $key
     * @param string|null $label
     * @return $this
     */
    public function statsMin(
        Builder $builder,
        string $column,
        bool $formatAsAmount = false,
        ?string $key = null,
        ?string $label = null
    ): self {
        $label = $label ?? trans('admin.minimum');
        $stat = (new Stat($builder, $label, $column))->min();

        if ($formatAsAmount) {
            $stat->value = Helper::displayAmount($stat->value);
        }

        $key = $key ?? "min_{$column}";
        
        return $this->withStat($stat, $key);
    }

    /**
     * Add maximum value statistic.
     *
     * @param Builder $builder
     * @param string $column
     * @param bool $formatAsAmount
     * @param string|null $key
     * @param string|null $label
     * @return $this
     */
    public function statsMax(
        Builder $builder,
        string $column,
        bool $formatAsAmount = false,
        ?string $key = null,
        ?string $label = null
    ): self {
        $label = $label ?? trans('admin.maximum');
        $stat = (new Stat($builder, $label, $column))->max();

        if ($formatAsAmount) {
            $stat->value = Helper::displayAmount($stat->value);
        }

        $key = $key ?? "max_{$column}";
        
        return $this->withStat($stat, $key);
    }

    /**
     * Add grouped statistics.
     *
     * @param Builder $builder
     * @param string $groupColumn
     * @param string $valueColumn
     * @param string $operation
     * @param string|null $key
     * @param string|null $label
     * @return $this
     */
    public function statsGrouped(
        Builder $builder,
        string $groupColumn,
        string $valueColumn,
        string $operation = 'count',
        ?string $key = null,
        ?string $label = null
    ): self {
        $label = $label ?? trans("admin.grouped_{$operation}");
        $stat = (new Stat($builder, $label, $valueColumn))->grouped($groupColumn, $operation);

        $key = $key ?? "grouped_{$operation}_{$groupColumn}";
        
        return $this->withStat($stat, $key);
    }

    /**
     * Add multiple common statistics at once.
     *
     * @param Builder $builder
     * @param array $columns
     * @param bool $formatAmounts
     * @return $this
     */
    public function addCommonStats(Builder $builder, array $columns = [], bool $formatAmounts = false): self
    {
        // Always add total count
        $this->statsCount($builder);

        // Add sum and average for specified columns
        foreach ($columns as $column) {
            $this->statsSum($builder, $column, $formatAmounts);
            $this->statsAverage($builder, $column, $formatAmounts);
        }

        return $this;
    }

    /**
     * Get statistics as a collection.
     *
     * @return Collection
     */
    public function getStatsCollection(): Collection
    {
        return collect($this->stats);
    }

    /**
     * Get statistics formatted for JSON response.
     *
     * @return array
     */
    public function getStatsForJson(): array
    {
        return array_map(function (Stat $stat) {
            return [
                'label' => $stat->label,
                'value' => $stat->value,
                'type' => $stat->type ?? 'default'
            ];
        }, $this->stats);
    }

    /**
     * Check if statistics are available.
     *
     * @return bool
     */
    public function hasStats(): bool
    {
        return !empty($this->stats);
    }

    /**
     * Get statistics count.
     *
     * @return int
     */
    public function getStatsCount(): int
    {
        return count($this->stats);
    }
}
