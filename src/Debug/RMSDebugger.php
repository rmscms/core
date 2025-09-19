<?php

namespace RMS\Core\Debug;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RMS\Core\Data\Field;
use RMS\Core\Data\FormGenerator;
use RMS\Core\Data\ListGenerator;
use Exception;
use Carbon\Carbon;

/**
 * RMSDebugger - سیستم حرفه‌ای debug برای RMS Core
 * 
 * قابلیت‌ها:
 * - تحلیل فرم‌ها و فیلدها
 * - بررسی validation rules
 * - مانیتورینگ performance
 * - تحلیل memory usage
 * - ردیابی database queries
 * - لاگ عملیات
 * 
 * @author RMS Core Team
 * @version 2.0.0
 */
class RMSDebugger
{
    private array $debugData = [];
    private array $performance = [];
    private array $memoryUsage = [];
    private array $databaseQueries = [];
    private array $validationRules = [];
    private array $fieldAnalysis = [];
    private float $startTime;
    private int $startMemory;
    private bool $enabled = false;
    private string $sessionId;

    /**
     * Debug levels
     */
    public const LEVEL_INFO = 'info';
    public const LEVEL_WARNING = 'warning';
    public const LEVEL_ERROR = 'error';
    public const LEVEL_CRITICAL = 'critical';

    /**
     * Debug categories
     */
    public const CATEGORY_FORM = 'form';
    public const CATEGORY_FIELD = 'field';
    public const CATEGORY_VALIDATION = 'validation';
    public const CATEGORY_PERFORMANCE = 'performance';
    public const CATEGORY_MEMORY = 'memory';
    public const CATEGORY_DATABASE = 'database';

    public function __construct(bool $loadFromLogs = false)
    {
        $this->enabled = config('app.debug', false) && config('rms.debug.enabled', true);
        $this->startTime = microtime(true);
        $this->startMemory = memory_get_usage(true);
        
        // ✅ مقداردهی اولیه آرای کوئری‌ها
        $this->databaseQueries = [];
        
        if ($loadFromLogs && $this->enabled) {
            // Don't create new session, will be set by loadFromLogs
            $this->sessionId = 'temp';
            $this->loadFromLogs();
        } else {
            $this->sessionId = uniqid('rms_debug_', true);
            if ($this->enabled) {
                $this->initializeDebugSession();
            }
        }
    }

    /**
     * مقداردهی اولیه session debug
     */
    private function initializeDebugSession(): void
    {
        $this->debugData = [
            'session_id' => $this->sessionId,
            'start_time' => $this->startTime,
            'start_memory' => $this->startMemory,
            'user_agent' => request()->header('User-Agent'),
            'ip_address' => request()->ip(),
            'request_uri' => request()->fullUrl(),
            'request_method' => request()->method(),
            'laravel_version' => app()->version(),
            'php_version' => PHP_VERSION,
            'rms_core_version' => '2.0.0'
        ];

        // Global query logging is handled by CoreServiceProvider
        // Local query logging disabled to avoid duplication
    }

    /**
     * راه‌اندازی سیستم ثبت کوئری برای کل درخواست
     */
    private function initializeQueryLogging(): void
    {
        // پاک کردن QueryLog قبلی و شروع مجدد
        DB::flushQueryLog();
        DB::enableQueryLog();
        
        // افزودن global query listener برای ثبت همه کوئری‌ها
        DB::listen(function ($query) {
            $this->databaseQueries[] = [
                'sql' => $query->sql,
                'query' => $query->sql, // برای سازگاری با JS
                'bindings' => $query->bindings,
                'time' => $query->time,
                'timestamp' => now()->toISOString(),
                'formatted_query' => $this->formatQueryWithBindings($query->sql, $query->bindings)
            ];
        });
    }

    /**
     * فعال/غیرفعال کردن debug
     */
    public function toggle(bool $enabled = null): self
    {
        if ($enabled !== null) {
            $this->enabled = $enabled;
        } else {
            $this->enabled = !$this->enabled;
        }

        if ($this->enabled && empty($this->debugData)) {
            $this->initializeDebugSession();
        }

        return $this;
    }

    /**
     * بررسی فعال بودن debug
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
    
    /**
     * دریافت global queries از CoreServiceProvider collector
     */
    private function getGlobalQueries(): array
    {
        if (app()->bound('rms.debug.queries')) {
            return app('rms.debug.queries');
        }
        return [];
    }

