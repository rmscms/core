# 📋 Changelog

All notable changes to **RMS Core Package** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

---

## [1.3.19] - 2025-11-15

### 🐛 Fixed
- **Image Uploader AJAX Routes**: رفع مشکل تولید URL برای روت‌های چند سطحی
  - اصلاح `getControllerName()` برای پشتیبانی از مسیرهای چند سطحی (مثل `/admin/shop/products/1`)
  - افزودن fallback برای مسیرهای تک سطحی (مثل `/admin/users/1`)
  - رفع خطای `The route admin/shop/1/ajax-upload/gallery[] could not be found`
  - بهبود regex برای استخراج صحیح controller path از URL

---

## [1.3.18] - 2025-11-15

### 🐛 Fixed
- **Blade Syntax Error**: اصلاح escape دوباره در `@case` directive
  - تغییر `@case(\\RMS\\Core\\Data\\Field::FILE)` به `@case(\RMS\Core\Data\Field::FILE)`
  - رفع خطای syntax در view `admin/form/index.blade.php`

---

## [1.3.17] - 2025-11-15

### 🚀 Added
- **Custom Package Namespace Support**: قابلیت استفاده از namespace سفارشی برای view ها
  - متد `setPackageNamespace(?string $namespace)` برای تنظیم namespace سفارشی
  - متد `getPackageNamespace()` برای دریافت namespace فعلی
  - متد `usePackageNamespace(string $namespace)` به عنوان helper method
  - پشتیبانی از پکیج‌های شخص ثالث برای استفاده از view های خودشان
- **Install Command Enhancement**: بهبود دستور `rms:install`
  - اضافه شدن publish خودکار `config/plugins.php` در هنگام نصب
  - اطمینان از وجود فایل plugins برای پروژه‌های جدید

### 🎨 Enhanced
- **View Template Manager**: بهبود سیستم مدیریت template ها
  - `buildTemplatePath()` حالا از namespace سفارشی پشتیبانی می‌کند
  - در صورت عدم تنظیم namespace، به صورت پیش‌فرض از `cms` استفاده می‌شود
  - سازگاری کامل با پروژه‌های موجود (backward compatible)

### 🏗️ Technical
- افزودن property `$packageNamespace` به trait `ViewTemplateManager`
- اپدیت متد `buildTemplatePath()` برای پشتیبانی از namespace دینامیک
- اضافه شدن متد `publishPluginsConfig()` به `InstallCommand`
- تست‌های کامل برای تضمین عملکرد صحیح با namespace های مختلف

### 📝 Example Usage
```php
// استفاده از namespace پیش‌فرض (cms)
$this->view->setTpl('admin.dashboard');
return $this->view(); // cms::admin.dashboard

// استفاده از namespace سفارشی
$this->view->usePackageNamespace('shop')
    ->setTpl('admin.dashboard');
return $this->view(); // shop::admin.dashboard

// نصب RMS Core با plugins config
php artisan rms:install
// حالا config/plugins.php خودکار publish میشه
```

---

## [1.3.16] - 2025-11-14

### 🔧 Changed
- مایگریشن `admins` در صورت وجود جدول فقط ستون `deleted_at` را اضافه می‌کند تا پروژه‌های قدیمی بدون از دست دادن داده‌ها ارتقا یابند.

---

## [1.3.15] - 2025-11-14

### ✅ Changed
- تمام مایگریشن‌های پیش‌فرض قبل از ساخت جدول بررسی می‌کنند و در صورت وجود جدول از ساخت دوباره صرف‌نظر می‌شود (جلوگیری از حذف ناخواسته داده‌های پروژه).

---

## [1.3.14] - 2025-11-12

### 🚀 Added
- `rms:publish-admin-controller` artisan command publishes the project AdminController stub on demand (`--force` supported) and is registered automatically via the core service provider.

### 🛠️ Fixed
- `rms:install` now delegates stub generation to the dedicated publish command, ensuring consistent behavior between automated install and manual execution.
- AdminController stub loads dashboard search assets unconditionally while keeping JS variable injection behind the feature flag, matching project expectations.

---

## [1.3.13] - 2025-11-12

### 🛠️ Fixed
- Panel navigation now resolves dashboard URLs via `route('admin.dashboard')` across sidebar/menu components, preventing fallbacks to `/admin` on projects with custom admin prefixes.
- Core admin route group redirects `/admin` to the named dashboard route while keeping `/admin/dashboard` canonical and named.

### 🏗️ Technical
- `DashboardController` now extends `ProjectAdminController`, ensuring installer safety before the published stub exists.
- Restored conditional registration of dashboard search assets in the published `AdminController` stub so projects opt-in through config.

---

## [1.3.12] - 2025-11-12

### 🛠️ Fixed
- Aligned `DashboardController` rendering flow with project namespaces; legacy `useUserTemplates()` call is disabled and the controller now forces the minimal `dashboard` template while still resolving assets through the project view layer.

