<?php

namespace RMS\Core\View\HelperList;

use Illuminate\Support\Str;
use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Data\Database;
use RMS\Core\Controllers\Features\Filter\HasSort;
use RMS\Core\Controllers\Features\Filter\ShouldFilter;

/**
 * Trait Generator
 * @package RMS\Core\View\HelperList
 */
class Generator
{
    use Actions;

    /**
     * Determine create button is active or not
     * @var bool $create
     */
    public $create = true;

    /**
     * Identifier key in each row
     * @var string $identifier
     */
    public $identifier = 'id';

    /**
     * If set to false, loop from foreach begins from 1
     * @var bool $view_id
     */
    public $view_id = true;

    /**
     * Base route parameter
     * @var string|null $route_parameter
     */
    public $route_parameter;

    /**
     * Display rows per page
     * @var int $per_page
     */
    public $per_page = 20;

    /**
     * Simple pagination
     * @var bool $simple_pagination
     */
    public $simple_pagination = false;

    /**
     * Fields
     * @var array $fields
     */
    protected $fields = [];

    /**
     * @var HasList $list
     */
    protected $list;

    /**
     * @var string $base_route
     */
    public $base_route;

    /**
     * @var array $links
     */
    public $links = [];

    /**
     * @var bool $batch_destroy
     */
    public $batch_destroy = true;

    /**
     * @var bool $batch_active
     */
    public $batch_active = false;

    /**
     * Generator constructor.
     * @param HasList $list
     */
    public function __construct(HasList $list)
    {
        $this->list = $list;
        $this->route_parameter = $list->routeParameter();
        $this->base_route = $list->baseRoute();
        $this->fields = $this->list->getListFields();
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
    public function link(Link $link)
    {
        $this->links[] = $link;
        return $this;
    }
}
