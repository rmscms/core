# 🎯 RMS Controller Creation System

⚠️ **CRITICAL WARNING - READ FIRST:**

**The following methods DO NOT exist in the Field class:**
- `enableBoolAction()` ❌ - Boolean toggles are handled by the `ChangeBoolField` interface
- `filterable()` ❌ - Use proper filtering setup with field configurations

**Instead use:**
- `setOptions([])` ✅ - For select field options
- `advanced()` ✅ - For enhanced select fields
- `ChangeBoolField` interface ✅ - For boolean toggle functionality

**StatCard methods:**
- `->icon()` ❌ - Does NOT exist!
- `->color()` ❌ - Does NOT exist!
- `->withIcon()` ✅ - Correct method
- `->withColor()` ✅ - Correct method
- `->withUnit()` ✅ - For units like 'نفر', 'تومان'
- `StatCard::userCount()` ✅ - Quick factory method

**The `getListConfig()` method is also OPTIONAL and should only be included if you need to override default list configurations.**

🚨 **GOLDEN RULE - NEVER FORGET:**
**NEVER use Persian text directly in code!**
- ✅ Correct: `Field::string('name', trans('admin.users.fields.name'))`
- ❌ Wrong: `Field::string('name', 'نام')`
- ✅ Correct: `StatCard::make(trans('admin.users.stats.total'), (string)$total)`
- ❌ Wrong: `StatCard::make('مجموع', $total)`

**StatCard value parameter must be string!**
- ✅ Correct: `StatCard::make($title, (string)$count)`
- ❌ Wrong: `StatCard::make($title, $count)`

**All translations must be in `resources/lang/fa/admin.php`!**

---

## 📋 **CONTROLLER CREATION CHECKLIST**

### 🚀 **STEP 1: Initial Questions**
Before creating any controller, ask these questions:

#### ✅ **Default Interfaces** (Always included):
- `HasList` - ✅ Always needed for listing
- `HasForm` - ✅ Always needed for create/edit forms  
- `ShouldFilter` - ✅ Always needed for filtering

#### ❓ **Optional Interfaces** (Ask user):
- `HasStats` - Statistics cards in list view?
- `HasFormStats` - Statistics in edit forms?  
- `ChangeBoolField` - Quick boolean toggles (active/published/etc)?
- `ShouldExport` - Excel export functionality?
- `HasBatch` - Bulk operations (delete, activate, etc)?
- `HasUploadConfig` - File/image upload fields?
- `HasSort` - Custom sorting beyond default?

#### 🗂️ **Model Information**:
- Model name (singular): `User`, `Product`, `Category`
- Table name: `users`, `products`, `categories`
- Route name (plural): `users`, `products`, `categories`
- Route parameter (singular): `user`, `product`, `category`

---

## 📝 **STEP 2: Pre-Flight Checks**

### 🔍 **Model Check**:
```bash
# Check if model exists
php artisan tinker
>>> App\Models\{ModelName}::first()
```

### 🗃️ **Migration Check**:
```bash
# Check if migration exists  
php artisan migrate:status | grep {table_name}
```

### 📊 **Database Schema**:
```sql
-- Check table structure
DESCRIBE {table_name};
```

---

## 🏗️ **STEP 3: Creation Order**

### 1️⃣ **Create Model** (if not exists):
```bash
php artisan make:model {ModelName} -m
```

### 2️⃣ **Create Migration** (if needed):
```bash  
php artisan make:migration create_{table_name}_table
```

### 3️⃣ **Create Controller**:
```bash
# DON'T USE ARTISAN - Create manually for RMS Core compatibility
# File: app/Http/Controllers/Admin/{ModelName}Controller.php
```

### 4️⃣ **Register Routes**:
```php
// In routes/web.php
use RMS\Core\Helpers\RouteHelper;
RouteHelper::adminResource({ModelName}Controller::class, '{route_name}', $options);
Route::resource('{route_name}', {ModelName}Controller::class);
```

### 5️⃣ **Add Sidebar Menu**:
```php
// Add menu item to sidebar configuration
```

---

## 🎯 **STEP 4: Controller Template**

