<?php

namespace RMS\Core\Data;

use Illuminate\Support\Str;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Filter\HasSort;
use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\List\HasList;

/**
 * Class ListGenerator
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
    protected $list;

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
     * ListGenerator constructor.
     *
     * @param HasList $list
     */
    public function __construct(HasList $list)
    {
        $this->list = $list;
        $this->route_parameter = $list->routeParameter();
        $this->base_route = $list->baseRoute();
        $this->fields = $list->getListFields();
    }

    /**
     * Generate the list response.
     *
     * @return ListResponse
     */
    public function generate(): ListResponse
    {
        if ($this->list instanceof UseDatabase) {
            return new ListResponse($this->builder()->get($this->perPage(), $this->simple_pagination), $this, $this->fields);
        }
        throw new \Exception('This class should implement: ' . UseDatabase::class);
    }

    /**
     * Build the database query.
     *
     * @return Database
     */
    public function builder(): Database
    {
        $database = new Database($this->fields, $this->list->table());
        $this->list->query($database->sql);
        if ($this->list instanceof ShouldFilter) {
            $database->withFilters($this->list->getFilters());
        }
        if ($this->list instanceof HasSort && $column = $this->list->orderBy()) {
            $database->sort($this->list->orderBy(), $this->list->orderWay());
        }
        return $database;
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
}
