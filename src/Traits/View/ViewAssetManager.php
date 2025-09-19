<?php

declare(strict_types=1);

namespace RMS\Core\Traits\View;

use InvalidArgumentException;
use Illuminate\Support\Facades\Log;

/**
 * Trait for managing view assets (JS, CSS, plugins).
 * 
 * @package RMS\Core\Traits\View
 */
trait ViewAssetManager
{
    use CustomPluginLoader;
    /**
     * JavaScript files to include.
     */
    protected array $js = [];

    /**
     * CSS files to include.
     */
    protected array $css = [];

    /**
     * Loaded plugins cache.
     */
    protected array $loadedPlugins = [];

    /**
     * Asset version for cache busting.
     */
    protected ?string $assetVersion = null;

    /**
     * Add a JS file to view.
     *
     * @param string $js
     * @param bool $absolute
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withJs(string $js, bool $absolute = false): self
    {
        if (empty($js)) {
            throw new InvalidArgumentException('JS file path cannot be empty');
        }
        
        return $this->withMultipleJs([
            ['path' => $js, 'absolute' => $absolute]
        ]);
    }

    /**
     * Add multiple JS files to view.
     *
     * @param array $js
     * @return $this
     */
    public function withMultipleJs(array $js): self
    {
        foreach ($js as $file) {
            if (!is_array($file) || !isset($file['path'])) {
                continue;
            }
            
            $filePath = $this->resolveAssetPath($file['path'], 'js', $file['absolute'] ?? false);
            
            if (!in_array($filePath, $this->js) && $this->assetExists($filePath)) {
                $this->js[] = $filePath;
            }
        }
        
        return $this;
    }

    /**
     * Add a CSS file to view.
     *
     * @param string $css
     * @param bool $absolute
     * @return $this
     * @throws InvalidArgumentException
     */
    public function withCss(string $css, bool $absolute = false): self
    {
        if (empty($css)) {
            throw new InvalidArgumentException('CSS file path cannot be empty');
        }
        
        return $this->withMultipleCss([
            ['path' => $css, 'absolute' => $absolute]
        ]);
    }

    /**
     * Add multiple CSS files to view.
     *
     * @param array $css
     * @return $this
     */
    public function withMultipleCss(array $css): self
    {
        foreach ($css as $file) {
            if (!is_array($file) || !isset($file['path'])) {
                continue;
            }
            
            $filePath = $this->resolveAssetPath($file['path'], 'css', $file['absolute'] ?? false);
            
            if (!in_array($filePath, $this->css) && $this->assetExists($filePath)) {
                $this->css[] = $filePath;
            }
        }
        
        return $this;
    }

    /**
     * Add plugins to view with enhanced error handling.
     *
     * @param array $plugins
     * @return $this
     */
    public function withPlugins(array $plugins): self
    {
        $css = [];
        $js = [];
        
        // Sort plugins by load order (custom plugins first)
        $sortedPlugins = $this->sortPluginsByLoadOrder($plugins);
        
        foreach ($sortedPlugins as $plugin) {
            if (empty($plugin) || in_array($plugin, $this->loadedPlugins)) {
                continue;
            }
            
            $this->loadPlugin($plugin, $css, $js);
        }
        
        return $this->withMultipleJs($js)->withMultipleCss($css);
    }

    /**
     * Load a single plugin and prepare its assets.
     *
     * @param string $plugin
     * @param array $css
     * @param array $js
     * @return void
     */
    protected function loadPlugin(string $plugin, array &$css, array &$js): void
    {
        // Check if this is a custom plugin with special configuration
        if ($this->hasCustomPluginConfig($plugin)) {
            // Check dependencies before loading
            if (!$this->checkPluginDependencies($plugin)) {
                return;
            }
            
            $this->loadCustomPlugin($plugin, $css, $js);
            return;
        }
        
        // Default plugin loading (single CSS and JS file)
        $pluginCss = $this->theme . '/plugins/' . $plugin . '/' . $plugin . '.css';
        $pluginJs = $this->theme . '/plugins/' . $plugin . '/' . $plugin . '.js';
        
        if ($this->assetExists($pluginCss)) {
            $css[] = ['path' => $pluginCss, 'absolute' => true];
        }
        
        if ($this->assetExists($pluginJs)) {
            $js[] = ['path' => $pluginJs, 'absolute' => true];
        }
        
        $this->loadedPlugins[] = $plugin;
    }

    /**
     * Set asset version for cache busting.
     *
     * @param string $version
     * @return $this
     */
    public function setAssetVersion(string $version): self
    {
        $this->assetVersion = $version;
        return $this;
    }

    /**
     * Get asset version.
     *
     * @return string|null
     */
    public function getAssetVersion(): ?string
    {
        return $this->assetVersion;
    }

    /**
     * Get all loaded JavaScript files.
     *
     * @return array
     */
    public function getJs(): array
    {
        return $this->js;
    }

    /**
     * Get all loaded CSS files.
     *
     * @return array
     */
    public function getCss(): array
    {
        return $this->css;
    }

    /**
     * Get loaded plugins list.
     *
     * @return array
     */
    public function getLoadedPlugins(): array
    {
        return $this->loadedPlugins;
    }

    /**
     * Clear all assets (JS and CSS).
     *
     * @return $this
     */
    public function clearAssets(): self
    {
        $this->js = [];
        $this->css = [];
        $this->loadedPlugins = [];
        return $this;
    }

    /**
     * Check if a specific plugin is loaded.
     *
     * @param string $plugin
     * @return bool
     */
    public function hasPlugin(string $plugin): bool
    {
        return in_array($plugin, $this->loadedPlugins);
    }

    /**
     * Remove a loaded plugin.
     *
     * @param string $plugin
     * @return $this
     */
    public function removePlugin(string $plugin): self
    {
        $this->loadedPlugins = array_diff($this->loadedPlugins, [$plugin]);
        return $this;
    }

    /**
     * Resolve asset path based on type and absolute flag.
     *
     * @param string $path
     * @param string $type
     * @param bool $absolute
     * @return string
     */
    protected function resolveAssetPath(string $path, string $type, bool $absolute): string
    {
        if ($absolute) {
            return $path;
        }
        
        return $this->theme . '/' . $type . '/' . $path;
    }

    /**
     * Check if asset file exists.
     *
     * @param string $path
     * @return bool
     */
    protected function assetExists(string $path): bool
    {
        try {
            return $this->filesystem->exists(public_path($path));
        } catch (\Exception $e) {
            Log::warning('Asset existence check failed', [
                'path' => $path,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Add versioned asset URL.
     *
     * @param string $path
     * @return string
     */
    protected function versionedAsset(string $path): string
    {
        if ($this->assetVersion) {
            $separator = str_contains($path, '?') ? '&' : '?';
            return $path . $separator . 'v=' . $this->assetVersion;
        }
        
        return $path;
    }

    /**
     * Get assets count.
     *
     * @return array
     */
    public function getAssetsCount(): array
    {
        return [
            'js' => count($this->js),
            'css' => count($this->css),
            'plugins' => count($this->loadedPlugins)
        ];
    }
}
