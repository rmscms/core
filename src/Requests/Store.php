<?php

declare(strict_types=1);

namespace RMS\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use RMS\Core\Contracts\Requests\RequestForm;
use RMS\Core\Exceptions\ValidationException;
use RuntimeException;

/**
 * Enhanced form request for storing data with modern Laravel 12 features.
 * 
 * این کلاس برای اعتبارسنجی درخواست‌های ذخیره داده استفاده می‌شود
 * 
 * @package RMS\Core\Requests
 */
class Store extends FormRequest
{
    /**
     * The controller instance implementing RequestForm.
     */
    protected ?RequestForm $controller = null;

    /**
     * Whether the controller has been resolved and validated.
     */
    protected bool $controllerResolved = false;

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        // بررسی احراز هویت کاربر ادمین
        $isAuthenticated = Auth::guard('admin')->check();
        
        if (!$isAuthenticated) {
            Log::warning('Unauthorized store request attempt', [
                'ip' => $this->ip(),
                'user_agent' => $this->userAgent(),
                'route' => $this->route()?->getName()
            ]);
        }
        
        return $isAuthenticated;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     * @throws ValidationException
     */
    public function rules(): array
    {
        $controller = $this->resolveController();
        
        try {
            $rules = $controller->rules();
            
            // اعتبارسنجی که rules یک آرایه معتبر باشد
            if (!is_array($rules)) {
                throw new ValidationException('Validation rules must be an array');
            }
            
            return $rules;
        } catch (\Exception $e) {
            Log::error('Failed to get validation rules', [
                'controller' => get_class($controller),
                'error' => $e->getMessage()
            ]);
            
            throw new ValidationException('Unable to retrieve validation rules: ' . $e->getMessage());
        }
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        $controller = $this->resolveController();
        
        try {
            $messages = $controller->messages();
            return is_array($messages) ? $messages : [];
        } catch (\Exception $e) {
            Log::warning('Failed to get validation messages', [
                'controller' => get_class($controller),
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Get custom attribute names.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        $controller = $this->resolveController();
        
        try {
            $attributes = $controller->attributes();
            return is_array($attributes) ? $attributes : [];
        } catch (\Exception $e) {
            Log::warning('Failed to get validation attributes', [
                'controller' => get_class($controller),
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Prepare the request for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        try {
            $controller = $this->resolveController();
            $controller->prepareForValidation($this);
        } catch (\Exception $e) {
            Log::error('Failed to prepare request for validation', [
                'error' => $e->getMessage(),
                'route' => $this->route()?->getName()
            ]);
            // در صورت خطا در preparation، ادامه می‌دهیم
        }
    }

    /**
     * Resolve and validate the controller instance.
     *
     * @return RequestForm
     * @throws ValidationException
     */
    protected function resolveController(): RequestForm
    {
        if ($this->controllerResolved && $this->controller instanceof RequestForm) {
            return $this->controller;
        }
        
        $routeController = $this->route()?->controller;
        
        if (!$routeController) {
            throw new ValidationException('No controller found in route');
        }
        
        if (!$routeController instanceof RequestForm) {
            throw new ValidationException(
                sprintf(
                    'Controller %s must implement RequestForm interface',
                    get_class($routeController)
                )
            );
        }
        
        $this->controller = $routeController;
        $this->controllerResolved = true;
        
        return $this->controller;
    }

    /**
     * Get the controller instance if resolved.
     *
     * @return RequestForm|null
     */
    public function getController(): ?RequestForm
    {
        return $this->controller;
    }

    /**
     * Check if controller has been resolved successfully.
     *
     * @return bool
     */
    public function hasController(): bool
    {
        return $this->controllerResolved && $this->controller instanceof RequestForm;
    }
}
