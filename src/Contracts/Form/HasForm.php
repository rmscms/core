<?php

namespace RMS\Core\Contracts\Form;

use RMS\Core\Contracts\Requests\RequestForm;

/**
 * Interface for classes that manage forms.
 */
interface HasForm
{
    /**
     * Get the form fields.
     *
     * @return array
     */
    public function getFieldsForm(): array;

    /**
     * Get the form URL.
     *
     * @return string
     */
    public function formUrl(): string;

    /**
     * Set the form template.
     *
     * @return void
     */
    public function setTplForm(): void;

    /**
     * Get the form validation rules.
     *
     * @return array
     */
    public function getValidationRules(): array;

    /**
     * Get the form configuration (e.g., method, enctype).
     *
     * @return array
     */
    public function getFormConfig(): array;
}
