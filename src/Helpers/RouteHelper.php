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
    public static function toggleField($controller, string $route, string $field = 'active', string $method = 'post', string $action = 'toggleBoolField'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $routePath = static::convertRouteNameToPath($route) . "/{" . Str::singular(static::getLastRouteSegment($route)) . "}/toggle/{$field}";
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
     * Register filter route for a resource.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $method HTTP method (default: 'post')
     * @param string $action Controller method name (default: 'filter')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function filter($controller, string $route, string $method = 'post', string $action = 'filter'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $routePath = static::convertRouteNameToPath($route) . "/filter";

        Route::{$method}($routePath, $controllerAction)->name($route . '.filter');
    }

    /**
     * Register clear filter route for a resource.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $method HTTP method (default: 'get')
     * @param string $action Controller method name (default: 'clearFilter')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function clearFilter($controller, string $route, string $method = 'get', string $action = 'clearFilter'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $routePath = static::convertRouteNameToPath($route) . "/clear-filter";

        Route::{$method}($routePath, $controllerAction)->name($route . '.clear_filter');
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
     * Register AJAX file upload route for a specific field.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $fieldName Field name (e.g., 'avatar', 'gallery')
     * @param string $method HTTP method (default: 'post')
     * @param string $action Controller method name (default: 'ajaxUpload')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function ajaxFileUpload($controller, string $route, string $fieldName, string $method = 'post', string $action = 'ajaxUpload'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        if (empty($fieldName)) {
            throw new InvalidArgumentException('Field name cannot be empty');
        }

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $singular = Str::singular(static::getLastRouteSegment($route));
        // تغییر: fieldName به عنوان پارامتر Laravel تعریف می‌شه
        $routePath = static::convertRouteNameToPath($route) . "/{{$singular}}/ajax-upload/{{$fieldName}}";
        $routeName = $route . ".ajax_upload_{$fieldName}";

        Route::{$method}($routePath, $controllerAction)->name($routeName);
    }

    /**
     * Register AJAX file delete route for a specific field.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $fieldName Field name (e.g., 'avatar', 'gallery')
     * @param string $method HTTP method (default: 'delete')
     * @param string $action Controller method name (default: 'ajaxDeleteFile')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function ajaxFileDelete($controller, string $route, string $fieldName, string $method = 'delete', string $action = 'ajaxDeleteFile'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        if (empty($fieldName)) {
            throw new InvalidArgumentException('Field name cannot be empty');
        }

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $singular = Str::singular(static::getLastRouteSegment($route));
        // تغییر: fieldName به عنوان پارامتر Laravel تعریف می‌شه
        $routePath = static::convertRouteNameToPath($route) . "/{{$singular}}/ajax-delete/{{$fieldName}}";
        $routeName = $route . ".ajax_delete_{$fieldName}";

        Route::{$method}($routePath, $controllerAction)->name($routeName);
    }

    /**
     * Register AJAX file upload and delete routes for multiple fields.
     *
     * @param string|array $controller Controller class
     * @param string $route Route name
     * @param array $fields Array of field names
     * @return void
     */
    public static function ajaxFileRoutes($controller, string $route, array $fields): void
    {
        foreach ($fields as $fieldName) {
            static::ajaxFileUpload($controller, $route, $fieldName);
            static::ajaxFileDelete($controller, $route, $fieldName);
        }
    }

    /**
     * Register image viewer route for displaying images in modal.
     *
     * @param string|array $controller Controller class or [class, method]
     * @param string $route Route name (e.g., 'admin.users')
     * @param string $method HTTP method (default: 'get')
     * @param string $action Controller method name (default: 'handleImageViewer')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function imageViewer($controller, string $route, string $method = 'get', string $action = 'handleImageViewer'): void
    {
        static::validateController($controller);
        static::validateRoute($route);
        static::validateMethod($method);

        $controllerAction = is_array($controller) ? $controller : [$controller, $action];
        $singular = Str::singular(static::getLastRouteSegment($route));
        $routePath = static::convertRouteNameToPath($route) . "/{{$singular}}/image-viewer/{field}";
        $routeName = $route . ".image-viewer";

        Route::{$method}($routePath, $controllerAction)->name($routeName);
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
            'filter' => true,
            'toggle_active' => true,
            'batch_actions' => static::DEFAULT_BATCH_ACTIONS,
            'ajax_files' => [], // Array of field names for AJAX file upload
            'image_viewer' => false, // Enable image viewer for IMAGE fields
        ];

        $options = array_merge($defaultOptions, $options);

        if ($options['export']) {
            static::export($controller, $route);
        }

        if ($options['sort']) {
            static::sort($controller, $route);
        }

        if ($options['filter']) {
            static::filter($controller, $route);
            // Automatically register clear filter route when filter is enabled
            static::clearFilter($controller, $route);
        }

        if ($options['toggle_active']) {
            static::toggleField($controller, $route, 'active');
            
            // Auto-register toggle routes for all boolean fields if controller implements ChangeBoolField
            $controllerClass = is_array($controller) ? $controller[0] : $controller;
            if (is_string($controllerClass) && class_exists($controllerClass)) {
                $reflection = new \ReflectionClass($controllerClass);
                if ($reflection->implementsInterface(\RMS\Core\Contracts\Actions\ChangeBoolField::class)) {
                    // Get boolean fields from controller
                    $tempController = app($controllerClass);
                    if (method_exists($tempController, 'boolFields')) {
                        $boolFields = $tempController->boolFields();
                        foreach ($boolFields as $field) {
                            if ($field !== 'active') { // Skip 'active' as it's already registered
                                static::toggleField($controller, $route, $field);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($options['batch_actions'])) {
            static::batchActions($controller, $route, $options['batch_actions']);
        }

        if (!empty($options['ajax_files'])) {
            static::ajaxFileRoutes($controller, $route, $options['ajax_files']);
        }

        if ($options['image_viewer']) {
            static::imageViewer($controller, $route);
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
     * @param string $route Route name (e.g., 'admin.users' or just 'users')
     * @return string URL path (e.g., 'users' - uses last segment only)
     */
    protected static function convertRouteNameToPath(string $route): string
    {
        // Use only the last segment of the route name
        // This works whether we get 'admin.users' or just 'users'
        return static::getLastRouteSegment($route);
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
     * Register Cache Management routes.
     * 
     * These routes provide cache management functionality including:
     * - Clear all cache
     * - Clear specific cache types
     * - Cache status information
     * - Cache statistics
     *
     * @param string|array $controller Controller class (usually CacheManagerController)
     * @param string $routePrefix Route prefix (default: 'admin.cache')
     * @return void
     * @throws InvalidArgumentException
     */
    public static function adminCacheRoutes($controller = 'RMS\\Core\\Http\\Controllers\\Admin\\CacheManagerController', string $routePrefix = 'admin.cache'): void
    {
        static::validateController($controller);
        static::validateRoute($routePrefix);

        $basePath = static::convertRouteNameToPath($routePrefix);
        
        // Clear all cache - POST /admin/cache/clear
        $clearAllAction = is_array($controller) ? $controller : [$controller, 'clearAll'];
        Route::post("{$basePath}/clear", $clearAllAction)->name($routePrefix . '.clear_all');
        
        // Clear specific cache - POST /admin/cache/clear/{type}
        $clearSpecificAction = is_array($controller) ? $controller : [$controller, 'clearSpecific'];
        Route::post("{$basePath}/clear/{type}", $clearSpecificAction)
            ->name($routePrefix . '.clear_specific')
            ->where('type', 'application|config|route|view|optimize|opcache');
        
        // Cache status - GET /admin/cache/status
        $statusAction = is_array($controller) ? $controller : [$controller, 'status'];
        Route::get("{$basePath}/status", $statusAction)->name($routePrefix . '.status');
        
        // Cache stats - GET /admin/cache/stats  
        $statsAction = is_array($controller) ? $controller : [$controller, 'stats'];
        Route::get("{$basePath}/stats", $statsAction)->name($routePrefix . '.stats');
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
