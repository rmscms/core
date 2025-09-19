<?php

namespace RMS\Core\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use RMS\Core\Helpers\BugLogger;
use Throwable;

/**
 * RMS Core Exception Handler
 * 
 * This handler automatically logs all exceptions to the Bug Tracking system
 * while maintaining Laravel's default logging behavior.
 * 
 * هندلر خطاهای RMS Core که تمام خطاها را به سیستم پیگیری باگ لاگ می‌کند
 */
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'api_key',
        'token',
        'secret',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            // Auto-log to our Bug Tracking system
            $this->logToBugTracker($e);
        });
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $e
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $e)
    {
        // Debug: Log every exception that comes here
        logger()->info('Handler::report called for: ' . get_class($e) . ': ' . $e->getMessage());
        
        // Log to Bug Tracking System first
        $this->logToBugTracker($e);
        
        // Then call parent to also log to Laravel logs
        parent::report($e);
    }

    /**
     * Log exception to our Bug Tracking system
     *
     * @param Throwable $exception
     * @return void
     */
    protected function logToBugTracker(Throwable $exception): void
    {
        try {
            // Only log if we have database connection and not in testing
            if ($this->shouldLogToBugTracker($exception)) {
                BugLogger::autoLog($exception, request());
            }
        } catch (\Exception $e) {
            // If bug logging fails, don't break the application
            // Just log to Laravel's default log
            $this->logBugTrackerFailure($exception, $e);
        }
    }

    /**
     * Check if exception should be logged to bug tracker
     *
     * @param Throwable $exception
     * @return bool
     */
    protected function shouldLogToBugTracker(Throwable $exception): bool
    {
        // Skip if running in console (artisan commands) or testing
        if (app()->runningInConsole() || config('app.env') === 'testing') {
            return false;
        }

        // Skip if no database connection available
        try {
            \DB::connection()->getPdo();
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Log bug tracker failure to Laravel log
     *
     * @param Throwable $originalException
     * @param \Exception $bugLoggerError
     * @return void
     */
    protected function logBugTrackerFailure(Throwable $originalException, \Exception $bugLoggerError): void
    {
        logger()->error('Bug tracking system failed', [
            'original_exception' => [
                'message' => $originalException->getMessage(),
                'file' => $originalException->getFile(),
                'line' => $originalException->getLine(),
                'class' => get_class($originalException)
            ],
            'bug_logger_error' => [
                'message' => $bugLoggerError->getMessage(),
                'file' => $bugLoggerError->getFile(),
                'line' => $bugLoggerError->getLine()
            ]
        ]);
    }

    /**
     * Get the default context variables for logging.
     *
     * @return array<string, mixed>
     */
    protected function context(): array
    {
        try {
            return array_merge(parent::context(), [
                'user_id' => auth()->id(),
                'session_id' => session()->getId(),
                'request_id' => request()->header('X-Request-ID', uniqid()),
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
                'request_url' => request()->fullUrl(),
                'request_method' => request()->method(),
            ]);
        } catch (\Exception $e) {
            // If context gathering fails, return parent context only
            return parent::context();
        }
    }

    /**
     * Create a response object from the given validation exception.
     *
     * @param  \Illuminate\Validation\ValidationException  $e
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function convertValidationExceptionToResponse(\Illuminate\Validation\ValidationException $e, $request)
    {
        // Don't log validation exceptions to bug tracker (they're user errors, not bugs)
        return parent::convertValidationExceptionToResponse($e, $request);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $e
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $e)
    {
        // Add custom error pages or handling here if needed
        // For now, use Laravel's default rendering
        return parent::render($request, $e);
    }
}
