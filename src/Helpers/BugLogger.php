<?php

namespace RMS\Core\Helpers;

use RMS\Core\Models\BugLog;
use Throwable;
use Illuminate\Http\Request;

/**
 * Bug Logger Helper for tracking and managing bugs
 * 
 * کلاس کمکی لاگ خطاها برای پیگیری و مدیریت باگ‌ها
 */
class BugLogger 
{
    /**
     * Log an error/exception to the bug tracking system
     * 
     * @param Throwable $exception
     * @param Request|null $request
     * @param array $debugInfo Additional debug information
     * @return BugLog
     */
    public static function logError(Throwable $exception, ?Request $request = null, array $debugInfo = []): BugLog
    {
        $data = [
            'title' => static::generateTitle($exception),
            'error_message' => $exception->getMessage(),
            'error_code' => $exception->getCode() ?: null,
            'file_path' => $exception->getFile(),
            'line_number' => $exception->getLine(),
            'stack_trace' => $exception->getTraceAsString(),
            'severity' => static::determineSeverity($exception),
            'category' => static::determineCategory($exception),
            'debug_info' => $debugInfo,
            'occurred_at' => now()
        ];

        // Add request information if available
        if ($request) {
            $data = array_merge($data, [
                'request_url' => $request->fullUrl(),
                'request_method' => $request->method(),
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'user_agent' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'request_data' => static::sanitizeRequestData($request->all())
            ]);
        }

        return BugLog::create($data);
    }

    /**
     * Mark a bug as fixed by AI
     * 
     * @param int $bugId
     * @param string $description
     * @param array $files
     * @return bool
     */
    public static function markAsFixed(int $bugId, string $description, array $files = []): bool
    {
        $bugLog = BugLog::find($bugId);
        
        if (!$bugLog) {
            return false;
        }

        return $bugLog->markAsFixed($description, $files);
    }

    /**
     * Confirm a fix by human
     * 
     * @param int $bugId
     * @param string $notes
     * @return bool
     */
    public static function confirmFix(int $bugId, string $notes = ''): bool
    {
        $bugLog = BugLog::find($bugId);
        
        if (!$bugLog) {
            return false;
        }

        return $bugLog->confirmFix($notes);
    }

    /**
     * Reject a fix by human
     * 
     * @param int $bugId
     * @param string $reason
     * @return bool
     */
    public static function rejectFix(int $bugId, string $reason = ''): bool
    {
        $bugLog = BugLog::find($bugId);
        
        if (!$bugLog) {
            return false;
        }

        return $bugLog->rejectFix($reason);
    }

    /**
     * Get bugs that need human confirmation
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getBugsNeedingConfirmation()
    {
        return BugLog::fixed()
            ->orderBy('ai_fixed_at', 'desc')
            ->get();
    }

    /**
     * Get new/unresolved bugs
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getNewBugs()
    {
        return BugLog::new()
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    /**
     * Get bug statistics
     * 
     * @return array
     */
    public static function getStats(): array
    {
        return [
            'total' => BugLog::count(),
            'new' => BugLog::new()->count(),
            'fixed' => BugLog::fixed()->count(),
            'confirmed' => BugLog::confirmed()->count(),
            'critical' => BugLog::critical()->count(),
            'recent_24h' => BugLog::recent(1)->count(),
            'recent_7d' => BugLog::recent(7)->count(),
            'by_category' => BugLog::selectRaw('category, COUNT(*) as count')
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_severity' => BugLog::selectRaw('severity, COUNT(*) as count')
                ->groupBy('severity')
                ->pluck('count', 'severity')
                ->toArray()
        ];
    }

    /**
     * Auto-log Laravel exceptions using this system
     * Use in App\Exceptions\Handler
     * 
     * @param Throwable $exception
     * @param Request|null $request
     * @return BugLog|null
     */
    public static function autoLog(Throwable $exception, ?Request $request = null): ?BugLog
    {
        // Skip certain exception types that don't need logging
        if (static::shouldSkipException($exception)) {
            return null;
        }

        return static::logError($exception, $request);
    }

