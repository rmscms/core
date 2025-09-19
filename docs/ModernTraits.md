# Modern RMS Core Traits Documentation

این documentation برای trait‌های مدرن شده RMS Core که برای Laravel 12 و PHP 8.2+ طراحی شده‌اند.

## ویژگی‌های جدید

### 🔥 مزایای کلیدی
- **Type Safety**: تمام trait‌ها از strict types استفاده میکنند
- **Better Error Handling**: مدیریت خطا بهتر با logging و exception handling
- **Modern PHP Features**: استفاده از union types, typed properties, و nullable types  
- **Separation of Concerns**: هر trait مسئولیت مشخصی داره
- **Laravel 12 Compatible**: سازگار با آخرین ویژگی‌های Laravel

### 🏗️ ساختار جدید

```
src/Traits/
├── FormAndList.php (Main trait)
├── Actions/
│   ├── DeleteAction.php
│   ├── StoreAction.php
│   └── BoolAction.php
├── Controllers/
│   └── HelperController.php
├── Data/
│   └── UseDatabaseHelper.php
├── Export/
│   └── ExportList.php
├── Filter/
│   ├── FilterList.php
│   └── Sortable.php
├── Form/
│   └── GenerateForm.php
├── List/
│   ├── GenerateList.php
│   └── PerPageList.php
└── Stats/
    └── Statable.php
```

## استفاده ساده

### Controller ساده

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use RMS\Core\Contracts\Data\UseDatabase;
use RMS\Core\Contracts\List\HasList;
use RMS\Core\Contracts\Form\HasForm;
use RMS\Core\Controllers\Admin\AdminController;
use RMS\Core\Traits\FormAndList;

class UsersController extends AdminController implements UseDatabase, HasList, HasForm
{
    use FormAndList;

    public function __construct()
    {
        parent::__construct();
        $this->initializeFormAndList();
        $this->setTitle('مدیریت کاربران');
    }

    public function modelName(): string
    {
        return User::class;
    }

    public function baseRoute(): string
    {
        return 'users';
    }

    public function table(): string
    {
        return 'users';
    }

    public function getListFields(): array
    {
        return [
            Field::make('name', 'نام')->sortable(),
            Field::make('email', 'ایمیل')->sortable(),
            Field::make('status', 'وضعیت')->displayAsBoolean()
        ];
    }

    public function getFieldsForm(): array
    {
        return [
            Field::make('name', 'نام')->required(),
            Field::make('email', 'ایمیل')->required(),
            Field::make('status', 'وضعیت')->type('checkbox')
        ];
    }

    public function boolFields(): array
    {
        return ['status'];
    }
}
```

## Trait‌های جداگانه

### 1. GenerateList
مسئول تولید و مدیریت لیست‌ها

```php
use RMS\Core\Traits\List\GenerateList;

// Methods available:
$this->generateList()
$this->routeParameter()
$this->boolFieldUrl($id, $key)
$this->setTplList()
```

### 2. FilterList  
مسئول فیلتر کردن لیست‌ها

```php
use RMS\Core\Traits\Filter\FilterList;

// Methods available:
$this->filter($request)
$this->getFilters()
$this->cacheFilter()
```

### 3. DeleteAction
مسئول عملیات حذف

```php
use RMS\Core\Traits\Actions\DeleteAction;

// Methods available:
$this->destroy($request, $id)
$this->batchDestroy($request)
$this->beforeDestroy($id) // Hook
$this->afterDestroy($id)  // Hook
```

### 4. StoreAction
مسئول عملیات ذخیره و بروزرسانی

```php
use RMS\Core\Traits\Actions\StoreAction;

// Methods available:
$this->store($request)
$this->processStore($request)
$this->update($request, $id)
$this->add($request)
$this->beforeAdd($request)    // Hook
$this->afterAdd($request, $id, $model) // Hook
$this->beforeUpdate($request, $id) // Hook
$this->afterUpdate($request, $id, $model) // Hook
```

### 5. Statable
مسئول آمار و statistics

```php
use RMS\Core\Traits\Stats\Statable;

