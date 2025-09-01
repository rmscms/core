<?php

declare(strict_types=1);

namespace RMS\Core\Traits;

use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Traits\Actions\DeleteAction;
use RMS\Core\Traits\Actions\StoreAction;
use RMS\Core\Traits\Controllers\HelperController;
use RMS\Core\Traits\Data\UseDatabaseHelper;
use RMS\Core\Traits\Export\ExportList;
use RMS\Core\Traits\Filter\FilterList;
use RMS\Core\Traits\Filter\Sortable;
use RMS\Core\Traits\Form\GenerateForm;
use RMS\Core\Traits\List\GenerateList;
use RMS\Core\Traits\List\PerPageList;
use RMS\Core\Traits\Stats\Statable;
use RMS\Core\View\HelperForm\Generator as FormGenerator;
use RMS\Core\View\HelperList\Generator as ListGenerator;
use RMS\Core\View\View;

/**
 * Main trait that combines form and list functionality.
 * This trait provides a complete solution for CRUD operations with lists and forms.
 * 
 * @package RMS\Core\Traits
 */
trait FormAndList
{
    use GenerateList, FilterList, PerPageList;
    use DeleteAction, StoreAction;
    use UseDatabaseHelper;
    use GenerateForm;
    use RequestFormHelper;
    use ExportList;
    use Statable;
    use HelperController;
    use Sortable;

    /**
     * Page title for each route.
     */
    protected ?string $title = null;

    /**
     * View instance for rendering.
     */
    protected View $view;

    /**
     * Route prefix for the controller.
     */
    public string $prefix_route = 'admin.';

    /**
     * Theme name for assets and templates.
     */
    public string $theme = 'admin';

    /**
     * Set the page title.
     *
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /**
     * Get the page title.
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Set the route prefix.
     *
     * @param string $prefix
     * @return $this
     */
    public function setRoutePrefix(string $prefix): self
    {
        $this->prefix_route = $prefix;
        return $this;
    }

    /**
     * Set the theme.
     *
     * @param string $theme
     * @return $this
     */
    public function setTheme(string $theme): self
    {
        $this->theme = $theme;
        return $this;
    }

    /**
     * Hook method called before list generation.
     * Configures common assets and plugins for list views.
     *
     * @param ListGenerator $generator
     * @return void
     */
    protected function beforeGenerateList(ListGenerator &$generator): void
    {
        $this->configureListAssets();
        $this->configureListVariables($generator);
    }

    /**
     * Hook method called before form generation.
     * Configures common assets and plugins for form views.
     *
     * @param FormGenerator $generator
     * @return void
     */
    protected function beforeGenerateForm(FormGenerator &$generator): void
    {
        $this->configureFormAssets();
        $this->configureFormVariables();
    }

    /**
     * Configure assets for list views.
     *
     * @return void
     */
    protected function configureListAssets(): void
    {
        $this->view
            ->withPlugins(['bootstrap-select', 'select2', 'persian-datepicker', 'sweetalert'])
            ->withJs($this->theme . '/plugins/persian-datepicker/persian-date.min.js', true)
            ->withJs('list.js');
    }

    /**
     * Configure assets for form views.
     *
     * @return void
     */
    protected function configureFormAssets(): void
    {
        $this->view
            ->withPlugins(['bootstrap-select', 'select2', 'persian-datepicker', 'sweetalert', 'summernote'])
            ->withJs($this->theme . '/plugins/persian-datepicker/persian-date.min.js', true)
            ->withJs($this->theme . '/plugins/summernote/fa.js', true)
            ->withJs('form.js');
    }

    /**
     * Configure variables for list views.
     *
     * @param ListGenerator $generator
     * @return void
     */
    protected function configureListVariables(ListGenerator $generator): void
    {
        $variables = [];
        
        if ($this->title) {
            $variables['title'] = $this->title;
        }
        
        if ($this instanceof ShouldFilter) {
            $variables['filters'] = $this->getFilters();
        }
        
        if (!empty($variables)) {
            $this->view->withVariables($variables);
        }
        
        if ($this instanceof HasStats) {
            $this->statsCount($generator->builder()->sql);
            $this->statsToTpl();
        }
    }

    /**
     * Configure variables for form views.
     *
     * @return void
     */
    protected function configureFormVariables(): void
    {
        $variables = [];
        
        if ($this->title) {
            $variables['title'] = $this->title;
        }
        
        if ($this instanceof ShouldFilter) {
            $variables['filters'] = $this->getFilters();
        }
        
        if (!empty($variables)) {
            $this->view->withVariables($variables);
        }
    }

    /**
     * Hook method called before any view rendering.
     * Override this method to add global assets and configurations.
     *
     * @return void
     */
    protected function beforeRenderView(): void
    {
        // Override in child classes for global configurations
    }

    /**
     * Get the view instance.
     *
     * @return View
     */
    protected function view(): View
    {
        return $this->view;
    }

    /**
     * Set the view instance.
     *
     * @param View $view
     * @return $this
     */
    public function setView(View $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Initialize the trait with default configurations.
     * Call this method in your controller's constructor.
     *
     * @return void
     */
    protected function initializeFormAndList(): void
    {
        if (!isset($this->view)) {
            $this->view = new View();
        }
        
        // Set default theme-based configurations
        $this->configureDefaults();
    }

    /**
     * Configure default settings based on theme.
     *
     * @return void
     */
    protected function configureDefaults(): void
    {
        // Set default per-page based on configuration
        if (method_exists($this, 'setDefaultPerPage')) {
            $defaultPerPage = config('cms.list.default_per_page', 15);
            $this->setDefaultPerPage($defaultPerPage);
        }
        
        // Set maximum per-page based on configuration
        if (method_exists($this, 'setMaxPerPage')) {
            $maxPerPage = config('cms.list.max_per_page', 100);
            $this->setMaxPerPage($maxPerPage);
        }
    }

    /**
     * Get configuration for this controller.
     *
     * @return array
     */
    public function getConfiguration(): array
    {
        return [
            'title' => $this->title,
            'theme' => $this->theme,
            'prefix_route' => $this->prefix_route,
            'base_route' => method_exists($this, 'baseRoute') ? $this->baseRoute() : null,
            'model_name' => method_exists($this, 'modelName') ? $this->modelName() : null,
            'features' => $this->getEnabledFeatures()
        ];
    }

    /**
     * Get list of enabled features.
     *
     * @return array
     */
    protected function getEnabledFeatures(): array
    {
        return [
            'filter' => $this instanceof ShouldFilter,
            'export' => method_exists($this, 'export'),
            'stats' => $this instanceof HasStats,
            'sort' => method_exists($this, 'sort'),
            'per_page' => method_exists($this, 'perPage'),
            'batch_actions' => true
        ];
    }
}
