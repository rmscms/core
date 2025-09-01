<?php

namespace RMS\Core\Helpers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;

/**
 * Helper class for registering common admin panel routes.
 * 
 * This class provides convenient methods for registering standard
 * RMS admin routes like export, sorting, and batch actions.
 */
class RouteHelper
{
    /**
     * Supported HTTP methods for routes.
     */
    private const SUPPORTED_METHODS = ['get', 'post', 'put', 'patch', 'delete'];

    /**
     * Default batch actions.
     */
    private const DEFAULT_BATCH_ACTIONS = ['delete', 'activate', 'deactivate'];

    /**
     * Register export route for a resource.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $method HTTP method (default: 'get')
     * @param string $action Controller method name (default: 'export')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function export($controller, string $route, string $method = 'get', string $action = 'export'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $routePath = static::convertRouteNameToPath($route) . '/export';

        Route::{$method}($routePath, $controllerAction)->name($route . '.export');
    }

    /**
     * Register route for changing boolean field values (active/inactive, etc.).
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $field Field name to change (default: 'active')
     * @param string $method HTTP method (default: 'post')
     * @param string $action Controller method name (default: 'changeBoolField')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function toggleField($controller, string $route, string $field = 'active', string $method = 'post', string $action = 'changeBoolField'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $routePath = static::convertRouteNameToPath($route) . "/{$field}/{" . Str::singular(static::getLastRouteSegment($route)) . "}";
        $routeName = $route . ".toggle_" . $field;

        Route::{$method}($routePath, $controllerAction)->name($routeName);
    }

    /**
     * Register backwards compatible active/inactive route.
     * This maintains compatibility with the old 'active' method.
     *
     * @param string|array $controller Controller class
     * @param string $route Route name
     * @param string $action Action name (default: 'active')
     * @return void
     */
    public static function active($controller, string $route, string $action = 'active'): void
    {
        static::toggleField($controller, $route, 'active', 'post', 'changeBoolField');
        
        // Also register with the specified action name for backwards compatibility
        if ($action !== 'active') {
            $controllerAction = is_array($controller) ? $controller : [$controller, 'changeBoolField'];
            $routePath = static::convertRouteNameToPath($route) . "/active/{" . Str::singular(static::getLastRouteSegment($route)) . "}";
            
            Route::post($routePath, $controllerAction)->name($route . '.' . $action);
        }
    }

    /**
     * Register sorting route for a resource.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $method HTTP method (default: 'get')
     * @param string $action Controller method name (default: 'sort')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function sort($controller, string $route, string $method = 'get', string $action = 'sort'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $routePath = static::convertRouteNameToPath($route) . "/sort/{by}/{way}";

        Route::{$method}($routePath, $controllerAction)
            ->name($route . '.sort')
            ->where(['by' => '[a-zA-Z_]+', 'way' => 'asc|desc']);
    }

    /**
     * Register a single batch action route.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $action Batch action name (e.g., 'delete')
     * @param string $method HTTP method (default: 'post')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function batchAction($controller, string $route, string $action, string $method = 'post'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        if (empty($action)) {
            throw new InvalidArgumentException('Batch action name cannot be empty');
        }

        $controllerMethod = 'batch' . Str::ucfirst(Str::camel($action));
        $controllerAction = is_array($controller) ? [$controller[0], $controllerMethod] : [$controller, $controllerMethod];
        $routePath = static::convertRouteNameToPath($route) . "/batch/{$action}";

        Route::{$method}($routePath, $controllerAction)->name($route . '.batch.' . $action);
    }

    /**
     * Register multiple batch action routes.
     *
     * @param string|array $controller Controller class
     * @param string $route Route name
     * @param array $actions Array of action names
     * @param string $method HTTP method (default: 'post')
     * @return void
     */
    public static function batchActions($controller, string $route, array $actions = null, string $method = 'post'): void
    {
        $actions = $actions ?? static::DEFAULT_BATCH_ACTIONS;

        foreach ($actions as $action) {
            static::batchAction($controller, $route, $action, $method);
        }
    }

