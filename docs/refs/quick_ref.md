# ⚡ RMS Core Development - Quick Reference

این فایل به مسیر جدید منتقل شد:
- docs/refs/quick_ref.md

لطفاً نسخه موجود در پوشه refs را مطالعه کنید. مسیرهای مرجع مرتبط:
- docs/refs/CREATE_CONTROLLER.md
- docs/refs/panel_custom_page_pattern.md
- docs/refs/LIMITLESS_REFERENCE.md

## 🗺️ **PROJECT PATHS** - مسیرها و فایل‌های مهم پروژه

### 🏗️ **پروژه‌های IRAS:**
```
C:\laragon\www\iras\                          # 📂 پروژه قدیمی (RMS1 - Legacy)
├── ساختار RMS1 (قدیمی)                      # نسخه اولیه سیستم
├── کمتر امکانات                              # امکانات محدود
├── نیاز به ارتقا                              # باید به RMS2 منتقل شود
└── مرجع برای انتقال داده‌ها                   # کنترلرها و منطق باید بررسی شود

C:\laragon\www\iras\new\                       # 🚀 پروژه جدید (RMS2 - Current)
├── Laravel 12 + RMS Core v1.0.3             # فریمورک جدید + Select2
├── Extended Models در app/Models/           # 8 مدل گسترش یافته ✅ کامل
├── rmscms/core پکیج                         # هسته مدرن
└── مقصد نهایی انتقال                         # همه چیز اینجا منتقل می‌شود
```

### 📋 **برنامه کاری انتقال:**
1. **✅ بررسی پروژه قدیمی** (`C:\laragon\www\iras`) - شناسایی کنترلرها و منطق ✅ تکمیل شده
2. **✅ استخراج قابلیت‌ها** - لیست کردن امکانات موجود در RMS1 ✅ انجام شده 
3. **🔄 انتقال به RMS2** - پیاده‌سازی در پروژه جدید با معماری مدرن:

### ✅ **کنترلرهای کامل شده:**
   - ✅ **AdminsController** - در RMS Core (67-82) با RouteHelper کامل
   - ✅ **UsersController** - در RMS Core (84-99) با RouteHelper کامل  
   - ✅ **SettingsController** - در RMS Core (خط 102) ساده
   - ✅ **AccountsController** - در پروژه IRAS کامل پیاده‌سازی شده
   - ✅ **BugLogController, CacheManager, Debug** - کنترلرهای فنی RMS Core

### 🔄 **کنترلرهای مورد نیاز IRAS:**
   - 🔄 **GroupsController** - برای مدل Group (نیاز فوری)
   - 🔄 **ProtocolsController** - برای مدل Protocol (نیاز فوری)
   - 🔄 **LocationsController** - برای مدل Location (نیاز متوسط)
   - ⏳ **ServersController** - اختیاری (اگر مدل داشته باشیم)
4. **⏳ تست و اعتبارسنجی** - اطمینان از صحت انتقال
5. **⏳ حذف Legacy** - پس از اطمینان از انتقال کامل

### 📁 **Core Project Structure (RMS2):**
```
C:\laragon\www\iras\new\                       # پروژه جدید RMS2
├── app/Models/                               # Extended Models (Admin, User, ...)
├── app/Http/Controllers/Admin/               # کنترلرهای پروژه
├── vendor/rmscms/core/                       # پکیج RMS Core v1.0.3
│   ├── src/Controllers/Admin/AdminController.php    # کلاس پایه کنترلرها
│   ├── src/Contracts/                        # Interfaces اصلی
│   │   ├── List/HasList.php                  # Interface لیست
│   │   ├── Form/HasForm.php                  # Interface فرم
│   │   ├── Actions/ChangeBoolField.php       # Interface تغییر Boolean
│   │   ├── Filter/ShouldFilter.php           # Interface فیلتر
│   │   ├── Export/ShouldExport.php           # Interface خروجی Excel
│   │   ├── Stats/HasStats.php                # Interface آمار لیست
│   │   ├── Stats/HasFormStats.php            # Interface آمار فرم
│   │   ├── Upload/HasUploadConfig.php        # Interface آپلود فایل
│   │   └── Batch/HasBatch.php                # Interface عملیات گروهی
│   ├── src/Data/Field.php                    # کلاس Field اصلی
│   ├── src/Debug/RMSDebugger.php            # سیستم دیباگ مرکزی
│   ├── src/Traits/FormAndList.php           # Trait اصلی CRUD
│   ├── resources/views/admin/layout/        # Templates اصلی
│   └── assets/                               # CSS/JS برای کپی به public
├── public/admin/                             # Assets کپی شده از package
├── storage/logs/rms_debug/                   # Debug logs
├── limitless-template-full/                  # قالب Limitless (مرجع کامل)
├── database/seeders/IrasProjectSetupSeeder.php  # راه‌اندازی خودکار
├── CHANGELOG.md                              # تاریخچه تغییرات
└── quick_ref.md                             # ← همین فایل (مرجع فنی)
```

### 📄 **Critical Files - فایل‌های حیاتی:**
- `quick_ref.md` ← **همیشه اول بخون!** تمام اطلاعات مهم اینجاست
- `docs/refs/CREATE_CONTROLLER.md` ← راهنمای ساخت کنترلر (مرجع قطعی)
- `docs/refs/panel_custom_page_pattern.md` ← الگوی صفحه اختصاصی پنل (بدون فرم RMS)
- `docs/refs/LIMITLESS_REFERENCE.md` ← مرجع سریع Limitless در پروژه
- `RMS_CORE_COMPLETE_MAP_V2.md` ← نقشه کامل معماری Core
- `form_implementation_log.md` ← تاریخچه پیاده‌سازی
- `vendor/rmscms/core/src/Controllers/Admin/AdminController.php` ← کلاس پایه
- `vendor/rmscms/core/src/Traits/FormAndList.php` ← Trait اصلی CRUD
- `vendor/rmscms/core/src/Helpers/RouteHelper.php` ← ثبت سریع روت‌ها
- `vendor/rmscms/core/src/Data/Field.php` ← تعریف Field و متدها
- `app/Http/Controllers/Admin/UsersController.php` ← کنترلر فعلی کار

### 🧭 RMS Core Deep Map (لینک‌های سریع)
- Controllers Base: `vendor/rmscms/core/src/Controllers/Admin/AdminController.php`
- Traits:
  - FormAndList: `vendor/rmscms/core/src/Traits/FormAndList.php`
  - GenerateList: `vendor/rmscms/core/src/Traits/List/GenerateList.php`
  - GenerateForm: `vendor/rmscms/core/src/Traits/Form/GenerateForm.php`
  - StoreAction: `vendor/rmscms/core/src/Traits/Actions/StoreAction.php`
  - DeleteAction: `vendor/rmscms/core/src/Traits/Actions/DeleteAction.php`
  - BoolAction: `vendor/rmscms/core/src/Traits/Actions/BoolAction.php`
  - ExportList: `vendor/rmscms/core/src/Traits/List/ExportList.php`
- Contracts:
  - HasList: `vendor/rmscms/core/src/Contracts/List/HasList.php`
  - HasForm: `vendor/rmscms/core/src/Contracts/Form/HasForm.php`
  - ShouldFilter: `vendor/rmscms/core/src/Contracts/Filter/ShouldFilter.php`
  - ChangeBoolField: `vendor/rmscms/core/src/Contracts/Actions/ChangeBoolField.php`
  - HasStats: `vendor/rmscms/core/src/Contracts/Stats/HasStats.php`
  - HasFormStats: `vendor/rmscms/core/src/Contracts/Stats/HasFormStats.php`
  - ShouldExport: `vendor/rmscms/core/src/Contracts/Export/ShouldExport.php`
- Helpers:
  - RouteHelper: `vendor/rmscms/core/src/Helpers/RouteHelper.php`

### ❓ FAQ (سریع)
- فیلتر select کار نمی‌کند؟ حتماً `filterType(Field::SELECT)` + `setOptions([...])` تنظیم شود.
- toggle بولین کار نمی‌کند؟ Interface `ChangeBoolField` را implement کن و `boolFields()` را برگردان.
- مقدار StatCard خطا می‌دهد؟ پارامتر دوم باید string باشد: `(string)$count`.
- مسیر template اشتباه؟ هرگز `admin.` در مسیر Blade نگذار: فقط `'pages.xxx.yyy'`.
- متن فارسی در کد؟ ممنوع؛ همیشه از `trans()` استفاده کن.

---

### 😨 **CRITICAL RULES** - قوانین حیاتی (هرگز فراموش نکن!)

### 🔥 **ABSOLUTE RULES - قوانین مطلق:**

1. **🚨 TEMPLATE PATH - هرگز admin. prefix نذار!** قبلاً تعریف شده!
   - ❌ غلط: `'admin.pages.users.edit'`
   - ✅ درست: `'pages.users.edit'`
   - **دلیل:** prefix admin قبلاً در ViewTemplateManager تعریف شده!
2. **🎨 COLORS - از رنگ‌های استاندارد Limitless استفاده کن!**
   - **📍 اولویت 1:** Bootstrap 5 color classes (`.text-primary`, `.bg-success`)
   - **📍 اولویت 2:** Limitless theme variables (خودکار لود شده در قالب)
   - **📄 مرجع Variables:** `C:\laragon\www\iras\new\public\admin\css\limitless-variables.css`
   - **✅ درست:** `.text-danger`, `.bg-light`, `.border-primary`
   - **❌ غلط:** `color: #dc3545`, `background: #ffffff`
   - **🌙 مزیت:** خودکار در Dark/Light theme کار می‌کند
3. **📁 هرگز vendor فایل‌ها کپی نکن!** فقط CSS/JS assets از package کپی می‌شه
2. **⚙️ AdminController قبلاً `FormAndList` trait دارد** - دوباره use نکن!
3. **😨 NEVER RUN `php artisan serve`** - پروژه روی Laragon اجراست (localhost)
4. **⚡ getTable() استفاده کن و table name string بذار**
5. **📝 Field::withDefaultValue()** نه `withDefault()` (نام صحیح مهمه!)
6. **🔗 baseRoute()** بدون prefix - `'users'` نه `'admin.users'`
7. **🌙 هر CSS باید dark theme support داشته باشه!**
8. **🔒 Field constants هرگز تغییر نکن!** فقط آخر لیست اضافه کن
10. **🗺️ Template path:** `cms::admin.form.index` (مسیر استاندارد)
11. **🗺️ هرگز متن فارسی مستقیم در کد نذار!** همیشه از trans() استفاده کن
12. **🍞 TOAST NOTIFICATIONS - فقط Toast برای Ajax!**
   - ✅ **درست:** `this.showToast(message, 'success')` - سبک و زیبا
   - ❌ **غلط:** `Swal.fire()` - حجیم و پیچیده
   - ❌ **غلط:** `alert()` - زشت و بدنما
   - **مزیت:** Dark theme, نمایش گوشه، انیمیشن حرفه‌ای
   - **مرجع:** cache-manager.js (showToast method)

### 🗺️ **قوانین مهم پروژه:**
- **📆 هر task قبل شروع این فایل رو بخون** (قانون BD2YLMDBVgd29B8NqRFPMv)
- **⚡ اگه core تغییر کرد این فایل رو به‌روزرسانی کن**
- **🏢 Laravel 12 alignment** - پروژه با Laravel 12 هماهنگه
- **📦 Composer شامل vendor directory** - کامل package
- **🎯 برای ساخت کنترلر جدید:** همیشه `CREATE_CONTROLLER.md` را مطالعه کن

### 🗄️ **DATABASE INSPECTOR - بررسی ساختار دیتابیس:**
- **📋 لیست جدولها:** `php artisan db:inspect --list`
- **🔍 بررسی جدول:** `php artisan db:inspect TABLE_NAME`
- **📊 جدول + نمونه دیتا:** `php artisan db:inspect TABLE_NAME --all`
- **💡 مثال:** `php artisan db:inspect servers` - ساختار کامل جدول servers
- **⚡ فایده:** جایگزین tinker برای بررسی ساختار دیتابیس بدون خطا

### 😨 **Debug System Rules - قوانین سیستم دیباگ:**
- **📁 Debug System خودکار از log files دیتا لود می‌کنه** اگر memory خالی باشه
- **⚠️ اگر `/admin/debug/export` خالیه** ← log ها پاک شدن یا هنوز form debug نشده
- **📅 Debug data مسیر:** `storage/logs/rms_debug/rms_system-YYYY-MM-DD.log`
- **🔄 تکرار محتوا** ← نشانه عدم فعال‌سازی صحیح debug در controller methods

### ⚡ **AdminController Architecture - معماری کنترلر پایه:**
```php
AbstractAdminController extends Controller implements UseDatabase {
  use AuthorizesRequests, ValidatesRequests, FormAndList {
    AuthorizesRequests::authorize insteadof FormAndList; // حل collision
  }
  
  // ✅ متدهای خودکار از RequestForm interface:
  public function authorize(Request $request): bool
  public function getTableName(): string  // ← با getTable()
}
```

---

## ✨ **SELECT2 PLUGIN** - سیستم Select های پیشرفته

### 🔧 **Select2 Integration RMS v1.0.3:**
- **کتابخانه:** Select2 v4.1.0 از Limitless اصلی
- **Bootstrap 5:** سازگاری کامل با Bootstrap 5
- **Dark Theme:** پشتیبانی کامل از تم تاریک RMS
- **RTL/Persian:** پشتیبانی کامل از زبان فارسی و راست به چپ
- **Simple Wrapper:** initialization ساده مثل Limitless

### 📁 **فایل‌های Select2:**
- **select2.min.js** (79KB) - کتابخانه اصلی
- **select2.min.css** (9KB) - استایل‌های پایه
- **select2-bootstrap.css** (11KB) - تطبیق Bootstrap 5 + تم تاریک
- **select2-init.js** (1.5KB) - wrapper ساده RMS

### 🎨 **استفاده در فرم‌ها:**
```php
// در Controller فیلدهای فرم:
Field::make('group_id', 'group_id')->withTitle('گروه')
    ->type(Field::SELECT)
    ->setOptions([
        '' => 'انتخاب گروه',
        1 => 'مدیران',
        2 => 'کارکنان',
        3 => 'مشتریان'
    ])
    ->advanced()  // ← این خط Select2 را فعال می‌کند
    ->required();
```

### 🌙 **تم تاریک (Dark Theme):**
- **Background:** `#2c2d33` - مطابق input های Limitless
- **Border:** `var(--border-color)` - از متغیر تم استفاده می‌کند
- **Search Spacing:** فاصله مناسب برای icon در RTL

### ⚡ **فعال‌سازی خودکار:**
- **کلاس‌ها:** `.enhanced-select`, `.select2`, `[data-enhanced]`
- **Modal/Offcanvas:** خودکار در popup ها initialize می‌شود
- **Theme Change:** خودکار با تغییر تم به‌روزرسانی می‌شود

