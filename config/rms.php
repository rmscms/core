<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | RMS Debug System Configuration
    |--------------------------------------------------------------------------
    |
    | تنظیمات سیستم debug حرفه‌ای RMS Core برای تحلیل و رفع مشکل فرم‌ها
    |
    */
    
    'debug' => [
        
        /*
        |--------------------------------------------------------------------------
        | Debug System Enable/Disable
        |--------------------------------------------------------------------------
        |
        | فعال یا غیرفعال کردن کل سیستم debug
        | فقط در حالت APP_DEBUG=true کار می‌کند
        |
        */
        'enabled' => env('RMS_DEBUG_ENABLED', true),
        
        /*
        |--------------------------------------------------------------------------
        | Debug Logging Configuration
        |--------------------------------------------------------------------------
        |
        | تنظیمات سیستم لاگ فایل debug
        |
        */
        'logging' => [
            'enabled' => env('RMS_DEBUG_LOGGING_ENABLED', true),
            'max_file_size' => env('RMS_DEBUG_MAX_FILE_SIZE', 10), // MB
            'max_files' => env('RMS_DEBUG_MAX_FILES', 30),
            'include_stack_trace' => env('RMS_DEBUG_INCLUDE_STACK_TRACE', true),
            'detailed_analysis' => env('RMS_DEBUG_DETAILED_ANALYSIS', true),
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Debug Middleware Configuration  
        |--------------------------------------------------------------------------
        |
        | تنظیمات middleware خودکار debug
        |
        */
        'middleware' => [
            'enabled' => env('RMS_DEBUG_MIDDLEWARE_ENABLED', true),
        ],
        
        /*
        |--------------------------------------------------------------------------
        | Performance Thresholds
        |--------------------------------------------------------------------------
        |
        | حد آستانه‌های performance برای تشخیص مسائل
        |
        */
        'slow_query_threshold' => env('RMS_DEBUG_SLOW_QUERY_THRESHOLD', 100), // ms
        'slow_request_threshold' => env('RMS_DEBUG_SLOW_REQUEST_THRESHOLD', 1000), // ms
        'memory_threshold' => env('RMS_DEBUG_MEMORY_THRESHOLD', 50), // MB
        
        /*
        |--------------------------------------------------------------------------
        | UI Panel Configuration
        |--------------------------------------------------------------------------
        |
        | تنظیمات رابط کاربری debug panel
        |
        */
        'panel' => [
            'auto_refresh' => env('RMS_DEBUG_PANEL_AUTO_REFRESH', false),
            'refresh_interval' => env('RMS_DEBUG_PANEL_REFRESH_INTERVAL', 5000), // ms
            'max_log_entries' => env('RMS_DEBUG_PANEL_MAX_LOG_ENTRIES', 1000),
        ],
        
    ],
    
    /*
    |--------------------------------------------------------------------------
    | RMS Core General Configuration
    |--------------------------------------------------------------------------
    |
    | سایر تنظیمات عمومی RMS Core
    |
    */
    
    'version' => '2.0.0',
    
    'cache' => [
        'prefix' => env('RMS_CACHE_PREFIX', 'rms_'),
        'ttl' => env('RMS_CACHE_TTL', 3600), // seconds
    ],
    
];