<?php

namespace RMS\Core\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use RMS\Core\Debug\RMSDebugger;
use RMS\Core\Debug\DebugLogger;

/**
 * DebugMiddleware - ضبط خودکار اطلاعات debug در هر request
 * 
 * این middleware برای هر request اطلاعات debug را جمع‌آوری کرده
 * و در فایل‌های لاگ ذخیره می‌کند تا قابل بررسی خارجی باشد
 * 
 * @author RMS Core Team
 * @version 2.0.0
 */
class DebugMiddleware
{
    /**
     * Instance RMSDebugger
     */
    private ?RMSDebugger $debugger = null;

    /**
     * Instance DebugLogger
     */
    private ?DebugLogger $logger = null;

    /**
     * زمان شروع request
     */
    private float $startTime;

    /**
     * حافظه شروع request
     */
    private int $startMemory;

    public function __construct()
    {
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        
        // ✅ فعال‌سازی مقدم debugger برای ثبت همه کوئری‌ها
        if ($this->shouldEnableDebug()) {
            // ایجاد instance جدید برای هر request
            $this->debugger = new RMSDebugger(false); // loadFromLogs = false
            $this->logger = new DebugLogger();
            
            // فعال‌سازی فوری سیستم ثبت کوئری
            $this->debugger->toggle(true);
        }
    }

    /**
     * Handle an incoming request
     */
    public function handle(Request $request, Closure $next)
    {
        // Pre-processing
        $this->logRequestStart($request);
        
        try {
            $response = $next($request);
            
            // Post-processing
            $this->logRequestEnd($request, $response);
            
            return $response;
            
        } catch (\Throwable $exception) {
            // Log exception
            $this->logRequestException($request, $exception);
            throw $exception;
        }
    }

    /**
     * لاگ شروع request
     */
    private function logRequestStart(Request $request): void
    {
        if (!$this->shouldLog()) return;

        $requestData = [
            'timestamp' => now()->toISOString(),
            'type' => 'REQUEST_START',
            'request_id' => $this->generateRequestId(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'route' => $request->route()?->getName() ?? 'unknown',
            'controller' => $this->getControllerName($request),
            'action' => $this->getActionName($request),
            'ip' => $request->ip(),
            'user_agent' => $request->header('User-Agent'),
            'headers' => $this->filterSensitiveHeaders($request->headers->all()),
            'query_params' => $request->query(),
            'has_files' => $request->hasFile('*'),
            'files_count' => count($request->allFiles()),
            'content_type' => $request->header('Content-Type'),
            'request_size' => strlen($request->getContent()),
            'memory_at_start' => $this->startMemory,
            'memory_at_start_formatted' => $this->formatBytes($this->startMemory)
        ];

        // اگر POST/PUT request باشد، داده‌های فرم را لاگ کن (بدون sensitive data)
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            $requestData['form_data'] = $this->filterSensitiveFormData($request->all());
            $requestData['form_fields_count'] = count($request->all());
        }

        $this->logger?->writeToLogFile('request_start', $requestData);

        // اگر این form request باشد، شروع تحلیل فرم
        if ($this->isFormRequest($request)) {
            $this->initializeFormDebugging($request);
        }
    }

    /**
     * لاگ پایان request
     */
    private function logRequestEnd(Request $request, $response): void
    {
        if (!$this->shouldLog()) return;

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $responseData = [
            'timestamp' => now()->toISOString(),
            'type' => 'REQUEST_END',
            'request_id' => $this->generateRequestId(),
            'execution_time' => round(($endTime - $this->startTime) * 1000, 2), // ms
            'memory_used' => $endMemory - $this->startMemory,
            'memory_used_formatted' => $this->formatBytes($endMemory - $this->startMemory),
            'peak_memory' => memory_get_peak_usage(true),
            'peak_memory_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
            'response_status' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 'unknown',
            'response_size' => method_exists($response, 'getContent') ? strlen($response->getContent()) : 0,
            'database_queries' => count(\DB::getQueryLog()),
            'session_data' => $this->getSessionSummary($request)
        ];

        // تحلیل نوع response
        $responseData['response_type'] = $this->analyzeResponseType($response);

        // اگر redirect باشد
        if (method_exists($response, 'getTargetUrl')) {
            $responseData['redirect_url'] = $response->getTargetUrl();
        }

        // اگر validation errors داشت
        if ($request->hasSession() && $request->session()->has('errors')) {
            $responseData['validation_errors'] = $request->session()->get('errors')->toArray();
            $responseData['validation_errors_count'] = count($responseData['validation_errors']);
        }

        $this->logger?->writeToLogFile('request_end', $responseData);

        // اگر request کند بود، جداگانه لاگ کن
        if ($responseData['execution_time'] > 1000) { // > 1 second
            $this->logSlowRequest($request, $responseData);
        }

        // اگر memory زیاد مصرف کرد
        if ($responseData['memory_used'] > 50 * 1024 * 1024) { // > 50MB
            $this->logMemoryIntensiveRequest($request, $responseData);
        }

        // اگر form request بود، پایان تحلیل فرم
        if ($this->isFormRequest($request)) {
            $this->finalizeFormDebugging($request, $response);
        }
    }