---

---

## 🆔 **EXTENDED MODELS** - مدل‌های گسترش یافته IRAS

### ✅ **مدل‌های کامل شده (8 مدل):**
```
✅ Admin.php (3.8KB) - گسترش RMS Core با theme, telegram_chat_id
✅ User.php (6.5KB) - گسترش کامل با روابط SSH, Account, Location
✅ Account.php (6.9KB) - مدل اصلی IRAS با 266 خط کد
✅ Group.php (2.6KB) - گروه‌ها + Scope ها
✅ Protocol.php (1.8KB) - پروتکل‌ها (SSTP, WireGuard, V2Ray...)
✅ SSH.php (1.6KB) - اتصالات SSH
✅ Location.php (1.3KB) - مکان‌ها
✅ UserLocation.php (1.2KB) - روابط User-Location
```

### 📊 **آمار Models:**
- **مجموع:** 8 مدل Extended
- **حجم کل:** ~25KB کد
- **روابط:** کاملاً تعریف شده
- **Scopes & Relations:** پیاده‌سازی شده

---

### 🎯 **FormAndList Trait شامل تمام قابلیت‌ها:**
```php
// 📍 مسیر کامل: vendor/rmscms/core/src/Traits/FormAndList.php

// 📄 قابلیت‌های لیست:
use GenerateList, FilterList, PerPageList;     // لیست خودکار + فیلتر + صفحه‌بندی

// 💾 قابلیت‌های CRUD:
use DeleteAction, StoreAction;                  // حذف و ذخیره خودکار
use UseDatabaseHelper;                          // دیتابیس + model() helper

// 🗺️ قابلیت‌های فرم:
use GenerateForm;                               // تولید فرم خودکار
use RequestFormHelper;                          // request + rules() + authorize()

// 🚀 قابلیت‌های پیشرفته:
use ExportList, Statable, BoolAction;           // خروجی Excel + وضعیت + boolean
use StatsCardControl;                           // کنترل وضعیت کارت آمار
use HelperController, Sortable;                 // متدهای کمکی + مرتب‌سازی
use PersianDateConverter;                       // تبدیل تاریخ شمسی خودکار
use HasFileUpload;                              // سیستم آپلود فایل

// ✅ متدهای موجود در FormAndList (نیازی به تعریف مجدد نیست!):

// 📄 از GenerateList trait:
public function index(Request $request): Response           // لیست خودکار
public function routeParameter(): string                   // پارامتر route (خودکار)
public function setTplList(): void                         // تنظیم template لیست

// 💾 از PerPageList trait:
public function getPerPage(): int                          // تعداد آیتم در صفحه
public function perPage(Request $request): RedirectResponse // تغییر تعداد آیتم
public function setDefaultPerPage(int $perPage): self      // تنظیم پیش‌فرض

// 🗺️ از GenerateForm trait:
public function create(Request $request): View             // فرم create خودکار
public function edit(Request $request, $id): View          // فرم edit خودکار
public function formUrl(): string                          // URL فرم (خودکار)
public function getFormConfig(): array                     // پیکربندی فرم
public function setTplForm(): void                         // تنظیم template فرم
public function setFormUrl(string $url): self              // تنظیم URL سفارشی

// 📦 از StoreAction trait:
public function store(Store $request): RedirectResponse    // ذخیره خودکار
public function update(Request $request, $id): RedirectResponse // به‌روزرسانی خودکار
protected function beforeAdd(Request &$request): void      // Hook قبل create
protected function afterAdd(Request $request, $id, Model $model): void // Hook بعد create
protected function beforeUpdate(Request &$request, $id): void // Hook قبل update
protected function afterUpdate(Request $request, $id, Model $model): void // Hook بعد update

// 🗑️ از DeleteAction trait:
public function destroy(Request $request, $id): RedirectResponse // حذف خودکار
protected function beforeDestroy($id): void                // Hook قبل حذف
protected function afterDestroy($id): void                 // Hook بعد حذف

// 💾 از UseDatabaseHelper trait:
public function model(?int $id = null): ?Model             // دریافت/ایجاد model
public function modelOrFail(int $id): Model                // دریافت model اجباری
protected function query(Builder $sql): void               // سفارشی‌سازی کوئری

// 🔘 از BoolAction trait:
public function boolFields(): array                        // فیلدهای boolean
public function boolFieldUrl($id, string $key): string     // URL تغییر boolean
public function toggleBoolField(Request $request, $id): JsonResponse // تغییر boolean

// 📋 از RequestFormHelper trait:
public function rules(): array                             // قوانین validation
public function messages(): array                          // پیام‌های سفارشی
public function attributes(): array                        // نام‌های فیلدها
public function prepareForValidation(Request &$request): void // آماده‌سازی قبل validation
public function authorize(Request $request): bool          // بررسی مجوز
```

---

## 🗣️ **LOCALIZATION SYSTEM** - سیستم چندزبانه و ترجمه

### 🚨 **قانون طلایی: NO HARDCODED PERSIAN!**

#### ✅ **روش صحیح:**
```php
// ⚡ همیشه از trans() استفاده کن:
trans('admin.users_management')
trans('admin.create_new_user')
trans('admin.user_created_successfully')

// در controllers:
$this->title(trans('admin.users_management'));
return back()->with('success', trans('admin.user_updated'));

// در blade templates:
{{ trans('admin.welcome_message') }}
@lang('admin.dashboard')
```

#### ❌ **روش غلط:**
```php
// هرگز اینکار نکن!!
$this->title('مدیریت کاربران');
Field::make('name')->withTitle('نام کاربر'); 
return back()->with('success', 'کاربر با موفقیت حذف شد');
```

### 📁 **فایل مرجع ترجمه‌ها:**
- **مسیر:** `resources/lang/fa/admin.php`
- **محتوا:** تمام متن‌های فارسی بخش مدیریت
- **سازماندهی:** بر اساس دسته‌بندی قابلیت‌ها و بخش‌ها

### 🗗️ **نمونه فایل admin.php:**
```php
<?php

return [
    // عمومی
    'dashboard' => 'داشبورد',
    'management' => 'مدیریت',
    'list' => 'لیست',
    'create' => 'ایجاد',
    'edit' => 'ویرایش',
    'delete' => 'حذف',
    'save' => 'ذخیره',
    'cancel' => 'انصراف',
    'back' => 'بازگشت',
    
    // کاربران
    'users_management' => 'مدیریت کاربران',
    'create_new_user' => 'ایجاد کاربر جدید',
    'edit_user' => 'ویرایش کاربر',
    'user_name' => 'نام کاربر',
    'email_address' => 'آدرس ایمیل',
    'mobile_number' => 'شماره موبایل',
    'password' => 'گذرواژه',
    'password_confirmation' => 'تکرار گذرواژه',
    'user_role' => 'نقش کاربر',
    'user_status' => 'وضعیت',
    'active' => 'فعال',
    'inactive' => 'غیرفعال',
    
    // پیام‌ها
    'user_created_successfully' => 'کاربر با موفقیت ایجاد شد',
    'user_updated_successfully' => 'کاربر با موفقیت به‌روزرسانی شد',
    'user_deleted_successfully' => 'کاربر با موفقیت حذف شد',
    'operation_failed' => 'عملیات با خطا مواجه شد',
    'are_you_sure' => 'آیا مطمئن هستید؟',
    'this_action_cannot_be_undone' => 'این عملیات قابل بازگشت نیست',
    
    // فرم‌ها
    'required_field' => 'این فیلد اجباری است',
    'invalid_email' => 'فرمت ایمیل اشتباه است',
    'password_min_length' => 'گذرواژه باید حداقل :min کاراکتر باشد',
    'passwords_do_not_match' => 'گذرواژه و تکرار آن یکسان نیستند',
    'email_already_exists' => 'این ایمیل قبلاً ثبت شده است',
    
    // آمار
    'total_users' => 'مجموع کاربران',
    'active_users' => 'کاربران فعال',
    'inactive_users' => 'کاربران غیرفعال',
    'new_users_today' => 'کاربران جدید امروز',
    'users_count_suffix' => 'نفر',
    
    // مدیران
    'admins_management' => 'مدیریت مدیران',
    'super_admin' => 'سوپر ادمین',
    'admin' => 'مدیر',
    'moderator' => 'ناظر',
    'editor' => 'ویراستار',
    'you_can_only_edit_your_profile' => 'شما فقط مجاز به ویرایش پروفایل خود هستید',
    'only_super_admin_can_delete' => 'فقط سوپر ادمین مجاز به حذف مدیران است',
    'cannot_delete_yourself' => 'نمی‌توانید خود را حذف کنید',
];
```

### 🛠️ **بهترین رویه‌ها:**

1. **📝 نامگذاری کلیدها:**
   - `section_item` برای آیتم‌های عمومی
   - `section_item_action` برای عملیات خاص
   - `section_message_type` برای پیام‌ها

2. **👥 دسته‌بندی موضوعی:**
   ```php
   // کاربران
   'users_management', 'users_list', 'users_create'...
   
   // محصولات  
   'products_management', 'products_list', 'products_create'...
   
   // سفارشات
   'orders_management', 'orders_list', 'orders_view'...
   ```

3. **⚡ پارامترها و جایگزینی:**
   ```php
   trans('admin.item_count', ['count' => 5, 'type' => 'کاربر'])
   // خروجی: "تعداد 5 کاربر یافت شد"
   
   // در فایل lang:
   'item_count' => 'تعداد :count :type یافت شد'
   ```

4. **🔍 Fallback و مدیریت خطا:**
   ```php
   // با fallback
   trans('admin.users_management', [], 'fa') ?: 'مدیریت کاربران'
   
   // بررسی وجود کلید
   if (trans('admin.some_key') !== 'admin.some_key') {
       // کلید وجود دارد
   }
   ```

### 📊 **برنامه مهاجرت متن‌ها:**
پس از آماده بودن فایل ترجمه، باید تمام متن‌های فارسی موجود را جایگزین کرد:

1. **AdminsController.php** - متن‌های مدیریت مدیران
2. **UsersController.php** - متن‌های مدیریت کاربران
3. **تمام controllers جدید** - همیشه از ابتدا با trans() بنویس
4. **Blade templates** - جایگزینی متن‌های سفت‌کد شده

---

## 📚 **MAIN INTERFACES** - رابط‌های اصلی و مستندات

### 📄 **HasList Interface (اجباری برای لیست):**
```php
// ✅ متدهای اجباری:
public function getListFields(): array              // تعریف فیلدهای لیست (Field objects)
public function baseRoute(): string                 // 'users' (بدون admin prefix!)
public function routeParameter(): string            // 'user' (مفرد - singular)
public function getListConfig(): array              // تنظیمات لیست

// ⚠️ توجه: setTplList() خودکار از AdminController آمده - تکرار نکن!
```

### 🗺️ **HasForm Interface (برای فرم) - extends RequestForm:**
```php
// ✅ متدهای فرم:
public function getFieldsForm(): array              // تعریف فیلدهای فرم (Field objects)
public function formUrl(): string                   // URL ارسال فرم (خودکار!)
public function getFormConfig(): array              // تنظیمات فرم

// ✅ متدهای RequestForm (وراثتی):
public function rules(): array                      // قوانین validation (اجباری!)
public function authorize(Request $request): bool   // مجوز فرم
public function attributes(): array                 // نام‌های فارسی فیلدها
public function messages(): array                   // پیام‌های سفارشی خطا
public function prepareForValidation(Request &$request): void // آماده‌سازی قبل validation

// ⚠️ توجه: setTplForm() خودکار از AdminController آمده - تکرار نکن!

// 💾 STAY IN FORM - کنترل دکمه "ذخیره و ماندن":
$this->setShowStayButton(false); // غیرفعال کردن
$this->setShowStayButton(true);  // فعال (پیش‌فرض)
```

### 🔘 **ChangeBoolField Interface (برای فیلدهای boolean):**
```php
// ✅ متدهای اجباری:
public function boolFields(): array                 // لیست فیلدهای boolean (['active', 'email_notifications'])
public function boolFieldUrl($id, $field): string   // URL تغییر فیلد

// ⚠️ changeBoolField() خودکار از BoolAction trait آمده!
```

### 🔍 **ShouldFilter Interface (برای فیلتر):**
```php
// ✅ متدهای فیلتر:
public function getFilters(): array                 // تعریف فیلترها (Field objects)
public function getCachedFilterData(): array        // داده‌های cache شده برای selectها
```

### 🔄 **HasSort Interface (برای مرتب‌سازی):**
```php
// ✅ متدهای مرتب‌سازی:
public function orderBy(): ?string                  // فیلد پیش‌فرض مرتب‌سازی ('id', 'created_at')
public function orderWay(): string                  // جهت مرتب‌سازی ('ASC'/'DESC')
public function fieldOrdered(): ?string             // فیلد فعلی مرتب شده
```

### 📄 **ShouldExport Interface (برای خروجی Excel):**
```php
// ✅ فقط interface را implement کنید - ExportList trait قبلاً در FormAndList موجود!
class UsersController extends AdminController implements ShouldExport

// 🚀 متدهای خودکار از ExportList trait:
public function export(?string $filename = null, string $format = 'xlsx'): Response
public function exportFiltered(array $filters, ?string $filename = null): Response  
public function exportColumns(array $columns, ?string $filename = null): Response

// 🔧 متدهای helper قابل override:
protected function getExportHeaders(): array        // سرتیتر ستون‌های Excel
protected function getExportColumns(): array        // نام ستون‌های دیتابیس
protected function getExportConfig(): array         // تنظیمات (max_rows, timeout, memory)
protected function canExport(): bool               // بررسی مجوز export
```

### 📊 **HasBatch Interface (عملیات گروهی):**
```php
// ✅ متدهای عملیات گروهی:
public function getBatchActions(): array            // عملیات موجود (['delete', 'activate', 'deactivate'])
public function canPerformBatchAction(string $action): bool  // بررسی مجوز
```

### 📈 **HasStats Interface (آمار در لیست‌ها):**
```php
// ✅ متدهای آماری:
public function getStats(?\Illuminate\Database\Query\Builder $query = null): array
public function getStatSummary(?\Illuminate\Database\Query\Builder $query = null): array

// 🎯 نحوه پیاده‌سازی:
class UsersController extends AdminController implements HasStats
{
    public function getStats(?\Illuminate\Database\Query\Builder $query = null): array
    {
        $baseQuery = $query ?? app($this->modelName())->newQuery();
        
        return [
            [
                'title' => 'مجموع کاربران',
                'value' => number_format((clone $baseQuery)->count()),
                'unit' => 'نفر',
                'icon' => 'users',
                'color' => 'primary',
                'colSize' => 'col-xl-3 col-md-6',
                'description' => $query ? 'بر اساس فیلتر فعال' : null
            ]
        ];
    }
}
```

