<?php

declare(strict_types=1);

namespace RMS\Core\Traits\View;

/**
 * Trait for view utility methods and helpers.
 * 
 * @package RMS\Core\Traits\View
 */
trait ViewUtilities
{
    /**
     * Get view configuration as array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'theme' => $this->theme,
            'template' => $this->tpl,
            'js_files' => $this->js,
            'css_files' => $this->css,
            'variables' => $this->vars,
            'js_variables' => $this->js_vars,
            'loaded_plugins' => $this->loadedPlugins,
            'asset_version' => $this->assetVersion
        ];
    }

    /**
     * Get view configuration as JSON.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    /**
     * Reset view to initial state.
     *
     * @return $this
     */
    public function reset(): self
    {
        $this->clearAssets();
        $this->clearVariables();
        $this->tpl = null;
        
        return $this;
    }

    /**
     * Clone view with optional modifications.
     *
     * @param array $overrides
     * @return static
     */
    public function clone(array $overrides = []): static
    {
        $cloned = new static($this->filesystem, $this->theme);
        
        // Copy current state
        $cloned->js = $this->js;
        $cloned->css = $this->css;
        $cloned->vars = $this->vars;
        $cloned->js_vars = $this->js_vars;
        $cloned->loadedPlugins = $this->loadedPlugins;
        $cloned->tpl = $this->tpl;
        $cloned->assetVersion = $this->assetVersion;
        
        // Apply overrides
        foreach ($overrides as $property => $value) {
            if (property_exists($cloned, $property)) {
                $cloned->{$property} = $value;
            }
        }
        
        return $cloned;
    }

    /**
     * Check if view is empty (no assets, variables, or template).
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->js) && 
               empty($this->css) && 
               empty($this->vars) && 
               empty($this->js_vars) && 
               empty($this->tpl);
    }

    /**
     * Get view summary for debugging.
     *
     * @return array
     */
    public function getSummary(): array
    {
        return [
            'theme' => $this->theme,
            'template' => $this->tpl,
            'assets_count' => $this->getAssetsCount(),
            'variables_count' => $this->getVariablesCount(),
            'loaded_plugins_count' => count($this->loadedPlugins),
            'is_empty' => $this->isEmpty()
        ];
    }

    /**
     * Validate view state before rendering.
     *
     * @return array
     */
    public function validate(): array
    {
        $errors = [];
        
        if (!$this->theme) {
            $errors[] = 'Theme is not set';
        }
        
        if (!$this->tpl) {
            $errors[] = 'Template is not set';
        }
        
        if ($this->tpl && !$this->templateExists()) {
            $errors[] = "Template '{$this->tpl}' does not exist in theme '{$this->theme}'";
        }
        
        return $errors;
    }

    /**
     * Apply view configuration from array.
     *
     * @param array $config
     * @return $this
     */
    public function fromArray(array $config): self
    {
        if (isset($config['theme'])) {
            $this->theme($config['theme']);
        }
        
        if (isset($config['template'])) {
            $this->setTpl($config['template']);
        }
        
        if (isset($config['js_files'])) {
            $this->js = array_merge($this->js, $config['js_files']);
        }
        
        if (isset($config['css_files'])) {
            $this->css = array_merge($this->css, $config['css_files']);
        }
        
        if (isset($config['variables'])) {
            $this->withVariables($config['variables']);
        }
        
        if (isset($config['js_variables'])) {
            $this->withJsVariables($config['js_variables']);
        }
        
        if (isset($config['plugins'])) {
            $this->withPlugins($config['plugins']);
        }
        
        if (isset($config['asset_version'])) {
            $this->setAssetVersion($config['asset_version']);
        }
        
        return $this;
    }

    /**
     * Export view configuration for serialization.
     *
     * @return array
     */
    public function export(): array
    {
        return [
            'theme' => $this->theme,
            'template' => $this->tpl,
            'js_files' => $this->js,
            'css_files' => $this->css,
            'variables' => $this->vars,
            'js_variables' => $this->js_vars,
            'loaded_plugins' => $this->loadedPlugins,
            'asset_version' => $this->assetVersion,
            'timestamp' => now()->toISOString()
        ];
    }
}