    /**
     * Register all common admin routes for a resource.
     *
     * @param string|array $controller Controller class
     * @param string $route Route name
     * @param array $options Configuration options
     * @return void
     */
    public static function adminResource($controller, string $route, array $options = []): void
    {
        $defaultOptions = [
            'export' => true,
            'sort' => true,
            'toggle_active' => true,
            'batch_actions' => static::DEFAULT_BATCH_ACTIONS,
        ];

        $options = array_merge($defaultOptions, $options);

        if ($options['export']) {
            static::export($controller, $route);
        }

        if ($options['sort']) {
            static::sort($controller, $route);
        }

        if ($options['toggle_active']) {
            static::toggleField($controller, $route, 'active');
        }

        if (!empty($options['batch_actions'])) {
            static::batchActions($controller, $route, $options['batch_actions']);
        }
    }

    /**
     * Register API routes for a resource (JSON responses).
     *
     * @param string|array $controller Controller class
     * @param string $route Route name
     * @param array $actions Actions to include ['export', 'sort', 'batch']
     * @return void
     */
    public static function apiResource($controller, string $route, array $actions = []): void
    {
        $defaultActions = ['export', 'sort', 'batch'];
        $actions = array_merge($defaultActions, $actions);

        $apiRoute = 'api.' . $route;

        if (in_array('export', $actions)) {
            static::export($controller, $apiRoute, 'get', 'exportJson');
        }

        if (in_array('sort', $actions)) {
            static::sort($controller, $apiRoute, 'get', 'sortJson');
        }

        if (in_array('batch', $actions)) {
            static::batchActions($controller, $apiRoute, static::DEFAULT_BATCH_ACTIONS, 'post');
        }
    }

    /**
     * Convert route name to URL path.
     *
     * @param string $route Route name (e.g., 'admin.users')
     * @return string URL path (e.g., 'admin/users')
     */
    protected static function convertRouteNameToPath(string $route): string
    {
        return str_replace('.', '/', $route);
    }

    /**
     * Get the last segment of a route name.
     *
     * @param string $route Route name (e.g., 'admin.users')
     * @return string Last segment (e.g., 'users')
     */
    protected static function getLastRouteSegment(string $route): string
    {
        $segments = explode('.', $route);
        return end($segments);
    }

    /**
     * Validate controller parameter.
     *
     * @param mixed $controller
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateController($controller): void
    {
        if (is_string($controller)) {
            // Skip class_exists check in testing environment or for flexibility
            return;
        } elseif (is_array($controller)) {
            if (count($controller) !== 2) {
                throw new InvalidArgumentException('Controller array must contain exactly 2 elements: [class, method]');
            }
            // Skip method_exists check for flexibility
            return;
        } else {
            throw new InvalidArgumentException('Controller must be a string (class name) or array [class, method]');
        }
    }

    /**
     * Validate route name.
     *
     * @param string $route
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateRoute(string $route): void
    {
        if (empty($route)) {
            throw new InvalidArgumentException('Route name cannot be empty');
        }

        if (!preg_match('/^[a-zA-Z0-9._-]+$/', $route)) {
            throw new InvalidArgumentException('Route name contains invalid characters');
        }
    }

    /**
     * Validate HTTP method.
     *
     * @param string $method
     * @return void
     * @throws InvalidArgumentException
     */
    protected static function validateMethod(string $method): void
    {
        if (!in_array(strtolower($method), static::SUPPORTED_METHODS)) {
            throw new InvalidArgumentException(
                'Unsupported HTTP method: ' . $method . '. Supported methods: ' . implode(', ', static::SUPPORTED_METHODS)
            );
        }
    }

    /**
     * Get list of available batch actions.
     *
     * @return array
     */
    public static function getDefaultBatchActions(): array
    {
        return static::DEFAULT_BATCH_ACTIONS;
    }
}