#### **✨ ویژگی‌های HasStats:**
- **🔍 فیلتر هوشمند:** آمار بر اساس فیلترهای فعال محاسبه می‌شود
- **🎨 کارت‌های زیبا:** استفاده از کامپوننت `statistical-card`
- **📱 Responsive:** سازگار با تمام اندازه صفحه‌ها
- **🌙 Dark Theme:** پشتیبانی کامل
- **⚡ خودکار:** بدون نیاز به تنظیم اضافی در template
- **📊 Real-time:** آمار براساس query فعال محاسبه می‌شود

### 📝 **HasFormStats Interface (آمار در فرم‌ها):**
```php
// ✅ متدهای آماری برای فرم‌ها:
public function getFormStats(?\Illuminate\Database\Eloquent\Model $model = null, bool $isEditMode = false): array

// 🎯 نحوه پیاده‌سازی:
class UsersController extends AdminController implements HasFormStats
{
    public function getFormStats(?\Illuminate\Database\Eloquent\Model $model = null, bool $isEditMode = false): array
    {
        if (!$isEditMode || !$model) {
            return []; // فقط در edit mode نمایش داده می‌شود
        }
        
        return [
            [
                'title' => 'تاریخ عضویت',
                'value' => $model->created_at ? \RMS\Helper\persian_date($model->created_at, 'Y/m/d') : 'نامعلوم',
                'unit' => '',
                'icon' => 'calendar',
                'color' => 'info',
                'colSize' => 'col-md-3',
            ],
            [
                'title' => 'آخرین فعالیت',
                'value' => $model->updated_at ? \RMS\Helper\persian_date($model->updated_at, 'Y/m/d') : 'هرگز',
                'unit' => '',
                'icon' => 'clock',
                'color' => 'warning',
                'colSize' => 'col-md-3',
            ]
        ];
    }
}
```

#### **✨ ویژگی‌های HasFormStats:**
- **📝 فرم محور:** آمار مربوط به رکورد خاص در حال ویرایش
- **🎯 Edit Mode:** فقط در صفحه ویرایش نمایش داده می‌شود
- **🎨 کارت‌های یکسان:** استفاده از همان کامپوننت `statistical-card`
- **📱 Responsive:** سازگار با تمام اندازه صفحه‌ها
- **🌙 Dark Theme:** پشتیبانی کامل
- **⚡ خودکار:** بدون نیاز به تنظیم اضافی در template
- **🔧 منعطف:** امکان نمایش اطلاعات تاریخچه، آمار شخصی، و غیره

### 🔧 **نکات فنی مهم:**
- **GenerateForm trait:** خودکار `$this->model($id)` را صدا می‌زند برای دریافت model
- **Template Integration:** آمار در بالای فرم و زیر عنوان نمایش داده می‌شود
- **Error Handling:** اگر model پیدا نشود، آمار نمایش داده نمی‌شود
- **Performance:** فقط در edit mode کوئری اضافی اجرا می‌شود

### 🎯 **Limitless Collapse System:**
- **✅ صحیح:** `data-card-action="collapse"` - سیستم خودکار Limitless
- **❌ غلط:** `data-bs-toggle="collapse"` - Bootstrap manual که animation آیکون نداره
- **🔄 Auto Animation:** آیکون `ph-caret-down` خودکار با CSS Limitless چرخش می‌کند
- **🎨 Card Structure:** `<div class="collapse show" id="unique-id">` برای محتوای collapse
- **📱 Mobile Compatible:** کاملاً سازگار با نسخه موبایل Limitless

### ⚙️ **StatsCardControl در FormAndList (اتوماتیک در دسترس):**
- از این پس متدهای زیر در تمام کنترلرهایی که از `FormAndList` استفاده می‌کنند در دسترس است:
  - `getStatsCardExpanded(): bool` — وضعیت پیش‌فرض کارت آمار (باز/بسته)
  - `setStatsCardExpanded(bool $expanded): void` — تنظیم دستی وضعیت
  - `collapseStatsCard(): void` — بستن کارت
  - `expandStatsCard(): void` — باز کردن کارت
- حالت پیش‌فرض: باز (true). اگر فیلتر فعال باشد، کارت خودکار باز است.
- می‌توانید فقط با اضافه کردن این متد به کنترلر، کارت را بسته کنید:
```php
public function getStatsCardExpanded(): bool { return false; }
```

---

## 🎁 **AUTO METHODS** - متدهای خودکار و Hookها

### ✨ **CRUD خودکار (از Traits آماده!):**
```php
// 📄 از GenerateList trait:
public function index(Request $request): View       // لیست کامل خودکار

// 🗺️ از GenerateForm trait:
public function create(Request $request): View      // فرم create خودکار
public function edit(Request $request, $id): View   // فرم edit خودکار

// 💾 از StoreAction trait:
public function store(Store $request): RedirectResponse    // ذخیره و validation خودکار
public function update(Request $request, $id): RedirectResponse  // به‌روزرسانی خودکار

// 🗑️ از DeleteAction trait:
public function destroy(Request $request, int|string $id): RedirectResponse  // حذف خودکار
public function batchDestroy(Request $request): RedirectResponse  // حذف گروهی

// 🔘 از BoolAction trait:
public function changeBoolField(Request $request, $id, $field): JsonResponse  // تغییر boolean سریع

// 📄 از ExportList trait:
public function export(): Response                 // خروجی Excel/CSV خودکار

// 🔄 از Sortable trait:
public function sort(Request $request): RedirectResponse  // مرتب‌سازی خودکار
```

### 🌣 **Hook Methods - متدهای Hook برای سفارشی‌سازی:**

```php
// 💾 StoreAction hooks (قبل و بعد ذخیره):
protected function beforeAdd(Request &$request): void       // قبل create - تغییر $request
protected function afterAdd(Request $request, $id, Model $model): void  // بعد create
protected function beforeUpdate(Request &$request, $id): void     // قبل update
protected function afterUpdate(Request $request, $id, Model $model): void // بعد update

// 🗑️ DeleteAction hooks (قبل و بعد حذف):
protected function beforeDestroy(int|string $id): void      // قبل حذف
protected function afterDestroy(int|string $id): void       // بعد حذف
protected function canDelete(int|string $id): bool          // بررسی مجوز حذف

// 🗺️ Form/List Generation hooks:
protected function beforeGenerateForm(FormGenerator &$generator): void    // قبل تولید فرم
protected function beforeGenerateList(ListGenerator &$generator): void    // قبل تولید لیست

// ⭐ beforeSendToTemplate - مهم‌ترین hook برای تغییر داینامیک!
protected function beforeSendToTemplate(array &$templateData, FormResponse $generated): void
```

#### 🎯 **مثال کاربردی beforeSendToTemplate:**
```php
// تغییر داینامیک فیلدها بر اساس create/edit mode:
protected function beforeSendToTemplate(array &$templateData, FormResponse $generated): void
{
    // فراخوانی parent اجباری!
    parent::beforeSendToTemplate($templateData, $generated);
    
    // اگه edit mode باشه (آی‌دی موجود داره):
    if ($generated->getGenerator()->getId()) {
        foreach ($templateData['fields'] as $field) {
            // password را اختیاری کن
            if (in_array($field->key, ['password', 'password_confirmation'])) {
                $field->setRequired(false);
                if ($field->key === 'password') {
                    $field->withHint('خالی بگذارید اگر نمی‌خواهید تغییر دهید');
                }
            }
        }
    }
}
```

---

## 🏗️ **FIELD SYSTEM** - سیستم کامل فیلدها و انواع

### 🎯 **Factory Methods - متدهای تولید فیلد:**

```php
// 🔸 متدهای اصلی:
Field::make(string $key, ?string $database_column = null, bool $method_sql = false)
Field::create(string $key, string $title, ?string $database_column = null)

// 🎭 فیلدهای مخصوص (با قابلیت‌های خودکار!):
Field::string($key, $title = null, $database_column = null)      // متن عادی
Field::number($key, $title = null, $min = null, $max = null)     // عدد
Field::boolean($key, $title = null)                             // بولین
Field::select($key, $title = null, $options = null)             // سلکت
Field::date($key, $title = null)                                // تاریخ (با persian-datepicker)
Field::datetime($key, $title = null)                            // تاریخ و زمان
Field::hidden($key, $value = null)                             // مخفی
Field::price($key, $title = null, $currency = 'تومان')     // مبلغ (با amount-formatter)
Field::image($key, $title = null, $options = [])               // تصویر (با image-uploader)
```

### 🔧 **متدهای تنظیمات مهم:**

```php
// 🏠 تنظیمات پایه:
->withTitle(string $title)                    // عنوان فیلد (فارسی)
->withType(int $type)                        // نوع فیلد (Field::STRING, Field::BOOL, etc.)
->withDefaultValue($value)                   // مقدار پیش‌فرض ⚠️
->customMethod(string $method)               // متد سفارشی برای نمایش
->withAttributes(array $attributes)          // ویژگی‌های HTML
->withHint(string $hint)                     // راهنمایی زیر فیلد

// ✅ Validation (اعتبارسنجی):
->required(array $additionalRules = [])      // اجباری کردن
->optional(array $validationRules = [])      // اختیاری کردن
->setRequired(bool $required)                // تغییر داینامیک اجباری بودن
->withValidation(array $rules)               // قوانین Laravel validation

// 📄 برای لیست:
->sortable(bool $sortable = true)            // قابل مرتب‌سازی
->searchable(bool $searchable = true)        // قابل جستجو
->filterable(bool $filterable = true)        // قابل فیلتر
->width(string $width)                       // عرض ستون ('100px', '20%')
->hiddenInExport()                          // مخفی در Excel export

// 🗒 برای SELECT فیلدها:
->setOptions(array $options)                 // ❗ اجباری برای filterType(Field::SELECT)
->advanced()                                 // فعال‌سازی Enhanced Select
->multipleSelect()                           // انتخاب چندگانه
->ajax(string $url)                          // AJAX Select
->creatable()                               // قابلیت ایجاد آیتم جدید
```

### 😨 **Field نکات مهم و قوانین:**

1. **📅 پارامتر دوم `database_column`** - فقط وقتی نام فیلد با نام ستون فرق دارد!
2. **📄 `type()`** برای نمایش در جدول: `Field::STRING`, `Field::BOOL`, `Field::DATE_TIME`
3. **🔍 `filterType()`** برای نوع فیلتر: `Field::SELECT`, `Field::BOOL`, `Field::DATE_TIME`
4. **❗ وقتی `filterType(Field::SELECT)` → `setOptions()` اجباری!**
5. **🎭 برای custom display:** `->customMethod('getMethodName')` (متد در model)
6. **📝 برای اکثر فیلدها:** `Field::make('field_name')->withTitle('...')`
7. **⚠️ فقط `withDefaultValue()` نه `withDefault()`** (نام صحیح!)

### 🎆 **نمونه‌های کاربردی صحیح:**

```php
// 🗒 SELECT فیلد با فیلتر:
Field::make('group_id')->withTitle('گروه کاربری')
    ->type(Field::STRING)                    // برای نمایش در جدول
    ->filterType(Field::SELECT)              // فیلتر SELECT
    ->filterable()                           // فعال کردن فیلتر
    ->setOptions([                           // ❗ اجباری!
        1 => 'مدیران',
        2 => 'کارکنان',
        3 => 'مشتریان',
        4 => 'مهمان'
    ])
    ->customMethod('renderUserGroup'),        // نمایش custom

// 💰 PRICE فیلد:
Field::price('amount', 'مبلغ', 'amount')
    ->required()
    ->withHint('مبلغ به تومان'),

// 📅 DATE فیلد:
Field::date('birth_date', 'تاریخ تولد')
    ->optional()
    ->withHint('فرمت: ۱۴۰۴/۰۴/۲۹'),

// 🔘 BOOLEAN فیلد:
Field::boolean('active', 'فعال')
    ->withDefaultValue(true)
```

---

## 🎯 **CONTROLLER CREATION SYSTEM** - سیستم ساخت کنترلر

### 🚨 **قبل از شروع:** 
📄 **همیشه `CREATE_CONTROLLER.md` رو بخون!** هیچ کنترلری بدون بررسی این فایل نساز!

⚠️ **هشدار مهم:** متدهای زیر در کلاس Field وجود ندارند:
- `enableBoolAction()` ❌
- `filterable()` ❌

در عوض از `setOptions([])` و `advanced()` استفاده کن!

🚨 **قانون طلایی - هیچ وقت فراموش نکن:**
**هرگز وسط کد متن فارسی ننویس!**
- ✅ درست: `Field::string('name', trans('admin.users.fields.name'))`
- ❌ نادرست: `Field::string('name', 'نام')`
- ✅ درست: `StatCard::make(trans('admin.users.stats.total'), $total)`
- ❌ نادرست: `StatCard::make('مجموع', $total)`

**همه ترجمه‌ها توی `resources/lang/fa/admin.php` باشن!**

### 📦 **مراحل سریع (Quick Steps):**
1. **سوالات ابتدایی**: کدام Interface ها نیازه?
2. **بررسی Model**: وجود دارد یا نه?
3. **بررسی Migration**: جدول مهیاست یا نه?
4. **ساخت Controller**: با template استاندارد
5. **ثبت Routes**: RouteHelper + Resource
6. **اضافه Menu**: به sidebar

### ✅ **Interface های پیش‌فرض (همیشه اضافه کن):**
- `HasList` - لیست رکوردها
- `HasForm` - فرم ایجاد/ویرایش  
- `ShouldFilter` - فیلتر و جستجو

### ❓ **Interface های اختیاری (از کاربر بپرس):**
- `HasStats` - کارت‌های آمار در لیست
- `ChangeBoolField` - تغییر فیلدهای boolean (active/published)
- `ShouldExport` - خروجی Excel
- `HasBatch` - عملیات گروهی
- `HasUploadConfig` - آپلود فایل/تصویر
- `HasFormStats` - آمار در فرم ویرایش

### 📝 **نکات مهم برای Controller:**
- **Model Name**: مفرد (User, Product, Category)
- **Table Name**: جمع (users, products, categories)  
- **Route Name**: جمع (users, products, categories)
- **Route Parameter**: مفرد (user, product, category)
- **همیشه Field Types رو از CREATE_CONTROLLER.md کپی کن!**

### 🚀 **توابع اجباری در هر Controller:**
```php
// ✅ اجباری برای همه:
public function table(): string              // 'نام جدول'
public function modelName(): string          // ModelName::class
public function baseRoute(): string          // 'نام route' (بدون admin.)
public function routeParameter(): string     // 'پارامتر' (مفرد)
public function getFieldsForm(): array       // فیلدهای فرم
public function getListFields(): array       // فیلدهای لیست
public function rules(): array               // قوانین validation

// ❓ اختیاری (بر اساس interface):
public function getStats(?Builder $query = null): array  // اگر HasStats
public function boolFields(): array          // اگر ChangeBoolField
// getListConfig() فقط برای override - معمولاً نیاز نیست!
```

