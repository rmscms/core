<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Form;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RMS\Core\Data\FormResponse;
use RMS\Core\Data\FormGenerator as Generator;

/**
 * Trait for generating and managing forms.
 * 
 * @package RMS\Core\Traits\Form
 */
trait GenerateForm
{
    /**
     * Default form URL.
     */
    protected ?string $form_url = null;

    /**
     * Default form template.
     */
    protected string $tpl_form = 'form.index';

    /**
     * Show "Save and Stay" button in form.
     */
    protected bool $show_stay_button = true;

    /**
     * Generate form view with specified configuration.
     *
     * @param int|string|null $id
     * @param array|null $fields
     * @param array $options
     * @param bool $renderHtml
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    protected function generateForm(
        int|string|null $id = null,
        ?array $fields = null,
        array $options = [],
        bool $renderHtml = false
    ) {
        $generator = new Generator($this, $id, $fields ?: []);

        // Call rendering pipeline
        $this->beforeRenderView();
        $this->beforeGenerateForm($generator);

        // Apply custom options to generator
        $this->applyGeneratorOptions($generator, $options);

        // Get the model instance before generating FormResponse (for HasFormStats)
        $model = null;
        $isEditMode = $id !== null;
        
        if ($this instanceof \RMS\Core\Contracts\Stats\HasFormStats && $isEditMode) {
            // Get model using the form's model() method
            $model = $this->model($id);
        }
        
        $generated = $generator->generate();
        $this->transformFormResponse($generated);

        // Get form stats if controller supports HasFormStats
        $formStatsData = null;
        
        if ($this instanceof \RMS\Core\Contracts\Stats\HasFormStats) {
            $formStatsData = $this->getFormStats($model, $isEditMode);
        }

        // Prepare data for dynamic template
        $templateData = [
            'fields' => $this->getFieldsForm(),
            'form_values' => $generated->getValues(),
            'form_url' => $this->formUrl(),
            'form_config' => $this->getFormConfig(),
            'title' => $this->title ?? $this->getTitle() ?? 'فرم',
            'form' => $generated,
            'form_stats' => $formStatsData,
            'is_edit_mode' => $isEditMode,
            'model' => $model
        ];
        
        // Call hook to allow controller to modify template data before sending to blade
        $this->beforeSendToTemplate($templateData, $generated);

        $this->view->withVariables($templateData);

        if (!$this->view->hasTpl()) {
            if ($renderHtml) {
                $this->tpl_form = 'form.form';
            }
            $this->view->setTpl($this->getFormTemplate());
        }

        return $this->view->render();
    }

    /**
     * Hook method called before form generation.
     * Override this method to customize form behavior.
     *
     * @param Generator $generator
     * @return void
     */
    protected function beforeGenerateForm(Generator &$generator): void
    {
        // Override in child classes
    }

    /**
     * Transform form response data before rendering.
     * Override this method to modify form data.
     *
     * @param FormResponse $form
     * @return void
     */
    protected function transformFormResponse(FormResponse &$form): void
    {
        // Override in child classes
    }
    
    /**
     * Hook called before sending template data to blade view.
     * Override this method to modify template data before rendering.
     *
     * @param array $templateData Reference to template data array
     * @param FormResponse $generated The generated form response
     * @return void
     */
    protected function beforeSendToTemplate(array &$templateData, FormResponse $generated): void
    {
        // Override in child classes
        // Example usage:
        // $templateData['custom_data'] = 'some value';
        // $templateData['form_values']['field_name'] = 'modified value';
    }

    /**
     * Apply custom options to the generator.
     *
     * @param Generator $generator
     * @param array $options
     * @return void
     */
    protected function applyGeneratorOptions(Generator $generator, array $options): void
    {
        foreach ($options as $key => $value) {
            if (property_exists($generator, $key)) {
                $generator->{$key} = $value;
            }
        }
    }

    /**
     * Get the form submission URL.
     * Automatically detects create vs edit context and returns appropriate URL.
     *
     * @return string
     */
    public function formUrl(): string
    {
        // If a custom form URL is set, use it
        if ($this->form_url !== null) {
            return $this->form_url;
        }

        // Auto-detect create vs edit mode from route parameters
        $routeParameter = $this->routeParameter();
        $paramValue = request()->route($routeParameter);
        
        if ($paramValue !== null) {
            // Edit mode - has route parameter (user, post, etc.)
            return route($this->prefix_route . $this->baseRoute() . '.update', $paramValue);
        }
        
        // Create mode - no route parameter
        return route($this->prefix_route . $this->baseRoute() . '.store');
    }

    /**
     * Set a custom form URL.
     *
     * @param string $url
     * @return $this
     */
    public function setFormUrl(string $url): self
    {
        $this->form_url = $url;
        return $this;
    }

    /**
     * Set whether to show "Save and Stay" button.
     *
     * @param bool $show
     * @return $this
     */
    public function setShowStayButton(bool $show): self
    {
        $this->show_stay_button = $show;
        return $this;
    }

    /**
     * Default create method for showing new resource form.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        return $this->generateForm();
    }

    /**
     * Default edit method for showing existing resource form.
     *
     * @param Request $request
     * @param int|string $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\View\View
     */
    public function edit(Request $request, int|string $id)
    {
        return $this->generateForm($id);
    }

    /**
     * Get the form template name.
     *
     * @return string
     */
    public function getFormTemplate(): string
    {
        return $this->tpl_form;
    }

    /**
     * Set the form template name.
     *
     * @param string $template
     * @return $this
     */
    public function setFormTemplate(string $template): self
    {
        $this->tpl_form = $template;
        return $this;
    }

    /**
     * Generate inline form HTML.
     *
     * @param int|string|null $id
     * @param array|null $fields
     * @param array $options
     * @return string
     */
    public function generateInlineForm(
        int|string|null $id = null,
        ?array $fields = null,
        array $options = []
    ): string {
        return $this->generateForm($id, $fields, $options, true);
    }

    /**
     * Generate modal form HTML.
     *
     * @param int|string|null $id
     * @param array|null $fields
     * @param array $options
     * @return string
     */
    public function generateModalForm(
        int|string|null $id = null,
        ?array $fields = null,
        array $options = []
    ): string {
        $options = array_merge(['modal' => true], $options);
        return $this->generateForm($id, $fields, $options, true);
    }

    /**
     * Get form configuration.
     *
     * @return array
     */
    public function getFormConfig(): array
    {
        return [
            'enctype' => 'multipart/form-data',
            'class' => 'form-horizontal',
            'autocomplete' => 'off',
            'show_stay_button' => $this->show_stay_button
        ];
    }

    /**
     * Check if form should use AJAX submission.
     *
     * @return bool
     */
    protected function useAjaxForm(): bool
    {
        return false;
    }

    /**
     * Get form validation rules.
     * Override this method to provide custom validation rules.
     *
     * @return array
     */
    protected function getFormValidationRules(): array
    {
        return [];
    }

    /**
     * Get form validation messages.
     * Override this method to provide custom validation messages.
     *
     * @return array
     */
    protected function getFormValidationMessages(): array
    {
        return [];
    }

    /**
     * Set the form template.
     * Override this method in child classes to use custom template.
     *
     * @return void
     */
    public function setTplForm(): void
    {
        $this->view->setTpl($this->tpl_form);
    }
}
