# 📋 Changelog

All notable changes to **RMS Core Package** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
  - Core templates: `cms::admin.form.index` (with package namespace)
  - User templates: `admin.form.index` (without package namespace)
  - Automatic template path resolution based on namespace setting
  - Enhanced ViewTemplateManager with comprehensive path building logic

### 🔧 Fixed
- **Form Generation Pipeline**: Proper template setup in CRUD operations
  - Fixed create/edit methods to properly call setTplForm() for template setup
  - Resolved template resolution conflicts in form generation
  - Enhanced template path resolution with namespace support
  - Improved error handling for missing templates

### 🏗️ Technical Improvements
- Backward compatible implementation - no breaking changes
- Enhanced flexibility for template customization
- Better separation of core vs user template management
- Improved developer experience with clear template switching methods

---

## [1.0.5] - 2025-01-21

### 🔧 Fixed
- **Complete Authorization Fix**: Permanently resolved authorize() method collision
  - Renamed `RequestForm::authorize()` to `RequestForm::authorizeRequest()`
  - Eliminated all trait collision complications
  - Laravel's `authorize()` for policies now works without any conflicts
  - Clean, maintainable solution without trait aliases or workarounds

### 🏗️ Technical Improvements
- Removed complex trait collision handling
- Simplified AdminController architecture
- Better separation of concerns between policy and form authorization
- Enhanced IDE support with no more signature warnings

---

## [1.0.4] - 2025-01-21

### 🔧 Fixed
- **Authorization System Collision**: Resolved critical method signature conflict
  - Fixed `AuthorizesRequests::authorize()` vs `RequestForm::authorize()` collision
  - Eliminated PhpStorm IDE warnings about incompatible signatures
  - Both Laravel policy authorization and form validation now work seamlessly
  - Enhanced IDE code completion and error detection

### 🗑️ Removed
- **Unused HasStats Method**: Cleaned up interface complexity
  - Removed `getStatSummary()` method from `HasStats` interface
  - Simplified statistics to use only `getStats()` method
  - No breaking changes - method was never used in practice
  - Prevents future implementation confusion

### 🏗️ Technical Improvements
- Used trait aliasing pattern: `FormAndList::authorize as authorizeFormRequest`
- Maintained 100% backward compatibility for existing controllers
- Enhanced developer experience with better IDE support

---

## [1.0.3] - 2025-01-20

### ✨ Added
- **Select2 Plugin Integration**: Advanced select dropdowns with search
  - Complete Bootstrap 5 theme compatibility
  - RTL/Persian language support with proper spacing
  - Dark/Light theme automatic switching
  - Simple initialization matching Limitless patterns
  - Enhanced user experience for form selects

### 🎨 Enhanced
- Improved form field styling consistency
- Better dark theme color scheme alignment
- Enhanced mobile responsiveness for select inputs

---

## [1.0.2] - 2025-01-19

### 🐛 Fixed
- Form validation error display improvements
- Asset loading optimization for better performance
- Template rendering stability fixes
- Memory usage optimizations

---

## [1.0.1] - 2025-01-18

### 🚀 Added
- Enhanced debug system with query analysis
- Improved error handling and logging
- Better plugin architecture foundation

### 🔧 Fixed
- Initial stability improvements
- Asset path corrections
- Configuration loading fixes

---

## [1.0.0] - 2025-01-17

### 🎉 Initial Release

#### 🚀 Core Features
- **Complete CRUD System** with AdminController base class
- **Dynamic Form Generation** with comprehensive Field system
- **Advanced List Management** with filtering, sorting, and pagination
- **Statistical Cards & Analytics** for dashboard insights
- **Multi-language Support** (Persian/Farsi with RTL)
- **File Upload System** with image handling
- **Excel Export Functionality** for data management
- **Plugin Architecture** for extensibility

#### 🎨 UI Components
- **Bootstrap 5 + Limitless Theme** integration
- **Mobile-Responsive Design** with touch support
- **Dark/Light Theme System** with user preference
- **Rich Form Components** with validation display
- **Advanced Data Tables** with real-time search
- **Navigation System** (sidebar, mobile footer)
- **Dashboard Layout** with customizable widgets

#### ⚙️ Technical Foundation
- **Interface-Driven Development** with contracts
- **Trait-Based Architecture** for modularity
- **Laravel 11+ Integration** with service providers
- **Flexible Configuration** system
- **Comprehensive Validation** with Laravel rules
- **Authentication & Authorization** middleware
- **Error Handling & Logging** system

---

## 📝 Legend

- 🚀 **Added** - New features and functionality
- 🎨 **Enhanced** - Improvements to existing features  
- 🔧 **Fixed** - Bug fixes and corrections
- 🗑️ **Removed** - Removed or deprecated features
- 🏗️ **Technical** - Internal/architectural changes
- ⚠️ **Breaking** - Breaking changes (major versions)
- 🎉 **Release** - Major milestone releases

---

**Links:**
- [GitHub Repository](https://github.com/rmscms/core)
- [Issues & Bug Reports](https://github.com/rmscms/core/issues)
- [Documentation](README.md)
