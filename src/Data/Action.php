<?php

namespace RMS\Core\Data;

use RMS\Core\Data\Confirm;

/**
 * Class for defining a single action in a list.
 */
class Action
{
    /**
     * The title of the action.
     *
     * @var string
     */
    public string $title;

    /**
     * The route for the action.
     *
     * @var string
     */
    public string $route;

    /**
     * The icon for the action (e.g., Phosphor icon class).
     *
     * @var string|null
     */
    public ?string $icon;

    /**
     * The CSS class for the action.
     *
     * @var string|null
     */
    public ?string $class;

    /**
     * IDs to skip this action for specific rows.
     *
     * @var array
     */
    public array $skip = [];

    /**
     * The confirmation dialog for the action.
     *
     * @var Confirm|bool
     */
    public Confirm|bool $confirm = false;

    /**
     * Whether the action uses AJAX.
     *
     * @var bool
     */
    public bool $ajax = false;

    /**
     * The AJAX request type (e.g., get, post).
     *
     * @var string
     */
    public string $ajax_type = 'get';
    
    /**
     * The HTTP method for the action (e.g., GET, POST, DELETE).
     *
     * @var string
     */
    public string $method = 'GET';

    /**
     * Additional HTML attributes for the action.
     *
     * @var array
     */
    public array $attributes = [];

    /**
     * Action constructor.
     *
     * @param string $title
     * @param string $route
     * @param string|null $icon
     * @param string|null $class
     */
    public function __construct(string $title, string $route, ?string $icon = null, ?string $class = null)
    {
        $this->title = $title;
        $this->route = $route;
        $this->icon = $icon;
        $this->class = $class;
    }

    /**
     * Set IDs to skip this action for specific rows.
     *
     * @param array $ids
     * @return $this
     */
    public function withSkips(array $ids): self
    {
        $this->skip = array_merge($ids, $this->skip);
        return $this;
    }

    /**
     * Set the confirmation dialog for the action.
     *
     * @param Confirm $confirm
     * @return $this
     */
    public function withConfirm(Confirm $confirm): self
    {
        $this->confirm = $confirm;
        return $this;
    }

    /**
     * Enable AJAX call for the action.
     *
     * @param string $type
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function ajaxCall(string $type = 'get'): self
    {
        $validTypes = ['get', 'post', 'put', 'delete'];
        if (!in_array(strtolower($type), $validTypes)) {
            throw new \InvalidArgumentException("Invalid AJAX type: $type");
        }
        $this->ajax = true;
        $this->ajax_type = strtolower($type);
        return $this;
    }

    /**
     * Add additional HTML attributes to the action.
     *
     * @param array $attributes
     * @return $this
     */
    public function withAttributes(array $attributes): self
    {
        $this->attributes = array_merge($this->attributes, $attributes);
        return $this;
    }

    /**
     * Set permissions for the action.
     *
     * @param array $permissions
     * @return $this
     */
    public function withPermissions(array $permissions): self
    {
        $this->attributes['data-permissions'] = json_encode($permissions);
        return $this;
    }
    
    /**
     * Set the HTTP method for the action.
     *
     * @param string $method
     * @return $this
     */
    public function withMethod(string $method): self
    {
        $this->method = strtoupper($method);
        return $this;
    }
}
