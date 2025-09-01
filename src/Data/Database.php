<?php

namespace RMS\Core\Data;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use RMS\Core\Data\Column;
use RMS\Core\Data\Field;
use InvalidArgumentException;

/**
 * Enhanced database query builder for RMS CMS.
 * 
 * This class provides a fluent interface for building complex queries
 * for forms and lists with security and performance optimizations.
 */
class Database
{
    /**
     * The Query Builder instance.
     *
     * @var Builder
     */
    public Builder $sql;

    /**
     * Array of fields for the query.
     *
     * @var array
     */
    protected array $fields;

    /**
     * Selected columns for the query.
     *
     * @var array
     */
    protected array $select = [];

    /**
     * The database table name.
     *
     * @var string
     */
    protected string $table;

    /**
     * Table alias for the main query.
     *
     * @var string
     */
    protected string $alias = 'a';

    /**
     * Applied filters.
     *
     * @var array
     */
    protected array $appliedFilters = [];

    /**
     * Applied sorting.
     *
     * @var array|null
     */
    protected ?array $appliedSorting = null;

    /**
     * Query optimization hints.
     *
     * @var array
     */
    protected array $optimizations = [];

    /**
     * Security constraints.
     *
     * @var array
     */
    protected array $securityConstraints = [];

    /**
     * Database constructor.
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
                // For wildcard, create a basic field
                $fields[] = Field::make('all', '*');
                break;
            }
            $fields[] = Field::make($column);
        }
        
        return new static($fields, $table, $alias);
    }

    /**
     * Add array of filters to the query with validation.
     *
     * @param array $filters
     * @return $this
     * @throws InvalidArgumentException
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
     * @throws InvalidArgumentException
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
    public function where(string $column, string $operator, $value): self
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
     * Get paginated results.
     *
     * @param int $perPage
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function get(int $perPage = 15, int $page = 1)
    {
        $this->validatePerPage($perPage);
        
        return $this->sql->paginate($perPage, ['*'], 'page', $page);
    }

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
     * Add GROUP BY to the query.
     *
     * @param string|array $columns
     * @return $this
     */
    public function groupBy($columns): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
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
    public function having(string $column, string $operator, $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);
        
