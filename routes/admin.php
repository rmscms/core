<?php

use Illuminate\Support\Facades\Route;
use RMS\Core\Controllers\Auth\AdminLoginController;

Route::middleware('web')->prefix(config('cms.admin_url'))->group(function () {
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login']);
    Route::get('/dashboard', function () {
        return view('cms::admin.dashboard');
    })->name('admin.dashboard');
});
