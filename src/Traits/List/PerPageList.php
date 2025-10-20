<?php

declare(strict_types=1);

namespace RMS\Core\Traits\List;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use RMS\Core\Controllers\Admin\AdminController;

/**
 * Trait for handling per-page pagination settings.
 *
 * @package RMS\Core\Traits\List
 */
trait PerPageList
{
    /**
     * Default per-page value.
     */
    protected int $defaultPerPage = 15;

    /**
     * Maximum allowed per-page value.
     */
    protected int $maxPerPage = 100;

    /**
     * Handle per-page setting request.
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function perPage(Request $request): RedirectResponse
    {
        $request->validate([
            'key' => 'required|string',
            'perPage' => 'required|integer|min:1|max:' . $this->maxPerPage,
        ]);

        try {
            $controller = $this->controller($request);
            $perPage = (int) $request->input('perPage');

            $this->cachePerPageValue($controller, $perPage);

            return back()->with('success', trans('admin.per_page_updated'));
        } catch (\Exception $exception) {
            abort(403, 'Invalid controller or unauthorized access');
        }
    }

    /**
     * Determine if per-page should be cached.
     *
     * @return bool
     */
    public function cachePerPage(): bool
    {
        return true;
    }

    /**
     * Get the current per-page setting.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        // اول بررسی کنیم آیا توی request هست per_page
        $requestPerPage = request('per_page');
        
        if ($requestPerPage && is_numeric($requestPerPage)) {
            $perPage = (int) $requestPerPage;
            
            // بررسی محدوده مجاز
            if ($perPage >= 1 && $perPage <= $this->maxPerPage) {
                // کش کردن مقدار جدید
                $this->cachePerPageValue($this, $perPage);
                return $perPage;
            }
        }
        
        // اگر توی request نبود، از کش بخون
        $cached = $this->getCachedPerPage($this, !$this->cachePerPage());

        return $cached ?: $this->defaultPerPage;
    }

    /**
     * Set the default per-page value.
     *
     * @param int $perPage
     * @return $this
     */
    public function setDefaultPerPage(int $perPage): self
    {
        $this->defaultPerPage = max(1, min($perPage, $this->maxPerPage));
        return $this;
    }

    /**
     * Set the maximum per-page value.
     *
     * @param int $maxPerPage
     * @return $this
     */
    public function setMaxPerPage(int $maxPerPage): self
    {
        $this->maxPerPage = max(1, $maxPerPage);
        return $this;
    }

    /**
     * Cache per-page value for a specific controller.
     *
     * @param object $controller
     * @param int $perPage
     * @return void
     */
    protected function cachePerPageValue(object $controller, int $perPage): void
    {
        $key = $this->getPerPageCacheKey($controller);
        Cache::forever($key, $perPage);
    }

    /**
     * Get cached per-page value for a specific controller.
     *
     * @param object $controller
     * @param bool $oneTime
     * @return int|null
     */
    protected function getCachedPerPage(object $controller, bool $oneTime = false): ?int
    {
        $key = $this->getPerPageCacheKey($controller);

        if ($oneTime) {
            $perPage = Cache::get($key);
            Cache::forget($key);
            return $perPage;
        }

        return Cache::get($key);
    }

    /**
     * Get cache key for per-page setting.
     *
     * @param object $controller
     * @return string
     */
    protected function getPerPageCacheKey(object $controller): string
    {
        $baseKey = 'per_page' . get_class($controller);

        if ($controller instanceof AdminController) {
            // Handle console environment where admin might be null
            if (property_exists($this, 'admin') && $this->admin && isset($this->admin->id)) {
                return $baseKey . $this->admin->id;
            }
            // Fallback for console/testing environment
            return $baseKey . '_console_test';
        }

        // Handle regular auth guard
        if (auth()->guard()->check()) {
            return $baseKey . auth()->guard()->user()->id;
        }

        // Fallback for testing/console environment
        return $baseKey . '_guest';
    }
}
