<?php

declare(strict_types=1);

namespace RMS\Core\Traits\View;

/**
 * Trait for managing view variables (template and JavaScript).
 * 
 * @package RMS\Core\Traits\View
 */
trait ViewVariableManager
{
    /**
     * Template variables.
     */
    protected array $vars = [];

    /**
     * JavaScript variables to pass to frontend.
     */
    protected array $js_vars = [];

    /**
     * Add variables to view.
     *
     * @param array $vars
     * @return $this
     */
    public function withVariables(array $vars): self
    {
        $this->vars = array_merge($this->vars, $vars);
        return $this;
    }

    /**
     * Add a single variable to view.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function withVariable(string $key, mixed $value): self
    {
        $this->vars[$key] = $value;
        return $this;
    }

    /**
     * Add JavaScript variables to view.
     *
     * @param array $vars
     * @return $this
     */
    public function withJsVariables(array $vars): self
    {
        $this->js_vars = array_merge($this->js_vars, $vars);
        return $this;
    }

    /**
     * Add a single JavaScript variable to view.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function withJsVariable(string $key, mixed $value): self
    {
        $this->js_vars[$key] = $value;
        return $this;
    }

    /**
     * Remove a variable from view.
     *
     * @param string $key
     * @return $this
     */
    public function removeVariable(string $key): self
    {
        unset($this->vars[$key]);
        return $this;
    }

    /**
     * Remove a JavaScript variable from view.
     *
     * @param string $key
     * @return $this
     */
    public function removeJsVariable(string $key): self
    {
        unset($this->js_vars[$key]);
        return $this;
    }

    /**
     * Get all template variables.
     *
     * @return array
     */
    public function getVariables(): array
    {
        return $this->vars;
    }

    /**
     * Get a specific template variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getVariable(string $key, mixed $default = null): mixed
    {
        return $this->vars[$key] ?? $default;
    }

    /**
     * Get all JavaScript variables.
     *
     * @return array
     */
    public function getJsVariables(): array
    {
        return $this->js_vars;
    }

    /**
     * Get a specific JavaScript variable.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getJsVariable(string $key, mixed $default = null): mixed
    {
        return $this->js_vars[$key] ?? $default;
    }

    /**
     * Clear all variables.
     *
     * @return $this
     */
    public function clearVariables(): self
    {
        $this->vars = [];
        $this->js_vars = [];
        return $this;
    }

    /**
     * Check if a variable exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasVariable(string $key): bool
    {
        return array_key_exists($key, $this->vars);
    }

    /**
     * Check if a JavaScript variable exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasJsVariable(string $key): bool
    {
        return array_key_exists($key, $this->js_vars);
    }

    /**
     * Get variables count.
     *
     * @return array
     */
    public function getVariablesCount(): array
    {
        return [
            'template_vars' => count($this->vars),
            'js_vars' => count($this->js_vars)
        ];
    }

    /**
     * Merge variables from another source.
     *
     * @param array $vars
     * @param bool $overwrite
     * @return $this
     */
    public function mergeVariables(array $vars, bool $overwrite = true): self
    {
        if ($overwrite) {
            $this->vars = array_merge($this->vars, $vars);
        } else {
            foreach ($vars as $key => $value) {
                if (!array_key_exists($key, $this->vars)) {
                    $this->vars[$key] = $value;
                }
            }
        }
        
        return $this;
    }

    /**
     * Merge JavaScript variables from another source.
     *
     * @param array $vars
     * @param bool $overwrite
     * @return $this
     */
    public function mergeJsVariables(array $vars, bool $overwrite = true): self
    {
        if ($overwrite) {
            $this->js_vars = array_merge($this->js_vars, $vars);
        } else {
            foreach ($vars as $key => $value) {
                if (!array_key_exists($key, $this->js_vars)) {
                    $this->js_vars[$key] = $value;
                }
            }
        }
        
        return $this;
    }
}
