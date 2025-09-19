<?php

namespace RMS\Core\Services;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use RMS\Core\Models\Admin;

class AdminAuthService
{
    public function __construct(
        protected AdminRateLimiter $rateLimiter
    ) {}

    /**
     * Attempt to authenticate admin with given credentials.
     *
     * @param array $credentials
     * @param bool $remember
     * @param Request $request
     * @return bool
     */
    public function attemptLogin(array $credentials, bool $remember, Request $request): bool
    {
        $attempt = Auth::guard('admin')->attempt($credentials, $remember);
        
        if ($attempt) {
            $request->session()->regenerate();
            Log::info('Admin logged in successfully', [
                'admin_id' => Auth::guard('admin')->id(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }
        
        return $attempt;
    }

    /**
     * Handle successful login.
     *
     * @param Request $request
     * @param string|null $redirectUrl
     * @return RedirectResponse|JsonResponse
     */
    public function handleSuccessfulLogin(Request $request, ?string $redirectUrl = null)
    {
        $this->rateLimiter->clearRateLimit($request);
        $this->updateLoginMetadata($request);
        
        $redirectUrl = $redirectUrl ?: route(config('cms.admin_redirect_after_login', 'admin.dashboard'));
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => trans('auth.login_successful'),
                'redirect' => $redirectUrl
            ]);
        }
        
        return redirect()->intended($redirectUrl)
            ->with('success', trans('auth.login_successful'));
    }

    /**
     * Handle failed login attempt.
     *
     * @param Request $request
     * @param string $loginField
     * @return RedirectResponse|JsonResponse
     * @throws ValidationException
     */
    public function handleFailedLogin(Request $request, string $loginField)
    {
        $this->rateLimiter->incrementRateLimit($request);
        
        Log::warning('Admin login failed', [
            'login_field' => $loginField,
            'login_value' => $request->input($loginField),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        $errorMessage = trans('auth.failed');
        
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'errors' => [$loginField => $errorMessage]
            ], 422);
        }
        
        throw ValidationException::withMessages([
            $loginField => $errorMessage
        ]);
    }

    /**
     * Logout admin user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('admin')->logout();
        
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info('Admin logged out', ['ip' => $request->ip()]);
        
        return redirect()->route('admin.login')
            ->with('message', trans('auth.logged_out_successfully'));
    }

    /**
     * Update admin login metadata.
     *
     * @param Request $request
     */
    protected function updateLoginMetadata(Request $request): void
    {
        /** @var Admin $admin */
        $admin = Auth::guard('admin')->user();
        
        if ($admin) {
            $admin->update([
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
            ]);
        }
    }
}