        $this->sql->having($column, $operator, $value);
        return $this;
    }

    /**
     * Set query limit.
     *
     * @param int $limit
     * @return $this
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
     * @return Builder
     */
    protected function generateSql(): Builder
    {
        // Build select clause from fields
        $this->buildSelectClause();
        
        // Create query builder with table alias
        $this->sql = DB::table($this->table . " AS {$this->alias}")->select($this->select);
        
        // Apply security constraints if any
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
        
        // If no fields selected, select all
        if (empty($this->select)) {
            $this->select = ["*"];
        }
    }

    /**
     * Add a field to the SQL select clause with enhanced logic.
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
            // For SQL methods, use raw SQL
            $this->select[] = DB::raw($field->database_key . " as {$field->key}");
        } else {
            // For regular columns, use table alias
            $columnName = $this->prefixColumn($field->database_key);
            $this->select[] = $columnName . " as {$field->key}";
        }
    }

    /**
     * Prefix column name with table alias if needed.
     *
     * @param string $column
     * @return string
     */
    protected function prefixColumn(string $column): string
    {
        // If column already has a table prefix or is a function, don't add alias
        if (strpos($column, '.') !== false || strpos($column, '(') !== false) {
            return $column;
        }
        
        return "{$this->alias}.{$column}";
    }

    /**
     * Apply security constraints to the query.
     *
     * @return void
     */
    protected function applySecurityConstraints(): void
    {
        foreach ($this->securityConstraints as $constraint) {
            $this->sql->where($constraint['column'], $constraint['operator'], $constraint['value']);
        }
    }

    /**
     * Add security constraint to always apply.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function addSecurityConstraint(string $column, string $operator, $value): self
    {
        $this->validateColumn($column);
        $this->validateOperator($operator);
        
        $this->securityConstraints[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        return $this;
    }

    /**
     * Get query SQL string for debugging.
     *
     * @return string
     */
    public function toSql(): string
    {
        return $this->sql->toSql();
    }

    /**
     * Get query bindings for debugging.
     *
     * @return array
     */
    public function getBindings(): array
    {
        return $this->sql->getBindings();
    }

    /**
     * Get applied filters.
     *
     * @return array
     */
    public function getAppliedFilters(): array
    {
        return $this->appliedFilters;
    }

    /**
     * Get applied sorting.
     *
     * @return array|null
     */
    public function getAppliedSorting(): ?array
    {
        return $this->appliedSorting;
    }

    /**
     * Get the underlying query builder for advanced usage.
     *
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->sql;
    }

    /**
     * Get the table name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the table alias.
     *
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * Get the fields array.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * Validate table name for security.
     *
     * @param string $table
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateTable(string $table): void
    {
        if (empty($table)) {
            throw new InvalidArgumentException('Table name cannot be empty');
        }
        
        // Check for SQL injection patterns
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table)) {
            throw new InvalidArgumentException('Invalid table name format');
        }
    }

    /**
     * Validate fields array.
     *
     * @param array $fields
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFields(array $fields): void
    {
        if (empty($fields)) {
            throw new InvalidArgumentException('Fields array cannot be empty');
        }
        
        foreach ($fields as $field) {
            if (!$field instanceof Field) {
                throw new InvalidArgumentException('All fields must be instances of Field class');
            }
        }
    }

    /**
     * Validate column name for security.
     *
     * @param string $column
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateColumn(string $column): void
    {
        if (empty($column)) {
            throw new InvalidArgumentException('Column name cannot be empty');
        }
        
        // Allow table.column format, SQL functions, and special characters like * in functions
        if (!preg_match('/^[a-zA-Z0-9_.()\*\s]+$/', $column)) {
            throw new InvalidArgumentException('Invalid column name format');
        }
    }

    /**
     * Validate SQL operator.
     *
     * @param string $operator
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateOperator(string $operator): void
    {
        $allowedOperators = [
            '=', '!=', '<>', '<', '>', '<=', '>=',
            'LIKE', 'NOT LIKE', 'ILIKE', 'NOT ILIKE',
            'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN',
            'IS NULL', 'IS NOT NULL'
        ];
        
        if (!in_array(strtoupper($operator), $allowedOperators)) {
            throw new InvalidArgumentException('Invalid SQL operator');
        }
    }

    /**
     * Validate sort direction.
     *
     * @param string $direction
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateSortDirection(string $direction): void
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'])) {
            throw new InvalidArgumentException('Sort direction must be ASC or DESC');
        }
    }

    /**
     * Validate per page value.
     *
     * @param int $perPage
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validatePerPage(int $perPage): void
    {
        if ($perPage <= 0 || $perPage > 1000) {
            throw new InvalidArgumentException('Per page must be between 1 and 1000');
        }
    }

    /**
     * Validate filter object.
     *
     * @param Column $filter
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFilter(Column $filter): void
    {
        $this->validateColumn($filter->column);
        $this->validateOperator($filter->operator);
        
        // Additional validation for specific operators
        if (in_array(strtoupper($filter->operator), ['IN', 'NOT IN']) && !is_array($filter->value)) {
            throw new InvalidArgumentException('IN/NOT IN operators require array values');
        }
    }

    /**
     * Sanitize table name.
     *
     * @param string $table
     * @return string
     */
    protected function sanitizeTableName(string $table): string
    {
        return preg_replace('/[^a-zA-Z0-9_]/', '', $table);
    }

    /**
     * Sanitize search term.
     *
     * @param string $term
     * @return string
     */
    protected function sanitizeSearchTerm(string $term): string
    {
        // Remove dangerous characters and limit length
        $term = trim($term);
        $term = substr($term, 0, 255);
        return addslashes($term);
    }
}
