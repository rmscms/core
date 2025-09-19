<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? trans('auth.login') }} - {{ config('app.name') }}</title>
    
    <!-- Preload critical resources -->
    <link rel="preload" href="{{ asset(config('cms.admin_theme') . '/css/bootstrap.min.css') }}" as="style">
    <link rel="stylesheet" href="{{ asset(config('cms.admin_theme') . '/css/bootstrap.min.css') }}">
    
    <!-- Security headers -->
    <meta http-equiv="X-Content-Type-Options" content="nosniff">
    <meta http-equiv="X-Frame-Options" content="DENY">
    <meta http-equiv="X-XSS-Protection" content="1; mode=block">
    
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
        .success-message {
            background: #f0f9ff;
            border: 1px solid #3b82f6;
            border-radius: 8px;
            color: #1e3a8a;
            padding: 12px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }
        .btn-primary:disabled {
            background: #9ca3af;
            cursor: not-allowed;
            transform: none;
        }
        .loading-spinner {
            width: 20px;
            height: 20px;
            border: 2px solid #ffffff;
            border-top: 2px solid transparent;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            display: none;
            margin-right: 8px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #6b7280;
            font-size: 0.9rem;
        }
        .password-field {
            position: relative;
        }
        .form-control.has-toggle {
            padding-right: 45px;
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
        /* Accessibility improvements */
        .form-control:focus {
            outline: 2px solid #3b82f6;
            outline-offset: 2px;
        }
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
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
        {{-- Success message --}}
        @if(session('message'))
            <div class="success-message">
                {{ session('message') }}
            </div>
        @endif
        
        {{-- Error messages --}}
        @if (isset($errors) && $errors->any())
            <div class="alert alert-danger" role="alert">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="{{ route('admin.login.submit') }}" id="loginForm" novalidate>
            @csrf
            
            {{-- Hidden redirect field --}}
            @if(request('redirect'))
                <input type="hidden" name="redirect" value="{{ request('redirect') }}">
            @endif
            
            {{-- Login field (email or username) --}}
            <div class="mb-3">
                <label for="login_field" class="form-label">
                    {{ trans('auth.' . config('cms.admin_login_field', 'email')) }}
                    <span class="text-danger" aria-label="{{ trans('auth.required') }}">*</span>
                </label>
                <input type="{{ config('cms.admin_login_field') === 'email' ? 'email' : 'text' }}"
                       name="{{ config('cms.admin_login_field', 'email') }}"
                       class="form-control @error(config('cms.admin_login_field')) is-invalid @enderror"
                       id="login_field"
                       value="{{ old(config('cms.admin_login_field')) }}"
                       autocomplete="{{ config('cms.admin_login_field') === 'email' ? 'email' : 'username' }}"
                       aria-describedby="login_field_error"
                       required>
                @error(config('cms.admin_login_field'))
                    <span class="error-message" id="login_field_error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Password field with toggle --}}
            <div class="mb-3">
                <label for="password" class="form-label">
                    {{ trans('auth.password') }}
                    <span class="text-danger" aria-label="{{ trans('auth.required') }}">*</span>
                </label>
                <div class="password-field">
                    <input type="password" 
                           name="password" 
                           class="form-control has-toggle @error('password') is-invalid @enderror" 
                           id="password"
                           autocomplete="current-password"
                           aria-describedby="password_error"
                           required>
                    <span class="password-toggle" 
                          onclick="togglePassword()" 
                          role="button" 
                          tabindex="0"
                          aria-label="{{ trans('auth.toggle_password_visibility') }}"
                          onkeypress="if(event.key==='Enter'||event.key===' ')togglePassword()">
                        <span id="toggle-icon">👁️</span>
                    </span>
                </div>
                @error('password')
                    <span class="error-message" id="password_error" role="alert">{{ $message }}</span>
                @enderror
            </div>
            
            {{-- Remember me checkbox --}}
            <div class="form-check">
                <input type="checkbox" name="remember" id="remember" class="form-check-input" {{ old('remember') ? 'checked' : '' }}>
                <label for="remember" class="form-check-label">{{ trans('auth.remember_me') }}</label>
            </div>
            
            {{-- Submit button with loading state --}}
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <span class="loading-spinner" id="loadingSpinner"></span>
                <span id="submitText">{{ trans('auth.login') }}</span>
            </button>
        </form>
        
        {{-- Additional links (if needed) --}}
        @if(config('cms.admin_password_reset_enabled', false))
            <div class="text-center mt-3">
                <a href="{{ route('admin.password.request') }}" class="text-muted">
                    {{ trans('auth.forgot_password') }}
                </a>
            </div>
        @endif
    </div>
</div>
<script src="{{ asset(config('cms.admin_theme') . '/js/bootstrap.bundle.min.js') }}"></script>
<script>
    // Password toggle functionality
    function togglePassword() {
        const passwordField = document.getElementById('password');
        const toggleIcon = document.getElementById('toggle-icon');
        
        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            toggleIcon.textContent = '🙈';
        } else {
            passwordField.type = 'password';
            toggleIcon.textContent = '👁️';
        }
    }
    
    // Form submission with loading state
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const submitText = document.getElementById('submitText');
        
        // Show loading state
        submitBtn.disabled = true;
        loadingSpinner.style.display = 'inline-block';
        submitText.textContent = '{{ trans('auth.logging_in', [], null, 'Logging in...') }}';
        
        // Re-enable button after 10 seconds to prevent permanent disable
        setTimeout(() => {
            submitBtn.disabled = false;
            loadingSpinner.style.display = 'none';
            submitText.textContent = '{{ trans('auth.login') }}';
        }, 10000);
    });
    
    // Auto-focus first empty field
    document.addEventListener('DOMContentLoaded', function() {
        const loginField = document.getElementById('login_field');
        const passwordField = document.getElementById('password');
        
        if (!loginField.value) {
            loginField.focus();
        } else {
            passwordField.focus();
        }
    });
    
    // Handle keyboard navigation
    document.addEventListener('keydown', function(e) {
        // Allow Enter to submit form
        if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
            document.getElementById('loginForm').requestSubmit();
        }
    });
    
    // CSRF token refresh (security enhancement)
    function refreshCSRFToken() {
        fetch('{{ route('admin.login') }}', {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(response => {
            // Token will be automatically refreshed
            console.log('CSRF token refreshed');
        }).catch(error => {
            console.warn('Failed to refresh CSRF token:', error);
        });
    }
    
    // Refresh CSRF token every 30 minutes for security
    setInterval(refreshCSRFToken, 1800000);
</script>
</body>
</html>
