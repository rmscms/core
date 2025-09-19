# Changelog

All notable changes to RMS Core will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.0] - 2025-01-19

### Added
- **Statistical System v2.0** - Complete stats system for lists and forms
- **HasStats Interface** - Statistics for list views with filter integration
- **HasFormStats Interface** - Statistics for edit forms with model data
- **Statistical Card Component** - Reusable card component for stats display
- **UploadConfig Object** - Fluent configuration for file uploads
- **Enhanced Debug System v2.0** - Complete debugging with real query values
- **Virtual Fields Support** - skipDatabase() for non-database fields
- **StatsCardControl Trait** - Control stats card expand/collapse state
- **Menu Components System** - Reusable menu components (menu-item, submenu-item, menu-header)
- **Mobile Footer Navigation** - Optimized mobile navigation
- **Limitless Collapse System** - Proper collapse integration with Limitless theme

### Enhanced
- **Debug Panel UI v2.0** - Complete redesign with Limitless template integration
- **Database Analysis** - Show all queries with real binding values
- **Field Analysis** - Display current field values after template processing
- **Plugin System** - Improved plugin architecture with CustomPluginLoader
- **Image Uploader Plugin** - Enhanced with AJAX upload and SweetAlert2 integration
- **Persian DatePicker** - Fixed leap year 1403 issues
- **Enhanced Select (Choices.js)** - Complete dark theme support
- **Sidebar Mobile** - Fixed mobile sidebar issues with proper backdrop
- **SweetAlert2** - Updated with proper Bootstrap 5 styling and dark theme

### Changed
- **AdminController Architecture** - Improved FormAndList trait integration
- **Field System** - Enhanced field generation with better validation
- **Template System** - Better Blade component integration
- **Dark Theme Support** - Complete dark theme compatibility across all components
- **RTL Support** - Improved Persian/Farsi language support

### Fixed
- **GenerateForm Fix** - Resolved model() method issues in form generation
- **Sidebar Structure** - Fixed positioning and navbar-brand issues
- **Theme Switching** - Proper theme persistence and switching
- **Memory Management** - Improved memory usage in file operations
- **Plugin Loading** - Fixed plugin dependencies and load order

## [1.0.0] - 2024-08-22

### Added
- **Core Admin Panel** - Complete admin panel foundation
- **CRUD System** - Full Create, Read, Update, Delete operations
- **Form Generation** - Dynamic form generation with validation
- **List Management** - Advanced listing with pagination and sorting
- **AdminController Base** - Abstract base controller for admin operations
- **FormAndList Trait** - Main trait combining all admin functionalities
- **Field System** - Dynamic field generation and management
- **Authentication System** - Admin login and session management
- **Database Integration** - Complete database operations with query builder
- **Template System** - Blade template integration with Limitless theme
- **Asset Management** - CSS/JS asset loading and management
- **Route Helpers** - Simplified route registration for admin resources

### Core Features
- **Interface System** - Contract-based development with multiple interfaces
- **Trait System** - Modular functionality through traits
- **Service Provider** - Laravel service provider integration
- **Configuration** - Flexible configuration system
- **Localization** - Multi-language support foundation
- **Error Handling** - Comprehensive error handling and logging
- **Validation System** - Advanced form validation with Laravel rules
- **Middleware** - Admin authentication and authorization middleware

### UI Components
- **Limitless Theme Integration** - Complete Bootstrap 5 Limitless theme
- **Responsive Design** - Mobile-first responsive admin interface
- **Form Components** - Rich form inputs and validation display
- **List Components** - Advanced data tables with sorting and filtering
- **Navigation System** - Sidebar and top navigation
- **Dashboard Layout** - Admin dashboard with widgets and stats

[Unreleased]: https://github.com/rmscms/core/compare/v2.0.0...HEAD
[2.0.0]: https://github.com/rmscms/core/compare/v1.0.0...v2.0.0
[1.0.0]: https://github.com/rmscms/core/releases/tag/v1.0.0