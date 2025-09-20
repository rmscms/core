<?php

namespace RMS\Core\Data;

use Illuminate\Support\Str;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\List\HasList;
use InvalidArgumentException;

/**
 * Enhanced list generator with improved Database integration.
 * 
 * This class generates lists with full Database class support,
 * providing fluent interfaces, advanced filtering, and performance optimizations.
 */
class ListGenerator
{
    use Actions;

    /**
     * Determine create button is active or not
     *
     * @var bool
     */
    public bool $create = true;

    /**
     * Identifier key in each row
     *
     * @var string
     */
    public string $identifier = 'id';

    /**
     * If set to false, loop from foreach begins from 1
     *
     * @var bool
     */
    public bool $view_id = true;

    /**
     * Base route parameter
     *
     * @var string|null
     */
    public ?string $route_parameter;

    /**
     * Display rows per page
     *
     * @var int
     */
    public int $per_page = 20;

    /**
     * Simple pagination
     *
     * @var bool
     */
    public bool $simple_pagination = false;

    /**
     * Fields
     *
     * @var array
     */
    protected array $fields = [];

    /**
     * List instance implementing HasList
     *
     * @var HasList
     */
    protected HasList $list;

    /**
     * Base route
     *
     * @var string
     */
    public string $base_route;

    /**
     * Array of links
     *
     * @var array
     */
    public array $links = [];

    /**
     * Enable batch destroy
     *
     * @var bool
     */
    public bool $batch_destroy = true;

    /**
     * Enable batch active
     *
     * @var bool
     */
    public bool $batch_active = false;

    /**
     * Database instance for enhanced query building.
     *
     * @var Database|null
     */
    protected ?Database $database = null;

    /**
     * Applied search term.
     *
     * @var string|null
     */
    protected ?string $searchTerm = null;

    /**
     * Custom filters to apply.
     *
     * @var array
     */
    protected array $customFilters = [];

    /**
     * ListGenerator constructor.
     *
     * @param HasList $list
     * @throws InvalidArgumentException
     */
    public function __construct(HasList $list)
    {
        $this->list = $list;
        $this->route_parameter = $list->routeParameter();
        $this->base_route = $list->baseRoute();
        $this->fields = $list->getListFields();
        
        $this->validateFields();
        $this->initializeDatabase();
    }

    /**
     * Static factory method for creating ListGenerator instances.
     *
     * @param HasList $list
     * @return static
     */
    public static function make(HasList $list): static
    {
        return new static($list);
    }

    /**
     * Initialize Database instance if list uses database.
     * Filters out fields marked with skip_database.
     *
     * @return void
     */
    protected function initializeDatabase(): void
    {
        if ($this->list instanceof UseDatabase) {
            try {
                $tableName = $this->list->table();
                
                // ✅ فیلتر کردن فیلدهای skip_database
                $databaseFields = $this->filterSkipDatabaseFields($this->fields);
                
                $this->database = Database::make($databaseFields, $tableName);
                
                // Apply security constraints if available
                $this->applyListSecurityConstraints();
                
            } catch (\Exception $e) {
                error_log('ListGenerator: Could not initialize Database - ' . $e->getMessage());
                $this->database = null;
            }
        }
    }

    /**
     * Apply security constraints from list to database.
     *
     * @return void
     */
    protected function applyListSecurityConstraints(): void
    {
        if (!$this->database || !method_exists($this->list, 'getSecurityConstraints')) {
            return;
        }

        $constraints = $this->list->getSecurityConstraints();
        
        if (is_array($constraints)) {
            foreach ($constraints as $constraint) {
                if (isset($constraint['column'], $constraint['operator'], $constraint['value'])) {
                    $this->database->addSecurityConstraint(
                        $constraint['column'],
                        $constraint['operator'],
                        $constraint['value']
                    );
                }
            }
        }
    }

    /**
     * Validate that all fields are Field instances.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    protected function validateFields(): void
    {
        foreach ($this->fields as $field) {
            if (!$field instanceof Field) {
                throw new InvalidArgumentException('All fields must be instances of Field class');
            }
        }
    }

    /**
     * Generate the list response with enhanced functionality.
     *
     * @return ListResponse
     * @throws \Exception
     */
    public function generate(): ListResponse
    {
        if (!($this->list instanceof UseDatabase)) {
            throw new \Exception('This class should implement: ' . UseDatabase::class);
        }

        $database = $this->buildQuery();
        $data = $database->get($this->perPage(), 1, $this->simple_pagination);
        
        return new ListResponse($data, $this, $this->fields);
    }

    /**
     * Build the enhanced database query with all features.
     * Ensures skip_database fields are filtered out.
     *
     * @return Database
     */
    protected function buildQuery(): Database
    {
        // ✅ فیلتر کردن فیلدهای skip_database
        $databaseFields = $this->filterSkipDatabaseFields($this->fields);
        $database = $this->database ?: new Database($databaseFields, $this->list->table());
        
        // Allow list to customize the raw query
        if (method_exists($this->list, 'query')) {
            $this->list->query($database->getQueryBuilder());
        }
        
        // Apply filters from list
        $this->applyListFilters($database);
        
        // Apply search if available
        $this->applySearch($database);
        
        // Apply custom filters
        $this->applyCustomFilters($database);
        
        // Apply sorting
        $this->applySorting($database);
        
        return $database;
    }

    /**
     * Apply filters from the list implementation.
     *
     * @param Database $database
     * @return void
     */
    protected function applyListFilters(Database $database): void
    {
        if ($this->list instanceof ShouldFilter) {
            $filters = $this->list->getFilters();
            if (!empty($filters)) {
                $database->withFilters($filters);
            }
        }
    }