// Methods available:
$this->withStat($stat, $key)
$this->statsCount($builder)
$this->statsSum($builder, $column, $formatAsAmount)
$this->statsAverage($builder, $column)
$this->statsToTpl()
```

## Hooks و Customization

### Form Hooks
```php
protected function beforeGenerateForm(FormGenerator &$generator): void
{
    // Add custom form assets
    $this->view->withJs('custom-form.js');
}

protected function transformFormResponse(FormResponse &$form): void
{
    // Modify form data before rendering
    $form->setCustomData(['key' => 'value']);
}
```

### List Hooks
```php
protected function beforeGenerateList(ListGenerator &$generator): void
{
    // Add custom list assets
    $this->view->withCss('custom-list.css');
}
```

### Action Hooks
```php
protected function beforeAdd(Request &$request): void
{
    // Custom logic before adding
    $request->merge(['created_by' => auth()->id()]);
}

protected function afterAdd(Request $request, int|string $id, Model $model): void
{
    // Custom logic after adding
    Mail::send(new UserCreatedMail($model));
}
```

## مزایای مدرن‌سازی

### Type Safety
```php
// قبل:
public function destroy(Request $request, $id)

// حالا:
public function destroy(Request $request, int|string $id): RedirectResponse
```

### Better Error Handling
```php
try {
    $this->performDestroy($id);
    return back()->with('success', trans('admin.deleted_successfully'));
} catch (ModelNotFoundException $e) {
    return back()->withErrors(trans('admin.record_not_found'));
} catch (Throwable $e) {
    Log::error('Delete action failed', [
        'controller' => get_class($this),
        'id' => $id,
        'error' => $e->getMessage()
    ]);
    
    return back()->withErrors(trans('admin.delete_failed'));
}
```

### Modern PHP Features
```php
// Union types
public function toggleBoolField(Request $request, int|string $id): JsonResponse|RedirectResponse

// Typed properties
protected array $stats = [];
protected ?string $title = null;

// Named arguments support
$this->statsSum($builder, 'amount', formatAsAmount: true, key: 'total_amount');
```

## Migration Guide

### از رمز1 به rms2

1. **Replace old trait:**
```php
// Old
use RMS\Core\Controllers\FormAndList;

// New  
use RMS\Core\Traits\FormAndList;
```

2. **Add type hints:**
```php
// Old
protected function beforeAdd(Request &$request)

// New
protected function beforeAdd(Request &$request): void
```

3. **Implement required interfaces:**
```php
class YourController extends AdminController implements 
    UseDatabase, 
    HasList, 
    HasForm
{
    // ...
}
```

4. **Initialize in constructor:**
```php
public function __construct()
{
    parent::__construct();
    $this->initializeFormAndList();
}
```

## Performance Improvements

- **Better Caching**: Cache keys بهتر و مدیریت cache بهبود یافته
- **Database Optimization**: Query optimization و lazy loading
- **Memory Management**: بهتر memory usage برای export‌های بزرگ
- **Error Resilience**: Graceful degradation در صورت خطا

## Security Enhancements

- **Input Validation**: Validation قوی‌تر برای تمام inputs
- **Permission Checking**: سیستم permission checking بهتر
- **Audit Logging**: Log کردن تمام اکشن‌ها برای audit
- **CSRF Protection**: محافظت بهتر در برابر CSRF attacks

## Testing Support

تمام trait‌ها قابلیت test کردن دارند:

```php
// Test example
public function test_can_delete_user()
{
    $user = User::factory()->create();
    $response = $this->delete(route('admin.users.destroy', $user));
    
    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $user->id]);
}
```

## Best Practices

1. **همیشه type hints استفاده کنید**
2. **از hook methods برای customization استفاده کنید**  
3. **Error handling رو فراموش نکنید**
4. **Log اکشن‌های مهم رو**
5. **Permission checking رو implement کنید**

## مثال‌های پیشرفته

در پوشه `examples/Controllers/` مثال‌های مختلفی از نحوه استفاده موجود است.
