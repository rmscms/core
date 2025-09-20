<?php

declare(strict_types=1);

namespace RMS\Core\Traits\View;

/**
 * Trait for loading custom plugins with special configurations.
 * Separates plugin loading logic from core ViewAssetManager.
 *
 * @package RMS\Core\Traits\View
 */
trait CustomPluginLoader
{
    /**
     * Plugin configurations registry (loaded from config).
     */
    protected ?array $customPluginConfigs = null;

    /**
     * Load plugin configurations from config file.
     *
     * @return void
     */
    protected function loadPluginConfigs(): void
    {
        if ($this->customPluginConfigs === null) {
            $this->customPluginConfigs = config('plugins', []);
            
            // Filter out disabled plugins
            $this->customPluginConfigs = array_filter($this->customPluginConfigs, function($config) {
                return ($config['enabled'] ?? true) === true;
            });
        }
    }

    /**
     * Check if a plugin has custom configuration.
     *
     * @param string $plugin
     * @return bool
     */
    protected function hasCustomPluginConfig(string $plugin): bool
    {
        $this->loadPluginConfigs();
        return isset($this->customPluginConfigs[$plugin]);
    }

    /**
     * Load a custom plugin with special configuration.
     *
     * @param string $plugin
     * @param array $css
     * @param array $js
     * @return void
     */
    protected function loadCustomPlugin(string $plugin, array &$css, array &$js): void
    {
        if (!$this->hasCustomPluginConfig($plugin)) {
            return;
        }

        $config = $this->customPluginConfigs[$plugin];
        
        // Use plugin_path if specified, otherwise use plugin name as directory
        $pluginDirectory = $config['plugin_path'] ?? $plugin;
        $pluginPath = $this->theme . '/plugins/' . $pluginDirectory . '/';

        // Load CSS files
        if (isset($config['css'])) {
            foreach ($config['css'] as $cssFile) {
                $fullPath = $pluginPath . $cssFile;
                if ($this->assetExists($fullPath)) {
                    $css[] = ['path' => $fullPath, 'absolute' => true];
                }
            }
        }

        // Load JS files
        if (isset($config['js'])) {
            foreach ($config['js'] as $jsFile) {
                $fullPath = $pluginPath . $jsFile;
                if ($this->assetExists($fullPath)) {
                    $js[] = ['path' => $fullPath, 'absolute' => true];
                }
            }
        }

        $this->loadedPlugins[] = $plugin;
    }

    /**
     * Register a new custom plugin configuration.
     *
     * @param string $plugin
     * @param array $config
     * @return $this
     */
    public function registerCustomPlugin(string $plugin, array $config): self
    {
        $this->customPluginConfigs[$plugin] = array_merge([
            'css' => [],
            'js' => [],
            'dependencies' => [],
            'load_order' => 10
        ], $config);

        return $this;
    }

    /**
     * Get custom plugin configuration.
     *
     * @param string $plugin
     * @return array|null
     */
    public function getCustomPluginConfig(string $plugin): ?array
    {
        $this->loadPluginConfigs();
        return $this->customPluginConfigs[$plugin] ?? null;
    }

    /**
     * Get all registered custom plugins.
     *
     * @return array
     */
    public function getRegisteredCustomPlugins(): array
    {
        $this->loadPluginConfigs();
        return array_keys($this->customPluginConfigs);
    }

    /**
     * Check if plugin dependencies are met.
     *
     * @param string $plugin
     * @return bool
     */
    protected function checkPluginDependencies(string $plugin): bool
    {
        $config = $this->getCustomPluginConfig($plugin);
        
        if (!$config || empty($config['dependencies'])) {
            return true;
        }

        foreach ($config['dependencies'] as $dependency) {
            if (!$this->isDependencyAvailable($dependency)) {
                \Illuminate\Support\Facades\Log::warning("Plugin dependency not met", [
                    'plugin' => $plugin,
                    'missing_dependency' => $dependency
                ]);
                return false;
            }
        }

        return true;
    }

    /**
     * Check if a dependency is available.
     *
     * @param string $dependency
     * @return bool
     */
    protected function isDependencyAvailable(string $dependency): bool
    {
        switch ($dependency) {
            case 'jquery':
                // Assume jQuery is always available in admin panel
                return true;
            case 'moment':
                // Check if moment.js is loaded
                return in_array('moment', $this->loadedPlugins);
            default:
                // For other dependencies, check if they're in loaded plugins
                return in_array($dependency, $this->loadedPlugins);
        }
    }

    /**
     * Sort plugins by load order.
     *
     * @param array $plugins
     * @return array
     */
    protected function sortPluginsByLoadOrder(array $plugins): array
    {
        usort($plugins, function ($a, $b) {
            $orderA = $this->getCustomPluginConfig($a)['load_order'] ?? 10;
            $orderB = $this->getCustomPluginConfig($b)['load_order'] ?? 10;
            return $orderA <=> $orderB;
        });

        return $plugins;
    }
}
