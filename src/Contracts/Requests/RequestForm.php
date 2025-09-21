<?php

namespace RMS\Core\Contracts\Requests;

use Illuminate\Http\Request;

/**
 * Interface for form request validation.
 */
interface RequestForm
{
    /**
     * Get the validation rules for the form.
     *
     * @return array
     */
    public function rules(): array;

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array;

    /**
     * Get custom attribute names for the form.
     *
     * @return array
     */
    public function attributes(): array;

    /**
     * Prepare the request for validation.
     *
     * @param Request $request
     * @return void
     */
    public function prepareForValidation(Request &$request): void;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @param Request $request
     * @return bool
     */
    public function authorizeRequest(Request $request): bool;
}