    /**
     * لاگ exception در request
     */
    private function logRequestException(Request $request, \Throwable $exception): void
    {
        if (!$this->shouldLog()) return;

        $endTime = microtime(true);

        $exceptionData = [
            'timestamp' => now()->toISOString(),
            'type' => 'REQUEST_EXCEPTION',
            'request_id' => $this->generateRequestId(),
            'execution_time_before_exception' => round(($endTime - $this->startTime) * 1000, 2),
            'exception' => [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString()
            ],
            'request_details' => [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'controller' => $this->getControllerName($request),
                'action' => $this->getActionName($request)
            ],
            'memory_at_exception' => memory_get_usage(true),
            'database_queries_before_exception' => count(\DB::getQueryLog())
        ];

        $this->logger?->writeToLogFile('exceptions', $exceptionData);
        
        // همچنین به debugger هم ارسال کن
        $this->debugger?->log(
            RMSDebugger::LEVEL_ERROR, 
            RMSDebugger::CATEGORY_PERFORMANCE, 
            'Request Exception: ' . $exception->getMessage(),
            $exceptionData
        );
    }

    /**
     * مقداردهی اولیه debug فرم
     */
    private function initializeFormDebugging(Request $request): void
    {
        if (!$this->debugger || !$this->logger) return;

        // شروع memory tracking
        $this->debugger->trackMemoryUsage('form_request_start');

        // لاگ اطلاعات فرم
        $formData = [
            'timestamp' => now()->toISOString(),
            'type' => 'FORM_REQUEST_START',
            'request_id' => $this->generateRequestId(),
            'controller' => $this->getControllerName($request),
            'action' => $this->getActionName($request),
            'form_fields_count' => count($request->all()),
            'has_file_uploads' => $request->hasFile('*'),
            'form_data_size' => strlen(serialize($request->except(['_token', 'password', 'password_confirmation'])))
        ];

        $this->logger->writeToLogFile('form_debug', $formData);
    }

    /**
     * پایان تحلیل فرم
     */
    private function finalizeFormDebugging(Request $request, $response): void
    {
        if (!$this->debugger || !$this->logger) return;

        // پایان memory tracking
        $this->debugger->trackMemoryUsage('form_request_end');

        // تحلیل database queries
        $queryAnalysis = $this->debugger->analyzeDatabaseQueries();
        if (!empty($queryAnalysis)) {
            $this->logger->logDatabaseAnalysis($queryAnalysis);
        }

        // دریافت کل debug data
        $debugData = $this->debugger->getDebugData();
        
        // لاگ session summary
        $sessionId = $debugData['session_info']['session_id'] ?? uniqid('form_session_', true);
        $this->logger->logSessionSummary($debugData, $sessionId);

        // لاگ کامل debug session
        $this->logger->logDebugSession($debugData, $sessionId);
    }

    /**
     * لاگ request های کند
     */
    private function logSlowRequest(Request $request, array $responseData): void
    {
        $slowRequestData = [
            'timestamp' => now()->toISOString(),
            'type' => 'SLOW_REQUEST',
            'request_id' => $this->generateRequestId(),
            'execution_time' => $responseData['execution_time'],
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'controller' => $this->getControllerName($request),
            'action' => $this->getActionName($request),
            'database_queries' => $responseData['database_queries'],
            'memory_used' => $responseData['memory_used_formatted'],
            'alert_level' => $this->determineSlowRequestAlertLevel($responseData['execution_time'])
        ];

        $this->logger?->writeToLogFile('slow_requests', $slowRequestData);
    }

    /**
     * لاگ request های memory intensive
     */
    private function logMemoryIntensiveRequest(Request $request, array $responseData): void
    {
        $memoryData = [
            'timestamp' => now()->toISOString(),
            'type' => 'MEMORY_INTENSIVE_REQUEST',
            'request_id' => $this->generateRequestId(),
            'memory_used' => $responseData['memory_used_formatted'],
            'peak_memory' => $responseData['peak_memory_formatted'],
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'controller' => $this->getControllerName($request),
            'action' => $this->getActionName($request),
            'execution_time' => $responseData['execution_time'],
            'alert_level' => $this->determineMemoryAlertLevel($responseData['memory_used'])
        ];

        $this->logger?->writeToLogFile('memory_intensive', $memoryData);
    }

