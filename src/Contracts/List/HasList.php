<?php

namespace RMS\Core\Contracts\List;

use RMS\Core\Contracts\List\PerPageContract;

/**
 * Interface for classes that manage lists.
 */
interface HasList extends PerPageContract
{
    /**
     * Get the list fields.
     *
     * @return array
     */
    public function getListFields(): array;

    /**
     * Get the base route of the controller.
     *
     * @return string
     */
    public function baseRoute(): string;

    /**
     * Get the route parameter for URLs.
     *
     * @return string
     */
    public function routeParameter(): string;

    /**
     * Set the default template for the list.
     *
     * @return void
     */
    public function setTplList(): void;

    /**
     * Get the list configuration.
     *
     * @return array
     */
    public function getListConfig(): array;
}
