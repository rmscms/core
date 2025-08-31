<?php

namespace RMS\Core\Data;

/**
 * Trait for managing boolean field options.
 */
trait BoolOptions
{
    /**
     * Title for the enabled state.
     *
     * @var string|bool
     */
    public string|bool $enable_title = false;

    /**
     * Title for the disabled state.
     *
     * @var string|bool
     */
    public string|bool $disable_title = false;

    /**
     * Value for the enabled state.
     *
     * @var int
     */
    public int $enable = 1;

    /**
     * Value for the disabled state.
     *
     * @var int
     */
    public int $disable = 0;

    /**
     * AJAX route for enable/disable actions in lists.
     *
     * @var string|bool
     */
    public string|bool $ajax_action_route = false;

    /**
     * Set the title for the enabled state.
     *
     * @param string $title
     * @return $this
     */
    public function withEnableTitle(string $title): self
    {
        $this->enable_title = $title;
        return $this;
    }

    /**
     * Set the title for the disabled state.
     *
     * @param string $title
     * @return $this
     */
    public function withDisableTitle(string $title): self
    {
        $this->disable_title = $title;
        return $this;
    }

    /**
     * Set the values for enabled and disabled states.
     *
     * @param int $enable
     * @param int $disable
     * @return $this
     */
    public function withValues(int $enable = 1, int $disable = 0): self
    {
        $this->enable = $enable;
        $this->disable = $disable;
        return $this;
    }

    /**
     * Set the AJAX route for enable/disable actions.
     *
     * @param string $route
     * @return $this
     */
    public function ajaxActionRoute(string $route): self
    {
        $this->ajax_action_route = $route;
        return $this;
    }
}
