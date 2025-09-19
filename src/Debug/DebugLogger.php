<?php

namespace RMS\Core\Debug;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * DebugLogger - سیستم لاگ کامل اطلاعات debug
 * 
 * برای ذخیره تمام اطلاعات debug در فایل‌هایی که قابل دسترسی خارجی باشند
 * چون AI نمی‌تواند وارد پنل ادمین شود، تمام debug data در فایل‌های text ذخیره می‌شود
 * 
 * @author RMS Core Team
 * @version 2.0.0
 */
class DebugLogger
{
    /**
     * مسیر پوشه logs
     */
    private string $logPath;

    /**
     * نام فایل لاگ فعلی
     */
    private string $currentLogFile;

    /**
     * حداکثر سایز فایل لاگ (MB)
     */
    private int $maxFileSize = 10;

    /**
     * تنظیمات لاگ
     */
    private array $config;

    public function __construct()
    {
        $this->logPath = storage_path('logs/rms_debug');
        $this->config = [
            'enabled' => config('rms.debug.logging.enabled', true),
            'max_file_size' => config('rms.debug.logging.max_file_size', 10), // MB
            'max_files' => config('rms.debug.logging.max_files', 30),
            'include_stack_trace' => config('rms.debug.logging.include_stack_trace', true),
            'detailed_analysis' => config('rms.debug.logging.detailed_analysis', true)
        ];

        $this->currentLogFile = $this->generateLogFileName();
        $this->ensureLogDirectoryExists();
        $this->cleanOldLogFiles();
    }

    /**
     * لاگ اطلاعات debug session
     */
    public function logDebugSession(array $debugData, string $sessionId = null): void
    {
        if (!$this->config['enabled']) return;

        $sessionId = $sessionId ?: uniqid('debug_session_', true);
        
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'session_id' => $sessionId,
            'type' => 'DEBUG_SESSION',
            'data' => $debugData
        ];