    /**
     * Apply search functionality if search term is set.
     *
     * @param Database $database
     * @return void
     */
    protected function applySearch(Database $database): void
    {
        if ($this->searchTerm && method_exists($this->list, 'getSearchableColumns')) {
            $searchableColumns = $this->list->getSearchableColumns();
            if (!empty($searchableColumns)) {
                $database->search($this->searchTerm, $searchableColumns);
            }
        }
    }

    /**
     * Apply custom filters.
     *
     * @param Database $database
     * @return void
     */
    protected function applyCustomFilters(Database $database): void
    {
        if (!empty($this->customFilters)) {
            $database->withFilters($this->customFilters);
        }
    }

    /**
     * Apply sorting from the list.
     *
     * @param Database $database
     * @return void
     */
    protected function applySorting(Database $database): void
    {
        if ($this->list instanceof HasSort && $orderBy = $this->list->orderBy()) {
            $database->sort($orderBy, $this->list->orderWay());
        }
    }

    /**
     * Build the database query (legacy method for compatibility).
     *
     * @return Database
     */
    public function builder(): Database
    {
        return $this->buildQuery();
    }

    /**
     * Set search term for the list.
     *
     * @param string $searchTerm
     * @return $this
     */
    public function search(string $searchTerm): self
    {
        $this->searchTerm = trim($searchTerm);
        return $this;
    }

    /**
     * Add custom filter to the list.
     *
     * @param Column $filter
     * @return $this
     */
    public function addFilter(Column $filter): self
    {
        $this->customFilters[] = $filter;
        return $this;
    }

    /**
     * Add multiple custom filters to the list.
     *
     * @param array $filters
     * @return $this
     */
    public function addFilters(array $filters): self
    {
        foreach ($filters as $filter) {
            if ($filter instanceof Column) {
                $this->customFilters[] = $filter;
            }
        }
        return $this;
    }

    /**
     * Set custom per page value.
     *
     * @param int $perPage
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setPerPage(int $perPage): self
    {
        if ($perPage <= 0 || $perPage > 1000) {
            throw new InvalidArgumentException('Per page must be between 1 and 1000');
        }
        
        $this->per_page = $perPage;
        return $this;
    }

    /**
     * Enable/disable simple pagination.
     *
     * @param bool $simple
     * @return $this
     */
    public function useSimplePagination(bool $simple = true): self
    {
        $this->simple_pagination = $simple;
        return $this;
    }

    /**
     * Enable/disable create button.
     *
     * @param bool $show
     * @return $this
     */
    public function showCreateButton(bool $show = true): self
    {
        $this->create = $show;
        return $this;
    }

    /**
     * Enable/disable batch destroy.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableBatchDestroy(bool $enable = true): self
    {
        $this->batch_destroy = $enable;
        return $this;
    }

    /**
     * Enable/disable batch active.
     *
     * @param bool $enable
     * @return $this
     */
    public function enableBatchActive(bool $enable = true): self
    {
        $this->batch_active = $enable;
        return $this;
    }

    /**
     * Set identifier key.
     *
     * @param string $identifier
     * @return $this
     */
    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;
        return $this;
    }

    /**
     * Calculate per page.
     *
     * @return int
     */
    public function perPage(): int
    {
        $cached_per_page = $this->list->getPerPage();
        if ($cached_per_page > 0) {
            $this->per_page = $cached_per_page;
        }
        return $this->per_page;
    }

    /**
     * Add a link to the list.
     *
     * @param Link $link
     * @return $this
     */
    public function link(Link $link): self
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * Get the Database instance if available.
     *
     * @return Database|null
     */
    public function getDatabase(): ?Database
    {
        return $this->database;
    }

    /**
     * Get the list instance.
     *
     * @return HasList
     */
    public function getList(): HasList
    {
        return $this->list;
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
     * Get current search term.
     *
     * @return string|null
     */
    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    /**
     * Get custom filters.
     *
     * @return array
     */
    public function getCustomFilters(): array
    {
        return $this->customFilters;
    }

    /**
     * Get base route.
     *
     * @return string
     */
    public function getBaseRoute(): string
    {
        return $this->base_route;
    }

    /**
     * Get route parameter.
     *
     * @return string|null
     */
    public function getRouteParameter(): ?string
    {
        return $this->route_parameter;
    }

    /**
     * Check if create button is enabled.
     *
     * @return bool
     */
    public function hasCreateButton(): bool
    {
        return $this->create;
    }

    /**
     * Check if batch destroy is enabled.
     *
     * @return bool
     */
    public function hasBatchDestroy(): bool
    {
        return $this->batch_destroy;
    }

    /**
     * Check if batch active is enabled.
     *
     * @return bool
     */
    public function hasBatchActive(): bool
    {
        return $this->batch_active;
    }

    /**
     * Get links array.
     *
     * @return array
     */
    public function getLinks(): array
    {
        return $this->links;
    }

    /**
     * Filter out fields marked with skip_database from list operations.
     * This prevents virtual fields from being queried from database.
     *
     * @param array $fields
     * @return array
     */
    protected function filterSkipDatabaseFields(array $fields): array
    {
        $filtered = [];
        
        foreach ($fields as $field) {
            if ($field instanceof Field) {
                // حذف فیلدهای skip_database
                if (isset($field->skip_database) && $field->skip_database === true) {
                    continue; // این فیلد را از لیست حذف کن
                }
                
                // حذف فیلدهایی که database_key ندارند
                if ($field->database_key === null) {
                    continue;
                }
                
                $filtered[] = $field;
            } else {
                // اگر Field نیست, به هر حال اضافه کن
                $filtered[] = $field;
            }
        }
        
        return $filtered;
    }
}