---

## [1.3.11] - 2025-10-21

### 🚀 Added
- **Install Command Locale Setup**
  - New `configureAppLocale` step in `rms:install`
  - Automatically sets `APP_LOCALE=fa` and `APP_FALLBACK_LOCALE=fa` in `.env`
  - Skips gracefully if `.env` missing; logs status in installer summary
- **Project AdminController Stub Publishing**
  - Installer now publishes `App\Http\Controllers\Admin\AdminController` stub
  - Stub registers shared view variables, optional dashboard search assets, and global hooks based on config flag

### 🎨 Enhanced
- **Navbar Layout Parity with Project**
  - Core navbar now matches project customization (dynamic avatar, conditional profile links, logout form)
  - Theme dropdown includes font selector (`#navbarFontSelect`) synced with global font switcher
  - Placeholder search results removed; dropdown ready for dynamic JS injection
- **Controller Hierarchy Alignment**
  - Core admin controllers (`AdminsController`, `UsersController`, `SettingsController`, `NotificationsController`) now extend published project AdminController for shared behaviors

### 🏗️ Technical
- Added `config('cms.admin_controller.enable_dashboard_search_assets')` toggle for optional asset preload
- Synced `assets/css/theme-font.css` and `assets/js/fonts.js` with project implementations (localStorage font persistence, multi-select support)
- Updated installer progress list and messages to reflect new steps
- Introduced `ProjectAdminController` bridge class to gracefully fall back to core base when project stub is absent, preventing installer bootstrap failures

---

## [1.3.7] - 2025-10-20

### 🔧 Fixed
- **Per-Page Pagination**: Fixed per_page selector not working from URL query string
  - `getPerPage()` now checks request parameter first before reading from cache
  - Automatically caches new per_page value when changed via dropdown
  - Fixes issue where changing "نمایش" dropdown had no effect on list items count
  - Query flow: URL `?per_page=50` → Cache → Display
  - **Affected File**: `src/Traits/List/PerPageList.php` (lines 71-92)
  - **Issue**: Dropdown value was sent in URL but never read, causing pagination to ignore user selection
  - **Solution**: Added request parameter check with validation before cache lookup

### 🏗️ Technical
- Enhanced `PerPageList::getPerPage()` with request parameter detection
- Added range validation (1-100) for per_page values from request
- Maintains backward compatibility with existing cache-only behavior

---

## [1.3.5] - 2025-10-20

### 🚀 Added
- **Confirmation Modal**: New confirm-modal plugin for safe deletion operations
  - Enhanced list UI with modal-based confirmation dialogs
  - Prevents accidental bulk deletions with clear confirmation flow
  - Added modal plugin assets under `assets/plugins/confirm-modal/`
- **DbDiff Command**: New database schema comparison command
  - Compare schema between local and remote databases
  - Generate migration files based on differences
  - Helpful for development and debugging

### 🎨 Enhanced
- **List UI**: Improved confirmation workflow for delete operations
  - Better visual feedback with modal dialogs
  - Enhanced list.js with confirmation handling
  - Consistent delete action flows across all controllers
- **Plugin System**: Added confirm-modal to core plugins
  - Auto-registered plugin system for modals
  - Enhanced config/plugins.php with new plugin configuration
- **Notifications**: Improved notification display and handling
  - Enhanced notification.js with better state management
  - Support for HTML-formatted notification content
  - Better error handling and fallbacks

### 🔧 Fixed
- **BoolAction**: Further improvements to route parameter detection
  - Enhanced compatibility with various controller types
  - Better fallback mechanisms for route generation
  - Fixed edge cases in parameter resolution

### 📚 Documentation
- Added DB_SHIFT_COMMAND.md comprehensive documentation
- Added README_DB_SHIFT.md migration guide
- Added comprehensive docs/refs/ reference documentation
- Database schema shift command documentation

### 🏗️ Technical
- Database comparison tools (DbDiffCommand)
- Enhanced Console Commands namespace
- Improved plugin registration system
- Better event handling for UI confirmations

---

## [1.3.4] - 2025-10-19

### 🐛 Fixed
- **BoolAction**: Fixed `boolFieldUrl()` method not recognizing `routeParameter()` method in controllers
  - Method now checks for `routeParameter()` method first before falling back to `route_parameter` property
  - Added auto-detection of route prefix (e.g., `admin.`) from current route name
  - Fixed "Missing required parameter" error for toggle routes in controllers with custom route parameters
  - **Issue**: Controllers like `WirguardController` and `WireZeroController` that implement `routeParameter()` method were causing URL generation failures when toggling boolean fields
  - **Solution**: Modified parameter detection logic to prioritize method calls over property access
  - **Affected Code**: `src/Traits/Actions/BoolAction.php` (lines 207-254)

