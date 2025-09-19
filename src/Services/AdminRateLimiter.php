<?php

namespace RMS\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AdminRateLimiter
{
    /**
     * Check rate limiting for login attempts.
     *
     * @param Request $request
     * @throws ValidationException
     */
    public function checkRateLimit(Request $request): void
    {
        $key = $this->getRateLimitKey($request);
        $maxAttempts = config('cms.admin_login_attempts', 5);
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $loginField = config('cms.admin_login_field', 'email');
            
            Log::warning('Admin login rate limit exceeded', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'available_in' => $seconds
            ]);
            
            throw ValidationException::withMessages([
                $loginField => trans('auth.throttle', ['seconds' => $seconds])
            ]);
        }
    }

    /**
     * Clear rate limit for successful login.
     *
     * @param Request $request
     */
    public function clearRateLimit(Request $request): void
    {
        RateLimiter::clear($this->getRateLimitKey($request));
    }

    /**
     * Increment rate limit for failed login.
     *
     * @param Request $request
     */
    public function incrementRateLimit(Request $request): void
    {
        $key = $this->getRateLimitKey($request);
        $lockoutTime = config('cms.admin_login_lockout_time', 60);
        
        RateLimiter::hit($key, $lockoutTime * 60); // Convert to seconds
    }

    /**
     * Get rate limit key for the request.
     *
     * @param Request $request
     * @return string
     */
    protected function getRateLimitKey(Request $request): string
    {
        $loginField = config('cms.admin_login_field', 'email');
        $loginValue = $request->input($loginField, '');
        
        // Combine IP and login field for better rate limiting
        return 'admin_login:' . $request->ip() . ':' . strtolower($loginValue);
    }
}
