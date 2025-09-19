<?php

declare(strict_types=1);

namespace RMS\Core\Traits;

use RMS\Core\Contracts\Filter\ShouldFilter;
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Traits\Actions\BoolAction;
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
use RMS\Core\Traits\Stats\StatsCardControl;
use RMS\Core\Traits\RequestFormHelper;
use RMS\Core\Traits\PersianDateConverter;
use RMS\Core\Traits\Upload\HasFileUpload;
use RMS\Core\Data\FormGenerator;
use RMS\Core\Data\UploadConfig;
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
    use StatsCardControl;
    use BoolAction;
    use HelperController;
    use Sortable;
    use PersianDateConverter;
    use HasFileUpload;

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
        $plugins = [];

        // Check if we need persian-datepicker for date/datetime filters
        if ($this->hasDateTimeFields()) {
            $plugins[] = 'persian-datepicker';
        }

        // Check if we need amount-formatter for price/amount filters
        if ($this->hasAmountFields()) {
            $plugins[] = 'amount-formatter';
        }

        // Check if we need advanced-select for enhanced select filters
        if ($this->hasSelectListFields()) {
            $plugins[] = 'advanced-select';
        }

        // Check if we need avatar-viewer for IMAGE fields with viewer_enabled
        if ($this->hasImageListFields()) {
            $plugins[] = 'avatar-viewer';
        }

        $this->view
            ->withPlugins($plugins)
            ->withJs('list.js');
    }

    /**
     * Configure assets for form views.
     *
     * @return void
     */
    protected function configureFormAssets(): void
    {
        $plugins = [];

        // Check if we need persian-datepicker for date/datetime form fields
        if ($this->hasDateTimeFormFields()) {
            $plugins[] = 'persian-datepicker';
        }

        // Check if we need amount-formatter for price/amount form fields
        if ($this->hasAmountFormFields()) {
            $plugins[] = 'amount-formatter';
        }

        // Check if we need advanced-select for enhanced select fields
        if ($this->hasSelectFormFields()) {
            $plugins[] = 'advanced-select';
        }

        // Check if we need image-uploader for image form fields
        if ($this->hasImageFormFields()) {
            $plugins[] = 'image-uploader';
        }

        $this->view
            ->withPlugins($plugins)
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

    /**
     * Check if controller has date/datetime fields in list for filtering.
     *
     * @return bool
     */
    protected function hasDateTimeFields(): bool
    {
        if (!method_exists($this, 'getListFields')) {
            return false;
        }

        foreach ($this->getListFields() as $field) {
            // Check if field is filterable and has date/datetime filter type
            if ((isset($field->attributes['filterable']) && $field->attributes['filterable']) ||
                (property_exists($field, 'filter') && $field->filter)) {
                if (property_exists($field, 'filter_type') &&
                    in_array($field->filter_type, [\RMS\Core\Data\Field::DATE, \RMS\Core\Data\Field::DATE_TIME])) {
                    return true;
                }
            }
            // Also check if field type itself is date/datetime (for form fields in list)
            if (property_exists($field, 'type') &&
                in_array($field->type, [\RMS\Core\Data\Field::DATE, \RMS\Core\Data\Field::DATE_TIME])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has date/datetime fields in form.
     *
     * @return bool
     */
    protected function hasDateTimeFormFields(): bool
    {
        if (!method_exists($this, 'getFieldsForm')) {
            return false;
        }

        foreach ($this->getFieldsForm() as $field) {
            if (property_exists($field, 'type') &&
                in_array($field->type, [\RMS\Core\Data\Field::DATE, \RMS\Core\Data\Field::DATE_TIME])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has amount/price fields in list for filtering.
     *
     * @return bool
     */
    protected function hasAmountFields(): bool
    {
        if (!method_exists($this, 'getListFields')) {
            return false;
        }

        foreach ($this->getListFields() as $field) {
            // Check if field is filterable and has price/amount type
            if ((isset($field->attributes['filterable']) && $field->attributes['filterable']) ||
                (property_exists($field, 'filter') && $field->filter)) {
                if (property_exists($field, 'type') && $field->type === \RMS\Core\Data\Field::PRICE) {
                    return true;
                }
            }
            // Also check if field type itself is price/amount
            if (property_exists($field, 'type') && $field->type === \RMS\Core\Data\Field::PRICE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has amount/price fields in form.
     *
     * @return bool
     */
    protected function hasAmountFormFields(): bool
    {
        if (!method_exists($this, 'getFieldsForm')) {
            return false;
        }

        foreach ($this->getFieldsForm() as $field) {
            if (property_exists($field, 'type') && $field->type === \RMS\Core\Data\Field::PRICE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has image fields in form.
     *
     * @return bool
     */
    protected function hasImageFormFields(): bool
    {
        if (!method_exists($this, 'getFieldsForm')) {
            return false;
        }

        foreach ($this->getFieldsForm() as $field) {
            if (property_exists($field, 'type') && $field->type === \RMS\Core\Data\Field::IMAGE) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has select fields in form that need Tom Select.
     * Only enables Tom Select for advanced/complex select fields.
     *
     * @return bool
     */
    protected function hasSelectFormFields(): bool
    {
        if (!method_exists($this, 'getFieldsForm')) {
            return false;
        }

        foreach ($this->getFieldsForm() as $field) {
            if ($this->shouldUseAdvancedSelect($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has select fields in list (filters) that need Tom Select.
     * Only enables Tom Select for advanced/complex select fields.
     *
     * @return bool
     */
    protected function hasSelectListFields(): bool
    {
        if (!method_exists($this, 'getListFields')) {
            return false;
        }

        foreach ($this->getListFields() as $field) {
            // Check if field is filterable and needs advanced select
            if ((isset($field->attributes['filterable']) && $field->attributes['filterable']) ||
                (property_exists($field, 'filter') && $field->filter)) {
                if ($this->shouldUseAdvancedSelect($field)) {
                    return true;
                }
            }
            // Also check if field type itself needs advanced select
            if ($this->shouldUseAdvancedSelect($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a select field needs advanced select functionality.
     * Simple select fields (few options, no search needed) stay as native selects.
     *
     * @param mixed $field
     * @return bool
     */
    protected function shouldUseAdvancedSelect($field): bool
    {
        // Must be a SELECT type field first
        if (!property_exists($field, 'type') || $field->type !== \RMS\Core\Data\Field::SELECT) {
            // Also check for fields with select_data
            if (!property_exists($field, 'select_data') || empty($field->select_data)) {
                return false;
            }
        }

        // Primary check: if advanced() method was called on the field
        if (property_exists($field, 'advanced') && $field->advanced === true) {
            return true;
        }

        // Secondary checks for auto-detection (if advanced not explicitly set)

        // 1. Multiple selection always needs advanced UI
        if (property_exists($field, 'multiple') && $field->multiple === true) {
            return true;
        }

        // 2. AJAX loading requires advanced functionality
        if (property_exists($field, 'ajax_url') && !empty($field->ajax_url)) {
            return true;
        }

        // 3. Search functionality explicitly enabled
        if (property_exists($field, 'searchable') && $field->searchable === true) {
            return true;
        }

        // 4. Create new options functionality
        if (property_exists($field, 'creatable') && $field->creatable === true) {
            return true;
        }

        // 5. Large number of options (more than threshold)
        $options = $this->getFieldOptions($field);
        if (is_array($options) && count($options) > config('cms.select.advanced_threshold', 10)) {
            return true;
        }

        // 6. Complex option structure (nested, with images, etc.)
        if ($this->hasComplexOptions($options)) {
            return true;
        }

        // Default: use native select for simple cases
        return false;
    }

    /**
     * Get options for a select field.
     *
     * @param mixed $field
     * @return array|null
     */
    protected function getFieldOptions($field): ?array
    {
        // Check various ways options might be stored
        if (property_exists($field, 'select_data') && is_array($field->select_data)) {
            return $field->select_data;
        }

        if (property_exists($field, 'options') && is_array($field->options)) {
            return $field->options;
        }

        if (property_exists($field, 'choices') && is_array($field->choices)) {
            return $field->choices;
        }

        return null;
    }

    /**
     * Check if options are complex (nested, with metadata, etc.).
     *
     * @param array|null $options
     * @return bool
     */
    protected function hasComplexOptions(?array $options): bool
    {
        if (empty($options)) {
            return false;
        }

        // Check first few options for complexity
        $sampleOptions = array_slice($options, 0, 3, true);

        foreach ($sampleOptions as $key => $value) {
            // Array values indicate complex structure
            if (is_array($value)) {
                return true;
            }

            // Very long text might need search
            if (is_string($value) && strlen($value) > 50) {
                return true;
            }

            // HTML content
            if (is_string($value) && strip_tags($value) !== $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if controller has image fields in list that need viewer.
     *
     * @return bool
     */
    protected function hasImageListFields(): bool
    {
        if (!method_exists($this, 'getListFields')) {
            return false;
        }

        foreach ($this->getListFields() as $field) {
            if (property_exists($field, 'type') && $field->type === \RMS\Core\Data\Field::IMAGE) {
                // Check if this field has viewer_enabled in upload config
                if (method_exists($this, 'getUploadConfig') && $this->shouldEnableImageViewer($field->key)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Process IMAGE fields in list and add customMethod if viewer_enabled.
     *
     * @return void
     */
    protected function processImageFieldsForList(): void
    {

        if (!method_exists($this, 'getListFields') || !method_exists($this, 'getUploadConfig')) {
            return;
        }

        $uploadConfig = $this->getNormalizedUploadConfigForList();

        foreach ($this->getListFields() as &$field) {
            if (property_exists($field, 'type') && $field->type === \RMS\Core\Data\Field::IMAGE) {
                $fieldConfig = $uploadConfig[$field->key] ?? [];
                // اگر viewer فعال باشد و customMethod قبلاً تعریف نشده باشد
                if (($fieldConfig['viewer_enabled'] ?? false) && empty($field->customMethod)) {
                    $field->customMethod('renderImageField');
                }
            }
        }
        unset($field); // ✅ Clean up reference
    }

    /**
     * Check if a field should have image viewer enabled.
     *
     * @param string $fieldName
     * @return bool
     */
    protected function shouldEnableImageViewer(string $fieldName): bool
    {
        if (!method_exists($this, 'getUploadConfig')) {
            return false;
        }

        $uploadConfig = $this->getNormalizedUploadConfigForList();
        $fieldConfig = $uploadConfig[$fieldName] ?? [];

        return $fieldConfig['viewer_enabled'] ?? false;
    }

    /**
     * Normalize upload configuration to array
     * تبدیل UploadConfig Object به array برای سازگاری با کد موجود
     * 
     * @param mixed $config
     * @return array
     */
    protected function normalizeUploadConfigItem($config): array
    {
        if ($config instanceof UploadConfig) {
            return $config->toArray();
        }
        
        return (array) $config;
    }

    /**
     * دریافت تنظیمات آپلود به صورت normalized برای FormAndList
     * 
     * @return array
     */
    protected function getNormalizedUploadConfigForList(): array
    {
        if (!method_exists($this, 'getUploadConfig')) {
            return [];
        }
        
        $config = $this->getUploadConfig();
        $normalizedConfig = [];
        
        foreach ($config as $fieldName => $fieldConfig) {
            $normalizedConfig[$fieldName] = $this->normalizeUploadConfigItem($fieldConfig);
        }
        
        return $normalizedConfig;
    }
}
