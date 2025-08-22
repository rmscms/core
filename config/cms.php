<?php
return [
    'admin_url' => env('ADMIN_URL', 'admin'),
    'admin_theme' => env('ADMIN_THEME', 'admin'),
    'front_theme' => env('FRONT_THEME', 'panel'),
    'admin_redirect_after_login' => env('ADMIN_REDIRECT_AFTER_LOGIN', 'admin.dashboard'),
    'admin_login_field' => env('ADMIN_LOGIN_FIELD', 'email'),
    'admin_login_attempts' => env('ADMIN_LOGIN_ATTEMPTS', 5),
    'admin_login_lockout_time' => env('ADMIN_LOGIN_LOCKOUT_TIME', 60),
    'auth' => [
        'guards' => [
            'admin' => [
                'driver' => 'session',
                'provider' => 'admins',
            ],
        ],
        'providers' => [
            'admins' => [
                'driver' => 'eloquent',
                'model' => \RMS\Core\Models\Admin::class,
            ],
        ],
    ],
];
