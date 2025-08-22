<?php

namespace RMS\Core\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AdminLoginController
{
    public function showLoginForm()
    {
        return view('cms::admin.login');
    }

    public function login(Request $request)
    {
        $loginField = config('cms.admin_login_field', 'email');
        $loginValue = $request->input($loginField);
        $key = 'login:' . $request->ip(); // فقط IP کاربر
        $maxAttempts = config('cms.admin_login_attempts', 5);
        $lockoutTime = config('cms.admin_login_lockout_time', 60);

        if (RateLimiter::tooManyAttempts($key, $maxAttempts, null, 'file')) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                $loginField => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        $credentials = [
            $loginField => $loginValue,
            'password' => $request->input('password'),
        ];
        $remember = $request->has('remember');

        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            RateLimiter::clear($key);
            $admin = Auth::guard('admin')->user();
            $admin->update([
                'last_login_ip' => $request->ip(),
                'last_login_at' => now(),
            ]);
            return redirect()->route(config('cms.admin_redirect_after_login'));
        }

        RateLimiter::hit($key, $lockoutTime, 'file');
        return back()->withErrors([
            $loginField => trans('auth.failed'),
        ]);
    }
}
