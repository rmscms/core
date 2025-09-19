# RMS Core Examples

این پوشه شامل مثال‌های عملی از نحوه استفاده از کلاس‌های جدید RMS Core است.

## ساختار جدید RMS Core

### کلاس‌های اصلی:

1. **Database** - برای ساخت و مدیریت Query های پیچیده
2. **Field** - برای تعریف فیلدهای فرم و لیست
3. **Column** - برای فیلترهای پیشرفته
4. **FormGenerator** - برای ساخت فرم‌های خودکار
5. **ListGenerator** - برای ساخت لیست‌های خودکار

## مثال‌های موجود:

### 1. GroupsController.php
کنترلر کاملی که نشان می‌دهد:
- ساخت لیست با join و فیلترهای پیچیده
- ساخت فرم با validation
- امکانات پیشرفته مانند search، sorting، pagination
- آمار و گزارش‌گیری
- Export داده‌ها
- بهینه‌سازی کارایی

### 2. SimpleUsersController.php
مثال ساده‌تر که نشان می‌دهد:
- استفاده پایه از Database کلاس
- ساخت فرم ساده
- فیلترهای پایه
- امنیت پایه‌ای

## نحوه استفاده:

### ساخت لیست ساده:
```php
// تعریف فیلدها
$fields = [
    Field::make('id')->setDatabaseKey('id')->setTitle('ID'),
    Field::make('name')->setDatabaseKey('name')->setTitle('Name'),
];

// ساخت Database instance
$database = new Database($fields, 'users');

// اعمال فیلتر
$database->where('active', '=', 1);

// اعمال جستجو
$database->search('john', ['name', 'email']);

// اعمال مرتب‌سازی
$database->sort('created_at', 'DESC');

// دریافت نتایج
$results = $database->get(15);
```

### ساخت فرم:
```php
// تعریف فیلدهای فرم
$formFields = [
    Field::make('name')
        ->setTitle('Name')
        ->setType(Field::STRING)
        ->setRequired(true),
        
    Field::make('email')
        ->setTitle('Email')
        ->setType(Field::STRING)
        ->setRequired(true),
];

// ساخت FormGenerator
$formGenerator = new FormGenerator($formFields);

// رندر فرم
$form = $formGenerator->render();
```

### فیلترهای پیشرفته:
```php
$filters = [
    new Column('age', '>', 18, Field::INTEGER),
    new Column('role_id', 'IN', [1, 2, 3], Field::INTEGER),
    new Column('deleted_at', 'IS NULL', null, Field::DATE),
];

$database->withFilters($filters);
```

## مزایای ساختار جدید:

### 1. **سادگی کد:**
- کد کمتر برای عملکرد بیشتر
- Interface یکسان برای همه operations
- Fluent interface برای زنجیره‌سازی methods

### 2. **امنیت:**
- محافظت خودکار از SQL Injection
- اعتبارسنجی خودکار ورودی‌ها
- Security constraints برای کنترل دسترسی

### 3. **انعطاف‌پذیری:**
- پشتیبانی از join های پیچیده
- امکان group by و having
- پشتیبانی از SQL functions
- فیلترهای شرطی پیشرفته

### 4. **کارایی:**
- بهینه‌سازی خودکار queries
- pagination هوشمند
- امکان debugging و monitoring

### 5. **قابلیت نگهداری:**
- کد تمیز و خوانا
- جداسازی منطق business از presentation
- Test coverage کامل

## مقایسه با کد قدیم:

### کد قدیم:
```php
class GroupsController extends AdminController implements FormAndListUsingDatabase
{
    public function getFieldsForm(): array {
        return [
            (new Field('name'))->withTitle(trans('admin.name'))->required(),
            // ... بقیه فیلدها
        ];
    }
    
    public function getListFields(): array {
        return [
            (new Field('id'))->withTitle(trans('admin.id')),
            // ... بقیه فیلدها
        ];
    }
    
    public function table(): string {
        return 'groups';
    }
}
```

### کد جدید:
```php
class GroupsController
{
    public function getList(Request $request): JsonResponse
    {
        $database = new Database($this->getListFields(), 'groups');
        $database->leftJoin('protocols', 'groups.protocol_id', '=', 'protocols.id');
        
        $this->applyFilters($database, $request);
        
        $results = $database->get($request->get('per_page', 15));
        $listGenerator = new ListGenerator($this->getListFields(), $results);
        
        return response()->json(['data' => $listGenerator->render()]);
    }
}
```

## مزایای کد جدید:

1. **کنترل کامل**: دسترسی مستقیم به query builder
2. **قابلیت Debug**: امکان مشاهده SQL و bindings
3. **انعطاف بیشتر**: امکان اعمال هر نوع filter و join
4. **امنیت بهتر**: validation و sanitization خودکار
5. **کارایی بالاتر**: بهینه‌سازی خودکار queries

این ساختار جدید به شما امکان ساخت سریع و ایمن کنترلرهای CRUD با کمترین کد ممکن را می‌دهد.
