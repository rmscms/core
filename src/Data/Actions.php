<?php

namespace RMS\Core\Data;


use RMS\Core\Data\Batch\BatchAction;

/**
 * Trait Actions
 */
trait Actions
{
    /**
     * Array of batch actions
     *
     * @var BatchAction[]
     */
    public array $batches = [];

    /**
     * Array of actions
     *
     * @var Action[]
     */
    public array $actions = [];

    /**
     * Identifiers for skipping specific actions in each row
     *
     * @var array
     */
    public array $skips = [];

    /**
     * Add action.
     *
     * @param Action $action
     * @return $this
     */
    public function addAction(Action $action): self
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
    public function addBatchAction(BatchAction $action): self
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
    public function removeActions(string $route): self
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
    public function loadAction(string $route): Action
    {
        return $this->actions[$this->list->baseRoute() . '.' . $route];
    }
}
