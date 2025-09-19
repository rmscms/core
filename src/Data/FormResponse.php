<?php

namespace RMS\Core\Data;

use RMS\Core\Contracts\Form\HasForm;

/**
 * Class for wrapping form response data.
 */
class FormResponse
{
    /**
     * The FormGenerator instance.
     *
     * @var FormGenerator
     */
    public $generator;

    /**
     * The form instance implementing HasForm.
     *
     * @var HasForm
     */
    public $form;

    /**
     * The form values.
     *
     * @var array
     */
    public array $values;

    /**
     * FormResponse constructor.
     *
     * @param FormGenerator $generator
     * @param HasForm $form
     * @param array $values
     */
    public function __construct(FormGenerator $generator, $form, array $values)
    {
        if (!$form instanceof HasForm) {
            throw new \InvalidArgumentException('Form must implement HasForm');
        }
        $this->generator = $generator;
        $this->form = $form;
        $this->values = $values;
    }

    /**
     * Get the FormGenerator instance.
     *
     * @return FormGenerator
     */
    public function getGenerator(): FormGenerator
    {
        return $this->generator;
    }

    /**
     * Get the form instance.
     *
     * @return HasForm
     */
    public function getForm(): HasForm
    {
        return $this->form;
    }

    /**
     * Get the form values.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Get the form metadata.
     *
     * @return array
     */
    public function getMeta(): array
    {
        return $this->generator->meta;
    }

    /**
     * Convert the response to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'fields' => $this->generator->getFields(),
            'values' => $this->values,
            'links' => $this->generator->links,
            'back_button' => $this->generator->back_button,
            'save_and_stay_button' => $this->generator->save_and_stay_button,
            'meta' => $this->generator->meta,
            'validation_rules' => $this->generator->validation_rules,
        ];
    }

    /**
     * Convert the response to JSON.
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