---

## [1.3.3] - 2025-10-17

### 🎨 Enhanced
- **Notifications System**: HTML formatting support for rich notification messages
  - NotificationsController now returns both HTML and plain text versions of messages
  - Added `message_plain` field with `strip_tags()` for plain text compatibility
  - UI now renders HTML content directly for better formatting (bold, emojis, line breaks)
  - Removed `escapeHtml()` from message rendering in notification.js
  - Added `.notification-content` CSS class for styling notification messages
  - Supports rich formatting in ticket notifications and other notification types

### 🏗️ Technical
- Updated `notification.js` to render HTML messages without escaping
- Backend provides both `message` (HTML) and `message_plain` (text) for flexibility
- Backward compatible - existing plain text notifications still work
- Persian date formatting preserved in `created_at_persian` field

---

## [1.3.1] - 2025-10-07

### 🔧 Fixed
- Respect `skipDatabase()` for list columns in View Helper List Generator
  - Prevents virtual/display-only fields (e.g., custom actions column) from being selected in SQL
  - Fixes SQL errors like `Unknown column 'a.actions' in 'field list'`
  - Aligns behavior with Data List Generator which already filtered skipDatabase fields

### 🏗️ Technical
- Filter `skip_database` fields in `RMS\\Core\\View\\HelperList\\Generator::builder()` before creating `Database`
- Backward compatible; no API changes

---

## [1.3.0] - 2025-10-04

### 🚀 Added
- db:shift Artisan command for safe schema synchronization between databases (A ➜ B)
  - Smart-skip for Schema::create when table already exists on B (marks migration as Ran)
  - Smart-skip for add-column-only migrations when columns already exist on B (marks as Ran)
  - Vendor-aware scanning via Laravel Migrator (supports loadMigrationsFrom paths)
  - Fixed protected list for users/settings migrations (always Ran, never executed)
  - --dry-run support using --pretend (simulate without changes)
  - Final report summary table (what was marked Ran vs executed/planned)

### 🎨 Enhanced
- Pretty plan tables with icons and protected notes for clarity

### 🏗️ Technical
- Command registered in CoreServiceProvider
- PSR-4 autoload ready for Console\Commands namespace

---

## [1.2.0] - 2025-09-30

### 🚀 Added
- Image Uploader plugin now supports dynamic admin prefix and custom base URLs
  - New data-upload-url-base and data-controller attributes in Blade form for FILE fields
  - JS resolves base via data-upload-url-base -> window.cmsAdminPrefix -> fallback '/admin'
- Admin private file serve route and controller for viewing private attachments
  - Route: GET admin/files/{id} -> admin.files.show
  - Controller: RMS\\Core\\Http\\Controllers\\Admin\\FileServeController
- Existing preview support for multiple files via data-existing-files in form

### 🎨 Enhanced
- Blade form template: wraps FILE inputs with image-uploader wrapper when requested via data-uploader
- Proper propagation of data-* and accept/multiple attributes to underlying input

### 🏗️ Technical
- Assets moved/enhanced under core/assets/plugins/image-uploader/image-uploader.js with dynamic base helpers
- Backward-compatible defaults preserved

---

## [1.1.0] - 2025-09-26

### 🚀 Added
- Unified Notifications System in Core (rms_notifications, deliveries, schedules)
  - Migrations: rms_notifications, rms_notification_deliveries, rms_notification_schedules
  - Models: Notification, NotificationDelivery, NotificationSchedule
  - Service: NotificationsService (sendNow, schedule, cancel, recurrence calculation)
  - Jobs/Commands: SendNotificationJob, rms:notifications:process-due, rms:notifications:test-seed
  - Config: config/rms/notifications.php (push off by default, channels placeholders)
  - Controller/Routes: Admin NotificationsController + admin routes (unread, mark read/all)
  - UI: Navbar bell badge + Offcanvas notifications, dynamic via AJAX
  - Assets: notification.js with polling, mark-as-read handling, improved dark styling

### 🎨 Enhanced
- Dark-mode friendly offcanvas header and actions (info button, border accents)
- Persian date output in unread JSON (created_at_persian) and UI alignment tweaks

### 🏗️ Technical
- Service provider imports cleanup and class registration via use statements
- Route imports cleanup with proper controller imports

---

## [1.0.6] - 2025-01-22

### 🚀 Added
- **Template Namespace Management System**: Advanced template rendering control
  - Added `use_package_namespace` property to GenerateForm trait for template system control
  - New `useCoreTemplates()` method - enables core template usage with package namespace
  - New `useUserTemplates()` method - enables user custom template usage without namespace
  - Enhanced AdminController to use core templates by default with proper namespace resolution
  - Controllers can now easily switch between core and custom templates

### 🎨 Enhanced
- **Template Resolution System**: Flexible template path management
  - Core templates: `cms::admin.form.index`