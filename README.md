# RMS Core Package

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%5E8.1-blue.svg)](https://php.net/)
[![Laravel](https://img.shields.io/badge/laravel-%5E11.0-red.svg)](https://laravel.com/)

RMS Core is a powerful, modern admin panel package for Laravel applications. Built with Laravel 12 alignment and featuring a complete CRUD system with advanced form generation, list management, and administrative tools.

## ✨ Features

- **🎯 Advanced CRUD System** - Complete Create, Read, Update, Delete operations
- **📋 Dynamic Form Generation** - Automatic form generation with validation
- **📊 Smart List Management** - Advanced filtering, sorting, and pagination
- **🎨 Beautiful UI** - Built with Limitless Bootstrap 5 theme
- **🌙 Dark/Light Theme** - Complete theme switching support
- **📱 Mobile Responsive** - Fully responsive admin interface
- **🔐 Authentication System** - Built-in admin authentication
- **📈 Statistics & Analytics** - Real-time stats and reporting
- **🔍 Advanced Search & Filter** - Powerful search and filtering capabilities
- **📤 Excel Export** - Export data to Excel/CSV formats
- **🖼️ File Upload System** - Complete file and image upload management
- **🌐 Multi-language Support** - Persian/Farsi language support
- **⚡ Plugin System** - Extensible plugin architecture

## 🚀 Requirements

- PHP ^8.1
- Laravel ^11.0
- Composer
- Node.js & NPM (for assets)

## 📦 Installation

```bash
composer require rmscms/core
```

## 🎯 Quick Start

1. **Extend AdminController:**
```php
<?php

namespace App\Http\Controllers\Admin;

use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Form\HasForm;

class UsersController extends AdminController implements HasList, HasForm
{
    public function table(): string
    {
        return 'users';
    }
    
    public function modelName(): string
    {
        return \App\Models\User::class;
    }
    
    public function baseRoute(): string
    {
        return 'users';
    }
    
    public function routeParameter(): string
    {
        return 'user';
    }
    
    public function getListFields(): array
    {
        return [
            Field::make('id')->withTitle('ID')->sortable(),
            Field::make('name')->withTitle('Name')->searchable(),
            Field::make('email')->withTitle('Email')->searchable(),
            Field::make('created_at')->withTitle('Created')->type(Field::DATE_TIME),
        ];
    }
    
    public function getFieldsForm(): array
    {
        return [
            Field::string('name', 'Name')->required(),
            Field::string('email', 'Email')->required(['email']),
            Field::string('password', 'Password')->required(['min:8']),
        ];
    }
}
```

2. **Register Routes:**
```php
use RMS\Core\Helpers\RouteHelper;

RouteHelper::adminResource(UsersController::class, 'admin.users');
```

## 📚 Documentation

### Core Concepts

- **AdminController** - Base controller with built-in CRUD operations
- **Field System** - Dynamic field generation and validation
- **Traits System** - Modular functionality with traits
- **Interface System** - Contract-based development

### Available Interfaces

- `HasList` - For list/table views
- `HasForm` - For create/edit forms  
- `HasStats` - For statistics cards
- `ShouldFilter` - For advanced filtering
- `ShouldExport` - For Excel export
- `ChangeBoolField` - For quick boolean toggles

### Field Types

```php
Field::string('name', 'Name')           // Text input
Field::number('age', 'Age')             // Number input  
Field::boolean('active', 'Active')      // Boolean toggle
Field::select('role', 'Role', $options) // Select dropdown
Field::date('birth_date', 'Birth Date') // Persian date picker
Field::image('avatar', 'Avatar')        // Image upload
Field::price('amount', 'Amount')        // Price with formatter
```

## 🎨 UI Components

### Statistical Cards
```php
public function getStats(): array
{
    return [
        [
            'title' => 'Total Users',
            'value' => number_format(1234),
            'unit' => 'users',
            'icon' => 'users',
            'color' => 'primary',
            'colSize' => 'col-xl-3 col-md-6'
        ]
    ];
}
```

### Menu Components
```blade
<x-cms::menu-header title="Management" />

<x-cms::menu-item 
    title="Dashboard"
    url="/admin"
    icon="ph-house"
    :routes="['admin.dashboard']"
/>

<x-cms::submenu-item 
    title="Users"
    icon="ph-users"
    :children="$usersChildren"
/>
```

## 🔌 Plugin System

RMS Core includes a powerful plugin system:

- **Image Uploader** - Drag & drop image uploads
- **Persian DatePicker** - Shamsi calendar support
- **Enhanced Select** - Advanced select dropdowns
- **Amount Formatter** - Price formatting
- **SweetAlert2** - Beautiful notifications

## 🌐 Localization

Full Persian/Farsi language support with RTL layout:

```php
// Always use translation keys
trans('admin.users_management')
trans('admin.create_new_user')
trans('admin.user_created_successfully')
```

## 🛠️ Development

### Debug System

Enable debugging with `?debug=1` parameter:

- Performance monitoring
- Query analysis  
- Field validation
- Memory usage tracking

### Testing

```bash
composer test
```

## 📈 Changelog

See [CHANGELOG.md](CHANGELOG.md) for version history.

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Links

- **Documentation**: [Coming Soon]
- **GitHub Issues**: [https://github.com/rmscms/core/issues](https://github.com/rmscms/core/issues)
- **Discussions**: [https://github.com/rmscms/core/discussions](https://github.com/rmscms/core/discussions)

---

Made with ❤️ by RMS Team