    /**
     * تحلیل کامل فرم
     */
    public function analyzeForm(\RMS\Core\Data\FormGenerator $generator, array $templateData = []): array
    {
        if (!$this->enabled) return [];

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        try {
            $analysis = [
                'form_id' => $generator->getId(),
                'form_mode' => $generator->getId() ? 'edit' : 'create',
                'form_url' => $generator->getForm()->formUrl(),
                'form_config' => $generator->getForm()->getFormConfig(),
                'total_fields' => count($generator->getFields()),
                'fields_analysis' => [],
                'template_data' => [],
                'validation_summary' => [],
                'errors' => [],
                'warnings' => []
            ];

            // تحلیل فیلدها - با مقادیر از templateData
            foreach ($generator->getFields() as $field) {
                // ✅ دریافت مقدار فیلد از templateData (بعد از همه تغییرات)
                $fieldValue = $this->extractFieldValueFromTemplate($field, $templateData);
                $fieldAnalysis = $this->analyzeFieldWithValue($field, $fieldValue);
                $analysis['fields_analysis'][$field->key] = $fieldAnalysis;

                // بررسی مشکلات احتمالی
                if ($fieldAnalysis['potential_issues']) {
                    $analysis['warnings'] = array_merge($analysis['warnings'], $fieldAnalysis['potential_issues']);
                }
            }

            // تحلیل template data
            if (!empty($templateData)) {
                $analysis['template_data'] = [
                    'fields_count' => count($templateData['fields'] ?? []),
                    'has_validation_errors' => !empty($templateData['errors']),
                    'has_old_input' => !empty($templateData['old']),
                    'form_config' => $templateData['config'] ?? [],
                    'breadcrumbs' => $templateData['breadcrumbs'] ?? [],
                ];

                // بررسی consistency بین generator و template
                $generatorFieldCount = count($generator->getFields());
                $templateFieldCount = count($templateData['fields'] ?? []);
                
                if ($generatorFieldCount !== $templateFieldCount) {
                    $analysis['errors'][] = "Field count mismatch: Generator={$generatorFieldCount}, Template={$templateFieldCount}";
                }
            }

            // آمار performance
            $analysis['performance'] = [
                'analysis_time' => round((microtime(true) - $startTime) * 1000, 2) . 'ms',
                'memory_used' => $this->formatBytes(memory_get_usage(true) - $startMemory),
                'peak_memory' => $this->formatBytes(memory_get_peak_usage(true))
            ];

            $this->debugData['form_analysis'] = $analysis;
            $this->log(self::LEVEL_INFO, self::CATEGORY_FORM, 'Form analysis completed', $analysis);

            return $analysis;

        } catch (Exception $e) {
            $error = [
                'error' => 'Form analysis failed: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ];

            $this->log(self::LEVEL_ERROR, self::CATEGORY_FORM, 'Form analysis error', $error);
            return $error;
        }
    }

    /**
     * تحلیل جزئیات فیلد (قدیمی - بدون مقدار)
     */
    public function analyzeField(Field $field): array
    {
        return $this->analyzeFieldWithValue($field, null);
    }

    /**
     * تحلیل جزئیات فیلد با مقدار از templateData
     */
    public function analyzeFieldWithValue(Field $field, $fieldValue = null): array
    {
        if (!$this->enabled) return [];
        
        $analysis = [
            'key' => $field->key,
            'title' => $field->title,
            'type' => $field->type,
            'type_name' => $this->getFieldTypeName($field->type),
            'database_column' => $field->database_column ?? $field->key,
            'required' => $field->required ?? false,
            'validation_rules' => $field->validation ?? [],
            'attributes' => $field->attributes ?? [],
            'options' => property_exists($field, 'options') ? $field->options : null,
            'default_value' => $field->default ?? null,
            'value' => $fieldValue, // ✅ مقدار از templateData
            'has_value' => $fieldValue !== null && $fieldValue !== '',
            'placeholder' => $field->attributes['placeholder'] ?? null,
            'help_text' => $field->attributes['data-help'] ?? $field->attributes['title'] ?? null,
            'max_length' => $field->attributes['maxlength'] ?? null,
            'is_unique' => str_contains(implode('|', $field->validation ?? []), 'unique'),
            'validated' => false, // بعداً به‌روزرسانی می‌شود
            'potential_issues' => []
        ];

        // بررسی مشکلات احتمالی
        $this->checkFieldIssues($field, $analysis);

        $this->fieldAnalysis[$field->key] = $analysis;

        return $analysis;
    }

    /**
     * استخراج مقدار فیلد از templateData (بعد از همه تغییرات)
     */
    private function extractFieldValueFromTemplate(Field $field, array $templateData): mixed
    {
        $dbColumn = $field->database_column ?? $field->key;
        
        // اول: جستجو در form_values (مقادیر فرم لود شده از دیتابیس)
        if (isset($templateData['form_values'][$dbColumn])) {
            return $templateData['form_values'][$dbColumn];
        }
        
        if (isset($templateData['form_values'][$field->key])) {
            return $templateData['form_values'][$field->key];
        }

        // دوم: جستجو در old input data
        if (isset($templateData['old'][$field->key])) {
            return $templateData['old'][$field->key];
        }
        
        // سوم: جستجو در fields فرمت شده template (برای تغییرات در beforeSendToTemplate)
        if (isset($templateData['fields'])) {
            foreach ($templateData['fields'] as $templateField) {
                if (is_object($templateField) && isset($templateField->key) && $templateField->key === $field->key) {
                    // بررسی انواع مختلف مقدار در فیلد
                    if (isset($templateField->value)) {
                        return $templateField->value;
                    }
                    if (isset($templateField->default)) {
                        return $templateField->default;
                    }
                    // برای فیلدهای SELECT که selected option دارند
                    if (isset($templateField->selected)) {
                        return $templateField->selected;
                    }
                    break;
                }
            }
        }

        // چهارم: جستجو در model data (برای edit mode)
        if (isset($templateData['model']) && is_object($templateData['model'])) {
            $model = $templateData['model'];
            
            // بررسی اینکه آیا فیلد در model موجود است
            if (isset($model->$dbColumn)) {
                return $model->$dbColumn;
            }
            if (method_exists($model, 'getAttribute')) {
                return $model->getAttribute($dbColumn);
            }
        }

        // پنجم: مقدار پیش‌فرض فیلد
        if (isset($field->default)) {
            return $field->default;
        }

        return null;
    }

    /**
     * بررسی مشکلات احتمالی فیلد
     */
    private function checkFieldIssues(Field $field, array &$analysis): void
    {
        // بررسی نوع فیلد
        if (!defined('RMS\Core\Data\Field::' . strtoupper($this->getFieldConstantName($field->type)))) {
            $analysis['potential_issues'][] = "Unknown field type: {$field->type}";
        }

        // بررسی select options
        if (in_array($field->type, [Field::SELECT]) && empty($field->options)) {
            $analysis['potential_issues'][] = "SELECT field '{$field->key}' has no options";
        }

        // بررسی validation rules
        if ($field->required && empty($field->validation)) {
            $analysis['potential_issues'][] = "Required field '{$field->key}' has no validation rules";
        }

        // بررسی database column consistency
        if ($field->database_column && $field->database_column !== $field->key) {
            $analysis['potential_issues'][] = "Field '{$field->key}' maps to different database column: '{$field->database_column}'";
        }

        // بررسی فیلدهای خاص
        if ($field->type === Field::IMAGE && empty($field->attributes['accept'])) {
            $analysis['potential_issues'][] = "IMAGE field '{$field->key}' should have 'accept' attribute";
        }

        if ($field->type === Field::DATE && empty($field->attributes['class'])) {
            $analysis['potential_issues'][] = "DATE field '{$field->key}' might be missing persian-datepicker class";
        }
    }

    /**
     * تحلیل validation rules
     */
    public function analyzeValidation(array $rules, Request $request = null): array
    {
        if (!$this->enabled) return [];

        $analysis = [
            'total_rules' => count($rules),
            'rules_breakdown' => [],
            'potential_issues' => [],
            'request_validation' => []
        ];

        foreach ($rules as $field => $fieldRules) {
            $ruleString = is_array($fieldRules) ? implode('|', $fieldRules) : $fieldRules;
            $analysis['rules_breakdown'][$field] = [
                'rules' => $ruleString,
                'is_required' => str_contains($ruleString, 'required'),
                'has_validation' => !empty($fieldRules)
            ];
        }

        // اگر request موجود است، validation را بررسی کن
        if ($request) {
            try {
                $validator = validator($request->all(), $rules);
                $analysis['request_validation'] = [
                    'passes' => $validator->passes(),
                    'fails' => $validator->fails(),
                    'errors' => $validator->errors()->toArray(),
                    'validated_data_count' => $validator->passes() ? count($validator->validated()) : 0
                ];
            } catch (Exception $e) {
                $analysis['request_validation']['error'] = 'Validation analysis failed: ' . $e->getMessage();
            }
        }

        $this->validationRules = $analysis;
        $this->log(self::LEVEL_INFO, self::CATEGORY_VALIDATION, 'Validation analysis completed', $analysis);

        return $analysis;
    }

    /**
     * مانیتورینگ performance
     */
    public function measurePerformance(string $operation, callable $callback, array $context = [])
    {
        if (!$this->enabled) return $callback();

        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        $startQueries = count(DB::getQueryLog());

        try {
            $result = $callback();
            
            $endTime = microtime(true);
            $endMemory = memory_get_usage(true);
            $endQueries = count(DB::getQueryLog());

            $metrics = [
                'operation' => $operation,
                'execution_time' => round(($endTime - $startTime) * 1000, 2),
                'memory_used' => $this->formatBytes($endMemory - $startMemory),
                'memory_peak' => $this->formatBytes(memory_get_peak_usage(true)),
                'queries_executed' => $endQueries - $startQueries,
                'timestamp' => Carbon::now()->toISOString(),
                'context' => $context
            ];

            $this->performance[] = $metrics;
            $this->log(self::LEVEL_INFO, self::CATEGORY_PERFORMANCE, 'Performance measured', $metrics);

            return $result;

        } catch (Exception $e) {
            $this->log(self::LEVEL_ERROR, self::CATEGORY_PERFORMANCE, 'Performance measurement error', [
                'operation' => $operation,
                'error' => $e->getMessage(),
                'context' => $context
            ]);
            throw $e;
        }
    }

    /**
     * تحلیل database queries
     * از داده‌های جمع‌آوری شده توسط query listener استفاده می‌کند
     */
    public function analyzeDatabaseQueries(): array
    {
        if (!$this->enabled) return [];

        // ✅ استفاده از global query collector (priority) + Laravel Query Log (fallback)
        $globalQueries = $this->getGlobalQueries();
        $laravelQueries = DB::getQueryLog();
        
        // اگر global queries موجود باشد، از آن استفاده کنیم
        if (!empty($globalQueries)) {
            $allQueries = $globalQueries;
        } else {
            // fallback به Laravel Query Log
            $allQueries = array_map(function($query, $index) {
                return [
                    'index' => $index + 1,
                    'sql' => $query['query'],
                    'query' => $query['query'],
                    'time' => $query['time'] ?? 0,
                    'bindings' => $query['bindings'],
                    'timestamp' => now()->toISOString(),
                    'formatted_query' => $this->formatQueryWithBindings($query['query'], $query['bindings'])
                ];
            }, $laravelQueries, array_keys($laravelQueries));
        }
        
        $analysis = [
            'total_queries' => count($allQueries),
            'total_time' => 0,
            'slow_queries' => [],
            'duplicate_queries' => [],
            'queries' => $allQueries, // ✅ همه کوئری‌ها
            'query_types' => [],
            'bindings_summary' => []
        ];

        $queryHashes = [];
        $slowQueryThreshold = config('rms.debug.slow_query_threshold', 100); // ms

        foreach ($allQueries as $index => $query) {
            $queryTime = $query['time'] ?? 0;
            $analysis['total_time'] += $queryTime;
            
            // تشخیص slow queries
            if ($queryTime > $slowQueryThreshold) {
                $analysis['slow_queries'][] = [
                    'sql' => $query['sql'] ?? $query['query'],
                    'time' => $queryTime,
                    'bindings' => $query['bindings'] ?? []
                ];
            }

            // تشخیص duplicate queries
            $querySql = $query['sql'] ?? $query['query'];
            $queryHash = md5($querySql);
            if (isset($queryHashes[$queryHash])) {
                $queryHashes[$queryHash]++;
                if ($queryHashes[$queryHash] === 2) {
                    $analysis['duplicate_queries'][] = [
                        'sql' => $querySql,
                        'count' => $queryHashes[$queryHash]
                    ];
                }
            } else {
                $queryHashes[$queryHash] = 1;
            }

            // تحلیل نوع query
            $type = strtoupper(explode(' ', trim($querySql))[0]);
            $analysis['query_types'][$type] = ($analysis['query_types'][$type] ?? 0) + 1;

            // تحلیل bindings
            $bindings = $query['bindings'] ?? [];
            $analysis['bindings_summary'][] = count($bindings);
        }

        // به‌روزرسانی duplicate count
        foreach ($analysis['duplicate_queries'] as &$duplicate) {
            $queryHash = md5($duplicate['sql']);
            $duplicate['count'] = $queryHashes[$queryHash];
        }

        $this->databaseQueries = $analysis;
        $this->log(self::LEVEL_INFO, self::CATEGORY_DATABASE, 'Database queries analyzed', $analysis);

        return $analysis;
    }

    /**
     * مانیتورینگ memory
     */
    public function trackMemoryUsage(string $checkpoint): array
    {
        if (!$this->enabled) return [];

        $memoryData = [
            'checkpoint' => $checkpoint,
            'current_usage' => memory_get_usage(true),
            'current_usage_formatted' => $this->formatBytes(memory_get_usage(true)),
            'peak_usage' => memory_get_peak_usage(true),
            'peak_usage_formatted' => $this->formatBytes(memory_get_peak_usage(true)),
            'since_start' => memory_get_usage(true) - $this->startMemory,
            'since_start_formatted' => $this->formatBytes(memory_get_usage(true) - $this->startMemory),
            'timestamp' => Carbon::now()->toISOString()
        ];

        $this->memoryUsage[] = $memoryData;
        $this->log(self::LEVEL_INFO, self::CATEGORY_MEMORY, 'Memory checkpoint', $memoryData);

        return $memoryData;
    }

    /**
     * دریافت تمام اطلاعات debug
     */
    public function getDebugData(): array
    {
        if (!$this->enabled) return ['debug_disabled' => true];

        // اگر در memory دیتا نداریم، از logs بخونیم
        if (empty($this->debugData) || empty($this->fieldAnalysis)) {
            $this->loadFromLogs();
        }

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        return [
            'session_info' => $this->debugData,
            'performance_summary' => [
                'total_execution_time' => round(($endTime - $this->startTime) * 1000, 2) . 'ms',
                'total_memory_used' => $this->formatBytes($endMemory - $this->startMemory),
                'peak_memory_usage' => $this->formatBytes(memory_get_peak_usage(true)),
                'operations_measured' => count($this->performance)
            ],
            'performance_details' => $this->performance,
            'memory_tracking' => $this->memoryUsage,
            'database_analysis' => $this->databaseQueries,
            'field_analysis' => $this->fieldAnalysis,
            'validation_analysis' => $this->validationRules,
            'form_analysis' => $this->debugData['form_analysis'] ?? null
        ];
    }

    /**
     * لود کردن دیتا از log files
     */
    private function loadFromLogs(): void
    {
        try {
            $logPath = storage_path('logs/rms_debug/rms_system-' . date('Y-m-d') . '.log');
            
            if (!file_exists($logPath)) {
                return;
            }

            $logContent = file_get_contents($logPath);
            $lines = explode("\n", $logContent);
            
            // پیدا کردن آخرین session که Form analysis completed دارد
            $sessions = [];
            $latestFormAnalysisSession = null;
            $sessionTimestamps = [];
            
            foreach (array_reverse($lines) as $line) {
                if (empty($line)) continue;
                
                // استخراج JSON از log line
                if (preg_match('/\{.*\}/', $line, $matches)) {
                    $logData = json_decode($matches[0], true);
                    
                    if ($logData && isset($logData['session_id'])) {
                        $sessionId = $logData['session_id'];
                        $timestamp = $logData['timestamp'] ?? '';
                        
                        // اگر این session شامل Form analysis است
                        if ($logData['message'] === 'Form analysis completed') {
                            if (!$latestFormAnalysisSession || 
                                (isset($sessionTimestamps[$sessionId]) && $timestamp > $sessionTimestamps[$latestFormAnalysisSession])) {
                                $latestFormAnalysisSession = $sessionId;
                            }
                        }
                        
                        $sessionTimestamps[$sessionId] = $timestamp;
                        
                        if (!isset($sessions[$sessionId])) {
                            $sessions[$sessionId] = [];
                        }
                        $sessions[$sessionId][] = $logData;
                    }
                }
            }
            
            // اگر Form analysis session پیدا شد، از آن استفاده کن
            $targetSession = $latestFormAnalysisSession;
            
            // اگر نه، از آخرین session استفاده کن
            if (!$targetSession && !empty($sessions)) {
                $targetSession = array_keys($sessions)[0];
            }
            
            if ($targetSession && isset($sessions[$targetSession])) {
                // Use target session's ID
                $this->sessionId = $targetSession;
                
                // پردازش دیتاهای session به ترتیب زمانی
                $sessionLogs = $sessions[$targetSession];
                usort($sessionLogs, function($a, $b) {
                    return ($a['timestamp'] ?? '') <=> ($b['timestamp'] ?? '');
                });
                
                // Initialize debug data with session info
                $this->debugData = [
                    'session_id' => $targetSession,
                    'start_time' => $this->startTime,
                    'start_memory' => $this->startMemory,
                    'user_agent' => 'From Log',
                    'ip_address' => 'From Log', 
                    'request_uri' => 'From Log',
                    'request_method' => 'From Log',
                    'laravel_version' => app()->version(),
                    'php_version' => PHP_VERSION,
                    'rms_core_version' => '2.0.0'
                ];
                
                foreach ($sessionLogs as $logEntry) {
                    $this->processLogEntry($logEntry);
                }
            } else {
                // No logs found, create empty debug data
                $this->debugData = [
                    'session_id' => $this->sessionId,
                    'start_time' => $this->startTime,
                    'start_memory' => $this->startMemory,
                    'user_agent' => 'No logs found',
                    'ip_address' => 'No logs found',
                    'request_uri' => 'No logs found', 
                    'request_method' => 'No logs found',
                    'laravel_version' => app()->version(),
                    'php_version' => PHP_VERSION,
                    'rms_core_version' => '2.0.0'
                ];
            }
            
        } catch (Exception $e) {
            error_log('RMS Debug loadFromLogs error: ' . $e->getMessage());
        }
    }
    
    /**
     * پردازش یک entry از log
     */
    private function processLogEntry(array $logData): void
    {
        $category = $logData['category'] ?? '';
        $context = $logData['context'] ?? [];
        
        switch ($category) {
            case self::CATEGORY_FORM:
                if ($logData['message'] === 'Form analysis completed') {
                    $this->debugData['form_analysis'] = $context;
                    $this->fieldAnalysis = $context['fields_analysis'] ?? [];
                }
                break;
                
            case self::CATEGORY_PERFORMANCE:
                if ($logData['message'] === 'Performance measured') {
                    $this->performance[] = $context;
                }
                break;
                
            case self::CATEGORY_DATABASE:
                if ($logData['message'] === 'Database queries analyzed') {
                    $this->databaseQueries = $context;
                }
                break;
                
            case self::CATEGORY_MEMORY:
                if ($logData['message'] === 'Memory checkpoint') {
                    $this->memoryUsage[] = $context;
                }
                break;
                
            case self::CATEGORY_VALIDATION:
                if ($logData['message'] === 'Validation analysis completed') {
                    $this->validationRules = $context;
                }
                break;
        }
        
        // اطلاعات پایه session - به‌روزرسانی اگر از log اطلاعات بیشتری داریم
        if (isset($this->debugData['session_id']) && $this->debugData['session_id'] === ($logData['session_id'] ?? '')) {
            // Update from log context if available
            if (isset($logData['context'])) {
                $context = $logData['context'];
                if (isset($context['request_data'])) {
                    $this->debugData['request_uri'] = $context['request_uri'] ?? $this->debugData['request_uri'];
                    $this->debugData['request_method'] = $context['request_method'] ?? $this->debugData['request_method'];
                    $this->debugData['user_agent'] = $context['user_agent'] ?? $this->debugData['user_agent'];
                    $this->debugData['ip_address'] = $context['ip_address'] ?? $this->debugData['ip_address'];
                }
            }
        }
    }

    /**
     * لاگ debug
     */
    public function log(string $level, string $category, string $message, array $context = []): void
    {
        if (!$this->enabled) return;

        $logData = [
            'session_id' => $this->sessionId,
            'level' => $level,
            'category' => $category,
            'message' => $message,
            'context' => $context,
            'timestamp' => Carbon::now()->toISOString(),
            'memory' => memory_get_usage(true),
            'time_since_start' => microtime(true) - $this->startTime
        ];

        // Log to Laravel log with custom channel
        Log::channel('rms_debug')->log($level, $message, $logData);

        // Store in cache for UI access
        $cacheKey = "rms_debug_logs_{$this->sessionId}";
        $existingLogs = Cache::get($cacheKey, []);
        $existingLogs[] = $logData;
        Cache::put($cacheKey, $existingLogs, now()->addHours(1));
    }

    /**
     * پاک کردن debug data
     */
    public function clear(): void
    {
        $this->debugData = [];
        $this->performance = [];
        $this->memoryUsage = [];
        $this->databaseQueries = [];
        $this->validationRules = [];
        $this->fieldAnalysis = [];
        
        Cache::forget("rms_debug_logs_{$this->sessionId}");
    }

    /**
     * Export debug data
     */
    public function export(string $format = 'json'): string
    {
        $data = $this->getDebugData();

        switch ($format) {
            case 'json':
                return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            case 'yaml':
                return yaml_emit($data);
            
            case 'text':
                return $this->formatAsText($data);
            
            default:
                return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
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

    /**
     * دریافت نام نوع فیلد
     */
    private function getFieldTypeName(int $type): string
    {
        $types = [
            Field::STRING => 'STRING',
            Field::NUMBER => 'NUMBER',
            Field::INTEGER => 'INTEGER',
            Field::BOOL => 'BOOLEAN',
            Field::SELECT => 'SELECT',
            Field::DATE => 'DATE',
            Field::DATE_TIME => 'DATE_TIME',
            Field::TIME => 'TIME',
            Field::PASSWORD => 'PASSWORD',
            Field::HIDDEN => 'HIDDEN',
            Field::COMMENT => 'COMMENT',
            Field::FILE => 'FILE',
            Field::EDITOR => 'EDITOR',
            Field::LABEL => 'LABEL',
            Field::COLOR => 'COLOR',
            Field::RANGE => 'RANGE',
            Field::PRICE => 'PRICE',
            Field::IMAGE => 'IMAGE'
        ];

        return $types[$type] ?? "UNKNOWN({$type})";
    }

    /**
     * دریافت نام ثابت فیلد
     */
    private function getFieldConstantName(int $type): string
    {
        return match($type) {
            Field::STRING => 'STRING',
            Field::NUMBER => 'NUMBER',
            Field::INTEGER => 'INTEGER',
            Field::BOOL => 'BOOL',
            Field::SELECT => 'SELECT',
            Field::DATE => 'DATE',
            Field::DATE_TIME => 'DATE_TIME',
            Field::TIME => 'TIME',
            Field::PASSWORD => 'PASSWORD',
            Field::HIDDEN => 'HIDDEN',
            Field::COMMENT => 'COMMENT',
            Field::FILE => 'FILE',
            Field::EDITOR => 'EDITOR',
            Field::LABEL => 'LABEL',
            Field::COLOR => 'COLOR',
            Field::RANGE => 'RANGE',
            Field::PRICE => 'PRICE',
            Field::IMAGE => 'IMAGE',
            default => 'UNKNOWN'
        };
    }

    /**
     * فرمت کردن کوئری با bindings برای نمایش خوانا
     */
    private function formatQueryWithBindings(string $query, array $bindings): string
    {
        if (empty($bindings)) {
            return $query;
        }

        $formattedQuery = $query;
        
        // جایگزینی ? با مقادیر واقعی
        foreach ($bindings as $binding) {
            $value = $this->formatBindingValue($binding);
            $formattedQuery = preg_replace('/\?/', $value, $formattedQuery, 1);
        }

        return $formattedQuery;
    }

    /**
     * فرمت کردن مقدار binding برای نمایش
     */
    private function formatBindingValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        if (is_string($value)) {
            // اسکیپ کردن کووت‌ها
            $escaped = str_replace(["'", '"'], ["\\'", '\\"'], $value);
            return "'$escaped'";
        }
        
        if (is_array($value) || is_object($value)) {
            return "'" . json_encode($value) . "'";
        }
        
        return "'$value'";
    }

    /**
     * ترکیب کوئری‌ها از منابع مختلف به صورت unique
     *
     * @param array $globalQueries کوئری‌های global collector
     * @param array $collectedQueries کوئری‌های local debugger
     * @param array $laravelQueries کوئری‌های Laravel Query Log
     * @return array
     */
    private function mergeQuerySources(array $globalQueries, array $collectedQueries = [], array $laravelQueries = []): array
    {
        $merged = [];
        $processedHashes = [];
        
        // ابتدا global queries را اضافه کنیم
        foreach ($globalQueries as $index => $query) {
            $queryHash = md5(($query['sql'] ?? $query['query']) . serialize($query['bindings'] ?? []));
            
            if (!isset($processedHashes[$queryHash])) {
                $merged[] = [
                    'index' => $index + 1,
                    'sql' => $query['sql'] ?? $query['query'],
                    'query' => $query['sql'] ?? $query['query'],
                    'time' => $query['time'] ?? 0,
                    'bindings' => $query['bindings'] ?? [],
                    'timestamp' => $query['timestamp'] ?? now()->toISOString(),
                    'formatted_query' => $query['formatted_query'] ?? $this->formatQueryWithBindings($query['sql'] ?? $query['query'], $query['bindings'] ?? [])
                ];
                
                $processedHashes[$queryHash] = true;
            }
        }
        
        // سپس local collected queries را اضافه کنیم
        $baseIndex = count($merged);
        foreach ($collectedQueries as $index => $query) {
            $queryHash = md5(($query['sql'] ?? $query['query']) . serialize($query['bindings'] ?? []));
            
            if (!isset($processedHashes[$queryHash])) {
                $merged[] = [
                    'index' => $baseIndex + $index + 1,
                    'sql' => $query['sql'] ?? $query['query'],
                    'query' => $query['sql'] ?? $query['query'],
                    'time' => $query['time'] ?? 0,
                    'bindings' => $query['bindings'] ?? [],
                    'timestamp' => $query['timestamp'] ?? now()->toISOString(),
                    'formatted_query' => $query['formatted_query'] ?? $this->formatQueryWithBindings($query['sql'] ?? $query['query'], $query['bindings'] ?? [])
                ];
                
                $processedHashes[$queryHash] = true;
            }
        }
        
        // أخیرا Laravel Query Log را بررسی کنیم
        $baseIndex = count($merged);
        foreach ($laravelQueries as $index => $query) {
            $queryHash = md5($query['query'] . serialize($query['bindings']));
            
            if (!isset($processedHashes[$queryHash])) {
                $merged[] = [
                    'index' => $baseIndex + $index + 1,
                    'sql' => $query['query'],
                    'query' => $query['query'],
                    'time' => $query['time'] ?? 0,
                    'bindings' => $query['bindings'],
                    'timestamp' => now()->toISOString(),
                    'formatted_query' => $this->formatQueryWithBindings($query['query'], $query['bindings'])
                ];
                
                $processedHashes[$queryHash] = true;
            }
        }
        
        return $merged;
    }

    /**
     * فرمت کردن به متن
     */
    private function formatAsText(array $data): string
    {
        $output = "=== RMS Debug Report ===\n";
        $output .= "Session: {$data['session_info']['session_id']}\n";
        $output .= "Generated: " . Carbon::now()->toDateTimeString() . "\n\n";

        $output .= "=== Performance Summary ===\n";
        foreach ($data['performance_summary'] as $key => $value) {
            $output .= sprintf("%-25s: %s\n", ucwords(str_replace('_', ' ', $key)), $value);
        }

        $output .= "\n=== Field Analysis ===\n";
        foreach ($data['field_analysis'] as $field => $analysis) {
            $output .= "Field: {$field} ({$analysis['type_name']})\n";
            if (!empty($analysis['potential_issues'])) {
                $output .= "  Issues: " . implode(', ', $analysis['potential_issues']) . "\n";
            }
        }

        return $output;
    }

    /**
     * Get session ID
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }
    
    /**
     * Get singleton instance
     */
    public static function instance(): self
    {
        static $instance = null;
        
        if ($instance === null) {
            $instance = new self();
        }
        
        return $instance;
    }
}