    // ================================
    // Private Helper Methods
    // ================================

    /**
     * Generate a concise title from exception
     */
    private static function generateTitle(Throwable $exception): string
    {
        $message = $exception->getMessage();
        
        // If message is too long, truncate it
        if (strlen($message) > 100) {
            return substr($message, 0, 97) . '...';
        }

        return $message;
    }

    /**
     * Determine severity based on exception type and message
     */
    private static function determineSeverity(Throwable $exception): string
    {
        $message = strtolower($exception->getMessage());
        $class = get_class($exception);

        // Critical errors
        if (str_contains($message, 'fatal error') ||
            str_contains($message, 'out of memory') ||
            str_contains($class, 'FatalError')) {
            return BugLog::SEVERITY_CRITICAL;
        }

        // High severity
        if (str_contains($message, 'undefined method') || 
            str_contains($message, 'class not found') ||
            str_contains($message, 'call to undefined') ||
            str_contains($message, 'syntax error') ||
            str_contains($class, 'ParseError') ||
            str_contains($class, 'Error')) {
            return BugLog::SEVERITY_HIGH;
        }

        // Medium severity  
        if (str_contains($message, 'undefined variable') ||
            str_contains($message, 'undefined index') ||
            str_contains($message, 'undefined offset') ||
            str_contains($class, 'Exception')) {
            return BugLog::SEVERITY_MEDIUM;
        }

        // Default to medium
        return BugLog::SEVERITY_MEDIUM;
    }

    /**
     * Determine category based on file path and exception
     */
    private static function determineCategory(Throwable $exception): string
    {
        $file = strtolower($exception->getFile());
        $message = strtolower($exception->getMessage());

        // Form related
        if (str_contains($file, 'form') || 
            str_contains($message, 'form') ||
            str_contains($file, 'controller') && str_contains($message, 'field')) {
            return BugLog::CATEGORY_FORM;
        }

        // Database related
        if (str_contains($file, 'database') || 
            str_contains($message, 'database') ||
            str_contains($message, 'sql') ||
            str_contains($message, 'query') ||
            str_contains($file, 'migration')) {
            return BugLog::CATEGORY_DATABASE;
        }

        // Authentication related
        if (str_contains($file, 'auth') ||
            str_contains($message, 'auth') ||
            str_contains($message, 'login') ||
            str_contains($message, 'permission') ||
            str_contains($file, 'middleware')) {
            return BugLog::CATEGORY_AUTH;
        }

        // Validation related
        if (str_contains($message, 'validation') ||
            str_contains($file, 'validation') ||
            str_contains($file, 'request')) {
            return BugLog::CATEGORY_VALIDATION;
        }

        // Controller related
        if (str_contains($file, 'controller')) {
            return BugLog::CATEGORY_CONTROLLER;
        }

        // View related
        if (str_contains($file, 'view') ||
            str_contains($file, 'blade') ||
            str_contains($file, 'template')) {
            return BugLog::CATEGORY_VIEW;
        }

        return BugLog::CATEGORY_GENERAL;
    }

    /**
     * Sanitize request data to remove sensitive information
     */
    private static function sanitizeRequestData(array $data): array
    {
        $sensitiveFields = [
            'password', 'password_confirmation', 'token', 'api_key',
            'secret', 'private_key', 'credit_card', 'ssn'
        ];

        foreach ($sensitiveFields as $field) {
            if (isset($data[$field])) {
                $data[$field] = '[REDACTED]';
            }
        }

        return $data;
    }

    /**
     * Check if exception should be skipped from logging
     */
    private static function shouldSkipException(Throwable $exception): bool
    {
        $skipClasses = [
            \Illuminate\Validation\ValidationException::class,
            \Illuminate\Auth\AuthenticationException::class,
            \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
            \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
        ];

        foreach ($skipClasses as $skipClass) {
            if ($exception instanceof $skipClass) {
                return true;
            }
        }

        return false;
    }
}
