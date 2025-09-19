<?php

namespace RMS\Core\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use RMS\Core\Debug\RMSDebugger;
use RMS\Core\Data\FormGenerator;
use RMS\Core\Generators\ListGenerator;

/**
 * DebugPanel Trait
 * 
 * اضافه کردن قابلیت‌های debug به AdminController
 * امکان تحلیل فرم‌ها، فیلدها، performance و database queries
 * 
 * @author RMS Core Team
 * @version 2.0.0
 */
trait DebugPanel
{
    /**
     * Instance RMSDebugger
     */
    private ?RMSDebugger $debugger = null;

    /**
     * فعال/غیرفعال debug mode
     */
    private bool $debugMode = false;

    /**
     * مقداردهی اولیه debugger
     */
    protected function initializeDebugger(): void
    {
        if ($this->shouldEnableDebug()) {
            $this->debugger = RMSDebugger::instance();
            $this->debugMode = $this->debugger->isEnabled();
            
            if ($this->debugMode) {
                $this->debugger->trackMemoryUsage('controller_init');
            }
        }
    }

    /**
     * بررسی نیاز به فعال‌سازی debug
     */
    protected function shouldEnableDebug(): bool
    {
        return config('app.debug', false) && 
               config('rms.debug.enabled', true) && 
               (request()->has('debug') || session('rms_debug_enabled', false));
    }

    /**
     * فعال‌سازی debug mode
     */
    public function enableDebugMode(): self
    {
        $this->initializeDebugger();
        if ($this->debugger) {
            $this->debugger->toggle(true);
            $this->debugMode = true;
            session(['rms_debug_enabled' => true]);
        }
        return $this;
    }

    /**
     * غیرفعال‌سازی debug mode
     */
    public function disableDebugMode(): self
    {
        if ($this->debugger) {
            $this->debugger->toggle(false);
        }
        $this->debugMode = false;
        session()->forget('rms_debug_enabled');
        return $this;
    }

    /**
     * تحلیل فرم با debugger
     */
    protected function debugForm(\RMS\Core\Data\FormGenerator $generator, array $templateData = []): array
    {
        if (!$this->debugMode || !$this->debugger) {
            return [];
        }

        return $this->debugger->measurePerformance('form_analysis', function() use ($generator, $templateData) {
            return $this->debugger->analyzeForm($generator, $templateData);
        }, ['controller' => get_class($this), 'action' => 'form_debug']);
    }

    /**
     * تحلیل validation rules
     */
    protected function debugValidation(array $rules, Request $request = null): array
    {
        if (!$this->debugMode || !$this->debugger) {
            return [];
        }

        return $this->debugger->measurePerformance('validation_analysis', function() use ($rules, $request) {
            return $this->debugger->analyzeValidation($rules, $request);
        }, ['controller' => get_class($this), 'action' => 'validation_debug']);
    }

    /**
     * تحلیل database queries
     */
    protected function debugQueries(): array
    {
        if (!$this->debugMode || !$this->debugger) {
            return [];
        }

        return $this->debugger->measurePerformance('query_analysis', function() {
            return $this->debugger->analyzeDatabaseQueries();
        }, ['controller' => get_class($this), 'action' => 'query_debug']);
    }

    /**
     * ضبط checkpoint memory
     */
    protected function debugMemoryCheckpoint(string $checkpoint): array
    {
        if (!$this->debugMode || !$this->debugger) {
            return [];
        }

        return $this->debugger->trackMemoryUsage($checkpoint);
    }

    /**
     * اجرای عملیات با اندازه‌گیری performance
     */
    protected function debugMeasure(string $operation, callable $callback, array $context = [])
    {
        if (!$this->debugMode || !$this->debugger) {
            return $callback();
        }

        $context['controller'] = get_class($this);
        return $this->debugger->measurePerformance($operation, $callback, $context);
    }

    /**
     * لاگ debug
     */
    protected function debugLog(string $level, string $category, string $message, array $context = []): void
    {
        if (!$this->debugMode || !$this->debugger) {
            return;
        }

        $context['controller'] = get_class($this);
        $this->debugger->log($level, $category, $message, $context);
    }

