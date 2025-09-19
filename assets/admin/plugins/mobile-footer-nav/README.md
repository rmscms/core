# RMS Mobile Footer Navigation Plugin

پلاگین بهینه‌سازی navigation پایین صفحه برای نمایش موبایل در پروژه RMS2.

## ویژگی‌ها

### 🎯 **Core Features:**
- **Bootstrap Tooltips** - راهنمای hover حرفه‌ای با auto-hide
- **Floating Menu** - منوی شناور central add button با انیمیشن زیبا
- **Smooth Animations** - انیمیشن‌های روان و زیبا
- **Touch Feedback** - بازخورد لمسی برای دستگاه‌های موبایل
- **Badge Management** - مدیریت badge های dinamik
- **Haptic Feedback** - لرزش هاپتیک (در دستگاه‌های پشتیبان)

### 🌙 **Theme & Accessibility:**
- **Dark Theme Support** - پشتیبانی کامل تم تیره
- **RTL Support** - پشتیبانی کامل راست‌چین
- **Accessibility** - ویژگی‌های دسترسی کامل
- **Responsive Design** - طراحی responsive برای همه اندازه‌ها

### 📱 **Mobile Optimized:**
- **Touch Events** - مدیریت رویدادهای لمسی
- **Visual Feedback** - بازخورد بصری فوری
- **Performance** - بهینه شده برای عملکرد موبایل

## نحوه استفاده

### خودکار (پیشنهادی)
```javascript
// پلاگین خودکار فعال می‌شود - نیازی به کد اضافی نیست
```

### دستی
```javascript
// ایجاد instance جدید
const mobileNav = new RMSMobileFooterNav({
    enableVibration: false,
    enableBadgeUpdates: false
});
```

## API Methods

### Badge Management
```javascript
// به‌روزرسانی badge تیکت‌ها
window.rmsMobileFooterNav.updateBadgeCount('[href*="tickets"]', 5);

// نمایش loading برای badge
window.rmsMobileFooterNav.showBadgeLoading('[href*="tickets"]');

// مخفی کردن loading
window.rmsMobileFooterNav.hideBadgeLoading('[href*="tickets"]');
```

### Utility Functions
```javascript
// به‌روزرسانی سریع badge ها
RMSMobileFooterNav.utils.updateBadge('tickets', 10);
RMSMobileFooterNav.utils.updateBadge('deposits', 3);

// نمایش loading
RMSMobileFooterNav.utils.showLoading('tickets');
```

### Tooltip Management
```javascript
// تازه‌سازی tooltips
window.rmsMobileFooterNav.refreshTooltips();
```

## تنظیمات پیش‌فرض

```javascript
{
    selector: '.mobile-footer-nav',
    tooltipConfig: {
        trigger: 'hover focus',
        delay: { show: 300, hide: 100 },
        boundary: 'viewport',
        customClass: 'mobile-footer-nav-tooltip'
    },
    animationDuration: 300,
    enableBadgeUpdates: true,
    enableVibration: true
}
```

## کلاس‌های CSS مهم

- `.mobile-footer-nav` - کلاس اصلی navigation
- `.nav-icon-circle` - آیکون‌های navigation
- `.floating-item` - آیتم‌های floating menu
- `.badge.loading` - حالت loading برای badge ها
- `.touching` - کلاس موقت برای touch feedback

## Events & Animations

### Touch Events
- `touchstart` - شروع لمس با haptic feedback
- `touchend` - پایان لمس با حذف effect
- `click` - انیمیشن click با scale effect

### Floating Menu
- **Toggle** - کلیک دکمه add برای باز/بسته کردن منو
- **Outside Click** - بسته شدن با کلیک بیرون منو
- **ESC Key** - بسته شدن با کلید ESC
- **Icon Rotation** - چرخش 45 درجه آیکون + در زمان باز بودن
- **Staggered Animation** - انیمیشن تدریجی آیتم‌ها

### Badge Updates
- به‌روزرسانی خودکار هر 30 ثانیه
- انیمیشن loading با shimmer effect
- پشتیبانی از 99+ برای اعداد بالا

### Tooltip Features
- **Auto-hide** - خودکار بعد از 3 ثانیه محو می‌شوند
- **Dark Theme** - پس‌زمینه مشکی و متن سفید در حالت دارک
- **Light Theme** - پس‌زمینه خاکستری و متن سفید در حالت روشن
- **Smooth Animation** - انیمیشن نرم ظاهر/مخفی شدن
- **Custom Styling** - استایل سفارشی مطابق Limitless

## سازگاری

- ✅ Bootstrap 5
- ✅ Limitless Theme  
- ✅ jQuery (اختیاری)
- ✅ Mobile Devices
- ✅ Touch Devices
- ✅ Desktop Browsers

## مثال استفاده در Blade

```blade
<nav class="navbar fixed-bottom mobile-footer-nav">
    <a href="#" data-bs-toggle="tooltip" data-bs-title="Dashboard">
        <div class="nav-icon-circle bg-primary">
            <i class="ph-chart-line"></i>
        </div>
        <span>Dashboard</span>
    </a>
</nav>
```

## نسخه

**Version:** 1.0.0  
**Author:** RMS Core Team  
**Date:** 2025-01-18

## نکات مهم

- فقط در موبایل فعال می‌شود
- tooltips خودکار مدیریت می‌شوند
- Badge ها dinamik به‌روزرسانی می‌شوند  
- مدیریت memory با destroy method
- پشتیبانی کامل از accessibility