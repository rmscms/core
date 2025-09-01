<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Data;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Data\UseDatabase;

/**
 * Trait for database helper functionality.
 * 
 * @package RMS\Core\Traits\Data
 */
trait UseDatabaseHelper
{
    /**
     * Get model instance by ID or create new instance.
     *
     * @param int|string|null $id
     * @return Model|null
     */
    public function model(int|string|null $id = null): ?Model
    {
        try {
            $modelClass = $this->modelName();
            
            if (!class_exists($modelClass)) {
                throw new \InvalidArgumentException("Model class {$modelClass} does not exist");
            }
            
            if ($id === null) {
                return new $modelClass;
            }
            
            return $modelClass::find($id);
        } catch (\Throwable $e) {
            Log::error('Model instantiation failed', [
                'controller' => get_class($this),
                'model_class' => $this->modelName(),
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            
            return null;
        }
    }

    /**
     * Get model instance by ID or fail.
     *
     * @param int|string $id
     * @return Model
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function modelOrFail(int|string $id): Model
    {
        $modelClass = $this->modelName();
        return $modelClass::findOrFail($id);
    }

    /**
     * Create a new model instance with data.
     *
     * @param array $data
     * @return Model
     */
    public function createModel(array $data = []): Model
    {
        $modelClass = $this->modelName();
        return new $modelClass($data);
    }

    /**
     * Get query builder for the model.
     *
     * @return EloquentBuilder
     */
    public function getModelQuery(): EloquentBuilder
    {
        $modelClass = $this->modelName();
        return $modelClass::query();
    }

    /**
     * Customize the query builder.
     * Override this method to modify the base query.
     *
     * @param Builder $sql
     * @return void
     */
    public function query(Builder $sql): void
    {
        // Override in child classes to customize the query
    }

    /**
     * Apply filters to the query builder.
     *
     * @param EloquentBuilder $query
     * @param array $filters
     * @return EloquentBuilder
     */
    protected function applyFilters(EloquentBuilder $query, array $filters): EloquentBuilder
    {
        foreach ($filters as $filter => $value) {
            if ($value !== null && $value !== '') {
                $this->applyFilter($query, $filter, $value);
            }
        }
        
        return $query;
    }

    /**
     * Apply a single filter to the query.
     *
     * @param EloquentBuilder $query
     * @param string $filter
     * @param mixed $value
     * @return void
     */
    protected function applyFilter(EloquentBuilder $query, string $filter, mixed $value): void
    {
        // Default filter application - override in child classes for custom logic
        if (is_array($value)) {
            $query->whereIn($filter, $value);
        } else {
            $query->where($filter, 'like', "%{$value}%");
        }
    }

    /**
     * Apply sorting to the query builder.
     *
     * @param EloquentBuilder $query
     * @param string|null $orderBy
     * @param string $orderWay
     * @return EloquentBuilder
     */
    protected function applySorting(EloquentBuilder $query, ?string $orderBy, string $orderWay = 'asc'): EloquentBuilder
    {
        if ($orderBy) {
            $orderWay = in_array(strtolower($orderWay), ['asc', 'desc']) ? $orderWay : 'asc';
            $query->orderBy($orderBy, $orderWay);
        }
        
        return $query;
    }

    /**
     * Get paginated results from query.
     *
     * @param EloquentBuilder $query
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function getPaginatedResults(EloquentBuilder $query, int $perPage = 15): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $query->paginate($perPage);
    }

    /**
     * Get the model class name.
     * Must be implemented by the using class.
     *
     * @return string
     */
    abstract public function modelName(): string;

    /**
     * Check if model exists by ID.
     *
     * @param int|string $id
     * @return bool
     */
    public function modelExists(int|string $id): bool
    {
        $modelClass = $this->modelName();
        return $modelClass::where('id', $id)->exists();
    }

    /**
     * Get model count with optional filters.
     *
     * @param array $filters
     * @return int
     */
    public function getModelCount(array $filters = []): int
    {
        $query = $this->getModelQuery();
        
        if (!empty($filters)) {
            $this->applyFilters($query, $filters);
        }
        
        return $query->count();
    }

    /**
     * Get model sum for a specific column with optional filters.
     *
     * @param string $column
     * @param array $filters
     * @return float|int
     */
    public function getModelSum(string $column, array $filters = []): float|int
    {
        $query = $this->getModelQuery();
        
        if (!empty($filters)) {
            $this->applyFilters($query, $filters);
        }
        
        return $query->sum($column) ?? 0;
    }

    /**
     * Get model average for a specific column with optional filters.
     *
     * @param string $column
     * @param array $filters
     * @return float|int
     */
    public function getModelAverage(string $column, array $filters = []): float|int
    {
        $query = $this->getModelQuery();
        
        if (!empty($filters)) {
            $this->applyFilters($query, $filters);
        }
        
        return $query->avg($column) ?? 0;
    }
}
