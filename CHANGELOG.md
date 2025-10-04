# 📋 Changelog

All notable changes to **RMS Core Package** will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
