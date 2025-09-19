<?php

declare(strict_types=1);

namespace RMS\Core\Data;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RMS\Core\Data\Field;
use RMS\Core\Traits\Database\DatabaseQueryBuilder;
use RMS\Core\Traits\Database\DatabaseJoins;
use RMS\Core\Traits\Database\DatabaseSorting;
use RMS\Core\Traits\Database\DatabaseValidation;
use RMS\Core\Traits\Database\DatabaseSecurity;
use RMS\Core\Traits\Database\DatabaseUtilities;
use InvalidArgumentException;

/**
 * Enhanced database query builder for RMS CMS with trait-based architecture.
 * 
 * کلاس پیشرفته سازنده کوئری برای سیستم RMS با معماری مبتنی بر trait
 * All functionality is provided by traits for better separation of concerns.
 * 
 * @package RMS\Core\Data
 */
class Database
{
    use DatabaseQueryBuilder;
    use DatabaseJoins;
    use DatabaseSorting;
    use DatabaseValidation;
    use DatabaseSecurity;
    use DatabaseUtilities;

    /**
     * The Query Builder instance.
     */
    public Builder $sql;

    /**
     * Array of fields for the query.
     */
    protected array $fields;

    /**
     * Selected columns for the query.
     */
    protected array $select = [];

    /**
     * The database table name.
     */
    protected string $table;

    /**
     * Table alias for the main query.
     */
    protected string $alias = 'a';

    /**
     * Applied filters storage.
     */
    protected array $appliedFilters = [];

    /**
     * Applied sorting configuration.
     */
    protected ?array $appliedSorting = null;

    /**
     * Database constructor.
     * 
     * سازنده کلاس با اعتبارسنجی و مقداردهی اولیه
     *
     * @param array $fields Array of Field instances
     * @param string $table Table name
     * @param string $alias Table alias (default: 'a')
     * @throws InvalidArgumentException
     */
    public function __construct(array $fields, string $table, string $alias = 'a')
    {
        $this->validateTable($table);
        $this->validateFields($fields);
        
        $this->fields = $fields;
        $this->table = $this->sanitizeTableName($table);
        $this->alias = $alias;
        $this->generateSql();
    }

    /**
     * Static factory method for creating Database instances.
     *
     * @param array $fields
     * @param string $table
     * @param string $alias
     * @return static
     */
    public static function make(array $fields, string $table, string $alias = 'a'): static
    {
        return new static($fields, $table, $alias);
    }

    /**
     * Create Database instance from table and auto-generate basic fields.
     *
     * @param string $table
     * @param array $columns
     * @param string $alias
     * @return static
     */
    public static function fromTable(string $table, array $columns = ['*'], string $alias = 'a'): static
    {
        $fields = [];
        foreach ($columns as $column) {
            if ($column === '*') {
                $fields[] = Field::make('all', '*');
                break;
            }
            $fields[] = Field::make($column);
        }
        
        return new static($fields, $table, $alias);
    }

    /**
     * Get paginated results with validation.
     *
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get(int $perPage = 15, int $page = 1)
    {
        $this->validatePagination($page, $perPage);
        return $this->sql->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Set query limit.
     *
     * @param int $limit
     * @return $this
     * @throws InvalidArgumentException
     */
    public function limit(int $limit): self
    {
        if ($limit <= 0) {
            throw new InvalidArgumentException('Limit must be greater than 0');
        }
        
        $this->sql->limit($limit);
        return $this;
    }

    /**
     * Set query offset.
     *
     * @param int $offset
     * @return $this
     * @throws InvalidArgumentException
     */
    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new InvalidArgumentException('Offset must be >= 0');
        }
        
        $this->sql->offset($offset);
        return $this;
    }

    /**
     * Generate the Query Builder instance.
     * 
     * ایجاد نمونه Query Builder با تنظیمات اولیه
     *
     * @return Builder
     */
    protected function generateSql(): Builder
    {
        $this->buildSelectClause();
        $this->sql = DB::table($this->table . " AS {$this->alias}")->select($this->select);
        $this->applySecurityConstraints();
        
        return $this->sql;
    }

    /**
     * Build SELECT clause from fields.
     *
     * @return void
     */
    protected function buildSelectClause(): void
    {
        foreach ($this->fields as $field) {
            $this->addFieldToSql($field);
        }
        
        if (empty($this->select)) {
            $this->select = ["*"];
        }
    }

    /**
     * Add field to SQL select clause.
     *
     * @param Field $field
     * @return void
     */
    protected function addFieldToSql(Field $field): void
    {
        if (!$field->database_key) {
            $this->select[] = "{$this->alias}.{$field->key}";
            return;
        }
        
        if ($field->method_sql) {
            $this->select[] = DB::raw($field->database_key . " as {$field->key}");
        } else {
            $columnName = $this->prefixColumn($field->database_key);
            $this->select[] = $columnName . " as {$field->key}";
        }
    }

    /**
     * Prefix column with table alias if needed.
     *
     * @param string $column
     * @return string
     */
    protected function prefixColumn(string $column): string
    {
        if (strpos($column, '.') !== false || strpos($column, '(') !== false) {
            return $column;
        }
        
        return "{$this->alias}.{$column}";
    }

    /**
     * Get first record from query.
     *
     * @return object|null
     */
    public function first(): ?object
    {
        return $this->sql->first();
    }

    /**
     * Count records matching the query.
     *
     * @return int
     */
    public function count(): int
    {
        return $this->sql->count();
    }
}
