<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Form;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use RMS\Core\Data\FormResponse;
use RMS\Core\View\HelperForm\Generator;

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
     * Generate form view with specified configuration.
     *
     * @param int|string|null $id
     * @param array|null $fields
     * @param array $options
     * @param bool $renderHtml
     * @return Response|string
     */
    protected function generateForm(
        int|string|null $id = null,
        ?array $fields = null,
        array $options = [],
        bool $renderHtml = false
    ): Response|string {
        $generator = new Generator($this, $id, $fields);

        // Call rendering pipeline
        $this->beforeRenderView();
        $this->beforeGenerateForm($generator);

        // Apply custom options to generator
        $this->applyGeneratorOptions($generator, $options);

        $generated = $generator->generate();
        $this->transformFormResponse($generated);

        $this->view->withVariables(['form' => $generated]);

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
     *
     * @return string
     */
    public function formUrl(): string
    {
        return $this->form_url ?? route($this->prefix_route . $this->baseRoute() . '.store');
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
     * Default create method for showing new resource form.
     *
     * @param Request $request
     * @return Response|string
     */
    public function create(Request $request): Response|string
    {
        return $this->generateForm();
    }

    /**
     * Default edit method for showing existing resource form.
     *
     * @param Request $request
     * @param int|string $id
     * @return Response|string
     */
    public function edit(Request $request, int|string $id): Response|string
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
    protected function getFormConfig(): array
    {
        return [
            'method' => 'POST',
            'enctype' => 'multipart/form-data',
            'class' => 'form-horizontal',
            'autocomplete' => 'off'
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
}
