<?php

namespace RMS\Core\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use RMS\Core\Http\Requests\AdminLoginRequest;
use RMS\Core\Services\AdminAuthService;
use RMS\Core\Services\AdminRateLimiter;

class AdminLoginController
{
    public function __construct(
        protected AdminAuthService $authService,
        protected AdminRateLimiter $rateLimiter
    ) {}

    /**
     * Show the admin login form.
     *
     * @return View|RedirectResponse
     */
    public function showLoginForm()
    {
        // If already authenticated, redirect to dashboard
        if (Auth::guard('admin')->check()) {
            $redirectRoute = config('cms.admin_redirect_after_login', 'admin.dashboard');
            return redirect()->route($redirectRoute);
        }

        return view('cms::admin.login', [
            'loginField' => config('cms.admin_login_field', 'email'),
            'title' => trans('auth.admin_login_title', [], null, 'Login to Admin Panel')
        ]);
    }

    /**
     * Handle admin login request.
     *
     * @param AdminLoginRequest $request
     * @return RedirectResponse|JsonResponse
     */
    public function login(AdminLoginRequest $request)
    {
        $this->rateLimiter->checkRateLimit($request);

        $credentials = $request->getCredentials();
        $remember = $request->shouldRemember();
        $redirectUrl = $request->getRedirectUrl();
        $loginField = $request->getLoginField();

        if ($this->authService->attemptLogin($credentials, $remember, $request)) {
            return $this->authService->handleSuccessfulLogin($request, $redirectUrl);
        }

        return $this->authService->handleFailedLogin($request, $loginField);
    }

    /**
     * Logout admin user.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function logout(Request $request): RedirectResponse
    {
        return $this->authService->logout($request);
    }
}
