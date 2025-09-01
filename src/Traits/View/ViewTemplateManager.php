<?php

declare(strict_types=1);

namespace RMS\Core\Traits\View;

use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View as IlluminateView;
use InvalidArgumentException;

/**
 * Trait for managing view templates and themes.
 * 
 * @package RMS\Core\Traits\View
 */
trait ViewTemplateManager
{
    /**
     * Current theme name.
     */
    protected ?string $theme = null;

    /**
     * Template name to render.
     */
    protected ?string $tpl = null;

    /**
     * Set theme folder.
     *
     * @param string $theme
     * @return $this
     * @throws InvalidArgumentException
     */
    public function theme(string $theme): self
    {
        if (empty($theme)) {
            throw new InvalidArgumentException('Theme name cannot be empty');
        }
        
        $this->theme = $theme;
        return $this;
    }

    /**
     * Get current theme name.
     *
     * @return string|null
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * Set view template.
     *
     * @param string $tpl
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setTpl(string $tpl): self
    {
        if (empty($tpl)) {
            throw new InvalidArgumentException('Template name cannot be empty');
        }
        
        $this->tpl = $tpl;
        return $this;
    }

    /**
     * Get current template name.
     *
     * @return string|null
     */
    public function getTpl(): ?string
    {
        return $this->tpl;
    }

    /**
     * Check if template is set.
     *
     * @return bool
     */
    public function hasTpl(): bool
    {
        return !empty($this->tpl);
    }

    /**
     * Render the view with enhanced error handling.
     *
     * @param bool $usePackageNamespace
     * @return Factory|ViewContract|IlluminateView
     * @throws InvalidArgumentException
     */
    public function render(bool $usePackageNamespace = true): Factory|ViewContract|IlluminateView
    {
        if (!$this->tpl) {
            throw new InvalidArgumentException('Template must be set before rendering');
        }
        
        try {
            $tpl = $this->buildTemplatePath($usePackageNamespace);
            $parameters = $this->prepareRenderParameters();
            
            return view($tpl, $parameters);
        } catch (\Exception $e) {
            Log::error('View rendering failed', [
                'template' => $this->tpl,
                'theme' => $this->theme,
                'error' => $e->getMessage()
            ]);
            
            throw new InvalidArgumentException('Failed to render view: ' . $e->getMessage());
        }
    }

    /**
     * Build template path for view rendering.
     *
     * @param bool $usePackageNamespace
     * @return string
     */
    protected function buildTemplatePath(bool $usePackageNamespace): string
    {
        if ($usePackageNamespace) {
            return 'cms::' . $this->theme . '.' . $this->tpl;
        }
        
        return $this->theme . '.' . $this->tpl;
    }

    /**
     * Prepare parameters for view rendering.
     *
     * @return array
     */
    protected function prepareRenderParameters(): array
    {
        return array_merge([
            'css' => array_map([$this, 'versionedAsset'], $this->css),
            'js' => array_map([$this, 'versionedAsset'], $this->js),
            'theme' => $this->theme,
            'js_vars' => $this->js_vars,
            'asset_version' => $this->assetVersion,
            'loaded_plugins' => $this->loadedPlugins
        ], $this->vars);
    }

    /**
     * Check if template exists in current theme.
     *
     * @param string|null $template
     * @return bool
     */
    public function templateExists(?string $template = null): bool
    {
        $template = $template ?? $this->tpl;
        
        if (!$template) {
            return false;
        }
        
        try {
            $viewPath = 'cms::' . $this->theme . '.' . $template;
            return view()->exists($viewPath);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set theme with validation.
     *
     * @param string $theme
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setTheme(string $theme): self
    {
        if (!$this->themeExists($theme)) {
            throw new InvalidArgumentException("Theme '{$theme}' does not exist");
        }
        
        $this->theme = $theme;
        return $this;
    }

    /**
     * Check if theme exists.
     *
     * @param string $theme
     * @return bool
     */
    protected function themeExists(string $theme): bool
    {
        $themePath = resource_path('views/vendor/cms/' . $theme);
        return $this->filesystem->isDirectory($themePath);
    }

    /**
     * Get available themes.
     *
     * @return array
     */
    public function getAvailableThemes(): array
    {
        try {
            $themesPath = resource_path('views/vendor/cms');
            
            if (!$this->filesystem->isDirectory($themesPath)) {
                return [];
            }
            
            return array_filter(
                $this->filesystem->directories($themesPath),
                fn($dir) => $this->filesystem->isDirectory($dir)
            );
        } catch (\Exception $e) {
            Log::warning('Failed to get available themes', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}
