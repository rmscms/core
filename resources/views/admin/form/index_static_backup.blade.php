@extends('cms::admin.layout.index')
@section('content')

    <div class="d-flex flex-column" style="min-height: 100vh;">
        
        <!-- Input Fields Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">فیلدهای ورودی</h5>
            </div>

            <div class="card-body">
                <p class="mb-4">نمونه‌هایی از المان‌های فرم پایه. تمامی المان‌های ورودی با حالت‌ها و گزینه‌های اندازه‌بندی مختلف پشتیبانی می‌شوند.</p>

                <form action="#" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">فیلدهای متنی پایه</div>

                        <!-- نام -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">نام</label>
                            <div class="col-lg-9">
                                <input type="text" name="name" class="form-control" placeholder="نام خود را وارد کنید">
                            </div>
                        </div>

                        <!-- ایمیل -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">ایمیل</label>
                            <div class="col-lg-9">
                                <input type="email" name="email" class="form-control" placeholder="آدرس ایمیل خود را وارد کنید">
                            </div>
                        </div>

                        <!-- گذرواژه -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">گذرواژه</label>
                            <div class="col-lg-9">
                                <input type="password" name="password" class="form-control" placeholder="گذرواژه را وارد کنید">
                            </div>
                        </div>

                        <!-- شماره تلفن -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">شماره تلفن</label>
                            <div class="col-lg-9">
                                <input type="tel" name="phone" class="form-control" placeholder="شماره تلفن را وارد کنید">
                            </div>
                        </div>

                        <!-- توضیحات -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">توضیحات</label>
                            <div class="col-lg-9">
                                <textarea rows="4" name="description" class="form-control" placeholder="توضیحات یا نظرات خود را وارد کنید"></textarea>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">المان‌های انتخاب</div>

                        <!-- انتخاب منفرد -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">دسته‌بندی</label>
                            <div class="col-lg-9">
                                <select name="category" class="form-select">
                                    <option value="">انتخاب دسته‌بندی</option>
                                    <option value="1">دسته‌بندی ۱</option>
                                    <option value="2">دسته‌بندی ۲</option>
                                    <option value="3">دسته‌بندی ۳</option>
                                    <option value="4">دسته‌بندی ۴</option>
                                </select>
                            </div>
                        </div>

                        <!-- انتخاب چندگانه -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">برچسب‌ها</label>
                            <div class="col-lg-9">
                                <select multiple="multiple" name="tags[]" class="form-select" size="4">
                                    <option value="tag1">توسعه وب</option>      
                                    <option value="tag2">اپلیکیشن موبایل</option>
                                    <option value="tag3">طراحی پایگاه داده</option>
                                    <option value="tag4">توسعه API</option>
                                    <option value="tag5">رابط کاربری</option>
                                    <option value="tag6">سیستم‌های بک‌اند</option>
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">آپلود فایل</div>

                        <!-- آپلود فایل منفرد -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">تصویر آواتار</label>
                            <div class="col-lg-9">
                                <input type="file" name="avatar" class="form-control" accept="image/*">
                                <div class="form-text">فقط فایل‌های تصویری (JPG, PNG, GIF) پذیرفته می‌شوند</div>
                            </div>
                        </div>

                        <!-- آپلود چندین فایل -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">اسناد</label>
                            <div class="col-lg-9">
                                <input type="file" name="documents[]" class="form-control" multiple>
                                <div class="form-text">می‌توانید چندین فایل انتخاب کنید</div>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">کمک‌کننده‌های فرم</div>

                        <!-- کمک متنی -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">نام کاربری</label>
                            <div class="col-lg-9">
                                <input type="text" name="username" class="form-control" placeholder="نام کاربری را وارد کنید">
                                <div class="form-text">نام کاربری باید بین ۳ تا ۲۰ کاراکتر باشد و فقط شامل حروف، اعداد و خط زیر</div>
                            </div>
                        </div>

                        <!-- کمک نشان -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">وضعیت</label>
                            <div class="col-lg-9">
                                <select name="status" class="form-select">
                                    <option value="active">فعال</option>
                                    <option value="inactive">غیرفعال</option>
                                    <option value="pending">در انتظار</option>
                                </select>
                                <span class="badge bg-primary mt-1">وضعیت فعلی بر دسترسی سیستم تأثیر می‌گذارد</span>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">آیکن‌های ورودی</div>

                        <!-- ورودی با آیکن سمت چپ -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">جستجو</label>
                            <div class="col-lg-9">
                                <div class="form-control-feedback form-control-feedback-start">
                                    <input type="text" name="search" class="form-control" placeholder="جستجو برای موارد...">
                                    <div class="form-control-feedback-icon">
                                        <i class="ph-magnifying-glass"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ورودی با آیکن سمت راست -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">آدرس وب‌سایت</label>
                            <div class="col-lg-9">
                                <div class="form-control-feedback form-control-feedback-end">
                                    <input type="url" name="website" class="form-control" placeholder="https://example.com">
                                    <div class="form-control-feedback-icon">
                                        <i class="ph-globe"></i>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">چک‌باکس و دکمه‌های رادیو</div>

                        <!-- چک‌باکس‌ها -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">تنظیمات</label>
                            <div class="col-lg-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="newsletter" id="newsletter">
                                    <label class="form-check-label" for="newsletter">
                                        عضویت در خبرنامه
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="notifications" id="notifications">
                                    <label class="form-check-label" for="notifications">
                                        اعلان‌های ایمیل
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="preferences[]" value="updates" id="updates">
                                    <label class="form-check-label" for="updates">
                                        به‌روزرسانی‌های محصول
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- دکمه‌های رادیو -->
                        <div class="row mb-3">
                            <label class="col-form-label col-lg-3">نوع حساب</label>
                            <div class="col-lg-9">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="account_type" value="personal" id="personal">
                                    <label class="form-check-label" for="personal">
                                        حساب شخصی
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="account_type" value="business" id="business">
                                    <label class="form-check-label" for="business">
                                        حساب تجاری
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="account_type" value="enterprise" id="enterprise">
                                    <label class="form-check-label" for="enterprise">
                                        حساب سازمانی
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mb-4">
                        <div class="fw-bold border-bottom pb-2 mb-3">دکمه‌های عملیات</div>

                        <div class="row">
                            <div class="col-lg-9 offset-lg-3">
                                <button type="submit" class="btn btn-primary">
                                    <i class="ph-paper-plane-tilt me-2"></i>
                                    ارسال فرم
                                </button>
                                <button type="reset" class="btn btn-light ms-2">
                                    <i class="ph-arrow-counter-clockwise me-2"></i>
                                    بازنشانی فرم
                                </button>
                                <button type="button" class="btn btn-outline-secondary ms-2">
                                    <i class="ph-x me-2"></i>
                                    لغو
                                </button>
                            </div>
                        </div>

                    </div>

                </form>

            </div>
        </div>

        <!-- Form Validation Examples Card -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">حالت‌های اعتبارسنجی فرم</h5>
            </div>

            <div class="card-body">
                <p class="mb-4">نمونه‌هایی از حالت‌های اعتبارسنجی فرم شامل حالت‌های موفقیت، خطا و هشدار با استایل و پیام‌های بازخورد مناسب.</p>

                <div class="mb-4">
                    <div class="fw-bold border-bottom pb-2 mb-3">نمونه‌های اعتبارسنجی</div>

                    <!-- ورودی معتبر -->
                    <div class="row mb-3">
                        <label class="col-form-label col-lg-3">ورودی معتبر</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control is-valid" value="داده ورودی معتبر">
                            <div class="valid-feedback">این فیلد خوب به نظر می‌رسد!</div>
                        </div>
                    </div>

                    <!-- ورودی نامعتبر -->
                    <div class="row mb-3">
                        <label class="col-form-label col-lg-3">ورودی نامعتبر</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control is-invalid" value="داده نامعتبر">
                            <div class="invalid-feedback">لطفاً یک ورودی معتبر ارائه دهید.</div>
                        </div>
                    </div>

                    <!-- ورودی هشدار -->
                    <div class="row mb-3">
                        <label class="col-form-label col-lg-3">ورودی هشدار</label>
                        <div class="col-lg-9">
                            <input type="text" class="form-control" value="حالت هشدار">
                            <div class="form-text text-warning">
                                <i class="ph-warning me-1"></i>
                                این فیلد دارای پیام هشدار است.
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

    </div>

@endsection

@push('styles')
<style>
/* Custom form styles */
.form-control-feedback {
    position: relative;
}

.form-control-feedback-icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 4;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 2.5rem;
    height: 2.5rem;
    color: #6c757d;
    pointer-events: none;
}

.form-control-feedback-start .form-control-feedback-icon {
    right: 0;
}

.form-control-feedback-end .form-control-feedback-icon {
    left: 0;
}

.form-control-feedback-start .form-control {
    padding-right: 2.5rem;
}

.form-control-feedback-end .form-control {
    padding-left: 2.5rem;
}

/* Responsive form adjustments */
@media (max-width: 768px) {
    .row .col-lg-3,
    .row .col-lg-9 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .row .col-lg-9.offset-lg-3 {
        margin-right: 0;
    }
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add any form-specific JavaScript here
    console.log('Form page loaded successfully');
});
</script>
@endpush
