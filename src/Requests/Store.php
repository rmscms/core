<?php

namespace RMS\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use RMS\Core\Contracts\Requests\RequestForm;

/**
 * Form request for storing data.
 */
class Store extends FormRequest
{
    /**
     * The controller instance implementing RequestForm.
     *
     * @var RequestForm|null
     */
    protected ?RequestForm $controller = null;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return Auth::guard('admin')->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $this->controller = $this->route()->controller;
        if (!$this->controller instanceof RequestForm) {
            throw new \RuntimeException('Controller must implement RequestForm');
        }
        return $this->controller->rules();
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function messages(): array
    {
        if (!$this->controller instanceof RequestForm) {
            throw new \RuntimeException('Controller must implement RequestForm');
        }
        return $this->controller->messages();
    }

    /**
     * Get custom attribute names.
     *
     * @return array
     */
    public function attributes(): array
    {
        if (!$this->controller instanceof RequestForm) {
            throw new \RuntimeException('Controller must implement RequestForm');
        }
        return $this->controller->attributes();
    }

    /**
     * Prepare the request for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->controller = $this->route()->controller;
        if ($this->controller instanceof RequestForm) {
            $this->controller->prepareForValidation($this);
        }
    }
}
