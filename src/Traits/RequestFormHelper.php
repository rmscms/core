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
     * Prepare the request for validation by cleaning amount fields and converting Persian dates.
     *
     * @param Request $request
     * @return void
     */
    public function prepareForValidation(Request &$request): void
    {
        // Clean amount fields (remove commas)
        foreach ($this->amounts as $amount) {
            if ($request->has($amount)) {
                $request->merge([
                    $amount => str_replace(',', '', $request->input($amount)),
                ]);
            }
        }
        
        // Convert Persian dates to Gregorian if PersianDateConverter trait is available
        if (method_exists($this, 'convertPersianDatesToGregorian')) {
            $this->convertPersianDatesToGregorian($request);
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

    /**
     * Get validation rules for the form.
     * Override this method in your controller to provide validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Determine if the user is authorized to make this request.
     * Override this method in your controller for custom authorization.
     *
     * @param Request $request
     * @return bool
     */
    public function authorize(Request $request): bool
    {
        return true; // Default: allow all requests
    }
}
