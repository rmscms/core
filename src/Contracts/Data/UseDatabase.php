<?php

namespace RMS\Core\Contracts\Data;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

/**
 * Interface for database interaction in controllers.
 */
interface UseDatabase
{
    /**
     * Return the table name for data retrieval.
     *
     * @return string
     */
    public function table(): string;

    /**
     * Modify the query builder instance.
     *
     * @param Builder $sql
     * @return void
     */
    public function query(Builder $sql): void;

    /**
     * Load a specific model instance by ID or return a new instance.
     *
     * @param int|string|null $id
     * @return Model|null
     */
    public function model(int|string|null $id = null): ?Model;

    /**
     * Return the model class name.
     *
     * @return string
     */
    public function modelName(): string;
}
