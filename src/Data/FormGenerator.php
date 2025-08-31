<?php

namespace RMS\Core\Data;

use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\Form\HasForm;

/**
 * Form generator
 */
class FormGenerator
{
    /**
     * Identifier key for model instance.
     *
     * @var string
     */
    public string $identifier = 'id';

    /**
     * Form instance implementing HasForm.
     *
     * @var HasForm
     */
    public $form;

    /**
     * Array of form fields.
     *
     * @var array
     */
    public array $fields = [];

    /**
     * Model ID for loading form values.
     *
     * @var int|null
     */
    public ?int $id = null;

    /**
     * Values of the form.
     *
     * @var array
     */
    public array $values = [];

    /**
     * Whether to display the back button.
     *
     * @var bool
     */
    public bool $back_button = true;

    /**
     * Array of links for the form.
     *
     * @var array
     */
    public array $links = [];

    /**
     * Whether to display the save and stay button.
     *
     * @var bool
     */
    public bool $save_and_stay_button = true;

    /**
     * Additional metadata for the form.
     *
     * @var array
     */
    public array $meta = [];

    /**
     * Validation rules for the form.
     *
     * @var array
     */
    public array $validation_rules = [];

    /**
     * FormGenerator constructor.
     *
     * @param HasForm $form
     * @param int|null $id
     * @param array $fields
     */
    public function __construct($form, ?int $id = null, array $fields = [])
    {
        if (!$form instanceof HasForm) {
            throw new \InvalidArgumentException('Form must implement HasForm');
        }
        $this->form = $form;
        $this->fields = $fields ?: $form->getFieldsForm();
        $this->id = $id;
    }

    /**
     * Generate the form response.
     *
     * @return FormResponse|null
     */
    public function generate(): ?FormResponse
    {
        $values = $this->form instanceof UseDatabase ? $this->form->model($this->id)->toArray() : [];
        return new FormResponse($this, $this->form, $values);
    }

    /**
     * Add a link to the form.
     *
     * @param Link $link
     * @return $this
     */
    public function link(Link $link): self
    {
        $this->links[] = $link;
        return $this;
    }

    /**
     * Add metadata to the form.
     *
     * @param array $meta
     * @return $this
     */
    public function withMeta(array $meta): self
    {
        $this->meta = array_merge($this->meta, $meta);
        return $this;
    }

    /**
     * Set validation rules for the form.
     *
     * @param array $rules
     * @return $this
     */
    public function withValidation(array $rules): self
    {
        $this->validation_rules = $rules;
        return $this;
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
     * Get the form fields.
     *
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
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
}