    /**
     * دریافت تمام اطلاعات debug
     */
    protected function getDebugData(): array
    {
        if (!$this->debugMode || !$this->debugger) {
            return ['debug_disabled' => true];
        }

        return $this->debugger->getDebugData();
    }

    /**
     * نمایش پنل debug در response
     */
    protected function addDebugToResponse($response, string $debugType = 'full'): mixed
    {
        if (!$this->debugMode || !$this->debugger) {
            return $response;
        }

        $debugData = $this->getDebugData();
        
        if ($response instanceof JsonResponse) {
            // برای JSON response، debug data را به response اضافه کن
            $data = $response->getData(true);
            $data['_debug'] = $debugData;
            $response->setData($data);
            
        } elseif ($response instanceof View || method_exists($response, 'with')) {
            // برای View response، debug data را به متغیرها اضافه کن
            if (method_exists($response, 'with')) {
                $response->with('_debug', $debugData);
                $response->with('_debug_enabled', true);
            }
        }

        return $response;
    }

    /**
     * Route برای نمایش debug panel
     */
    public function showDebugPanel(): JsonResponse
    {
        if (!$this->debugMode || !$this->debugger) {
            return response()->json(['error' => 'Debug mode is disabled'], 403);
        }

        $debugData = $this->getDebugData();
        
        return response()->json([
            'success' => true,
            'debug_data' => $debugData,
            'timestamp' => now()->toISOString(),
            'controller' => get_class($this)
        ]);
    }

