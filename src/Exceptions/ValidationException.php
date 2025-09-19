<?php

declare(strict_types=1);

namespace RMS\Core\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Custom validation exception for RMS Core.
 * 
 * استثناء سفارشی برای مدیریت خطاهای اعتبارسنجی در سیستم RMS
 * 
 * @package RMS\Core\Exceptions
 */
class ValidationException extends Exception
{
    /**
     * Additional context data for the exception.
     */
    protected array $context;

    /**
     * The error code for this exception type.
     */
    protected string $errorCode;

    /**
     * ValidationException constructor.
     *
     * @param string $message
     * @param array $context
     * @param string $errorCode
     * @param int $code
     * @param Exception|null $previous
     */
    public function __construct(
        string $message = 'Validation failed',
        array $context = [],
        string $errorCode = 'VALIDATION_ERROR',
        int $code = 422,
        ?Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        
        $this->context = $context;
        $this->errorCode = $errorCode;
        
        // لاگ کردن خطا برای بررسی بعدی
        Log::error('ValidationException occurred', [
            'message' => $message,
            'error_code' => $errorCode,
            'context' => $context,
            'trace' => $this->getTraceAsString()
        ]);
    }

    /**
     * Get the exception context data.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Get the error code.
     *
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function render(Request $request): JsonResponse|RedirectResponse
    {
        // اگر درخواست AJAX باشد، پاسخ JSON برگردانیم
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => false,
                'message' => $this->getMessage(),
                'error_code' => $this->errorCode,
                'context' => $this->context
            ], $this->getCode());
        }

        // برای درخواست‌های معمولی، redirect با پیام خطا
        return redirect()->back()
            ->withInput($request->input())
            ->withErrors(['validation' => $this->getMessage()]);
    }

    /**
     * Convert the exception to array format.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'message' => $this->getMessage(),
            'error_code' => $this->errorCode,
            'context' => $this->context,
            'file' => $this->getFile(),
            'line' => $this->getLine()
        ];
    }

    /**
     * Create a validation exception for missing controller.
     *
     * @param string $controllerClass
     * @return static
     */
    public static function missingController(string $controllerClass): static
    {
        return new static(
            "Controller {$controllerClass} not found or not accessible",
            ['controller_class' => $controllerClass],
            'CONTROLLER_NOT_FOUND'
        );
    }

    /**
     * Create a validation exception for interface implementation.
     *
     * @param string $controllerClass
     * @param string $interfaceName
     * @return static
     */
    public static function interfaceNotImplemented(string $controllerClass, string $interfaceName): static
    {
        return new static(
            "Controller {$controllerClass} must implement {$interfaceName} interface",
            [
                'controller_class' => $controllerClass,
                'required_interface' => $interfaceName
            ],
            'INTERFACE_NOT_IMPLEMENTED'
        );
    }

    /**
     * Create a validation exception for invalid rules.
     *
     * @param mixed $rules
     * @return static
     */
    public static function invalidRules(mixed $rules): static
    {
        return new static(
            'Validation rules must be an array, ' . gettype($rules) . ' given',
            ['rules_type' => gettype($rules)],
            'INVALID_RULES_TYPE'
        );
    }
}
