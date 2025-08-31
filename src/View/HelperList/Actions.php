<?php

namespace RMS\Core\View\HelperList;

use RMS\Core\View\HelperList\Batch\BatchAction;

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
        unset($this->actions[$this->list->baseRoute() . '.' . $route]);
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
