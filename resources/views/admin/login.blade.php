<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>{{ trans('auth.login') }}</title>
    <link rel="stylesheet" href="{{ asset(config('cms.admin_theme') . '/css/bootstrap.min.css') }}">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(145deg, #1e3a8a, #3b82f6);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            overflow: hidden;
        }
        .login-container {
            max-width: 460px;
            width: 100%;
            margin: 20px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            animation: zoomIn 0.5s ease-in-out;
            direction: {{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }};
        }
        @keyframes zoomIn {
            from { transform: scale(0.8); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .login-header {
            background: #ffffff;
            padding: 25px;
            text-align: center;
            border-bottom: 1px solid #e9ecef;
        }
        .login-header h3 {
            margin: 0;
            font-size: 1.9rem;
            font-weight: 700;
            color: #1e3a8a;
        }
        .login-header .logo {
            max-width: 120px;
            margin-bottom: 15px;
        }
        .login-body {
            padding: 35px;
        }
        .form-control {
            border-radius: 8px;
            padding: 12px 16px;
            border: 1px solid #d1d5db;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: #f8fafc;
        }
        .form-control:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 12px rgba(59, 130, 246, 0.3);
            background: #ffffff;
        }
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }
        .btn-primary {
            background: #3b82f6;
            border: none;
            border-radius: 8px;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background: #1e3a8a;
            transform: translateY(-2px);
        }
        .form-check {
            margin-bottom: 20px;
        }
        .form-check-input {
            border-radius: 4px;
            cursor: pointer;
        }
        .form-check-label {
            color: #4b5563;
            font-size: 0.95rem;
            cursor: pointer;
        }
        .alert-danger {
            background: #fef2f2;
            border: 1px solid #f87171;
            border-radius: 8px;
            color: #b91c1c;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .error-message {
            color: #b91c1c;
            font-size: 0.85rem;
            margin-top: 6px;
            display: block;
        }
        @media (max-width: 576px) {
            .login-container {
                margin: 15px;
                padding: 15px;
            }
            .login-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="login-container">
    <div class="login-header">
        <img src="{{ asset(config('cms.admin_theme') . '/images/logo.png') }}" alt="Logo" class="logo">
        <h3>{{ trans('auth.login') }}</h3>
    </div>
    <div class="login-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ trans('auth.login_failed') }}
            </div>
        @endif
        <form method="POST" action="{{ route('admin.login') }}">
            @csrf
            <div class="mb-3">
                <label for="login_field" class="form-label">{{ trans('auth.' . config('cms.admin_login_field')) }}</label>
                <input type="{{ config('cms.admin_login_field') === 'email' ? 'email' : 'text' }}"
                       name="{{ config('cms.admin_login_field') }}"
                       class="form-control"
                       id="login_field"
                       value="{{ old(config('cms.admin_login_field')) }}"
                       required>
                @error(config('cms.admin_login_field'))
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">{{ trans('auth.password') }}</label>
                <input type="password" name="password" class="form-control" id="password" required>
                @error('password')
                <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input">
                <label for="remember" class="form-check-label">{{ trans('auth.remember_me') }}</label>
            </div>
            <button type="submit" class="btn btn-primary">{{ trans('auth.login') }}</button>
        </form>
    </div>
</div>
<script src="{{ asset(config('cms.admin_theme') . '/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
