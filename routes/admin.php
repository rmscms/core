<?php

use Illuminate\Support\Facades\Route;
use RMS\Core\Controllers\Admin\SettingsController;
use RMS\Core\Controllers\Auth\AdminLoginController;
use RMS\Core\Http\Controllers\Admin\BugLogController;
use RMS\Core\Helpers\RouteHelper;
use RMS\Core\Controllers\Admin\NotificationsController;

// Admin routes with security middleware
Route::middleware(['web'])
    ->prefix(config('cms.admin_url', 'admin'))
    ->name('admin.')
    ->group(function () {

        // Guest-only routes (for non-authenticated admins)
        Route::middleware('guest:admin')->group(function () {
            Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
            Route::post('/login', [AdminLoginController::class, 'login'])
                ->middleware('throttle:5,1') // More restrictive for login attempts
                ->name('login.submit');
    // Private file serve route (admin-only)
    Route::get('files/{id}', [\RMS\Core\Http\Controllers\Admin\FileServeController::class, 'show'])
        ->name('files.show');
});

        // Authenticated admin routes
        Route::middleware([\RMS\Core\Middleware\AdminAuthenticate::class, 'verified:admin'])->group(function () {
            // Logout route
            Route::post('/logout', [AdminLoginController::class, 'logout'])->name('logout');

            // Dashboard
            

            // Profile route - redirects to edit current admin
            Route::get('/profile', function () {
                $adminId = auth('admin')->id();
                return redirect()->route('admin.admins.edit', $adminId);
            })->name('profile');

            // Bug Log Management Routes - همه با RouteHelper::adminResource
            Route::resource('bug-logs', BugLogController::class, [
                'except' => ['create', 'store', 'edit', 'update']  // فقط index, show, destroy
            ]);

            // Custom routes for bug-specific actions
            Route::post('bug-logs/{id}/confirm-fix', [BugLogController::class, 'confirmFix'])->name('bug-logs.confirm-fix');
            Route::post('bug-logs/{id}/reject-fix', [BugLogController::class, 'rejectFix'])->name('bug-logs.reject-fix');

            // All common admin routes using RouteHelper
            RouteHelper::adminResource(BugLogController::class, 'bug-logs', [
                'toggle_active' => false,  // Bug logs don't have active field
                'batch_actions' => ['delete']  // Only delete batch action
            ]);

            // Cache Management Routes - Professional Cache System 🧹
            RouteHelper::adminCacheRoutes(
                \RMS\Core\Http\Controllers\Admin\CacheManagerController::class,
                'admin.cache'
            );

            // Admins Management Routes - Core Admin Management System 👨‍💻
            RouteHelper::adminResource(
                \RMS\Core\Controllers\Admin\AdminsController::class,
                'admins',
                [
                    'export' => true,
                    'sort' => true,
                    'filter' => true,
                    'toggle_active' => true,
                    'batch_actions' => ['delete', 'activate', 'deactivate'],
                    'ajax_files' => ['avatar'],
                    'image_viewer' => true,
                ]
            );

            Route::resource('admins', \RMS\Core\Controllers\Admin\AdminsController::class);

            // Users Management Routes - User Management System 👥
            // Settings Management Routes - Simple Key-Value Settings ⚙️
            Route::resource('settings', SettingsController::class);

            // Notifications Routes
            Route::prefix('notifications')->name('notifications.')->group(function () {
                Route::get('/', [NotificationsController::class, 'index'])->name('index');
                Route::get('/unread', [NotificationsController::class, 'unreadJson'])->name('unread');
                Route::post('/{id}/read', [NotificationsController::class, 'markRead'])->name('markRead');
                Route::post('/read-all', [NotificationsController::class, 'markAllRead'])->name('markAllRead');
            });

            // Debug System Routes - Professional Debug System 🔧
            Route::prefix('debug')->name('debug.')->group(function () {
                Route::get('/panel', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'showDebugPanel'])->name('panel');
                Route::post('/toggle', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'toggleDebugMode'])->name('toggle');
                Route::post('/clear', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'clearDebugData'])->name('clear');
                Route::get('/export', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'exportDebugData'])->name('export');
                Route::get('/logs', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'viewLogs'])->name('logs');
                Route::get('/stats', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'getDebugStats'])->name('stats');
                Route::get('/health', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'healthCheck'])->name('health');
                Route::get('/realtime', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'getRealTimeInfo'])->name('realtime');
                Route::post('/analyze-form', [\RMS\Core\Http\Controllers\Admin\DebugController::class, 'analyzeForm'])->name('analyze-form');
            });
        });

        // Password reset routes (if needed in future)
        Route::middleware('guest:admin')->group(function () {
            // Route::get('/password/reset', [AdminPasswordResetController::class, 'showForm'])->name('password.request');
            // Route::post('/password/email', [AdminPasswordResetController::class, 'sendResetLink'])->name('password.email');
        });
    });
