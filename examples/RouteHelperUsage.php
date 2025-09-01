<?php

/**
 * مثال‌هایی از نحوه استفاده از RouteHelper در فایل‌های route
 * 
 * این فایل فقط جهت آموزش است و باید در فایل‌های routes واقعی استفاده شود.
 */

use RMS\Core\Helpers\RouteHelper;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PostController;

// ============================================================================
// استفاده ساده از متدهای مختلف
// ============================================================================

// ثبت route export برای users
RouteHelper::export(UserController::class, 'admin.users');
// تولید می‌کند: GET admin/users/export -> admin.users.export

// ثبت route toggle active برای users  
RouteHelper::active(UserController::class, 'admin.users');
// تولید می‌کند: POST admin/users/active/{user} -> admin.users.toggle_active

// ثبت route sort برای users
RouteHelper::sort(UserController::class, 'admin.users');
// تولید می‌کند: GET admin/users/sort/{by}/{way} -> admin.users.sort

// ثبت batch action برای delete
RouteHelper::batchAction(UserController::class, 'admin.users', 'delete');
// تولید می‌کند: POST admin/users/batch/delete -> admin.users.batch.delete

// ============================================================================
// استفاده پیشرفته
// ============================================================================

// ثبت چندین batch action به صورت همزمان
RouteHelper::batchActions(UserController::class, 'admin.users', ['delete', 'activate', 'export']);

// استفاده از toggle field برای فیلدهای مختلف
RouteHelper::toggleField(PostController::class, 'admin.posts', 'published');
RouteHelper::toggleField(PostController::class, 'admin.posts', 'featured');
RouteHelper::toggleField(PostController::class, 'admin.posts', 'pinned');

// ============================================================================
// استفاده از adminResource - ثبت همه route های رایج
// ============================================================================

// ثبت تمام route های admin برای users
RouteHelper::adminResource(UserController::class, 'admin.users');
// تولید می‌کند:
// - GET admin/users/export -> admin.users.export
// - GET admin/users/sort/{by}/{way} -> admin.users.sort  
// - POST admin/users/active/{user} -> admin.users.toggle_active
// - POST admin/users/batch/delete -> admin.users.batch.delete
// - POST admin/users/batch/activate -> admin.users.batch.activate
// - POST admin/users/batch/deactivate -> admin.users.batch.deactivate

// ثبت با کانفیگ سفارشی
RouteHelper::adminResource(PostController::class, 'admin.posts', [
    'export' => true,
    'sort' => true,
    'toggle_active' => false, // غیرفعال کردن toggle active
    'batch_actions' => ['delete', 'publish', 'unpublish'] // batch actions سفارشی
]);

// ============================================================================
// استفاده از controller array format
// ============================================================================

// مشخص کردن دقیق method controller
RouteHelper::export([UserController::class, 'exportToExcel'], 'admin.users');
RouteHelper::sort([UserController::class, 'sortUsers'], 'admin.users');

// ============================================================================
// استفاده از HTTP methods مختلف
// ============================================================================

// استفاده از POST برای export (برای دیتای بزرگ)
RouteHelper::export(UserController::class, 'admin.users', 'post', 'exportLarge');

// استفاده از PUT برای sort
RouteHelper::sort(UserController::class, 'admin.users', 'put', 'updateSort');

// ============================================================================
// API Routes
// ============================================================================

// ثبت API routes برای JSON responses
RouteHelper::apiResource(UserController::class, 'users');
// تولید می‌کند:
// - GET api/users/export -> api.users.export (با method exportJson)
// - GET api/users/sort/{by}/{way} -> api.users.sort (با method sortJson)
// - POST api/users/batch/* -> api.users.batch.*

// ============================================================================
// مثال کامل در فایل routes/admin.php
// ============================================================================

/*
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {
    
    // Basic CRUD routes
    Route::resource('users', UserController::class);
    Route::resource('posts', PostController::class);
    Route::resource('categories', CategoryController::class);
    
    // Extended routes using RouteHelper
    RouteHelper::adminResource(UserController::class, 'admin.users');
    RouteHelper::adminResource(PostController::class, 'admin.posts', [
        'batch_actions' => ['delete', 'publish', 'unpublish', 'feature']
    ]);
    
    // Custom field toggles
    RouteHelper::toggleField(PostController::class, 'admin.posts', 'featured');
    RouteHelper::toggleField(PostController::class, 'admin.posts', 'pinned');
    RouteHelper::toggleField(UserController::class, 'admin.users', 'verified');
    
    // Special exports
    RouteHelper::export([UserController::class, 'exportDetailedReport'], 'admin.users.detailed');
    RouteHelper::export([PostController::class, 'exportWithComments'], 'admin.posts.with-comments');
});
*/

// ============================================================================
// نکات مهم در استفاده
// ============================================================================

/*
1. همیشه namespace کامل controller را استفاده کنید
2. نام route باید با الگوی 'prefix.resource' باشد
3. برای controller methods اطمینان حاصل کنید که exist کنند:
   - export, exportJson
   - sort, sortJson  
   - changeBoolField
   - batchDelete, batchActivate, etc.
4. Route parameters به صورت singular تولید می‌شوند
5. HTTP methods پشتیبانی شده: get, post, put, patch, delete
*/
