<?php

/**
 * مثال‌های جامع از استفاده Field کلاس بهینه‌سازی شده
 * 
 * این فایل نشان می‌دهد چگونه از static factory methods و fluent interface
 * برای ایجاد فیلدهای مختلف استفاده کنیم.
 */

use RMS\Core\Data\Field;

// ============================================================================
// استفاده از Static Factory Methods جدید
// ============================================================================

// روش قدیمی (هنوز کار می‌کند)
$oldWay = new Field('username', 'user_name');

// روش جدید - بهتر و قابل خواندن‌تر
$newWay = Field::make('username', 'user_name');

// ایجاد فیلد با title در یک خط
$fieldWithTitle = Field::create('username', 'نام کاربری', 'user_name');

// ============================================================================
// Static Factory Methods برای انواع فیلدها
// ============================================================================

// فیلد متنی ساده
$nameField = Field::string('name', 'نام');

// فیلد رمز عبور
$passwordField = Field::password('password', 'کلمه عبور');

// فیلد ایمیل با validation خودکار
$emailField = Field::email('email', 'ایمیل کاربر');

// فیلد عدد با محدودیت
$ageField = Field::number('age', 'سن', 18, 120);

// فیلد تاریخ
$birthDateField = Field::date('birth_date', 'تاریخ تولد');

// فیلد زمان کامل
$createdAtField = Field::datetime('created_at', 'زمان ایجاد');

// فیلد بولین
$activeField = Field::boolean('is_active', 'وضعیت فعال');

// فیلد انتخابی
$categoryOptions = [
    ['id' => 1, 'name' => 'تکنولوژی'],
    ['id' => 2, 'name' => 'ورزش'],
    ['id' => 3, 'name' => 'سرگرمی']
];
$categoryField = Field::select('category_id', 'دسته‌بندی', $categoryOptions);

// فیلد فایل با نوع‌های مجاز
$avatarField = Field::file('avatar', 'تصویر پروفایل', ['.jpg', '.jpeg', '.png']);

// فیلد مخفی
$hiddenField = Field::hidden('user_id', 123);

// فیلد قیمت
$priceField = Field::price('amount', 'مبلغ', 'تومان');

// فیلد ویرایشگر
$contentField = Field::editor('content', 'محتوای مطلب');

// فیلد رنگ
$colorField = Field::color('theme_color', 'رنگ قالب');

// ============================================================================
// Fluent Interface - Method Chaining پیشرفته
// ============================================================================

// فیلد کامل با زنجیره متدها
$complexField = Field::make('username')
    ->withTitle('نام کاربری')
    ->withType(Field::STRING)
    ->required(['alpha_spaces', 'unique:users,username'])
    ->length(3, 50)
    ->withPlaceHolder('نام کاربری خود را وارد کنید')
    ->withIcon('user')
    ->addClasses('form-control form-control-lg')
    ->withHint('نام کاربری باید منحصر به فرد باشد')
    ->sortable()
    ->searchable();

// فیلد موبایل ایرانی
$mobileField = Field::string('mobile', 'شماره موبایل')
    ->iranianMobile()
    ->required()
    ->withIcon('phone')
    ->addClasses('mobile-input')
    ->searchable();

// فیلد کد ملی
$nationalCodeField = Field::string('national_code', 'کد ملی')
    ->iranianNationalCode()
    ->required()
    ->withIcon('id-card')
    ->sortable();

// فیلد متن فارسی
$descriptionField = Field::string('description', 'توضیحات')
    ->persianText()
    ->optional()
    ->enableRTL()
    ->withAttributes(['rows' => 4])
    ->withHint('توضیحات اختیاری به زبان فارسی');

// فیلد ایمیل پیشرفته
$advancedEmailField = Field::email()
    ->withTitle('ایمیل اصلی')
    ->required(['unique:users,email'])
    ->withPlaceHolder('example@domain.com')
    ->withIcon('envelope')
    ->addClasses('email-input')
    ->withHint('این ایمیل برای ورود استفاده می‌شود')
    ->sortable()
    ->searchable();

// فیلد انتخابی پیشرفته
$advancedSelectField = Field::select('role_id', 'نقش کاربر')
    ->setSelectData([
        ['id' => 1, 'name' => 'مدیر'],
        ['id' => 2, 'name' => 'ویراستار'],
        ['id' => 3, 'name' => 'کاربر عادی']
    ])
    ->required()
    ->withEmptyOption()
    ->advanced() // Select2
    ->filterable()
    ->withHint('نقش کاربر در سیستم');

// فیلد قیمت پیشرفته
$advancedPriceField = Field::price('salary', 'حقوق', 'تومان')
    ->required(['numeric', 'min:1000000'])
    ->withPlaceHolder('1,000,000')
    ->addClasses('money-input')
    ->withIcon('dollar-sign')
    ->sortable()
    ->withHint('حقوق به تومان');

// ============================================================================
// استفاده در Controller ها
// ============================================================================

