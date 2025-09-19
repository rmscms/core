<?php

namespace RMS\Core\View\HelperList;

use RMS\Core\Data\Action;
use RMS\Core\Data\BatchAction;

/**
 * Trait Actions
 * @package RMS\Core\View\HelperList
 */
trait Actions
{
    /**
     * @var BatchAction[] $batches
     */
    public $batches = [];

    /**
     * @var Action[] $actions
     */
    public $actions = [];

    /**
     * Identifier for skipping specific actions in each row
     * @var array $skips
     */
    public $skips = [];

    /**
     * Add action.
     *
     * @param Action $action
     * @return $this
     */
    public function addAction(Action $action)
    {
        $this->actions[$action->route] = $action;
        return $this;
    }

    /**
     * Add batch action.
     *
     * @param BatchAction $action
     * @return $this
     */
    public function addBatchAction(BatchAction $action)
    {
        $this->batches[] = $action;
        return $this;
    }

    /**
     * Remove existing action.
     *
     * @param string $route
     * @return $this
     */
    public function removeActions($route)
    {
        // Build correct route name with prefix if exists
        $routeName = '';
        
        if (property_exists($this->list, 'prefix_route') && !empty($this->list->prefix_route)) {
            $routeName = rtrim($this->list->prefix_route, '.') . '.' . $this->list->baseRoute() . '.' . $route;
        } else {
            $routeName = $this->list->baseRoute() . '.' . $route;
        }
        
        unset($this->actions[$routeName]);
        return $this;
    }

    /**
     * Load existing action.
     *
     * @param string $route
     * @return Action
     */
    public function loadAction($route): Action
    {
        return $this->actions[$this->list->baseRoute() . '.' . $route];
    }
}
