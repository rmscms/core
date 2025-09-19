<?php

declare(strict_types=1);

namespace RMS\Core\Traits\Controllers;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * Trait for common controller helper methods.
 * 
 * @package RMS\Core\Traits\Controllers
 */
trait HelperController
{
    /**
     * Get controller instance from encrypted request key.
     *
     * @param Request $request
     * @return object
     * @throws HttpResponseException
     */
    protected function controller(Request $request): object
    {
        try {
            $encryptedKey = $request->input('key');
            
            if (!$encryptedKey) {
                throw new \InvalidArgumentException('Controller key is required');
            }
            
            $controllerClass = decrypt($encryptedKey);
            
            if (!class_exists($controllerClass)) {
                throw new \InvalidArgumentException("Controller class {$controllerClass} does not exist");
            }
            
            // Use Laravel's service container to resolve dependencies
            return app($controllerClass);
            
        } catch (\Exception $exception) {
            Log::warning('Invalid controller access attempt', [
                'request_data' => $request->only(['key']),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'error' => $exception->getMessage()
            ]);
            
            abort(403, 'Invalid controller or unauthorized access');
        }
    }

    /**
     * Stop execution and redirect with error message.
     *
     * @param string $message
     * @param string|null $route
     * @return never
     * @throws HttpResponseException
     */
    protected function stop(string $message, ?string $route = null): never
    {
        $redirect = $route ? redirect(route($route)) : back();
        
        throw new HttpResponseException($redirect->withErrors($message));
    }

    /**
     * Stop execution and redirect with validation errors.
     *
     * @param array $errors
     * @param string|null $route
     * @return never
     * @throws HttpResponseException
     */
    protected function stopWithErrors(array $errors, ?string $route = null): never
    {
        $redirect = $route ? redirect(route($route)) : back();
        
        throw new HttpResponseException($redirect->withErrors($errors));
    }

    /**
     * Stop execution and redirect with validation exception.
     *
     * @param ValidationException $exception
     * @param string|null $route
     * @return never
     * @throws HttpResponseException
     */
    protected function stopWithValidation(ValidationException $exception, ?string $route = null): never
    {
        $redirect = $route ? redirect(route($route)) : back();
        
        throw new HttpResponseException(
            $redirect
                ->withErrors($exception->errors())
                ->withInput()
        );
    }

    /**
     * Redirect with success message.
     *
     * @param string $message
     * @param string|null $route
     * @return RedirectResponse
     */
    protected function redirectWithSuccess(string $message, ?string $route = null): RedirectResponse
    {
        $redirect = $route ? redirect(route($route)) : back();
        
        return $redirect->with('success', $message);
    }

    /**
     * Redirect with warning message.
     *
     * @param string $message
     * @param string|null $route
     * @return RedirectResponse
     */
    protected function redirectWithWarning(string $message, ?string $route = null): RedirectResponse
    {
        $redirect = $route ? redirect(route($route)) : back();
        
        return $redirect->with('warning', $message);
    }

    /**
     * Redirect with info message.
     *
     * @param string $message
     * @param string|null $route
     * @return RedirectResponse
     */
    protected function redirectWithInfo(string $message, ?string $route = null): RedirectResponse
    {
        $redirect = $route ? redirect(route($route)) : back();
        
        return $redirect->with('info', $message);
    }

    /**
     * Check if current user is admin.
     *
     * @return bool
     */
    protected function isAdmin(): bool
    {
        return auth('admin')->check();
    }

    /**
     * Get current authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    protected function getCurrentUser(): ?\Illuminate\Contracts\Auth\Authenticatable
    {
        return $this->isAdmin() ? auth('admin')->user() : auth()->user();
    }

    /**
     * Check if current user has permission.
     *
     * @param string $permission
     * @return bool
     */
    protected function hasPermission(string $permission): bool
    {
        $user = $this->getCurrentUser();
        
        if (!$user) {
            return false;
        }
        
        return method_exists($user, 'can') ? $user->can($permission) : true;
    }

    /**
     * Abort if user doesn't have permission.
     *
     * @param string $permission
     * @return void
     * @throws HttpResponseException
     */
    protected function requirePermission(string $permission): void
    {
        if (!$this->hasPermission($permission)) {
            abort(403, trans('admin.insufficient_permissions'));
        }
    }

    /**
     * Log controller action for audit purposes.
     *
     * @param string $action
     * @param array $context
     * @return void
     */
    protected function logAction(string $action, array $context = []): void
    {
        Log::info('Controller action performed', [
            'controller' => get_class($this),
            'action' => $action,
            'user_id' => $this->getCurrentUser()?->id,
            'context' => $context,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Get controller base name for logging and caching.
     *
     * @return string
     */
    protected function getControllerBaseName(): string
    {
        return class_basename($this);
    }

    /**
     * Generate encrypted controller key for forms.
     *
     * @return string
     */
    protected function getEncryptedControllerKey(): string
    {
        return encrypt(get_class($this));
    }
}
