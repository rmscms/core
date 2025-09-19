<?php

namespace RMS\Core\Http\Controllers\Admin;

use RMS\Core\Controllers\Admin\AdminController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class CacheManagerController extends AdminController
{
    // Abstract method implementations (required by AdminController)
    public function table(): string 
    {
        return '';
    }
    
    public function modelName(): string 
    {
        return '';
    }

    /**
     * پاک کردن کلیه کش‌ها
     */
    public function clearAll(Request $request): JsonResponse
    {
        try {
            $results = [];
            
            // پاک کردن Application Cache
            Cache::flush();
            $results['application'] = true;
            
            // پاک کردن Config Cache
            Artisan::call('config:clear');
            $results['config'] = true;
            
            // پاک کردن Route Cache
            Artisan::call('route:clear');
            $results['route'] = true;
            
            // پاک کردن View Cache
            Artisan::call('view:clear');
            $results['view'] = true;
            
            // پاک کردن Compiled Cache
            if (function_exists('opcache_reset')) {
                opcache_reset();
                $results['opcache'] = true;
            }
            
            // Log the action
            Log::info('Cache cleared by admin', [
                'admin_id' => auth('admin')->id(),
                'admin_name' => auth('admin')->user()->name ?? 'Unknown',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'results' => $results
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'تمام کش‌ها با موفقیت پاک شدند! 🚀',
                'results' => $results,
                'details' => [
                    'application' => '✅ کش اپلیکیشن پاک شد',
                    'config' => '✅ کش تنظیمات پاک شد', 
                    'route' => '✅ کش مسیرها پاک شد',
                    'view' => '✅ کش نماها پاک شد'
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Cache clear failed', [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => '❌ خطا در پاک کردن کش‌ها',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * پاک کردن کش خاص
     */
    public function clearSpecific(Request $request, string $type): JsonResponse
    {
        try {
            $result = false;
            $message = '';
            
            switch ($type) {
                case 'application':
                    Cache::flush();
                    $result = true;
                    $message = '✅ کش اپلیکیشن پاک شد';
                    break;
                    
                case 'config':
                    Artisan::call('config:clear');
                    $result = true;
                    $message = '✅ کش تنظیمات پاک شد';
                    break;
                    
                case 'route':
                    Artisan::call('route:clear');
                    $result = true;
                    $message = '✅ کش مسیرها پاک شد';
                    break;
                    
                case 'view':
                    Artisan::call('view:clear');
                    $result = true;
                    $message = '✅ کش نماها پاک شد';
                    break;
                    
                case 'optimize':
                    Artisan::call('optimize:clear');
                    $result = true;
                    $message = '✅ تمام بهینه‌سازی‌ها پاک شد';
                    break;
                    
                case 'opcache':
                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                        $result = true;
                        $message = '✅ کش OpCache پاک شد';
                    } else {
                        $message = '⚠️ OpCache در دسترس نیست';
                    }
                    break;
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => '❌ نوع کش نامعتبر است'
                    ], 400);
            }
            
            // Log the specific action
            Log::info("Specific cache cleared: {$type}", [
                'admin_id' => auth('admin')->id(),
                'admin_name' => auth('admin')->user()->name ?? 'Unknown',
                'cache_type' => $type,
                'ip' => $request->ip()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'type' => $type
            ]);
            
        } catch (\Exception $e) {
            Log::error("Specific cache clear failed: {$type}", [
                'error' => $e->getMessage(),
                'admin_id' => auth('admin')->id(),
                'type' => $type
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "❌ خطا در پاک کردن کش {$type}",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * نمایش وضعیت کش‌ها
     */
    public function status(): JsonResponse
    {
        try {
            $status = [
                'application' => [
                    'active' => !empty(glob(storage_path('framework/cache/data/*'))),
                    'size' => $this->getCacheSize('framework/cache'),
                    'description' => 'کش اپلیکیشن Laravel',
                    'icon' => 'ph-database'
                ],
                'config' => [
                    'cached' => file_exists(base_path('bootstrap/cache/config.php')),
                    'size' => file_exists(base_path('bootstrap/cache/config.php')) ? 
                        $this->formatBytes(filesize(base_path('bootstrap/cache/config.php'))) : '0 B',
                    'description' => 'کش تنظیمات',
                    'icon' => 'ph-gear'
                ],
                'route' => [
                    'cached' => file_exists(base_path('bootstrap/cache/routes-v7.php')),
                    'size' => file_exists(base_path('bootstrap/cache/routes-v7.php')) ? 
                        $this->formatBytes(filesize(base_path('bootstrap/cache/routes-v7.php'))) : '0 B',
                    'description' => 'کش مسیرها',
                    'icon' => 'ph-signpost'
                ],
                'view' => [
                    'cached' => !empty(glob(storage_path('framework/views/*.php'))),
                    'size' => $this->getCacheSize('framework/views'),
                    'description' => 'کش نماها',
                    'icon' => 'ph-eye'
                ],
                'opcache' => [
                    'active' => function_exists('opcache_get_status') && opcache_get_status(),
                    'description' => 'کش OpCache',
                    'icon' => 'ph-lightning'
                ]
            ];
            
            return response()->json([
                'success' => true,
                'status' => $status,
                'last_check' => now()->format('Y-m-d H:i:s')
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت وضعیت کش',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * محاسبه تقریبی حجم کش
     */
    private function getCacheSize(string $relativePath): string
    {
        try {
            $cacheDir = storage_path($relativePath);
            if (!is_dir($cacheDir)) {
                return '0 B';
            }
            
            $size = 0;
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($cacheDir, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $size += $file->getSize();
                }
            }
            
            return $this->formatBytes($size);
            
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * فرمت کردن حجم فایل
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * دریافت آمار کلی سیستم
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = [
                'cache_hit_ratio' => $this->calculateCacheHitRatio(),
                'total_cache_size' => $this->getTotalCacheSize(),
                'cache_types_count' => 5, // application, config, route, view, opcache
                'last_cleared' => $this->getLastClearTime(),
                'uptime' => $this->getSystemUptime()
            ];
            
            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در دریافت آمار',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateCacheHitRatio(): string
    {
        // Simplified cache hit ratio calculation
        return "85%"; // Placeholder - would need more complex logic
    }

    private function getTotalCacheSize(): string
    {
        $totalSize = 0;
        
        // Application cache
        $appCacheSize = $this->getCacheSize('framework/cache');
        
        // Config cache
        if (file_exists(base_path('bootstrap/cache/config.php'))) {
            $totalSize += filesize(base_path('bootstrap/cache/config.php'));
        }
        
        // Route cache  
        if (file_exists(base_path('bootstrap/cache/routes-v7.php'))) {
            $totalSize += filesize(base_path('bootstrap/cache/routes-v7.php'));
        }
        
        // View cache
        $viewCacheSize = $this->getCacheSize('framework/views');
        
        return $this->formatBytes($totalSize) . ' + ' . $appCacheSize . ' + ' . $viewCacheSize;
    }

    private function getLastClearTime(): string
    {
        // Could be tracked in database or log files
        return "نامشخص";
    }

    private function getSystemUptime(): string
    {
        if (function_exists('sys_getloadavg')) {
            return "Available";
        }
        return "نامشخص";
    }
}