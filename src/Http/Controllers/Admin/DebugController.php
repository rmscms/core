<?php

namespace RMS\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Filesystem\Filesystem;
use RMS\Core\Debug\RMSDebugger;
use RMS\Core\Controllers\Admin\AdminController;

/**
 * DebugController - کنترلر اختصاصی برای عملیات debug
 * 
 * مسیرها و endpoints برای سیستم debug RMS Core
 * 
 * @author RMS Core Team  
 * @version 2.0.0
 */
class DebugController extends AdminController
{
    /**
     * Instance RMSDebugger
     */
    private RMSDebugger $debugger;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct($filesystem);
        
        // بررسی دسترسی debug
        if (!config('app.debug') || !config('rms.debug.enabled', true)) {
            abort(404, 'Debug mode is disabled');
        }
        
        $this->debugger = RMSDebugger::instance();
    }

    /**
     * نمایش پنل debug - GET /admin/debug/panel
     */
    public function showDebugPanel(Request $request): JsonResponse
    {
        try {
            // Create a new debugger instance that loads from logs
            $debugger = new RMSDebugger(true);
            // Get debug data (already loaded from logs)
            $debugData = $debugger->getDebugData();
            
            return response()->json([
                'success' => true,
                'debug_data' => $debugData,
                'timestamp' => now()->toISOString(),
                'session_id' => $debugger->getSessionId(),
                'loaded_from_logs' => !empty($debugData['form_analysis'])
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load debug data: ' . $e->getMessage(),
                'timestamp' => now()->toISOString()
            ], 500);
        }
    }

    /**
     * Abstract methods implementation
     */
    public function table(): string
    {
        return 'debug_sessions'; // فرضی
    }

    public function modelName(): string
    {
        return \stdClass::class; // فرضی
    }


    /**
     * تحلیل فرم خاص - POST /admin/debug/analyze-form
     */
    public function analyzeForm(Request $request): JsonResponse
    {
        $request->validate([
            'controller' => 'required|string',
            'action' => 'required|string|in:create,edit',
            'id' => 'nullable|string'
        ]);

        try {
            $controllerName = $request->get('controller');
            $action = $request->get('action');
            $id = $request->get('id');

            // شبیه‌سازی تحلیل فرم
            $analysis = [
                'controller' => $controllerName,
                'action' => $action,
                'id' => $id,
                'timestamp' => now()->toISOString(),
                'analysis' => 'Form analysis simulation - real implementation needed'
            ];

            return response()->json([
                'success' => true,
                'analysis' => $analysis
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Form analysis failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle debug mode - POST /admin/debug/toggle
     */
    public function toggleDebugMode(Request $request): JsonResponse
    {
        $enable = $request->boolean('enable');
        
        try {
            $this->debugger->toggle($enable);
            
            // ذخیره وضعیت در session
            session(['rms_debug_enabled' => $enable]);
            
            return response()->json([
                'success' => true,
                'message' => $enable ? 'Debug mode enabled' : 'Debug mode disabled',
                'debug_enabled' => $this->debugger->isEnabled(),
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to toggle debug mode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * پاک کردن debug data - POST /admin/debug/clear
     */
    public function clearDebugData(Request $request): JsonResponse
    {
        try {
            $this->debugger->clear();
            
            return response()->json([
                'success' => true,
                'message' => 'Debug data cleared successfully',
                'timestamp' => now()->toISOString()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to clear debug data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export debug data - GET /admin/debug/export
     */
    public function exportDebugData(Request $request): Response|JsonResponse
    {
        $format = $request->get('format', 'json');
        $filename = $request->get('filename', 'rms_debug_' . date('Y-m-d_H-i-s'));

        try {
            $exportData = $this->debugger->export($format);
            
            switch ($format) {
                case 'json':
                    return response($exportData)
                        ->header('Content-Type', 'application/json')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.json\"")
                        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
                
                case 'text':
                    return response($exportData)
                        ->header('Content-Type', 'text/plain; charset=utf-8')
                        ->header('Content-Disposition', "attachment; filename=\"{$filename}.txt\"")
                        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
                
                default:
                    return response()->json([
                        'success' => true,
                        'data' => $exportData,
                        'format' => $format,
                        'timestamp' => now()->toISOString()
                    ]);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * آمار سیستم debug - GET /admin/debug/stats
     */
    public function getDebugStats(Request $request): JsonResponse
    {
        try {
            $stats = [
                'system_info' => [
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'memory_limit' => ini_get('memory_limit'),
                    'max_execution_time' => ini_get('max_execution_time'),
                    'current_memory' => memory_get_usage(true),
                    'peak_memory' => memory_get_peak_usage(true)
                ],
                'debug_status' => [
                    'enabled' => $this->debugger->isEnabled(),
                    'session_active' => !empty(session('rms_debug_enabled')),
                    'last_activity' => now()->toISOString()
                ],
                'performance_summary' => $this->getPerformanceSummary()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get debug stats: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log viewer - GET /admin/debug/logs
     */
    public function viewLogs(Request $request): JsonResponse
    {
        $level = $request->get('level', 'all');
        $category = $request->get('category', 'all');
        $search = $request->get('search', '');
        $limit = $request->get('limit', 50);

        try {
            // Implementation برای خواندن logs از cache یا storage
            $sessionId = $request->get('session_id');
            $cacheKey = "rms_debug_logs_{$sessionId}";
            
            $logs = cache($cacheKey, []);
            
            // فیلتر کردن logs
            if ($level !== 'all') {
                $logs = array_filter($logs, fn($log) => $log['level'] === $level);
            }
            
            if ($category !== 'all') {
                $logs = array_filter($logs, fn($log) => $log['category'] === $category);
            }
            
            if (!empty($search)) {
                $logs = array_filter($logs, fn($log) => 
                    stripos($log['message'], $search) !== false ||
                    stripos(json_encode($log['context']), $search) !== false
                );
            }

            // محدود کردن تعداد
            $logs = array_slice($logs, 0, $limit);

            return response()->json([
                'success' => true,
                'logs' => array_values($logs),
                'total_count' => count($logs),
                'filters' => compact('level', 'category', 'search'),
                'timestamp' => now()->toISOString()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to load logs: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Real-time debug info - GET /admin/debug/realtime
     */
    public function getRealTimeInfo(Request $request): JsonResponse
    {
        try {
            $info = [
                'current_time' => now()->toISOString(),
                'memory_usage' => [
                    'current' => memory_get_usage(true),
                    'peak' => memory_get_peak_usage(true),
                    'formatted_current' => $this->formatBytes(memory_get_usage(true)),
                    'formatted_peak' => $this->formatBytes(memory_get_peak_usage(true))
                ],
                'database_queries' => count(\DB::getQueryLog()),
                'session_info' => [
                    'id' => session()->getId(),
                    'debug_enabled' => session('rms_debug_enabled', false)
                ]
            ];

            return response()->json([
                'success' => true,
                'info' => $info
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to get real-time info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Health check - GET /admin/debug/health
     */
    public function healthCheck(Request $request): JsonResponse
    {
        $checks = [
            'debug_enabled' => config('app.debug'),
            'rms_debug_enabled' => config('rms.debug.enabled', true),
            'memory_ok' => memory_get_usage(true) < (1024 * 1024 * 512), // < 512MB
            'database_connection' => false,
            'cache_working' => false,
            'logging_working' => false
        ];

        // تست اتصال دیتابیس
        try {
            \DB::connection()->getPdo();
            $checks['database_connection'] = true;
        } catch (\Exception $e) {
            // نادیده بگیر
        }

        // تست کش
        try {
            $testKey = 'debug_health_check_' . time();
            cache()->put($testKey, 'test', 60);
            $checks['cache_working'] = cache()->get($testKey) === 'test';
            cache()->forget($testKey);
        } catch (\Exception $e) {
            // نادیده بگیر
        }

        // تست logging
        try {
            \Log::info('Debug health check test');
            $checks['logging_working'] = true;
        } catch (\Exception $e) {
            // نادیده بگیر
        }

        $allHealthy = !in_array(false, $checks);

        return response()->json([
            'success' => true,
            'healthy' => $allHealthy,
            'checks' => $checks,
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * خلاصه performance
     */
    private function getPerformanceSummary(): array
    {
        $debugData = $this->debugger->getDebugData();
        
        return [
            'total_operations' => count($debugData['performance_details'] ?? []),
            'average_execution_time' => $this->calculateAverageExecutionTime($debugData['performance_details'] ?? []),
            'memory_usage' => $debugData['performance_summary']['total_memory_used'] ?? 'N/A',
            'queries_executed' => $debugData['database_analysis']['total_queries'] ?? 0
        ];
    }

    /**
     * محاسبه متوسط زمان اجرا
     */
    private function calculateAverageExecutionTime(array $performanceDetails): string
    {
        if (empty($performanceDetails)) {
            return 'N/A';
        }

        $totalTime = array_sum(array_column($performanceDetails, 'execution_time'));
        $averageTime = $totalTime / count($performanceDetails);

        return round($averageTime, 2) . 'ms';
    }

    /**
     * فرمت کردن bytes
     */
    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}