### 🚫 **چیزهایی که هرگز فراموش نکن:**
1. **Field Types**: همیشه از CREATE_CONTROLLER.md کپی کن
2. **Validation Rules**: باید با فیلدهای فرم match باشه
3. **Interface Signatures**: باید دقیقاً طبق CREATE_CONTROLLER.md باشه
4. **getTable() در Model**: باید تعریف شه
5. **Route Registration**: هم RouteHelper هم Resource لازمه
6. **Menu Integration**: بعد از Controller حتماً اضافو کن

---

### 🗺️ **ROUTEHELPER SYSTEM** - سیستم کامل مسیریابی RMS

> الگوی پیشنهادی برای هر کنترلر (All-in-One):
```php
use RMS\Core\Helpers\RouteHelper;

RouteHelper::adminResource(
    App\Http\Controllers\Admin\{ModelName}Controller::class,
    '{route_name}',
    [
        'export' => true,
        'sort' => true,
        'filter' => true,
        'toggle_active' => true,            // اگر ChangeBoolField دارید
        'batch_actions' => ['delete'],      // در صورت نیاز
        'ajax_files' => [],
        'image_viewer' => false,
    ]
);

Route::resource('{route_name}', {ModelName}Controller::class);
```

### 🎯 **RouteHelper Overview - نمای کلی:**

RouteHelper کلاس قدرتمندی است که ثبت route های استاندارد admin panel را آسان می‌کند.
**مسیر:** `vendor/rmscms/core/src/Helpers/RouteHelper.php`

#### ✨ **قابلیت‌های اصلی:**
- **📤 Export Routes** - خروجی Excel/CSV خودکار
- **🔄 Sort Routes** - مرتب‌سازی قابل تنظیم
- **🔍 Filter Routes** - فیلترگذاری پیشرفته
- **🔘 Boolean Toggle** - تغییر سریع فیلدهای boolean
- **📦 Batch Actions** - عملیات گروهی (حذف، فعال/غیرفعال)
- **📷 AJAX File Upload** - آپلود فایل بدون refresh
- **👁️ Image Viewer** - نمایش تصاویر در modal
- **🗑️ Cache Management** - مدیریت کش‌ها

### 🔧 **متدهای اصلی RouteHelper:**

#### **📤 Export Routes:**
```php
// ثبت route خروجی Excel
RouteHelper::export($controller, 'admin.users'); 
// ایجاد می‌کند: GET /admin/users/export → controller@export

// سفارشی‌سازی
RouteHelper::export($controller, 'admin.users', 'post', 'customExport');
```

#### **🔄 Sort Routes:**
```php
// ثبت route مرتب‌سازی
RouteHelper::sort($controller, 'admin.users');
// ایجاد می‌کند: GET /admin/users/sort/{by}/{way} → controller@sort
// مثال: /admin/users/sort/name/asc
```

#### **🔍 Filter Routes:**
```php
// ثبت route فیلتر
RouteHelper::filter($controller, 'admin.users');
// ایجاد می‌کند: POST /admin/users/filter → controller@filter

// ثبت route پاک کردن فیلتر
RouteHelper::clearFilter($controller, 'admin.users');
// ایجاد می‌کند: GET /admin/users/clear-filter → controller@clearFilter
```

#### **🔘 Boolean Toggle Routes:**
```php
// ثبت route تغییر فیلد boolean
RouteHelper::toggleField($controller, 'admin.users', 'active');
// ایجاد می‌کند: POST /admin/users/{user}/toggle/active → controller@toggleBoolField

// روش قدیمی (سازگار با کد موجود)
RouteHelper::active($controller, 'admin.users');
// ایجاد می‌کند: همان toggle routes برای فیلد 'active'
```

#### **📦 Batch Action Routes:**
```php
// یک عملیات گروهی
RouteHelper::batchAction($controller, 'admin.users', 'delete');
// ایجاد می‌کند: POST /admin/users/batch/delete → controller@batchDelete

// چندین عملیات گروهی
RouteHelper::batchActions($controller, 'admin.users', ['delete', 'activate', 'deactivate']);
// ایجاد می‌کند:
// POST /admin/users/batch/delete → controller@batchDelete
// POST /admin/users/batch/activate → controller@batchActivate  
// POST /admin/users/batch/deactivate → controller@batchDeactivate
```

#### **📷 AJAX File Routes:**
```php
// آپلود فایل AJAX
RouteHelper::ajaxFileUpload($controller, 'admin.users', 'avatar');
// ایجاد می‌کند: POST /admin/users/{user}/ajax-upload/{avatar} → controller@ajaxUpload

// حذف فایل AJAX
RouteHelper::ajaxFileDelete($controller, 'admin.users', 'avatar');
// ایجاد می‌کند: DELETE /admin/users/{user}/ajax-delete/{avatar} → controller@ajaxDeleteFile

// چندین فیلد فایل
RouteHelper::ajaxFileRoutes($controller, 'admin.users', ['avatar', 'gallery', 'documents']);
```

#### **👁️ Image Viewer Route:**
```php
// نمایش تصاویر در modal
RouteHelper::imageViewer($controller, 'admin.users');
// ایجاد می‌کند: GET /admin/users/{user}/image-viewer/{field} → controller@handleImageViewer
```

### 🚀 **adminResource - متد All-in-One:**

#### **🎯 روش توصیه شده استفاده:**
```php
// در admin.php routes
RouteHelper::adminResource(
    App\Http\Controllers\Admin\AccountsController::class,
    'accounts',
    [
        'export' => true,                    // فعال‌سازی export
        'sort' => true,                      // فعال‌سازی sort
        'filter' => true,                    // فعال‌سازی filter + clearFilter
        'toggle_active' => true,             // فعال‌سازی toggle برای 'active'
        'batch_actions' => ['delete', 'activate', 'deactivate'], // عملیات گروهی
        'ajax_files' => ['avatar', 'gallery'], // فایل‌های AJAX
        'image_viewer' => true,              // نمایش تصاویر در modal
    ]
);

// همچنین Route::resource اجباری
Route::resource('accounts', AccountsController::class);
```

#### **🧠 ویژگی‌های هوشمند adminResource:**
- **🔍 Auto-Detection:** اگر controller interface `ChangeBoolField` را implement کند، تمام boolean fields خودکار register می‌شوند
- **🔗 Smart Naming:** نام‌گذاری خودکار routes بر اساس الگوی استاندارد
- **⚙️ Flexible Options:** هر قابلیت قابل فعال/غیرفعال کردن
- **🔄 Backward Compatible:** سازگار با کد‌های موجود

### 🗑️ **Cache Management Routes:**

#### **🔧 نحوه استفاده:**
```php
// ثبت route های مدیریت کش
RouteHelper::adminCacheRoutes(
    \RMS\Core\Http\Controllers\Admin\CacheManagerController::class,
    'admin.cache'
);
```

#### **📋 Route های ایجاد شده:**
- `POST /admin/cache/clear` → clearAll (پاک کردن همه کش‌ها)
- `POST /admin/cache/clear/{type}` → clearSpecific (پاک کردن نوع خاص)
- `GET /admin/cache/status` → status (وضعیت کش‌ها)
- `GET /admin/cache/stats` → stats (آمار کش‌ها)

#### **🎯 انواع کش پشتیبانی شده:**
- `application` - کش اپلیکیشن Laravel
- `config` - کش تنظیمات
- `route` - کش route ها
- `view` - کش view ها
- `optimize` - کش بهینه‌سازی
- `opcache` - PHP OPcache

### 🔧 **Validation و خطایابی:**

RouteHelper تمام پارامترها را validate می‌کند:

#### **✅ Controller Validation:**
```php
// ✅ صحیح - string class name
RouteHelper::export('App\\Controllers\\UsersController', 'admin.users');

// ✅ صحیح - array [class, method]
RouteHelper::export(['App\\Controllers\\UsersController', 'customExport'], 'admin.users');

// ❌ غلط - فرمت نامعتبر
RouteHelper::export(123, 'admin.users'); // InvalidArgumentException
```

#### **✅ Route Name Validation:**
```php
// ✅ صحیح
'admin.users', 'users', 'admin.user-profiles'

// ❌ غلط
'', 'admin/users/', 'admin users' // InvalidArgumentException
```

#### **✅ HTTP Method Validation:**
```php
// ✅ صحیح
'get', 'post', 'put', 'patch', 'delete'

// ❌ غلط
'GET', 'POST', 'custom' // InvalidArgumentException
```

### 💡 **نکات مهم و Best Practices:**

#### **🎯 نام‌گذاری Route:**
```php
// RouteHelper از آخرین بخش route name استفاده می‌کند
'admin.users' → path: 'users'
'users' → path: 'users'
'admin.user-profiles' → path: 'user-profiles'
```

#### **⚡ Controller Method Naming:**
```php
// روش خودکار نام‌گذاری متدها:
batchDelete($request)     // برای batch action 'delete'
batchActivate($request)   // برای batch action 'activate' 
batchDeactivate($request) // برای batch action 'deactivate'

toggleBoolField($request, $id) // برای toggle fields
ajaxUpload($request)     // برای AJAX file upload
ajaxDeleteFile($request) // برای AJAX file delete
```

#### **🔄 کاربرد در کنترلر:**
```php
class AccountsController extends AdminController 
{
    // این متدها خودکار از traits آمده - تعریف نکن!
    // public function export() // ← از ExportList trait
    // public function sort() // ← از Sortable trait  
    // public function filter() // ← از FilterList trait
    // public function toggleBoolField() // ← از BoolAction trait
    // public function batchDelete() // ← از DeleteAction trait
    
    // فقط interface ها implement کن:
    public function boolFields(): array {
        return ['active', 'featured']; // فیلدهای boolean
    }
    
    public function getBatchActions(): array {
        return ['delete', 'activate']; // عملیات گروهی
    }
}
```

---

## 🔧 **DEBUG SYSTEM** - سیستم دیباگ پیشرفته

### 🚀 **نحوه استفاده - گام به گام:**
1. **فعال‌سازی:** برو `http://localhost/admin/users/create?debug=1` یا `edit/1?debug=1`
2. **تولید دیتا:** صفحه را بارگذاری کن تا debug data ساخته شود  
3. **مشاهده دیتا:** برو `http://localhost/admin/debug/export?format=json`
4. **Debug Panel:** در browser console: `window.rmsDebugPanel.togglePanel()`
5. **میانبر کیبورد:** `Ctrl+Shift+D` برای فعال/غیرفعال

### 📊 **Debug Data و Log System:**
- **📅 مسیر logs:** `storage/logs/rms_debug/rms_system-YYYY-MM-DD.log`
- **📊 شامل:** Form Analysis, Field Issues, Performance, Memory, Database Queries
- **⚠️ اگر export خالیه** → log ها پاک شدن یا هنوز form debug نشده
- **🔄 تکرار محتوا** → debug در controller methods فعال نیست

### 🆕 **Virtual Fields Support - پشتیبانی فیلدهای مجازی:**
- **🎯 مشکل:** فیلدهای IMAGE/FILE که ستون دیتابیسی ندارند باعث خطا می‌شدند
- **✅ راه‌حل:** قابلیت `skipDatabase()` اضافه شد به کلاس Field
- **🔧 کاربرد:**
  ```php
  // فیلدهای مجازی که نباید به دیتابیس ارسال شوند:
  Field::image('avatar')->skipDatabase()        // فیلد تصویر
  Field::make('calculated_field')->virtual()    // فیلد محاسباتی
  Field::file('attachment')->skipDatabase()      // فایل آپلود
  ```
- **⚡ خودکار:** فیلدهای `Field::image()` به صورت پیش‌فرض `skipDatabase()` فعال دارند
- **📍 تأثیر:** این فیلدها در Form نمایش داده می‌شوند اما در عملیات دیتابیس (save/load/query) نادیده گرفته می‌شوند
- **🔍 Debug:** در Debug Panel این فیلدها با علامت "virtual" مشخص می‌شوند

### 💻 **Debug Panel UI - رابط کاربری بهبود یافته:**

#### **🎆 ویژگی‌های جدید v2.0.0:**
- **🌨️ Limitless Template Integration** - HTML و CSS کاملاً مطابق قالب
- **🌙 Dark/Light Theme Support** - پشتیبانی کامل تم تیره/روشن
- **📱 Responsive Design** - سازگار با همه اندازه صفحه‌ها
- **🎯 Enhanced UX** - انیمیشن‌ها، ترنزیشن‌ها، میکرو انترکشن
- **🔍 Field Filters** - فیلتر فقط فیلدهای مشکل‌دار

#### **🔑 راه‌های فعال‌سازی:**
- **⌨️ Keyboard:** `Ctrl+Shift+D`
- **🔗 URL Parameter:** `?debug=1`
- **💻 JavaScript:** `window.rmsDebugPanel.togglePanel()` یا `showDebugPanel()`
- **💾 Session:** `rms_debug_enabled = true`
- **🎯 Auto-Show:** خودکار با ?debug=1 فعال می‌شود

#### **📁 تب‌های بهبود یافته:**
1. **نمای کلی (Overview)** - Session info + Performance summary
2. **تحلیل فرم (Form Analysis)** - اطلاعات کامل + validation rules
3. **فیلدها (Fields)** - فیلتر مشکل‌دار + جزئیات کامل + ✅ **مقادیر واقعی**
4. **عملکرد (Performance)** - جزئیات زمان و حافظه
5. **پایگاه داده (Database)** - ✅ **همه کوئری‌ها + مقادیر bindings** + کند + تکراری
6. **حافظه (Memory)** - Memory timeline + checkpoints
7. **گزارش‌ها (Logs)** - فیلتر پیشرفته + جستجو

### 💾 **Enhanced Database Analysis v2.0:**
- **✅ همه کوئری‌ها:** نمایش لیست کامل تمام کوئری‌های اجرا شده
- **✅ مقادیر واقعی:** کوئری‌ها با مقادیر bindings جایگذاری شده نمایش داده می‌شوند
- **✅ کوئری‌های فرمت شده:** `SELECT * FROM users WHERE id = 123` به جای `SELECT * FROM users WHERE id = ?`
- **🚀 عملکرد:** زمان اجرا + timestamp + تشخیص کوئری‌های کند/تکراری
- **📊 آمار:** کل کوئری‌ها + زمان کل + تعداد کند/تکراری

### 📋 **Enhanced Field Analysis v2.0:**
- **✅ مقادیر فعلی:** نمایش مقادیر فیلدها بعد از اعمال `beforeSendToTemplate`
- **✅ فرمت متنوع:** بولین, تاریخ, JSON, ایمیل, فایل, تصویر
- **✅ فیلدهای مجازی:** فیلدهای `skipDatabase()` با علامت "virtual" نمایش داده می‌شوند
- **✅ جزئیات کامل:** validation rules + options + placeholder + help text + unique constraints