### 📄 **Minimal Controller Structure**:
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\{ModelName};
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Data\Field;
use RMS\Core\Data\StatCard;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Contracts\Filter\ShouldFilter;
// Add optional interfaces based on user selection
use RMS\Core\Contracts\Stats\HasStats;
use RMS\Core\Contracts\Actions\ChangeBoolField;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class {ModelName}Controller extends AdminController implements
    HasList,
    HasForm,  
    ShouldFilter,
    HasStats,           // Optional
    ChangeBoolField     // Optional
{
    // ✅ REQUIRED METHODS:
    
    public function table(): string
    {
        return '{table_name}';
    }
    
    public function modelName(): string
    {
        return {ModelName}::class;
    }
    
    public function baseRoute(): string
    {
        return '{route_name}';
    }
    
    public function routeParameter(): string
    {
        return '{route_parameter}';
    }
    
    // ❌ getListConfig() اختیاری است - فقط برای override
    // اگر نیاز نیست حذف کن!
    
    public function getFieldsForm(): array
    {
        return [
            // Add form fields here - USE FIELD GENERATOR
        ];
    }
    
    public function getListFields(): array
    {
        return [
            // Add list fields here - USE FIELD GENERATOR
        ];
    }
    
    public function rules(): array
    {
        return [
            // Add validation rules
        ];
    }
    
    // ✅ OPTIONAL METHODS (based on interfaces):
    
    public function getStats(?QueryBuilder $query = null): array
    {
        // Add statistics if HasStats implemented
        return [];
    }
    
    public function boolFields(): array
    {
        // Add boolean fields if ChangeBoolField implemented
        return ['active'];
    }
    
    // ✅ CORRECT METHOD SIGNATURES:
    
    public function beforeAdd(Request &$request): void
    {
        // Process before creating new record
    }
    
    public function afterAdd(Request $request, string|int $id, Model $model): void
    {
        // Process after creating new record
    }
    
    public function beforeUpdate(Request &$request, string|int $id): void
    {
        // Process before updating record
    }
    
    public function afterUpdate(Request $request, string|int $id, Model $model): void
    {
        // Process after updating record
    }
    
    public function edit(Request $request, string|int $id)
    {
        // Custom edit page override if needed
        $response = parent::edit($request, $id);
        // Add custom data or template modifications
        return $response;
    }
}
```

---

## 🎨 **STEP 5: Field Generator System**

### 🔥 **Common Field Patterns**:

#### **📝 Form Fields**:
```php
public function getFieldsForm(): array
{
    return [
        // Text Input
        Field::string('name', 'Name')
            ->required()
            ->withHint('Display name'),
            
        // Number Input  
        Field::number('amount', 'Amount')
            ->withDefaultValue(0)
            ->required()
            ->withHint('Amount in numbers'),
            
        // Select Dropdown
        Field::select('category_id', 'Category')
            ->setOptions($this->getCategoryOptions())
            ->advanced() // ✅ Enable Select2
            ->required(),
            
        // Boolean Toggle
        Field::boolean('active', 'Active')
            ->withDefaultValue(true),
            
        // Date Picker
        Field::date('birth_date', 'Birth Date')
            ->optional(),
            
        // Hidden Field
        Field::hidden('user_id'),
            
        // Price Field
        Field::price('price', 'Price')
            ->withDefaultValue(0)
            ->withHint('Amount in Toman'),
    ];
}
```

#### **📊 List Fields**:
```php
public function getListFields(): array
{
    return [
        // ID Column
        Field::make('id')
            ->withTitle('ID')
            ->sortable()
            ->width('80px'),
            
        // Text Column with Search
        Field::make('name')
            ->withTitle('Name')
            ->searchable()
            ->sortable(),
            
        // Select Filter Column
        Field::select('category_id')
            ->withTitle('Category')
            ->setOptions(['' => 'All'] + $this->getCategoryOptions())
            ->customMethod('displayCategoryName')
            ->width('120px'),
            
        // Boolean Column - NO enableBoolAction() method!
        // ❌ Boolean toggles are handled by ChangeBoolField interface
        Field::boolean('active')
            ->withTitle('Status')
            ->sortable()
            ->width('100px'),
            
        // Date Column  
        Field::date('created_at')
            ->withTitle('Created')
            ->sortable()
            ->width('140px'),
    ];
}
```

### 🎯 **Field Types Reference**:
```php
// Text Types
Field::STRING        // Regular text
Field::TEXTAREA      // Multi-line text
Field::EDITOR        // Rich text editor
Field::PASSWORD      // Password field
Field::HIDDEN        // Hidden input

// Number Types  
Field::NUMBER        // Number input
Field::INTEGER       // Integer only
Field::PRICE         // Money with formatter

// Date Types
Field::DATE          // Date picker (Persian)
Field::DATE_TIME     // Date + time picker
Field::TIME          // Time only

// Selection Types
Field::SELECT        // Dropdown
Field::RADIO         // Radio buttons
Field::BOOL          // Boolean toggle

// File Types
Field::FILE          // File upload
Field::IMAGE         // Image upload with preview

// Display Types
Field::LABEL         // Read-only display
Field::COMMENT       // Help text
```

---

## 🧩 Form Plugins & Asset Hooks

RMS Core auto-loads common form/list plugins based on field types. You can also force-load plugins or register custom ones.

- Auto plugins for forms:
  - persian-datepicker: enabled if any Field::DATE or Field::DATE_TIME exists
  - amount-formatter: enabled if any Field::PRICE exists
  - advanced-select: enabled if any select field is marked advanced()
  - image-uploader: enabled automatically if any Field::IMAGE exists

If you use a generic file input (Field::FILE) but still want the image-uploader UX (drag & drop, previews, AJAX), explicitly enable the plugin in the form lifecycle hook and add data-attributes to the field.

Example: enable image-uploader in your controller
```php
protected function beforeGenerateForm(\RMS\Core\Data\FormGenerator &$generator): void
{
    if (method_exists(get_parent_class($this), 'beforeGenerateForm')) {
        parent::beforeGenerateForm($generator);
    }
    $this->view->withPlugins(['image-uploader']);
}
```

Example: annotate a file field to hint the plugin
```php
Field::file('attachments', trans('admin.example.fields.attachments'))
    ->multiple()
    ->withAttributes([
        'data-uploader' => 'image-uploader',
        'data-preview'  => 'true',
        'data-ajax'     => 'true'
    ])
    ->withHint(trans('admin.example.fields.attachments_hint'))
    ->skipDatabase(); // if uploads are handled via HasUploadConfig
```