    /**
     * بررسی نیاز به فعال‌سازی debug
     */
    private function shouldEnableDebug(): bool
    {
        return config('app.debug', false) && 
               config('rms.debug.enabled', true) &&
               config('rms.debug.middleware.enabled', true);
    }

    /**
     * بررسی نیاز به لاگ کردن
     */
    private function shouldLog(): bool
    {
        return $this->debugger && $this->logger && $this->shouldEnableDebug();
    }

    /**
     * تشخیص form request
     */
    private function isFormRequest(Request $request): bool
    {
        return in_array($request->method(), ['POST', 'PUT', 'PATCH']) && 
               !$request->isXmlHttpRequest() && // نه AJAX
               !str_contains($request->header('Accept', ''), 'application/json'); // نه API
    }

    /**
     * تولید ID یکتا برای request
     */
    private function generateRequestId(): string
    {
        static $requestId = null;
        
        if ($requestId === null) {
            $requestId = uniqid('req_', true);
        }
        
        return $requestId;
    }

    /**
     * دریافت نام کنترلر
     */
    private function getControllerName(Request $request): ?string
    {
        $route = $request->route();
        
        if ($route && is_string($route->getActionName())) {
            $action = $route->getActionName();
            if (str_contains($action, '@')) {
                return explode('@', $action)[0];
            }
        }
        
        return null;
    }

    /**
     * دریافت نام action
     */
    private function getActionName(Request $request): ?string
    {
        $route = $request->route();
        
        if ($route && is_string($route->getActionName())) {
            $action = $route->getActionName();
            if (str_contains($action, '@')) {
                return explode('@', $action)[1];
            }
        }
        
        return null;
    }

    /**
     * فیلتر کردن headers حساس
     */
    private function filterSensitiveHeaders(array $headers): array
    {
        $sensitiveHeaders = ['authorization', 'cookie', 'x-api-key', 'x-auth-token'];
        
        foreach ($sensitiveHeaders as $header) {
            if (isset($headers[$header])) {
                $headers[$header] = ['[FILTERED]'];
            }
        }
        
        return $headers;
    }

    /**
     * فیلتر کردن داده‌های حساس فرم
     */
    private function filterSensitiveFormData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'current_password', 'new_password',
            'api_key', 'secret_key', 'token', 'credit_card', 'ssn', '_token'
        ];
        
        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[FILTERED]';
            }
        }
        
        return $data;
    }

    /**
     * خلاصه session
     */
    private function getSessionSummary(Request $request): array
    {
        if (!$request->hasSession()) {
            return ['session_available' => false];
        }

        $session = $request->session();
        
        return [
            'session_available' => true,
            'session_id' => $session->getId(),
            'has_errors' => $session->has('errors'),
            'flash_data_keys' => array_keys($session->all()),
            'session_size' => strlen(serialize($session->all()))
        ];
    }

    /**
     * تحلیل نوع response
     */
    private function analyzeResponseType($response): string
    {
        if (method_exists($response, 'getStatusCode')) {
            $statusCode = $response->getStatusCode();
            
            if ($statusCode >= 200 && $statusCode < 300) return 'success';
            if ($statusCode >= 300 && $statusCode < 400) return 'redirect';
            if ($statusCode >= 400 && $statusCode < 500) return 'client_error';
            if ($statusCode >= 500) return 'server_error';
        }
        
        if (method_exists($response, 'getContent')) {
            $content = $response->getContent();
            if (is_string($content) && str_starts_with(trim($content), '<!DOCTYPE html')) {
                return 'html';
            }
            if (is_string($content) && (str_starts_with(trim($content), '{') || str_starts_with(trim($content), '['))) {
                return 'json';
            }
        }
        
        return 'unknown';
    }

    /**
     * تعیین سطح هشدار برای request کند
     */
    private function determineSlowRequestAlertLevel(float $executionTime): string
    {
        if ($executionTime > 5000) return 'CRITICAL'; // > 5s
        if ($executionTime > 3000) return 'HIGH';     // > 3s
        if ($executionTime > 1000) return 'MEDIUM';   // > 1s
        return 'LOW';
    }

    /**
     * تعیین سطح هشدار برای memory
     */
    private function determineMemoryAlertLevel(int $memoryUsed): string
    {
        $memoryMB = $memoryUsed / (1024 * 1024);
        
        if ($memoryMB > 200) return 'CRITICAL'; // > 200MB
        if ($memoryMB > 100) return 'HIGH';     // > 100MB
        if ($memoryMB > 50) return 'MEDIUM';    // > 50MB
        return 'LOW';
    }

    /**
     * فرمت کردن bytes
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}