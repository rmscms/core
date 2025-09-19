<?php

namespace RMS\Core\Contracts\Form;

use RMS\Core\Contracts\Requests\RequestForm;

/**
 * Interface for classes that manage forms.
 * Extends RequestForm for unified validation rules.
 */
interface HasForm extends RequestForm
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
     * Get the form configuration (e.g., method, enctype).
     *
     * @return array
     */
    public function getFormConfig(): array;
    
    // Note: rules() method is inherited from RequestForm interface
}