#### **🔧 عملیات جدید:**
- **♾️ تازه‌سازی:** دکمه Refresh برای بارگذاری مجدد
- **📥 خروجی:** Export داده‌ها به فرمت JSON
- **🗑️ پاک کردن:** Clear همه debug data
- **🔍 فیلترها:** سطح، دسته، جستجو در logs
- **🔍 Issues Filter:** نمایش فقط فیلدهای مشکل‌دار

### 🛠️ **Debug System Installation - نصب سیستم دیباگ:**

#### ♾️ **قدم ۱: فعال‌سازی در bootstrap/app.php**
```php
// ✅ اضافه کردن Debug Middleware به گروه web
->withMiddleware(function (Middleware $middleware): void {
    $middleware->group('web', [
        \RMS\Core\Http\Middleware\DebugMiddleware::class,
    ]);
})
```

#### ♾️ **قدم ۲: تنظیمات config**
```php
// config/app.php
'debug' => env('APP_DEBUG', false), // ✅ باید true باشد

// .env
APP_DEBUG=true
RMS_DEBUG_ENABLED=true
```

#### ♾️ **قدم ۳: ایجاد پوشه logs**
```bash
# ایجاد پوشه مخصوص debug logs
mkdir -p storage/logs/rms_debug
chmod 755 storage/logs/rms_debug
```

#### ♾️ **قدم ۴: فعال‌سازی در Controller**
```php
class YourController extends AdminController {
    use DebugPanel; // ✅ اضافه کردن trait
    
    public function __construct(\Illuminate\Filesystem\Filesystem $filesystem) {
        parent::__construct($filesystem);
        $this->initializeDebugger(); // ✅ فعال‌سازی
    }
}
```

#### ♾️ **قدم ۵: تست عملکرد**
- برو `http://your-project.test/admin/users/create?debug=1`
- فشار `Ctrl+Shift+D` برای باز کردن Debug Panel
- بررسی لاگ‌ها: `/admin/debug/export?format=json`
- بررسی فایل‌ها: `storage/logs/rms_debug/`

#### ⚠️ **نکات مهم:**
- **🔄 همه کوئری‌ها:** سیستم همه queries را از ابتدای درخواست ثبت می‌کند
- **💾 Virtual Fields:** فیلدهای IMAGE/FILE خودکار `skipDatabase()` دارند
- **📝 Log Files:** هر روز فایل جدید برای لاگ‌ها ایجاد می‌شود
- **🔍 Auto-Clean:** فایل‌های قدیمی (بیش از ۳۰ روز) خودکار پاک می‌شوند

---

## 🗺 **LIMITLESS TEMPLATE** - قالب مرجع

### 📁 **مسیرهای مرجع قالب:**
1. **مرجع اصلی:** `C:/laragon/www/rms2/limitless-template-full/` - نسخه کامل خریداری شده ✅
2. **مرجع کامل:** `LIMITLESS_TEMPLATE_REFERENCE.md` - راهنمای کامل تمام فایل‌های HTML و کامپوننت‌ها

### 🎯 **قوانین استفاده از Limitless:**
1. **هرگز از خودت کد ننویس** - همیشه از `limitless-template-full/` استفاده کن
2. **تطبیق کامل:** ساختار HTML، CSS و JS باید دقیقاً مطابق Limitless باشد
3. **فقط داده‌ها عوض کن:** فقط محتوا و داده‌ها را تغییر بده، نه ساختار
4. **مرجع کامل:** اگه پیدا نکردی، در `LIMITLESS_TEMPLATE_REFERENCE.md` جستجو کن

---

## 📅 **RMS HELPER PACKAGE** - پکیج ابزارهای کمکی

### 🔧 **پکیج rmscms/helper:**
- 📅 **تاریخ شمسی** - تبدیل و فرمت خودکار در لیست‌ها
- 💰 **مالی** - فرمت مبلغ و ارقام فارسی
- 🗺 **Excel** - ایمپورت/اکسپورت آسان

### 📊 **استفاده خودکار در لیست‌ها:**
```php
// فیلدهای date و date_time خودکار به فارسی تبدیل می‌شن:
Field::make('created_at')->withTitle('تاریخ ایجاد')
    ->type(Field::DATE_TIME)  // ← خودکار به 1404/04/29 14:30

Field::make('birth_date')->withTitle('تاریخ تولد')
    ->type(Field::DATE)       // ← خودکار به 1404/04/29
```

---

## 🗺 **LIMITLESS TEMPLATE** - قالب مرجع اصلی

### 📁 **مسیرهای مرجع قالب:**
1. **مرجع اصلی:** `limitless-template-full/` - نسخه کامل خریداری شده ✅
2. **مرجع کامل:** `LIMITLESS_TEMPLATE_REFERENCE.md` - راهنمای کامل HTML فایلها

### 🎯 **قوانین استفاده از Limitless:**
1. **هرگز از خودت کد ننویس** - همیشه از `limitless-template-full/` استفاده کن
2. **تطبیق کامل:** HTML، CSS و JS باید دقیقاً مطابق Limitless باشد
3. **فقط داده‌ها عوض کن:** فقط محتوا و داده‌ها
4. **مرجع کامل:** اگه پیدا نکردی، در `LIMITLESS_TEMPLATE_REFERENCE.md` جستجو کن

---

## 🎨 **UI COMPONENTS** - کامپوننت‌های رابط کاربری

### 📊 **Statistical Card Component - کامپوننت کارت آماری:**

#### 🎯 **مسیر:** `packages/rms/core/resources/views/admin/components/statistical-card.blade.php`

#### 📝 **نحوه استفاده:**
```blade
{{-- در Blade Template --}}
@include('cms::admin.components.statistical-card', [
    'title' => 'مجموع کاربران',
    'value' => '1,234',
    'unit' => 'نفر',
    'icon' => 'users',
    'color' => 'primary', // primary, success, warning, danger, info
    'colSize' => 'col-xl-3 col-md-6', // Bootstrap grid classes
    'description' => 'بر اساس فیلتر فعال' // اختیاری
])
```

#### ✨ **ویژگی‌ها:**
- **🎨 طراحی Limitless:** کاملاً مطابق قالب Limitless
- **🌙 Dark Theme:** پشتیبانی کامل تم تیره/روشن
- **📱 Responsive:** سازگار با تمام اندازه صفحه‌ها
- **🎨 رنگ‌های گوناگون:** primary, success, warning, danger, info
- **🔄 Grid منعطف:** پشتیبانی از Bootstrap Grid (col-xl-3, col-md-6, و غیره)
- **📝 توضیح اختیاری:** نمایش توضیح اضافی زیر آمار

#### 🎯 **نمونه کاربردی و متنوع:**
```php
// در HasStats Interface
return [
    // کارت آبی (رنگ اصلی)
    [
        'title' => 'مجموع کاربران',
        'value' => number_format(1234),
        'unit' => 'نفر',
        'icon' => 'users',
        'color' => 'primary',
        'colSize' => 'col-xl-3 col-md-6'
    ],
    // کارت سبز (موفقیت)
    [
        'title' => 'کاربران فعال',
        'value' => number_format(987),
        'unit' => 'نفر',
        'icon' => 'user-check',
        'color' => 'success',
        'colSize' => 'col-xl-3 col-md-6',
        'description' => 'فعال در 30 روز گذشته'
    ],
    // کارت زرد (هشدار)
    [
        'title' => 'نیاز به بررسی',
        'value' => number_format(23),
        'unit' => 'نفر',
        'icon' => 'alert-triangle',
        'color' => 'warning',
        'colSize' => 'col-xl-3 col-md-6'
    ],
    // کارت قرمز (خطر)
    [
        'title' => 'مسدود شده',
        'value' => number_format(5),
        'unit' => 'نفر',
        'icon' => 'user-x',
        'color' => 'danger',
        'colSize' => 'col-xl-3 col-md-6'
    ]
];
```

#### 🔧 **تنظیمات CSS:**
- **پایه:** Bootstrap 5 Cards
- **آیکون‌ها:** PhosporIcons یا Feather Icons
- **Dark Mode:** از `[data-color-theme="dark"]` استفاده می‌کند
- **رنگ‌ها:** مطابق Bootstrap 5 Color System

### 🌙 **Theme Switcher:**
- **مکان:** navbar کنار notification
- **حالت‌ها:** Light, Dark, Auto
- **ذخیره:** localStorage (`admin-theme`)
- **کنترل:** `window.themeSwitcher.toggle()`
- **Auto:** تشخیص خودکار بر اساس سیستم

### 🧹 **Cache Management:**
- **میانبر:** `Ctrl+Shift+C`
- **API:** `window.rmsCacheManager.clearAllCache()`
- **قابلیت‌ها:** پاک کردن تمام کش‌ها، نمایش وضعیت

### 📱 **MENU COMPONENTS** - کامپوننت‌های منو

#### 🎯 **کامپوننت‌های منو RMS (مسیر: `packages/rms/core/resources/views/components/`):**

##### **1️⃣ Menu Header Component - سرتیتر منو:**
```blade
{{-- استفاده در sidebar --}}
<x-cms::menu-header title="مین" />
<x-cms::menu-header title="{{ trans('auth.system') ?: 'سیستم' }}" />
```

**✨ ویژگی‌ها:**
- **🎨 طراحی Limitless:** کاملاً مطابق قالب
- **🌙 Dark Theme:** پشتیبانی کامل
- **📏 Responsive:** نمای compact در sidebar کوچک

##### **2️⃣ Menu Item Component - آیتم منوی ساده:**
```blade
{{-- منوی ساده با آیکون و توضیح --}}
<x-cms::menu-item 
    title="داشبورد"
    url="/admin"
    icon="ph-house"
    :routes="['admin.dashboard']"
    urlPattern="admin"
    description="صفحه اصلی مدیریت"
/>

{{-- منو با badge --}}
<x-cms::menu-item 
    title="کاربران"
    url="/admin/users"
    icon="ph-users"
    :badge="$totalUsers"
    badgeColor="text-muted"
    iconColor="primary"
    :routes="['admin.users.*']"
/>
```

