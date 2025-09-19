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
     * Plugin configurations registry.
     */
    protected array $customPluginConfigs = [
        'persian-datepicker' => [
            'css' => [
                'persian-datepicker.css',
                'pwt.datepicker.min.css'
            ],
            'js' => [
                'jalaali.js',                    // Jalaali library for leap year fix (must be first)
                'persian-date.min.js',           // Persian date library
                'pwt.datepicker.min.js',         // DatePicker plugin
                'persian-datepicker-simple.js'   // Our custom initialization
            ],
            'dependencies' => ['jquery'],
            'load_order' => 1  // Load early
        ],
        // Future plugins can be added here
        'persian-number-input' => [
            'css' => ['persian-number.css'],
            'js' => ['persian-number.js'],
            'dependencies' => ['jquery']
        ],
        'jalali-moment' => [
            'js' => ['moment-jalaali.min.js', 'jalali-moment-init.js'],
            'dependencies' => ['moment']
        ],
        'advanced-select' => [
            'css' => [
                'choices.min.css',                  // Base Choices.js styles
                'choices-bootstrap5.css'            // Bootstrap 5 integration styles
            ],
            'js' => [
                'choices.min.js',                   // Choices.js library
                'enhanced-select-init.js'           // Our custom initialization
            ],
            'dependencies' => [], // No dependencies - vanilla JS
            'load_order' => 3,  // Load after core plugins
            'plugin_path' => 'choices'              // ← Actual plugin directory name
        ],
        'amount-formatter' => [
            'css' => [
                'amount-formatter.css'              // Amount field styling
            ],
            'js' => [
                'amount-formatter.js'               // Amount field formatting logic
            ],
            'dependencies' => [], // No dependencies - vanilla JS
            'load_order' => 2,  // Load early for form fields
            'plugin_path' => 'amount-formatter'     // Plugin directory name
        ],
        'image-uploader' => [
            'css' => [
                'image-uploader.css'                // Image uploader styling with dark theme support
            ],
            'js' => [
                'image-uploader.js'                 // Professional image upload with preview & validation
            ],
            'dependencies' => [], // No dependencies - vanilla JS with Bootstrap 5
            'load_order' => 3,  // Load after core plugins but before advanced-select
            'plugin_path' => 'image-uploader'       // Plugin directory name (will use admin/js & admin/css directly)
        ],
        'sweetalert2' => [
            'css' => [
                'sweetalert2.css'                   // SweetAlert2 styles for Limitless theme with dark mode
            ],
            'js' => [
                'sweet_alert.min.js',               // SweetAlert2 library from Limitless
                'rms-sweetalert-new.js'             // RMS wrapper for SweetAlert2 with Limitless integration
            ],
            'dependencies' => [], // No dependencies - vanilla JS
            'load_order' => 1,  // Load very early (before other plugins might need it)
            'plugin_path' => 'sweetalert2'          // Plugin directory name
        ],
        'avatar-viewer' => [
            'css' => [
                'avatar-viewer.css'                 // Avatar thumbnail styles with hover effects
            ],
            'js' => [
                'avatar-viewer.js'                  // Avatar modal viewer with AJAX support
            ],
            'dependencies' => [], // No dependencies - uses SweetAlert2 which loads first
            'load_order' => 5,  // Load after SweetAlert2 and other core plugins
            'plugin_path' => 'avatar-viewer'        // Plugin directory name
        ],
        'sidebar-mobile' => [
            'css' => [
                'sidebar-mobile.css'                // Sidebar mobile fix styles with responsive design
            ],
            'js' => [
                'sidebar-mobile.js'                 // Sidebar mobile functionality for toggle & backdrop
            ],
            'dependencies' => [], // No dependencies - vanilla JS with DOM API
            'load_order' => 1,  // Load early (layout-critical plugin)
            'plugin_path' => 'sidebar-mobile'       // Plugin directory name
        ],
        'mobile-footer-nav' => [
            'css' => [
                'mobile-footer-nav.css'             // Mobile footer navigation styles with Bootstrap 5 integration
            ],
            'js' => [
                'mobile-footer-nav.js'              // Mobile footer nav with tooltips, animations & badge management
            ],
            'dependencies' => [], // No dependencies - works with Bootstrap 5 (already loaded)
            'load_order' => 2,  // Load after core layout plugins
            'plugin_path' => 'mobile-footer-nav'    // Plugin directory name
        ]
    ];

    /**
     * Check if a plugin has custom configuration.
     *
     * @param string $plugin
     * @return bool
     */
    protected function hasCustomPluginConfig(string $plugin): bool
    {
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
        return $this->customPluginConfigs[$plugin] ?? null;
    }

    /**
     * Get all registered custom plugins.
     *
     * @return array
     */
    public function getRegisteredCustomPlugins(): array
    {
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