        $this->writeToLogFile('debug_session', $logEntry);
        $this->writeDetailedAnalysis($debugData, $sessionId);
    }

    /**
     * لاگ تحلیل فرم
     */
    public function logFormAnalysis(array $formAnalysis, string $controller = null): void
    {
        if (!$this->config['enabled']) return;

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'FORM_ANALYSIS',
            'controller' => $controller,
            'analysis' => $formAnalysis,
            'summary' => [
                'form_mode' => $formAnalysis['form_mode'] ?? 'unknown',
                'total_fields' => $formAnalysis['total_fields'] ?? 0,
                'errors_count' => count($formAnalysis['errors'] ?? []),
                'warnings_count' => count($formAnalysis['warnings'] ?? []),
            ]
        ];

        $this->writeToLogFile('form_analysis', $logEntry);
        
        // اگر error یا warning داشت، جداگانه لاگ کن
        if (!empty($formAnalysis['errors']) || !empty($formAnalysis['warnings'])) {
            $this->logFormIssues($formAnalysis['errors'] ?? [], $formAnalysis['warnings'] ?? [], $controller);
        }
    }

    /**
     * لاگ مشکلات فرم
     */
    public function logFormIssues(array $errors, array $warnings, string $controller = null): void
    {
        if (!$this->config['enabled']) return;

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'FORM_ISSUES',
            'controller' => $controller,
            'severity' => !empty($errors) ? 'ERROR' : 'WARNING',
            'errors' => $errors,
            'warnings' => $warnings,
            'total_issues' => count($errors) + count($warnings)
        ];

        $this->writeToLogFile('form_issues', $logEntry);
        
        // هر issue را جداگانه هم لاگ کن برای جستجوی آسان
        foreach ($errors as $error) {
            $this->logSingleIssue('ERROR', $error, $controller, 'form');
        }
        foreach ($warnings as $warning) {
            $this->logSingleIssue('WARNING', $warning, $controller, 'form');
        }
    }

    /**
     * لاگ تحلیل فیلدها
     */
    public function logFieldAnalysis(array $fieldAnalysis, string $controller = null): void
    {
        if (!$this->config['enabled']) return;

        $fieldSummary = [];
        $totalIssues = 0;

        foreach ($fieldAnalysis as $fieldKey => $field) {
            $issueCount = count($field['potential_issues'] ?? []);
            $totalIssues += $issueCount;
            
            $fieldSummary[$fieldKey] = [
                'type' => $field['type_name'] ?? 'unknown',
                'required' => $field['required'] ?? false,
                'issues_count' => $issueCount,
                'issues' => $field['potential_issues'] ?? []
            ];
        }

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'FIELD_ANALYSIS',
            'controller' => $controller,
            'total_fields' => count($fieldAnalysis),
            'total_issues' => $totalIssues,
            'fields_summary' => $fieldSummary,
            'detailed_analysis' => $this->config['detailed_analysis'] ? $fieldAnalysis : null
        ];

        $this->writeToLogFile('field_analysis', $logEntry);
    }

    /**
     * لاگ عملکرد performance
     */
    public function logPerformanceData(array $performanceDetails, array $performanceSummary = []): void
    {
        if (!$this->config['enabled']) return;

        // آنالیز performance
        $slowOperations = array_filter($performanceDetails, fn($op) => $op['execution_time'] > 500);
        $memoryIntensive = array_filter($performanceDetails, fn($op) => 
            isset($op['memory_used']) && $this->parseMemoryValue($op['memory_used']) > 5 * 1024 * 1024 // > 5MB
        );

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'PERFORMANCE_ANALYSIS',
            'summary' => $performanceSummary,
            'total_operations' => count($performanceDetails),
            'slow_operations_count' => count($slowOperations),
            'memory_intensive_count' => count($memoryIntensive),
            'slow_operations' => $slowOperations,
            'memory_intensive_operations' => $memoryIntensive,
            'all_operations' => $this->config['detailed_analysis'] ? $performanceDetails : null
        ];

        $this->writeToLogFile('performance', $logEntry);

        // لاگ جداگانه برای عملیات کند
        foreach ($slowOperations as $operation) {
            $this->logSlowOperation($operation);
        }
    }

    /**
     * لاگ database queries
     */
    public function logDatabaseAnalysis(array $databaseAnalysis): void
    {
        if (!$this->config['enabled']) return;

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'DATABASE_ANALYSIS',
            'analysis' => $databaseAnalysis,
            'alerts' => []
        ];

        // ایجاد alerts بر اساس آنالیز
        if (($databaseAnalysis['total_queries'] ?? 0) > 50) {
            $logEntry['alerts'][] = 'HIGH_QUERY_COUNT: ' . $databaseAnalysis['total_queries'] . ' queries executed';
        }

        if (!empty($databaseAnalysis['slow_queries'])) {
            $logEntry['alerts'][] = 'SLOW_QUERIES_DETECTED: ' . count($databaseAnalysis['slow_queries']) . ' slow queries';
        }

        if (!empty($databaseAnalysis['duplicate_queries'])) {
            $logEntry['alerts'][] = 'DUPLICATE_QUERIES_DETECTED: ' . count($databaseAnalysis['duplicate_queries']) . ' duplicates';
        }

        $this->writeToLogFile('database_analysis', $logEntry);

        // لاگ جداگانه برای slow queries
        foreach ($databaseAnalysis['slow_queries'] ?? [] as $slowQuery) {
            $this->logSlowQuery($slowQuery);
        }
    }

    /**
     * لاگ memory tracking
     */
    public function logMemoryTracking(array $memoryTracking): void
    {
        if (!$this->config['enabled']) return;

        // پیدا کردن memory spikes
        $spikes = [];
        $previousUsage = 0;

        foreach ($memoryTracking as $checkpoint) {
            $currentUsage = $checkpoint['current_usage'] ?? 0;
            if ($previousUsage > 0 && $currentUsage > $previousUsage * 1.5) { // افزایش 50%+
                $spikes[] = [
                    'checkpoint' => $checkpoint['checkpoint'],
                    'increase' => $currentUsage - $previousUsage,
                    'increase_formatted' => $this->formatBytes($currentUsage - $previousUsage)
                ];
            }
            $previousUsage = $currentUsage;
        }

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'MEMORY_TRACKING',
            'checkpoints_count' => count($memoryTracking),
            'memory_spikes' => $spikes,
            'peak_memory' => max(array_column($memoryTracking, 'current_usage')),
            'peak_memory_formatted' => $this->formatBytes(max(array_column($memoryTracking, 'current_usage'))),
            'detailed_tracking' => $this->config['detailed_analysis'] ? $memoryTracking : null
        ];

        $this->writeToLogFile('memory_tracking', $logEntry);
    }

    /**
     * لاگ خطای خاص
     */
    public function logError(string $message, array $context = [], \Throwable $exception = null): void
    {
        if (!$this->config['enabled']) return;

        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'ERROR',
            'message' => $message,
            'context' => $context,
            'exception' => $exception ? [
                'class' => get_class($exception),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $this->config['include_stack_trace'] ? $exception->getTraceAsString() : null
            ] : null
        ];

        $this->writeToLogFile('errors', $logEntry);
        
        // همچنین در Laravel log هم بنویس
        Log::error('[RMS Debug] ' . $message, array_merge($context, [
            'exception' => $exception?->getMessage()
        ]));
    }

    /**
     * خلاصه کلی debug session
     */
    public function logSessionSummary(array $debugData, string $sessionId): void
    {
        if (!$this->config['enabled']) return;

        $summary = [
            'timestamp' => now()->toISOString(),
            'type' => 'SESSION_SUMMARY',
            'session_id' => $sessionId,
            'duration' => $this->calculateSessionDuration($debugData),
            'performance_summary' => $debugData['performance_summary'] ?? [],
            'total_operations' => count($debugData['performance_details'] ?? []),
            'total_fields_analyzed' => count($debugData['field_analysis'] ?? []),
            'form_issues_count' => $this->countFormIssues($debugData),
            'database_queries' => $debugData['database_analysis']['total_queries'] ?? 0,
            'memory_checkpoints' => count($debugData['memory_tracking'] ?? []),
            'overall_health' => $this->assessOverallHealth($debugData)
        ];

        $this->writeToLogFile('session_summary', $summary);
    }

    /**
     * نوشتن در فایل لاگ
     */
    public function writeToLogFile(string $type, array $data): void
    {
        $filename = $this->getLogFileForType($type);
        $logLine = $this->formatLogLine($data);
        
        try {
            File::append($filename, $logLine . PHP_EOL);
            
            // چک کردن سایز فایل
            if (File::size($filename) > $this->config['max_file_size'] * 1024 * 1024) {
                $this->rotateLogFile($filename, $type);
            }
            
        } catch (\Exception $e) {
            // Fallback به Laravel log
            Log::error('[RMS Debug Logger] Failed to write to log file: ' . $e->getMessage());
        }
    }

    /**
     * تولید نام فایل لاگ
     */
    private function generateLogFileName(): string
    {
        return 'rms_debug_' . date('Y-m-d') . '.log';
    }

    /**
     * دریافت نام فایل لاگ برای نوع خاص
     */
    private function getLogFileForType(string $type): string
    {
        $date = date('Y-m-d');
        return $this->logPath . "/rms_debug_{$type}_{$date}.log";
    }

    /**
     * فرمت کردن خط لاگ
     */
    private function formatLogLine(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * اطمینان از وجود پوشه لاگ
     */
    private function ensureLogDirectoryExists(): void
    {
        if (!File::exists($this->logPath)) {
            File::makeDirectory($this->logPath, 0755, true);
        }
    }

    /**
     * پاک کردن فایل‌های لاگ قدیمی
     */
    private function cleanOldLogFiles(): void
    {
        try {
            $files = File::glob($this->logPath . '/rms_debug_*.log');
            
            if (count($files) > $this->config['max_files']) {
                // مرتب کردن بر اساس تاریخ تغییر
                usort($files, fn($a, $b) => File::lastModified($a) - File::lastModified($b));
                
                // پاک کردن فایل‌های قدیمی
                $filesToDelete = array_slice($files, 0, count($files) - $this->config['max_files']);
                foreach ($filesToDelete as $file) {
                    File::delete($file);
                }
            }
        } catch (\Exception $e) {
            Log::error('[RMS Debug Logger] Failed to clean old log files: ' . $e->getMessage());
        }
    }

    /**
     * چرخش فایل لاگ
     */
    private function rotateLogFile(string $filename, string $type): void
    {
        $timestamp = time();
        $rotatedName = str_replace('.log', "_{$timestamp}.log", $filename);
        
        try {
            File::move($filename, $rotatedName);
        } catch (\Exception $e) {
            Log::error('[RMS Debug Logger] Failed to rotate log file: ' . $e->getMessage());
        }
    }

    /**
     * نوشتن تحلیل مفصل
     */
    private function writeDetailedAnalysis(array $debugData, string $sessionId): void
    {
        if (!$this->config['detailed_analysis']) return;

        $analysisFile = $this->logPath . "/detailed_analysis_{$sessionId}.txt";
        
        $content = "=== RMS DEBUG DETAILED ANALYSIS ===\n";
        $content .= "Session ID: {$sessionId}\n";
        $content .= "Generated: " . now()->toDateTimeString() . "\n\n";
        
        // Performance Analysis
        if (!empty($debugData['performance_summary'])) {
            $content .= "=== PERFORMANCE SUMMARY ===\n";
            foreach ($debugData['performance_summary'] as $key => $value) {
                $content .= sprintf("%-30s: %s\n", ucwords(str_replace('_', ' ', $key)), $value);
            }
            $content .= "\n";
        }

        // Form Analysis
        if (!empty($debugData['form_analysis'])) {
            $content .= "=== FORM ANALYSIS ===\n";
            $content .= "Form Mode: " . ($debugData['form_analysis']['form_mode'] ?? 'unknown') . "\n";
            $content .= "Total Fields: " . ($debugData['form_analysis']['total_fields'] ?? 0) . "\n";
            $content .= "Errors: " . count($debugData['form_analysis']['errors'] ?? []) . "\n";
            $content .= "Warnings: " . count($debugData['form_analysis']['warnings'] ?? []) . "\n\n";
            
            if (!empty($debugData['form_analysis']['errors'])) {
                $content .= "ERRORS:\n";
                foreach ($debugData['form_analysis']['errors'] as $error) {
                    $content .= "- {$error}\n";
                }
                $content .= "\n";
            }
        }

        // Field Issues
        if (!empty($debugData['field_analysis'])) {
            $content .= "=== FIELD ISSUES ===\n";
            foreach ($debugData['field_analysis'] as $fieldKey => $field) {
                if (!empty($field['potential_issues'])) {
                    $content .= "Field: {$fieldKey} ({$field['type_name']})\n";
                    foreach ($field['potential_issues'] as $issue) {
                        $content .= "  - {$issue}\n";
                    }
                    $content .= "\n";
                }
            }
        }

        try {
            File::put($analysisFile, $content);
        } catch (\Exception $e) {
            Log::error('[RMS Debug Logger] Failed to write detailed analysis: ' . $e->getMessage());
        }
    }

    /**
     * helper methods برای محاسبات
     */
    private function logSingleIssue(string $level, string $issue, ?string $controller, string $category): void
    {
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'SINGLE_ISSUE',
            'level' => $level,
            'category' => $category,
            'controller' => $controller,
            'issue' => $issue
        ];

        $this->writeToLogFile('issues', $logEntry);
    }

    private function logSlowOperation(array $operation): void
    {
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'SLOW_OPERATION',
            'operation' => $operation['operation'] ?? 'unknown',
            'execution_time' => $operation['execution_time'] ?? 0,
            'context' => $operation['context'] ?? []
        ];

        $this->writeToLogFile('slow_operations', $logEntry);
    }

    private function logSlowQuery(array $query): void
    {
        $logEntry = [
            'timestamp' => now()->toISOString(),
            'type' => 'SLOW_QUERY',
            'sql' => $query['sql'] ?? '',
            'time' => $query['time'] ?? 0,
            'bindings' => $query['bindings'] ?? []
        ];

        $this->writeToLogFile('slow_queries', $logEntry);
    }

    private function parseMemoryValue(string $memoryString): int
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*(B|KB|MB|GB)/', $memoryString, $matches)) {
            $value = floatval($matches[1]);
            $unit = $matches[2];
            
            return match($unit) {
                'KB' => $value * 1024,
                'MB' => $value * 1024 * 1024,
                'GB' => $value * 1024 * 1024 * 1024,
                default => $value
            };
        }
        
        return 0;
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    private function calculateSessionDuration(array $debugData): string
    {
        if (isset($debugData['session_info']['start_time']) && 
            isset($debugData['performance_summary']['total_execution_time'])) {
            return $debugData['performance_summary']['total_execution_time'];
        }
        
        return 'Unknown';
    }

    private function countFormIssues(array $debugData): int
    {
        $count = 0;
        if (isset($debugData['form_analysis']['errors'])) {
            $count += count($debugData['form_analysis']['errors']);
        }
        if (isset($debugData['form_analysis']['warnings'])) {
            $count += count($debugData['form_analysis']['warnings']);
        }
        return $count;
    }

    private function assessOverallHealth(array $debugData): string
    {
        $issues = [];
        
        // Check performance
        if (isset($debugData['performance_summary']['total_execution_time'])) {
            $execTime = floatval(str_replace('ms', '', $debugData['performance_summary']['total_execution_time']));
            if ($execTime > 2000) $issues[] = 'slow_execution';
        }
        
        // Check memory
        if (isset($debugData['performance_summary']['peak_memory_usage'])) {
            $memoryMB = $this->parseMemoryValue($debugData['performance_summary']['peak_memory_usage']) / (1024 * 1024);
            if ($memoryMB > 256) $issues[] = 'high_memory_usage';
        }
        
        // Check queries
        if (isset($debugData['database_analysis']['total_queries'])) {
            if ($debugData['database_analysis']['total_queries'] > 50) $issues[] = 'too_many_queries';
        }
        
        // Check form issues
        $formIssueCount = $this->countFormIssues($debugData);
        if ($formIssueCount > 0) $issues[] = 'form_issues';
        
        if (empty($issues)) return 'HEALTHY';
        if (count($issues) <= 2) return 'WARNING';
        return 'CRITICAL';
    }

    /**
     * دریافت خلاصه لاگ‌های امروز
     */
    public function getTodayLogsSummary(): array
    {
        $today = date('Y-m-d');
        $logFiles = File::glob($this->logPath . "/rms_debug_*_{$today}.log");
        
        $summary = [
            'date' => $today,
            'total_files' => count($logFiles),
            'total_size' => 0,
            'file_details' => []
        ];
        
        foreach ($logFiles as $file) {
            $size = File::size($file);
            $summary['total_size'] += $size;
            $summary['file_details'][] = [
                'name' => basename($file),
                'size' => $this->formatBytes($size),
                'lines' => substr_count(File::get($file), "\n")
            ];
        }
        
        $summary['total_size_formatted'] = $this->formatBytes($summary['total_size']);
        
        return $summary;
    }
}