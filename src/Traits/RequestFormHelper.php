<?php

namespace RMS\Core\Traits;

use Illuminate\Http\Request;

/**
 * Trait for helping with form request validation.
 */
trait RequestFormHelper
{
    /**
     * Fields to clean amounts (e.g., remove commas).
     *
     * @var array
     */
    protected array $amounts = ['amount'];

    /**
     * Get custom attribute names.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Prepare the request for validation by cleaning amount fields.
     *
     * @param Request $request
     * @return void
     */
    public function prepareForValidation(Request &$request): void
    {
        foreach ($this->amounts as $amount) {
            $request->merge([
                $amount => str_replace(',', '', $request->input($amount)),
            ]);
        }
    }

    /**
     * Add fields to clean amounts.
     *
     * @param array $fields
     * @return $this
     */
    public function addAmountFields(array $fields): self
    {
        $this->amounts = array_merge($this->amounts, $fields);
        return $this;
    }
}