    /**
     * Route برای toggle debug mode
     */
    public function toggleDebugMode(Request $request): JsonResponse
    {
        $enable = $request->boolean('enable', !$this->debugMode);
        
        if ($enable) {
            $this->enableDebugMode();
            $message = 'Debug mode enabled';
        } else {
            $this->disableDebugMode();
            $message = 'Debug mode disabled';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'debug_enabled' => $this->debugMode,
            'controller' => get_class($this)
        ]);
    }

    /**
     * Route برای پاک کردن debug data
     */
    public function clearDebugData(): JsonResponse
    {
        if (!$this->debugMode || !$this->debugger) {
            return response()->json(['error' => 'Debug mode is disabled'], 403);
        }

        $this->debugger->clear();

        return response()->json([
            'success' => true,
            'message' => 'Debug data cleared',
            'controller' => get_class($this)
        ]);
    }

    /**
     * Route برای export debug data
     */
    public function exportDebugData(Request $request): mixed
    {
        if (!$this->debugMode || !$this->debugger) {
            return response()->json(['error' => 'Debug mode is disabled'], 403);
        }

        $format = $request->get('format', 'json');
        $filename = $request->get('filename', 'rms_debug_' . date('Y-m-d_H-i-s'));
        
        try {
            $exportData = $this->debugger->export($format);
            
            switch ($format) {
                case 'json':
                    return response($exportData)
                        ->header('Content-Type', 'application/json')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"");
                
                case 'text':
                    return response($exportData)
                        ->header('Content-Type', 'text/plain')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.txt\"");
                
                default:
                    return response()->json([
                        'success' => true,
                        'data' => $exportData,
                        'format' => $format,
                        'controller' => get_class($this)
                    ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Export failed: ' . $e->getMessage(),
                'controller' => get_class($this)
            ], 500);
        }
    }

    /**
     * Hook در create method
     */
    protected function debugFormCreate(Request $request): void
    {
        if (!$this->debugMode || !$this->debugger) {
            return;
        }

        $this->debugMemoryCheckpoint('before_form_create');
        $this->debugLog(RMSDebugger::LEVEL_INFO, RMSDebugger::CATEGORY_FORM, 'Create form requested', [
            'request_data' => $request->except(['password', 'password_confirmation']),
            'has_files' => $request->hasFile('*')
        ]);
    }

    /**
     * Hook در edit method
     */
    protected function debugFormEdit(Request $request, $id): void
    {
        if (!$this->debugMode || !$this->debugger) {
            return;
        }

        $this->debugMemoryCheckpoint('before_form_edit');
        $this->debugLog(RMSDebugger::LEVEL_INFO, RMSDebugger::CATEGORY_FORM, 'Edit form requested', [
            'id' => $id,
            'request_data' => $request->except(['password', 'password_confirmation']),
            'has_files' => $request->hasFile('*')
        ]);
    }

    /**
     * Hook در store method
     */
    protected function debugStore(Request $request): void
    {
        if (!$this->debugMode || !$this->debugger) {
            return;
        }

        $this->debugMemoryCheckpoint('before_store');
        
        // تحلیل validation rules
        if (method_exists($this, 'rules')) {
            $this->debugValidation($this->rules(), $request);
        }

        $this->debugLog(RMSDebugger::LEVEL_INFO, RMSDebugger::CATEGORY_FORM, 'Store operation started', [
            'request_size' => $this->calculateSafeDataSize($request->all()),
            'has_validation_errors' => !empty($request->session()->get('errors'))
        ]);
    }

    /**
     * Hook در update method
     */
    protected function debugUpdate(Request $request, $id): void
    {
        if (!$this->debugMode || !$this->debugger) {
            return;
        }

        $this->debugMemoryCheckpoint('before_update');

        // تحلیل validation rules
        if (method_exists($this, 'rules')) {
            $this->debugValidation($this->rules(), $request);
        }

        $this->debugLog(RMSDebugger::LEVEL_INFO, RMSDebugger::CATEGORY_FORM, 'Update operation started', [
            'id' => $id,
            'request_size' => $this->calculateSafeDataSize($request->all()),
            'has_validation_errors' => !empty($request->session()->get('errors'))
        ]);
    }

    /**
     * Hook در index method (لیست)
     */
    protected function debugIndex(Request $request): void
    {
        if (!$this->debugMode || !$this->debugger) {
            return;
        }

        $this->debugMemoryCheckpoint('before_index');
        $this->debugLog(RMSDebugger::LEVEL_INFO, RMSDebugger::CATEGORY_PERFORMANCE, 'List view requested', [
            'has_filters' => $request->has('filter'),
            'page' => $request->get('page', 1),
            'per_page' => $request->get('per_page', 15)
        ]);
    }

    /**
     * تحلیل جامع فرم قبل ارسال به template
     */
    protected function analyzeFormBeforeTemplate($generator, $templateData): array
    {
        if (!$this->debugMode || !$this->debugger) {
            return [];
        }

        return $this->debugMeasure('comprehensive_form_analysis', function() use ($generator, $templateData) {
            $analysis = $this->debugForm($generator, $templateData);
            $queryAnalysis = $this->debugQueries();
            $memoryStatus = $this->debugMemoryCheckpoint('before_template_render');

            return [
                'form_analysis' => $analysis,
                'query_analysis' => $queryAnalysis,
                'memory_status' => $memoryStatus,
                'template_data_size' => $this->calculateSafeDataSize($templateData),
                'field_count' => count($templateData['fields'] ?? [])
            ];
        }, ['action' => 'comprehensive_analysis']);
    }
    
    /**
     * محاسبه سایز داده‌ها بدون serialization خطرناک
     */
    private function calculateSafeDataSize($data): string
    {
        try {
            // فقط داده‌های ساده را serialize کن
            $safeData = [];
            
            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (is_scalar($value) || is_array($value)) {
                        $safeData[$key] = $this->sanitizeForSerialization($value);
                    } elseif (is_object($value)) {
                        $safeData[$key] = get_class($value) . ' (object)';
                    } else {
                        $safeData[$key] = gettype($value);
                    }
                }
            }
            
            return strlen(serialize($safeData)) . ' bytes (safe calculation)';
            
        } catch (\Exception $e) {
            return 'Unable to calculate size: ' . $e->getMessage();
        }
    }
    
    /**
     * پاکسازی داده برای serialization امن
     */
    private function sanitizeForSerialization($value)
    {
        if (is_scalar($value)) {
            return $value;
        }
        
        if (is_array($value)) {
            $sanitized = [];
            $count = 0;
            foreach ($value as $k => $v) {
                if ($count++ > 100) { // محدود کردن تعداد elements
                    $sanitized['...truncated'] = '(' . (count($value) - 100) . ' more items)';
                    break;
                }
                $sanitized[$k] = $this->sanitizeForSerialization($v);
            }
            return $sanitized;
        }
        
        if (is_object($value)) {
            return get_class($value) . ' (object)';
        }
        
        return gettype($value);
    }
}