**🔧 پارامترهای کامل:**
- `title` - عنوان منو (اجباری)
- `url` - آدرس لینک (پیش‌فرض: #)
- `icon` - کلاس آیکون PhosphorIcons (اختیاری)
- `iconColor` - رنگ آیکون (success, danger, primary، ...)
- `badge` - متن badge (اختیاری)
- `badgeColor` - رنگ badge (پیش‌فرض: bg-primary)
- `description` - توضیح زیر عنوان (اختیاری)
- `routes` - آرایه نام route ها برای تشخیص active
- `urlPattern` - الگوی URL برای تشخیص active

**🎯 تشخیص هوشمند Active:**
- بررسی URL مطابقت
- بررسی نام route ها
- بررسی الگوی URL pattern
- کلاس `active` خودکار اعمال می‌شود

##### **3️⃣ Submenu Item Component - منوی دو سطحه:**
```blade
{{-- منوی با زیرمنو --}}
@php
    $usersChildren = [
        [
            'title' => 'لیست کاربران',
            'url' => '/admin/users',
            'icon' => 'ph-list',
            'routes' => ['admin.users.index'],
            'urlPattern' => 'admin/users',
            'badge' => $totalUsers > 0 ? (string)$totalUsers : null,
            'badgeColor' => 'text-muted'
        ],
        [
            'title' => 'کاربر جدید',
            'url' => '/admin/users/create',
            'icon' => 'ph-user-plus',
            'routes' => ['admin.users.create'],
        ],
        ['divider' => true], // جداکننده
        [
            'title' => 'کاربران فعال',
            'url' => '/admin/users?filter_active=1',
            'icon' => 'ph-check-circle',
            'iconColor' => 'success',
            'badge' => (string)$activeUsers,
            'badgeColor' => 'text-success'
        ]
    ];
@endphp

<x-cms::submenu-item 
    title="کاربران"
    icon="ph-users"
    :badge="$totalUsers > 0 ? (string)$totalUsers : null"
    :children="$usersChildren"
/>
```

**🔧 پارامترهای والد:**
- `title` - عنوان منوی اصلی (اجباری)
- `icon` - آیکون والد (اجباری)
- `iconColor` - رنگ آیکون والد
- `badge` - badge روی منوی والد
- `badgeColor` - رنگ badge والد
- `children` - آرایه زیرمنوها (اجباری)

**👶 پارامترهای فرزند (در آرایه children):**
- `title` - عنوان زیرمنو
- `url` - آدرس لینک
- `icon` - آیکون زیرمنو (اختیاری)
- `iconColor` - رنگ آیکون
- `badge` - badge زیرمنو
- `badgeColor` - رنگ badge
- `routes` - آرایه route names
- `urlPattern` - الگوی URL
- `description` - توضیح اضافی
- `divider` - true برای خط جداکننده

**🧠 ویژگی‌های هوشمند:**
- **Auto Expand:** اگر هر زیرمنو active باشد، منوی والد باز می‌شود
- **Parent Highlight:** والد نیز کلاس `active` می‌گیرد
- **Bootstrap Collapse:** استفاده از سیستم collapse برای انیمیشن
- **Smart Detection:** تشخیص active به روش‌های مختلف

#### 🌟 **مزایای کامپوننت‌های منو:**

✅ **قابل استفاده مجدد:** در admin و user area قابل استفاده
✅ **هوشمند:** تشخیص خودکار حالت active
✅ **انعطاف‌پذیر:** پشتیبانی از انواع badge، icon، description
✅ **Limitless Compatible:** کاملاً مطابق طراحی قالب
✅ **Dark Theme:** پشتیبانی کامل
✅ **RTL Ready:** سازگار با فارسی
✅ **Performance:** بهینه‌سازی شده برای سرعت

#### 🚀 **نمونه کاربرد کامل در Sidebar:**
```blade
{{-- سرتیتر اصلی --}}
<x-cms::menu-header title="مدیریت" />

{{-- منوی ساده --}}
<x-cms::menu-item 
    title="داشبورد"
    url="/admin"
    icon="ph-house"
    :routes="['admin.dashboard']"
    description="صفحه اصلی"
/>

{{-- منوی با زیرمنو --}}
<x-cms::submenu-item 
    title="کاربران"
    icon="ph-users"
    :badge="$totalUsers"
    :children="$usersChildren"
/>

{{-- سرتیتر جدید --}}
<x-cms::menu-header title="سیستم" />

{{-- منوهای سیستم... --}}
```

#### 🛠️ **نکات فنی مهم:**
1. **namespace:** همه کامپوننت‌ها با `cms::` شروع می‌شوند
2. **مسیر:** در `packages/rms/core/resources/views/components/`
3. **ثبت:** در `CoreServiceProvider` با `anonymousComponentPath`
4. **سازگاری:** Bootstrap 5 + PhosphorIcons
5. **عملکرد:** تشخیص active به روش‌های مختلف (URL، Route، Pattern)

### 🍯 **SweetAlert2 Usage - نحوه صحیح استفاده:**

#### **🎯 روش اول: مستقیم با Swal (پیشنهادی)**
```javascript
// ✅ Confirm Dialog - پیام تأیید (برای حذف و عملیات مهم)
Swal.fire({
    title: 'حذف فایل فعلی',
    text: 'آیا مطمئن هستید که می‌خواهید این فایل را حذف کنید؟',
    html: '<p>آیا مطمئن هستید که می‌خواهید <strong>"نام فایل"</strong> را حذف کنید؟</p><p class="text-muted small">این عملیات غیرقابل بازگشت است.</p>',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'بله، حذف کن',
    cancelButtonText: 'انصراف',
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-danger',
        cancelButton: 'btn btn-secondary'
    },
    allowOutsideClick: true,
    allowEscapeKey: true,
    reverseButtons: true,
    focusCancel: true
}).then((result) => {
    if (result.isConfirmed) {
        // کاربر تأیید کرد
        console.log('✅ User confirmed');
        // انجام عملیات...
    } else {
        // کاربر انصراف داد
        console.log('❌ User cancelled');
    }
});

// ✅ Success Message - پیام موفقیت
Swal.fire({
    title: 'حذف موفق',
    text: 'فایل با موفقیت حذف شد.',
    icon: 'success',
    timer: 2000,
    timerProgressBar: true,
    showConfirmButton: false,
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-success'
    },
    allowOutsideClick: true,
    allowEscapeKey: true
});

// ✅ Error Message - پیام خطا
Swal.fire({
    title: 'خطا در حذف',
    text: 'خطایی رخ داده است',
    icon: 'error',
    confirmButtonText: 'تأیید',
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-primary'
    },
    allowOutsideClick: true,
    allowEscapeKey: true
});

// ✅ Info Message - پیام اطلاعاتی
Swal.fire({
    title: 'توجه',
    text: 'این عملیات ممکن است زمان‌بر باشد',
    icon: 'info',
    confirmButtonText: 'متوجه شدم',
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-info'
    }
});

// ✅ Warning Message - پیام هشدار
Swal.fire({
    title: 'هشدار',
    text: 'لطفاً فایل‌های مهم را پشتیبان‌گیری کنید',
    icon: 'warning',
    confirmButtonText: 'متوجه شدم',
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-warning'
    }
});
```

#### **🎯 روش دوم: با Mixin (برای استفاده مکرر)**
```javascript
// تعریف mixin برای استفاده مکرر
const swalInit = Swal.mixin({
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-light'
    },
    allowOutsideClick: true,
    allowEscapeKey: true,
    reverseButtons: true
});

// استفاده از mixin
swalInit.fire({
    title: 'پیام تست',
    text: 'این یک پیام تست است',
    icon: 'info'
});
```

#### **⚠️ نکات مهم:**

1. **✅ همیشه استفاده کنید:**
   - `buttonsStyling: false` - برای استفاده از کلاس‌های Bootstrap
   - `allowOutsideClick: true` - برای بستن با کلیک بیرون
   - `allowEscapeKey: true` - برای بستن با ESC
   - `customClass` - برای استایل‌های Bootstrap

2. **🎨 کلاس‌های دکمه:**
   - `btn btn-primary` - دکمه اصلی
   - `btn btn-danger` - حذف/خطرناک
   - `btn btn-success` - موفقیت
   - `btn btn-secondary` - انصراف
   - `btn btn-warning` - هشدار
   - `btn btn-info` - اطلاعات

3. **🌙 پشتیبانی کامل از Dark Theme:**
   - CSS خودکار تشخیص می‌دهد
   - **⚠️ مهم:** Limitless از `data-color-theme="dark"` استفاده می‌کنه نه `data-bs-theme="dark"`
   - نیازی به تنظیم اضافی نیست

4. **📱 RTL Support:**
   - `reverseButtons: true` برای چیدمان راست‌چین
   - `focusCancel: true` برای focus روی انصراف

#### **😨 اشتباهات رایج:**

❌ **غلط:**
```javascript
// بدون buttonsStyling: false
Swal.fire({ title: 'تست' });

// استفاده از wrapper های قدیمی
window.showConfirm('تست');
```

✅ **درست:**
```javascript
// با تنظیمات کامل
Swal.fire({
    title: 'تست',
    buttonsStyling: false,
    customClass: { confirmButton: 'btn btn-primary' }
});
```

#### **🎨 نکات CSS برای Dark Theme:**

⚠️ **مهم برای CSS نویسان:**
```css
/* ❌ غلط - فقط Bootstrap selector */
[data-bs-theme="dark"] .my-element {
    background: #212529;
}

/* ✅ درست - پشتیبانی از هر دو Limitless و Bootstrap */
[data-color-theme="dark"] .my-element,
[data-bs-theme="dark"] .my-element {
    background: #2d2f33; /* Limitless card-bg */
    color: #ffffff;
}
```

**🎨 رنگ‌های استاندارد Limitless Dark:**
- `background-color: #2d2f33` (پس‌زمینه کارت)
- `color: #ffffff` (متن اصلی)
- `color: #9CA3AF` (متن کم‌رنگ - muted)
- `border: 1px solid rgba(255, 255, 255, 0.125)` (حاشیه)

### 📷 **Image Upload:**
- **Auto-load:** فقط اگر فیلد IMAGE موجود باشد
- **قابلیت‌ها:** Drag&Drop, Preview, Validation
- **API:** `window.rmsImageUploader.getFiles()`
- **Format:** JPG, PNG, GIF, WebP (Max: 1MB)

### 📅 **Persian DatePicker:**
- **Auto-load:** فقط برای فیلدهای DATE/DATE_TIME
- **کتابخانه‌ها:** persian-date + pwt.datepicker + jalaali.js
- **حل مشکل سال کبیسه ۱۴۰۳:** ✅ کاملاً حل شده

### 💰 **Amount Formatter:**
- **Auto-load:** فقط برای فیلدهای PRICE
- **قابلیت‌ها:** فقط عدد، کاما هر ۳ رقم
- **فرمت:** 1,234,567 تومان

### 🎯 **Enhanced Select (Choices.js):**
- **Auto-active:** اگر بیش از ۱۰ آیتم یا `->advanced()`
- **قابلیت‌ها:** Search, Create, Multiple, AJAX
- **RTL:** پشتیبانی کامل فارسی

---

## 📈 **EXCEL EXPORT SYSTEM** - سیستم خروجی Excel

### ⚙️ **Setup Requirements:**
1. **Interface:** Controller باید `ShouldExport` را implement کند
2. **Trait:** `ExportList` قبلاً در `FormAndList` موجود
3. **Route:** در `RouteHelper::adminResource` فعال: `'export' => true`
4. **Package:** `rmscms/helper` با `ExcelHelper`

### 🔧 **Quick Implementation:**
```php
// تنها یک interface اضافه کنید!
class UsersController extends AdminController implements 
    HasList, HasForm, ShouldFilter, ShouldExport  // ← فقط این!
{
    // هیچ کد اضافی نیاز نیست! 🎉
    // ExportList trait همه کار را انجام می‌دهد
}
```

### ✨ **Smart Features:**
- **✅ Auto Filename:** `users_2025-01-13_12-30.xlsx`
- **✅ Filter Integration:** فیلترهای فعال خودکار اعمال
- **✅ Field Mapping:** `database_key` به `title` mapping
- **✅ Hidden Fields:** `->hiddenInExport()` پشتیبانی
- **✅ Memory Safe:** مدیریت حافظه و timeout

---

---

---

## 🋠️ **CONTROLLER CREATION CHECKLIST** - چک لیست ساخت کنترلر

### 🎤 **مراحل کامل ساخت کنترلر در RMS Core:**

#### ✅ **مرحله ۱: کنترلر در Core**
- **📁 مسیر:** `packages/rms/core/src/Controllers/Admin/YourController.php`
- **🎯 Interface ها:** implement کردن `HasList`, `HasForm`, `HasStats`, `HasFormStats`, وغیره
- **📝 متدها:** تعریف `getListFields()`, `getFieldsForm()`, `table()`, `baseRoute()`, `modelName()`

#### ✅ **مرحله ۲: Upload Configuration**
- **متد اجباری:** `getUploadConfig()` برای `HasUploadConfig` interface
- **تنظیمات:** path, disk, max_size, allowed_types, resize, thumbnails

#### ✅ **مرحله ۳: Routes در Core**
- **مسیر:** `packages/rms/core/routes/admin.php`
- **استفاده از:** `RouteHelper::adminResource()` + `Route::resource()`
- **قابلیت‌ها:** export, sort, filter, toggle_active, batch_actions, ajax_files

#### ✅ **مرحله ۴: Sidebar Menu**
- **مسیر:** `packages/rms/core/resources/views/admin/layout/sidebar.blade.php`
- **افزودن:** منو یا زیرمنو به بخش مناسب
- **آیکون:** استفاده از PhosphorIcons با رنگ مناسب
- **شمارش:** نمایش تعداد records با کوئری خودکار

```blade
<li class="nav-item">
    <a href="{{ url('/admin/admins') }}" class="nav-link {{ request()->is('admin/admins*') ? 'active' : '' }}">
        <i class="ph-users-three text-danger"></i>
        <span>مدیران</span>
        @php
            $totalAdmins = \RMS\Core\Models\Admin::whereNull('deleted_at')->count() ?? 0;
        @endphp
        @if($totalAdmins > 0)
            <span class="text-muted ms-auto">{{ $totalAdmins }}</span>
        @endif
    </a>
</li>
```

#### ✅ **مرحله ۵: تست و بررسی**
- **لیست:** آزمون نمایش لیست و آمار
- **فرم:** آزمون create/edit/update
- **آپلود:** آزمون آپلود تصاویر
- **فیلتر:** آزمون فیلترها و جستجو
- **عملیات گروهی:** آزمون batch actions

#### 💡 **نکات مهم:**
- **۷️⃣ Interface Combination:** ترکیب مناسب interface ها برای قابلیت‌های مورد نیاز
- **🎨 PhosphorIcons:** استفاده از آیکون‌های زیبا و معنادار
- **🔢 StatCard Objects:** استفاده از `StatCard` برای stats نه array ساده
- **🌙 Dark Theme:** اطمینان از سازگاری با تم تیره
- **📱 Responsive:** قابلیت نمایش در تمام اندازه‌ها

---

## 🆕 **LATEST UPDATES** - آخرین به‌روزرسانی‌ها

### 📊 **Statistical System v2.0** - سیستم آماری نسخه 2

#### ✅ **پیاده‌سازی کامل HasStats (آمار لیست‌ها):**
- **Interface:** `HasStats` با متدهای `getStats()` و `getStatSummary()`
- **Component:** کامپوننت `statistical-card` قابل استفاده مجدد
- **Integration:** یکپارچه‌سازی خودکار در `GenerateList` trait
- **Template:** قالب خودکار برای نمایش آمار در بالای لیست
- **Filter Support:** آمار هوشمند بر اساس فیلترهای فعال

#### ✅ **پیاده‌سازی کامل HasFormStats (آمار فرم‌ها):**
- **Interface:** `HasFormStats` با متد `getFormStats()`
- **Integration:** یکپارچه‌سازی خودکار در `GenerateForm` trait
- **Template:** قالب خودکار برای نمایش آمار در بالای فرم
- **Model Support:** دریافت خودکار model با `$this->model($id)`
- **Edit Mode Only:** فقط در صفحه ویرایش نمایش داده می‌شود

#### ✅ **ویژگی‌های مشترک:**
- **🎨 Limitless Design:** کاملاً مطابق قالب Limitless
- **🌙 Dark Theme:** پشتیبانی کامل
- **📱 Responsive:** سازگار با تمام اندازه‌ها
- **⚡ Auto-Loading:** بارگذاری خودکار بدون تنظیم اضافی
- **🔧 Reusable:** استفاده مجدد در پروژه‌های مختلف

#### 🛠️ **نکات فنی مهم:**
- **GenerateForm Fix:** حل مشکل `getModel()` و استفاده از `model($id)`
- **Template Data:** ارسال `$model` و `$isEditMode` به template
- **Error Handling:** مدیریت خطا در صورت عدم وجود model
- **Performance:** بهینه‌سازی کوئری‌ها فقط در صورت نیاز

---

## 📅 **RMS HELPER PACKAGE** - پکیج ابزارهای کمکی

### 🔧 **پکیج rmscms/helper:**
- 📅 **تاریخ شمسی** - تبدیل و فرمت خودکار در لیست‌ها
- 💰 **مالی** - فرمت مبلغ و ارقام فارسی
- 🗺 **Excel** - ایمپورت/اکسپورت آسان
- 🔍 **Eloquent Scopes** - scopehaی کاربردی

### 📊 **استفاده خودکار در لیست‌ها:**
```php
// فیلدهای date و date_time خودکار به فارسی تبدیل می‌شن:
Field::make('created_at')->withTitle('تاریخ ایجاد')
    ->type(Field::DATE_TIME)  // ← خودکار به 1404/04/29 14:30

Field::make('birth_date')->withTitle('تاریخ تولد')
    ->type(Field::DATE)       // ← خودکار به 1404/04/29
```

### 🔧 **توابع تاریخ در دسترس:**
```php
// تبدیل میلادی به شمسی
\RMS\Helper\persian_date('2025-07-20', 'Y/m/d');        // 1404/04/29
\RMS\Helper\persian_date(Carbon::now(), 'Y/m/d H:i');   // 1404/04/29 14:30

// تبدیل شمسی به میلادی
\RMS\Helper\gregorian_date('1404/04/29');                // 2025/07/20

// تاریخ فعلی فارسی
\RMS\Helper\persian_now();                              // 1404/04/29 14:30:45
\RMS\Helper\persian_now('Y/m/d');                       // 1404/04/29
```

### 💰 **توابع مالی و اعداد:**
```php
// فرمت مبلغ با واحد پولی
\RMS\Helper\displayAmount(1000);          // 1,000 تومان
\RMS\Helper\displayAmount(1000, 'ریال');    // 1,000 ریال

// تبدیل ارقام فارسی/عربی به انگلیسی
\RMS\Helper\changeNumberToEn('۱۲۳۴۵۶');     // 123456
```

---

## 🆔 **EXTENDED MODELS** - مدل‌های گسترش یافته IRAS

### 💡 **فلسفه Extended Models:**
به جای تغییر مستقیم Core، مدل‌هایی در `app/Models/` می‌سازیم که Core Models را extend می‌کنند.

### 📁 **Admin Model گسترش یافته:**
**مسیر:** `app/Models/Admin.php`  
**Extends:** `RMS\Core\Models\Admin as BaseAdmin`

#### 🔧 **فیلدهای اضافی IRAS:**
- `theme` - تم مورد علاقه ادمین (light/dark/auto)
- `telegram_chat_id` - آی‌دی چت تلگرام برای اعلان‌ها

#### ⚡ **متدهای جدید:**
```php
$admin->getTheme(); // 'light' | 'dark' | 'auto'
$admin->hasTelegramIntegration(); // true/false
$admin->setTheme('dark');
$admin->setTelegramChatId('123456789');
```

#### 🔍 **Scope های جدید:**
```php
Admin::withTelegram()->get(); // ادمین‌هایی که تلگرام دارند
Admin::byTheme('dark')->get(); // ادمین‌های با تم خاص
```

#### 🧪 **تست Extended Models:**
**نکته:** تست‌های این قسمت توسط آقا شریف انجام می‌شود ✅

#### 📋 **نکات مهم:**
1. **📦 Extend از RMS Core:** `use RMS\Core\Models\Admin as BaseAdmin;`
2. **🔄 سازگاری کامل:** همه قابلیت‌های Core موجود است
3. **⚡ بدون تغییر هسته:** Core دست نخورده باقی می‌ماند
4. **🚀 قابل ارتقا:** با آپدیت Core مشکلی پیش نمی‌آید

### 🎯 **الگوی Extended Models:**
برای سایر مدل‌های IRAS نیز از همین الگو استفاده کنید:
```php
// مثال برای مدل User
namespace App\Models;
use RMS\Core\Models\User as BaseUser;

class User extends BaseUser
{
    // فیلدها و متدهای اختصاصی IRAS
}
```

---

## 🔌 **PLUGIN SYSTEM** - سیستم پلاگین RMS

### 📋 **قوانین حیاتی پلاگین‌ها:**

#### 🚨 **قانون اول: Core First - هسته اول!**
1. **همیشه ابتدا در Core پلاگین بساز/ویرایش کن:** `packages/rms/core/assets/plugins/`
2. **بعد از تست و تأیید عملکرد کپی کن به Public:** `public/admin/plugins/`
3. **هرگز مستقیماً در Public تغییر نده** - تغییرات با آپدیت از بین می‌روند!
4. **Source of Truth:** Core package است، نه Public directory

#### 🎯 **قانون دوم: CustomPluginLoader - مرجع تنظیمات**
- **📍 مکان:** `packages/rms/core/src/Traits/View/CustomPluginLoader.php`
- **🎪 هر پلاگین جدید باید اینجا تعریف شود**
- **⚙️ تنظیمات شامل:** CSS, JS, Dependencies, Load Order
- **🔄 Auto-load:** پس از تعریف خودکار در AdminController لود می‌شود

### 🗺 **مرجع قالب Limitless:**
- **📁 مسیر کامل:** `C:/laragon/www/rms2/limitless-template-full/`
- **🎨 Assets Path:** `limitless-template-full/bs5/template/assets/`
- **💎 JS Plugins:** `limitless-template-full/bs5/template/assets/js/vendor/`
- **🎭 CSS/SCSS:** `limitless-template-full/bs5/template/assets/scss/vendor/`
- **📚 مستندات:** `LIMITLESS_TEMPLATE_REFERENCE.md` - راهنمای کامل فایل‌ها

### 🏗️ **ساخت پلاگین از صفر تا صد:**

#### **قدم ۱: ایجاد پوشه پلاگین در Core**
```bash
# مثال: پلاگین جدید "my-plugin"
mkdir -p packages/rms/core/assets/plugins/my-plugin
```

#### **قدم ۲: ایجاد فایل‌های اصلی**
```
packages/rms/core/assets/plugins/my-plugin/
├── my-plugin.css          # استایل پلاگین
├── my-plugin.js           # منطق اصلی پلاگین
├── my-plugin-init.js      # راه‌اندازی و initialization
└── README.md              # مستندات پلاگین
```

#### **قدم ۳: تعریف در CustomPluginLoader**
```php
// در packages/rms/core/src/Traits/View/CustomPluginLoader.php
'my-plugin' => [
    'css' => [
        'my-plugin.css'                    // فایل استایل
    ],
    'js' => [
        'my-plugin.js',                    // فایل اصلی
        'my-plugin-init.js'                // فایل راه‌اندازی
    ],
    'dependencies' => ['jquery'],          // وابستگی‌ها (اختیاری)
    'load_order' => 5,                     // ترتیب بارگذاری (1=اول، 10=آخر)
    'plugin_path' => 'my-plugin'           // نام دایرکتوری
],
```

#### **قدم ۴: کپی به Public**
```bash
# کپی کردن فایل‌ها از Core به Public
cp -r packages/rms/core/assets/plugins/my-plugin public/admin/plugins/
```

#### **⚠️ نکته مهم: فقط CSS/JS کپی می‌شود**
- **🎯 پلاگین‌ها فقط شامل CSS و JS هستند** - فایل‌های blade کپی نمی‌شوند
- **📁 Blade فایل‌ها در Core باقی می‌مانند:** `packages/rms/core/resources/views/`
- **🔄 تغییر blade ها فقط در Core:** هرگز blade ها را در public کپی نکنید
- **💡 مثال:** `mobile-footer-nav` پلاگین فقط CSS/JS دارد، footerphone.blade.php در Core است

#### **🧩 Components vs Plugins - تفاوت کامپوننت‌ها و پلاگین‌ها:**
- **📱 Blade Components:** فایل‌های `.blade.php` در `resources/views/components/` (مثل menu-item)
- **🔌 JS/CSS Plugins:** فایل‌های `.js/.css` در `assets/plugins/` (مثل image-uploader)
- **🚀 کامپوننت‌ها:** قابل استفاده مجدد در template ها با `<x-cms::component-name>`
- **⚡ پلاگین‌ها:** فعال‌سازی خودکار در صفحات مربوطه

#### **قدم ۵: فعال‌سازی در Controller**
```php
// در AdminController یا کنترلر مخصوص
protected function beforeRenderView()
{
    parent::beforeRenderView();
    
    // فعال‌سازی پلاگین
    $this->view->withPlugins(['my-plugin']);
}
```

### 🎪 **پلاگین‌های موجود در سیستم:**

#### **🗓️ Persian DatePicker:**
- **مسیر:** `plugins/persian-datepicker/`
- **فایل‌ها:** `jalaali.js`, `persian-date.min.js`, `pwt.datepicker.min.js`
- **کاربرد:** فیلدهای DATE و DATE_TIME
- **ویژگی:** حل مشکل سال کبیسه ۱۴۰۳

#### **📷 Image Uploader:**
- **مسیر:** `plugins/image-uploader/`
- **فایل‌ها:** `image-uploader.js`, `image-uploader.css`
- **کاربرد:** آپلود تصاویر با Drag&Drop
- **ویژگی:** Preview, Validation, AJAX Upload

#### **💰 Amount Formatter:**
- **مسیر:** `plugins/amount-formatter/`
- **فایل‌ها:** `amount-formatter.js`, `amount-formatter.css`
- **کاربرد:** فرمت‌بندی فیلدهای مبلغ
- **ویژگی:** کاما گذاری، فقط عدد

#### **🎯 Enhanced Select (Choices.js):**
- **مسیر:** `plugins/choices/`
- **فایل‌ها:** `choices.min.js`, `choices-bootstrap5.css`, `enhanced-select-init.js`
- **کاربرد:** سلکت‌های پیشرفته
- **ویژگی:** Search, Multiple, AJAX, RTL
- **✨ جدید:** Dark Theme کاملاً اصلاح شده برای Limitless
- **🎨 رنگ‌ها:** مطابق استاندارد Limitless (`#2d2f33` بکگراند)

#### **👿 SweetAlert2:**
- **مسیر:** `plugins/sweetalert2/`
- **فایل‌ها:** `sweet_alert.min.js`, `rms-sweetalert-new.js`, `sweetalert2.css`
- **کاربرد:** پیام‌های زیبا و تعاملی
- **ویژگی:** Toast, Confirm, Progress, AJAX Helper
- **✅ وضعیت:** کاملاً فعال و بهینه‌سازی شده
- **🌙 Dark Theme:** پشتیبانی کامل
- **📱 RTL:** تنظیم شده برای فارسی

#### **📱 Sidebar Mobile:**
- **مسیر:** `plugins/sidebar-mobile/`
- **فایل‌ها:** `sidebar-mobile.js`, `sidebar-mobile.css`
- **کاربرد:** حل مشکل sidebar در موبایل
- **ویژگی:** Toggle, Backdrop, Touch Events, ESC Key
- **✅ وضعیت:** فعال و کاملاً تست شده
- **🌙 Dark Theme:** پشتیبانی کامل
- **📱 RTL:** پشتیبانی کامل

#### **🚀 Mobile Footer Navigation:**
- **مسیر:** `plugins/mobile-footer-nav/`
- **فایل‌ها:** `mobile-footer-nav.js`, `mobile-footer-nav.css`
- **کاربرد:** بهینه‌سازی footer navigation موبایل
- **ویژگی:** Bootstrap Tooltips, Touch Feedback, Badge Management, Haptic Feedback
- **✅ وضعیت:** فعال و کاملاً تست شده
- **🌙 Dark Theme:** پشتیبانی کامل
- **📱 RTL:** پشتیبانی کامل
- **💡 ویژه:** فقط در موبایل فعال می‌شود

### 🔧 **نکات مهم توسعه پلاگین:**

#### **📐 ساختار استاندارد JS Plugin:**
```javascript
/**
 * RMS [PluginName]
 * 
 * @version 1.0.0
 * @author RMS Core Team
 */
class RMS[PluginName] {
    constructor(options = {}) {
        this.options = {
            // default options
            ...options
        };
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.initializeElements();
        console.log('🎯 RMS [PluginName] initialized');
    }
    
    // سایر متدها...
}

// Global instance
window.RMS[PluginName] = RMS[PluginName];

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.rms[pluginName] === 'undefined') {
            window.rms[pluginName] = new RMS[PluginName]();
        }
    });
} else {
    if (typeof window.rms[pluginName] === 'undefined') {
        window.rms[pluginName] = new RMS[PluginName]();
    }
}
```

#### **🎨 ساختار استاندارد CSS Plugin:**
```css
/* RMS [PluginName] Styles
 * Compatible with Bootstrap 5 + Limitless Theme
 * Version: 1.0.0
 */

/* Main plugin styles */
.rms-[plugin-name] {
    /* Base styles */
}

/* Dark theme support */
[data-bs-theme="dark"] .rms-[plugin-name] {
    /* Dark mode overrides */
}

/* RTL support */
[dir="rtl"] .rms-[plugin-name] {
    /* RTL adjustments */
}

/* Responsive design */
@media (max-width: 768px) {
    .rms-[plugin-name] {
        /* Mobile adjustments */
    }
}
```

### ⚡ **Load Order راهنمای ترتیب بارگذاری:**
- **1:** SweetAlert2 (زودهنگام - سایر پلاگین‌ها ممکن است نیاز داشته باشند)
- **2:** Amount Formatter (برای فیلدهای فرم)
- **3:** Image Uploader, Enhanced Select (پلاگین‌های اصلی)
- **4:** Persian DatePicker (پس از سایر پلاگین‌ها)
- **5-9:** پلاگین‌های سفارشی
- **10:** پلاگین‌های اختیاری/آزمایشی

### 🚀 **بهترین پروسه توسعه:**
1. **📋 برنامه‌ریزی:** تعیین نیاز و ویژگی‌های پلاگین
2. **🔍 بررسی Limitless:** جستجو برای پلاگین مشابه در قالب
3. **🏗️ ساخت در Core:** ایجاد فایل‌ها در مسیر Core
4. **⚙️ تنظیم CustomPluginLoader:** اضافه کردن پیکربندی
5. **🧪 تست:** آزمایش عملکرد و compatibility
6. **📋 مستندسازی:** ثبت در quick_ref و README
7. **🚀 Deploy:** کپی به Public و استفاده در پروژه

---

## 📁 **FILE UPLOAD SYSTEM** - سیستم کامل آپلود فایل

### 🆕 **UploadConfig Object - کلاس جدید برای تنظیمات آپلود**

#### **🎯 مزایای UploadConfig Object:**
- **✅ Fluent Methods:** متدهای قابل چین کردن برای تنظیمات آسان
- **🔧 IntelliSense:** پشتیبانی کامل IDE و autocomplete
- **📝 Type Safety:** کنترل نوع داده‌ها و validation
- **🚀 Presets:** پیش‌تنظیم‌های آماده برای موارد رایج
- **🔄 Backward Compatible:** سازگار با کد موجود

#### **🔧 نحوه استفاده:**
```php
use RMS\Core\Data\UploadConfig;

public function getUploadConfig(): array
{
    return [
        // ✅ روش جدید - ساده و قابل فهم
        'avatar' => UploadConfig::create('avatar')
            ->avatar() // پیش‌تنظیم کامل
            ->ajaxUpload(true)
            ->listThumbnailSize(50, 50),
            
        // 🎨 پیکربندی دستی
        'documents' => UploadConfig::create('documents')
            ->forDocuments()
            ->disk('local')
            ->path('documents/users')
            ->maxSize('10MB')
            ->useModelId(false),
            
        // 🖼️ گالری پیشرفته
        'gallery' => UploadConfig::create('gallery')
            ->forImages()
            ->path('uploads/gallery')
            ->maxSize('5MB')
            ->multiple(true)
            ->ajaxUpload(true)
            ->resize(1200, 800, 90)
            ->thumbnails([
                'small' => [150, 150],
                'large' => [800, 600]
            ])
    ];
}
```

#### **🎯 پیش‌تنظیم‌های آماده:**
```php
// Avatar ساده
->avatar()  // شامل: resize 300x300, thumbnails, types: jpg,png,gif,webp

// گالری
->gallery() // شامل: multiple, ajax, 5MB max, viewer enabled

// اسناد
->documents() // شامل: pdf,doc,docx,txt, 10MB max, private storage

// تصاویر
->forImages() // فقط فرمت‌های تصویری

// اسناد
->forDocuments() // فرمت‌های سند
```

#### **⚡ متدهای کاربردی:**
```php
$config = UploadConfig::create('test')->avatar();

// Helper methods
echo $config->getMaxSizeFormatted(); // "2 MB"
echo $config->getAllowedTypesString(); // "JPG, PNG, GIF"

$config->isMultiple(); // false
$config->isAjaxEnabled(); // depends on setting
$config->isViewerEnabled(); // true

// تبدیل به array (خودکار)
$array = $config->toArray();
```

### 🚨 **دو سیستم موجود - انتخاب درست مهم است!**

#### **📷 سیستم اول: Image Uploader Plugin (فرانت‌اند)**
- **🎯 مناسب برای:** تصاویر با پیش‌نمایش زنده و رابط کاربری زیبا
- **⚡ نوع:** Client-side با قابلیت AJAX
- **🎨 ویژگی‌ها:** Drag&Drop, Preview, Validation, SweetAlert2 Integration

#### **🔧 سیستم دوم: HasFileUpload Trait (بک‌اند)**
- **🎯 مناسب برای:** مدیریت حرفه‌ای فایل‌ها در سرور
- **⚡ نوع:** Server-side با مدیریت کامل فایل‌ها
- **🏢 ویژگی‌ها:** Smart naming, Multiple storage disks, Model-based organization

---

## 📷 **IMAGE UPLOADER PLUGIN** - پلاگین آپلود تصویر

### ✨ **ویژگی‌های کامل:**

#### **🎯 Core Features:**
1. **📁 Drag & Drop Upload** - کشیدن و رها کردن تصاویر
2. **🖼️ Live Preview** - پیش‌نمایش فوری با thumbnail قابل تنظیم
3. **📏 File Size Validation** - اعتبارسنجی سایز (پیش‌فرض 2MB)
4. **🎨 File Type Validation** - پشتیبانی JPG, PNG, GIF, WebP
5. **📱 Multiple Upload Support** - آپلود چندگانه
6. **🌙 Dark Theme Support** - پشتیبانی کامل تم تیره Limitless
7. **🔄 RTL Support** - پشتیبانی کامل فارسی/راست‌چین
8. **📱 Responsive Design** - سازگار با موبایل

#### **⚡ Advanced Features:**
9. **🌐 AJAX Upload Mode** - آپلود فوری بدون refresh صفحه
10. **🗑️ AJAX Delete** - حذف فوری از سرور
11. **⏳ Loading States** - نمایش وضعیت بارگذاری با Progress Bar
12. **🍯 SweetAlert2 Integration** - پیام‌های زیبا به جای console.log
13. **🔄 Auto-Initialize** - فعال‌سازی خودکار روی .image-uploader elements
14. **👀 MutationObserver** - تشخیص خودکار عناصر dynamic جدید
15. **🎛️ Configurable Options** - تنظیمات کامل از طریق data attributes
16. **🧹 Memory Management** - مدیریت حافظه و cleanup مناسب

### 🛠️ **تنظیمات JavaScript:**
```javascript
{
    maxSize: 2 * 1024 * 1024,           // حداکثر سایز (2MB)
    allowedTypes: ['image/jpeg', ...],   // نوع فایل‌های مجاز
    multiple: false,                     // آپلود چندگانه
    dragDrop: true,                     // فعال‌سازی drag & drop
    preview: true,                      // نمایش پیش‌نمایش
    ajaxUpload: false,                  // حالت AJAX
    modelId: null,                      // ID مدل برای AJAX uploads
    fieldName: null,                    // نام فیلد برای AJAX uploads
    texts: { /* متن‌های فارسی */ }     // متن‌های قابل تغییر
}
```

### 🎨 **استفاده در Form Field:**
```php
// ✅ استفاده صحیح در کنترلر:
Field::image('avatar', 'تصویر پروفایل')
    ->withAttributes([
        'data-max-size' => '5MB',
        'data-preview' => 'true',
        'data-drag-drop' => 'true',
        'data-thumbnail' => json_encode(['width' => 150, 'height' => 150])
    ])
    ->withHint('فرمت‌های مجاز: JPG, PNG, GIF (حداکثر 5MB)')
    ->optional() // ✅ به طور خودکار skipDatabase() فعال است
```

### 🚀 **API Methods:**
```javascript
// Global Instance
window.rmsImageUploader.getFiles()        // دریافت فایل‌های آپلود شده
window.rmsImageUploader.clearFiles()      // پاک کردن تمام فایل‌ها

// Static Methods
RMSImageUploader.initialize('.my-uploader', options)  // فعال‌سازی دستی
RMSImageUploader.debug()                            // نمایش اطلاعات debug
```

---

## 🔧 **HASFILEUPLOAD TRAIT** - سیستم بک‌اند حرفه‌ای

### 📍 **مکان و شروع:**
- **Trait:** `packages/rms/core/src/Traits/Upload/HasFileUpload.php`
- **استفاده:** `use RMS\Core\Traits\Upload\HasFileUpload;` در کنترلر
- **Routes:** از `RouteHelper::adminResource` با `ajax_files` پشتیبانی می‌شود

### 🎯 **کاربردهای اصلی:**

#### **1️⃣ آپلود تکی (Avatar/Profile):**
```php
protected function getUploadConfig(): array
{
    return [
        'avatar' => [
            'disk' => 'public',              // استفاده از public storage
            'path' => 'uploads/avatars',     // مسیر ذخیره
            'types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
            'max_size' => 2048,             // 2MB
            'multiple' => false,            // فقط یک فایل
            'use_model_id' => true,         // نام فایل = model_id.ext
            'ajax_upload' => false,         // آپلود معمولی با فرم
        ]
    ];
}
```

#### **2️⃣ گالری چندتایی (Gallery):**
```php
'gallery' => [
    'disk' => 'public',
    'path' => 'uploads/gallery',
    'types' => ['jpeg', 'jpg', 'png', 'gif', 'webp'],
    'max_size' => 5120,                 // 5MB
    'multiple' => true,                 // فایل‌های متعدد
    'use_model_id' => true,             // ایجاد پوشه: gallery/123/
    'ajax_upload' => true,              // فقط در edit mode نمایش
    'dimensions' => ['width' => 1920, 'height' => 1080], // اختیاری
],
```

#### **3️⃣ اسناد خصوصی (Documents):**
```php
'documents' => [
    'disk' => 'local',                  // استفاده از private storage
    'path' => 'documents/users',
    'types' => ['pdf', 'doc', 'docx', 'txt'],
    'max_size' => 10240,               // 10MB
    'multiple' => false,
    'use_model_id' => false,           // استفاده از نام‌های تصادفی
    'ajax_upload' => false,            // آپلود معمولی
]
```

### ⚡ **Smart Features:**

#### **🏷️ Smart Naming System:**
- **Model ID mode:** `123.jpg` (برای single), `123/timestamp_random_name.jpg` (برای multiple)
- **Random mode:** `timestamp_random_originalname.jpg`
- **Auto cleanup:** حذف فایل‌های قدیمی هنگام جایگزینی

#### **📁 Smart Storage:**
- **Public disk:** فایل‌هایی که مستقیماً قابل دسترسی هستند
- **Local disk:** فایل‌های خصوصی که نیاز به مجوز دارند
- **Model folders:** ایجاد خودکار پوشه برای هر مدل در حالت multiple

### 🛠️ **Integration در Controller:**

#### **قدم ۱: اضافه کردن Trait**
```php
use RMS\Core\Traits\Upload\HasFileUpload;

class UsersController extends AdminController
{
    use HasFileUpload;
    
    // تنظیمات آپلود...
}
```

#### **قدم ۲: Hook Integration**
```php
// در beforeAdd/beforeUpdate hooks:
protected function beforeAdd(Request &$request): void
{
    // پردازش آپلود فایل‌های معمولی
    $uploadedFiles = $this->processFileUploads($request);
    
    // سایر پردازش‌ها...
}

// در afterDestroy hook:
protected function afterDestroy(int|string $id): void
{
    // پاک‌سازی فایل‌های مدل
    $this->cleanupModelFiles($id);
}
```

#### **قدم ۳: AJAX Routes (اختیاری)**
```php
// در routes/web.php:
RouteHelper::adminResource(UsersController::class, 'admin.users', [
    'ajax_files' => ['avatar', 'gallery'] // فیلدهایی که AJAX دارند
]);

// یا دستی:
Route::post('/admin/users/{user}/ajax-upload/{field}', [UsersController::class, 'handleAjaxUpload']);
Route::delete('/admin/users/{user}/ajax-delete/{field}', [UsersController::class, 'handleAjaxDelete']);
```

#### **قدم ۴: Template Filtering**
```php
protected function beforeSendToTemplate(array &$templateData, FormResponse $generated): void
{
    parent::beforeSendToTemplate($templateData, $generated);
    
    // حذف فیلدهای AJAX از حالت create
    $isCreateMode = !$generated->getGenerator()->getId();
    $this->filterAjaxUploadFields($templateData, $isCreateMode);
}
```

### 📊 **File Management Methods:**

```php
// پردازش آپلود‌ها
$uploaded = $this->processFileUploads($request, $modelId);

// حذف فایل قدیمی
$this->deleteOldFile('avatar', $oldPath);

// حذف کل پوشه مدل
$this->deleteModelFolder('gallery', $modelId);

// دریافت URL فایل
$url = $this->getFileUrl($filePath, 'public');

// دریافت اطلاعات فایل
$fileInfo = $this->getFileInfoForTemplate('avatar', $filePath);

// پاک‌سازی کامل فایل‌های مدل
$results = $this->cleanupModelFiles($modelId);
```

### 🌐 **AJAX Upload Endpoints:**
```php
// متد آپلود AJAX (خودکار از trait)
public function ajaxUpload(Request $request, $id, $fieldName)
{
    return $this->handleAjaxUpload($request, $id, $fieldName);
}

// متد حذف AJAX (خودکار از trait)
public function ajaxDeleteFile(Request $request, $id, $fieldName)
{
    $filePath = $request->query('file_path');
    return $this->handleAjaxDelete($request, $id, $fieldName, $filePath);
}
```

### 🔍 **Debug و Logging:**
- **✅ Integration با RMS Debug System** - لاگ‌های کامل
- **📊 Success/Error Tracking** - پیگیری تمام عملیات
- **🗂️ File Operations Log** - ثبت تمام تغییرات فایل
- **💾 Memory Usage Tracking** - مانیتورینگ مصرف حافظه

### 🚀 **Best Practices:**

#### **✅ Do's (انجام دهید):**
1. **همیشه `getUploadConfig()` را override کنید**
2. **فیلدهای AJAX را در create mode فیلتر کنید**
3. **در afterDestroy فایل‌ها را پاک کنید**
4. **برای فایل‌های حساس از disk 'local' استفاده کنید**
5. **محدودیت سایز و نوع فایل تعریف کنید**

#### **❌ Don'ts (انجام ندهید):**
1. **فایل‌ها را مستقیماً در public folder ذخیره نکنید**
2. **بدون validation فایل‌ها را آپلود نکنید**
3. **فایل‌های قدیمی را بدون حذف جایگزین نکنید**
4. **نام فایل‌های کاربر را مستقیماً استفاده نکنید**
5. **بدون cleanup مدل‌ها را حذف نکنید**

---

### 📱 **RECENT CHANGES** - تغییرات اخیر

### 🎯 **2025/01/19 - Core Package Bug Fixes:**
✅ **Assets Structure:** پلاگین‌ها js ها و css ها باید از `public/admin` که خودمان توسعه می‌دهیم به `packages/rms/core/assets` منتقل شوند - نباید فولدر admin ایجاد شود
✅ **Admin Avatar Column Fix:** خطای `SQLSTATE[HY000]: no such column: a.avatar` حل شد - ستون avatar در migration admins اضافه شده

### 🎯 **2025/01/19 - Sidebar Structure Fix & Collapse Cards:**
✅ **Sidebar Structure اصلاح شد:** انطباق کامل با ساختار Limitless template  
✅ **navbar-brand اضافه شد:** حل مشکل positioning navbar نسبت به sidebar  
✅ **sidebar-content reorganized:** جابجایی sidebar-section داخل sidebar-content  
✅ **Stats Card Collapse:** پیاده‌سازی صحیح `data-card-action="collapse"` مانند filter card  
✅ **Limitless Compatibility:** استفاده از سیستم collapse خود Limitless نه Bootstrap manual
✅ **Icon Animation:** آیکون `ph-caret-down` حالا درست چرخش می‌کند  

### 🇺🇵 **2025/01/19 - UploadConfig Object System:**
✅ **UploadConfig Object ایجاد شد:** سیستم fluent methods برای تنظیمات آپلود  
✅ **Normalize Support:** تبدیل خودکار Object به array در تمام traits  
✅ **Backward Compatible:** سازگاری کامل با کد موجود  
✅ **Presets Available:** avatar(), gallery(), documents() presets آماده  
✅ **IDE Support:** IntelliSense و Type Safety کامل

### 🌨️ **2025/01/19 - Avatar Viewer Dark Theme Fix:**
✅ **SweetAlert2 Theme Fix:** حل مشکل بکگراند CSS variables در Limitless theme  
✅ **Dynamic Theme Detection:** تشخیص خودکار dark/light mode  
✅ **Limitless Colors:** استفاده از رنگ‌های استاندارد Limitless (#2d2f33 dark, #ffffff light)  
✅ **Better Contrast:** بهبود readability و contrast در هر دو تم  
✅ **Responsive Design:** سازگار با موبایل و تمام اندازه‌ها  

### 🗓️ **2025/01/19 - Menu Components System:**
✅ **Menu Components ایجاد شد:** سه کامپوننت menu-header، menu-item، submenu-item  
✅ **مسیر بهینه‌سازی:** انتقال از admin/components به components (قابل استفاده مجدد)  
✅ **ServiceProvider به‌روزرسانی:** ثبت صحیح کامپوننت‌ها با anonymousComponentPath  
✅ **Sidebar refactor:** استفاده از کامپوننت‌ها به جای HTML خام  
✅ **Smart Active Detection:** تشخیص هوشمند حالت active برای منوها  
✅ **Auto Parent Expand:** باز شدن خودکار منوی والد اگر فرزند active باشد  

### 🗣️ **2025/01/19 - Localization System Implementation:**
✅ **Persian Translation File ایجاد شد:** `resources/lang/fa/admin.php` با بیش از 280 کلید ترجمه  
✅ **قانون طلایی:** NO HARDCODED PERSIAN - همیشه از trans() استفاده کن  
✅ **قوانین به‌روزرسانی:** اضافه قانون استفاده از سیستم چندزبانه  
✅ **بهترین رویه‌ها:** نامگذاری، دسته‌بندی، fallback و error handling  
✅ **محتوای کامل:** عمومی، کاربران، مدیران، پیام‌ها، خطاها، فرم‌ها و بیشتر  
✅ **آماده برای مهاجرت:** باید تمام hardcoded Persian در کد را جایگزین کرد  

### 🎨 **2025/01/19 - Enhanced Select Dark Theme Fix:**
✅ **Dark Theme اصلاح شد:** حل مشکل بکگراند زشت Enhanced Select در dark mode  
✅ **Limitless Compatibility:** اضافه شدن پشتیبانی از `[data-color-theme="dark"]` به جای فقط `[data-bs-theme="dark"]`  
✅ **Professional Colors:** استفاده از رنگ‌های استاندارد Limitless (`#2d2f33` background, `#ffffff` text)  
✅ **Better Contrast:** بهبود کنتراست و readability در تم تیره  
✅ **Consistent Styling:** هماهنگی کامل با سایر عناصر UI در dark mode  

---

*این فایل Quick Reference همیشه به‌روز نگه داشته می‌شود با آخرین تغییرات و دستاوردهای پروژه RMS Core*
