<?php

namespace RMS\Core\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'remember' => $this->boolean('remember'),
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $loginField = config('cms.admin_login_field', 'email');
        $fieldType = $loginField === 'email' ? 'email' : 'string';
        
        return [
            $loginField => $fieldType === 'email' ? 'required|email|max:255' : 'required|string|max:255',
            'password' => 'required|string|min:6|max:255',
            'remember' => 'nullable|boolean',
            'redirect' => 'nullable|url',
        ];
    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        $loginField = config('cms.admin_login_field', 'email');
        
        return [
            $loginField . '.required' => trans('auth.field_required', ['field' => trans('auth.' . $loginField)]),
            $loginField . '.email' => trans('auth.field_email_invalid'),
            'password.required' => trans('auth.field_required', ['field' => trans('auth.password')]),
            'password.min' => trans('auth.password_min_length', ['min' => 6]),
        ];
    }

    /**
     * Get the login credentials from the request.
     */
    public function getCredentials(): array
    {
        $loginField = config('cms.admin_login_field', 'email');
        
        return [
            $loginField => $this->input($loginField),
            'password' => $this->input('password'),
            'active' => 1, // Only allow active admins
        ];
    }

    /**
     * Get the login field name.
     */
    public function getLoginField(): string
    {
        return config('cms.admin_login_field', 'email');
    }

    /**
     * Check if remember me is requested.
     */
    public function shouldRemember(): bool
    {
        return $this->boolean('remember');
    }

    /**
     * Get the redirect URL if provided.
     */
    public function getRedirectUrl(): ?string
    {
        return $this->input('redirect');
    }
}