/*
class UserController extends Controller
{
    public function getFormFields(): array
    {
        return [
            Field::string('first_name', 'نام')
                ->required(['alpha_spaces'])
                ->length(2, 50)
                ->withPlaceHolder('نام خود را وارد کنید')
                ->searchable(),

            Field::string('last_name', 'نام خانوادگی')  
                ->required(['alpha_spaces'])
                ->length(2, 50)
                ->searchable(),

            Field::email()
                ->withTitle('ایمیل')
                ->required(['unique:users,email'])
                ->searchable(),

            Field::string('mobile', 'موبایل')
                ->iranianMobile()
                ->required()
                ->searchable(),

            Field::password('password', 'کلمه عبور')
                ->required(['min:8'])
                ->withHint('حداقل 8 کاراکتر'),

            Field::select('role_id', 'نقش')
                ->setSelectData($this->getRoles())
                ->required()
                ->advanced()
                ->filterable(),

            Field::boolean('is_active', 'وضعیت فعال')
                ->withEnableTitle('فعال')
                ->withDisableTitle('غیرفعال')
                ->ajaxActionRoute('admin.users.toggle_active'),

            Field::file('avatar', 'تصویر پروفایل', ['.jpg', '.png'])
                ->optional()
                ->withHint('حداکثر 2 مگابایت'),

            Field::date('birth_date', 'تاریخ تولد')
                ->optional()
                ->sortable(),

            Field::price('salary', 'حقوق', 'تومان')
                ->optional(['numeric', 'min:0'])
                ->sortable()
        ];
    }
}
*/

// ============================================================================
// استفاده در FormGenerator
// ============================================================================

/*
$formFields = [
    // فیلد شناسه مخفی
    Field::hidden('id'),
    
    // اطلاعات شخصی
    Field::string('first_name', 'نام')
        ->required(['alpha_spaces'])
        ->withIcon('user'),
        
    Field::string('last_name', 'نام خانوادگی')
        ->required(['alpha_spaces']),
        
    // اطلاعات تماس
    Field::email()
        ->required(['unique:users,email']),
        
    Field::string('mobile', 'موبایل')
        ->iranianMobile()
        ->required(),
        
    // اطلاعات امنیتی
    Field::password('password', 'کلمه عبور')
        ->required(['min:8', 'confirmed']),
        
    Field::password('password_confirmation', 'تأیید کلمه عبور')
        ->required(),
        
    // انتخاب نقش
    Field::select('role_id', 'نقش کاربر')
        ->setSelectData($roles)
        ->required()
        ->advanced(),
        
    // تنظیمات
    Field::boolean('is_active', 'فعال')
        ->withEnableTitle('فعال')
        ->withDisableTitle('غیرفعال'),
        
    Field::boolean('email_verified', 'ایمیل تأیید شده')
        ->optional()
];
*/

// ============================================================================
// ترکیب با Validation Rules سفارشی
// ============================================================================

$persianNameField = Field::string('persian_name', 'نام فارسی')
    ->persianText()
    ->required()
    ->enableRTL()
    ->withPlaceHolder('نام خود را به فارسی وارد کنید');

$nationalCodeField = Field::string('national_code', 'کد ملی')
    ->iranianNationalCode()
    ->required()
    ->withHint('کد ملی 10 رقمی معتبر')
    ->sortable();

$iranianMobileField = Field::string('mobile', 'موبایل')
    ->iranianMobile()
    ->required()
    ->searchable();

// ============================================================================
// مثال‌های پیچیده و ترکیبی
// ============================================================================

// فرم ثبت محصول
$productFields = [
    Field::string('name', 'نام محصول')
        ->required(['min:3'])
        ->searchable()
        ->withIcon('package'),
        
    Field::editor('description', 'توضیحات')
        ->optional()
        ->enableRTL(),
        
    Field::price('price', 'قیمت', 'تومان')
        ->required(['numeric', 'min:1000'])
        ->sortable(),
        
    Field::number('inventory', 'موجودی', 0, 10000)
        ->required()
        ->sortable(),
        
    Field::select('category_id', 'دسته‌بندی')
        ->setSelectData($categories)
        ->required()
        ->filterable(),
        
    Field::file('image', 'تصویر اصلی', ['.jpg', '.jpeg', '.png'])
        ->optional()
        ->withHint('تصویر با کیفیت بالا'),
        
    Field::boolean('is_featured', 'محصول ویژه')
        ->withEnableTitle('ویژه')
        ->withDisableTitle('عادی')
        ->filterable(),
        
    Field::color('brand_color', 'رنگ برند')
        ->optional()
        ->withHint('رنگ اختصاصی برند')
];

// ============================================================================
// نکات مهم در استفاده
// ============================================================================

/*
✅ مزایای روش جدید:

1. خوانایی بالاتر کد
2. Method chaining راحت‌تر
3. Type-specific factory methods
4. Validation یکپارچه با Field
5. پشتیبانی کامل از Persian/Iranian formats
6. سازگاری با کد قدیمی

✅ الگوهای توصیه شده:

// بجای این:
$field = new Field('email');
$field->withTitle('ایمیل');
$field->withType(Field::STRING);
$field->required();

// از این استفاده کنید:
$field = Field::email('email', 'ایمیل')->required();

✅ نکات عملکردی:

1. همیشه از static methods برای سرعت بیشتر
2. Chaining را تا جای ممکن استفاده کنید
3. Validation rules را با فیلد تعریف کنید
4. از Type-specific methods استفاده کنید
5. toArray() برای سریالیزه کردن

*/
