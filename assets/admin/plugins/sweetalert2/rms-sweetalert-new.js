/**
 * RMS SweetAlert2 - Limitless Template Integration
 * 
 * بر اساس demo های اصلی Limitless Template
 * Version: 2.0.0
 * Compatible with: Bootstrap 5 + Limitless Theme + RTL
 */

// اطمینان از وجود SweetAlert2
if (typeof Swal === 'undefined') {
    console.error('❌ SweetAlert2 library not loaded! Please include sweet_alert.min.js first.');
}

// تنظیمات پایه دقیقاً مثل demo های Limitless
const swalInit = Swal.mixin({
    buttonsStyling: false,
    customClass: {
        confirmButton: 'btn btn-primary',
        cancelButton: 'btn btn-light',
        denyButton: 'btn btn-light',
        input: 'form-control'
    },
    // تنظیمات مهم برای جلوگیری از قفل شدن
    allowOutsideClick: true,
    allowEscapeKey: true,
    backdrop: true,
    // انیمیشن‌های Limitless
    showClass: {
        popup: 'animate__animated animate__fadeInDown',
        backdrop: 'swal2-backdrop-show'
    },
    hideClass: {
        popup: 'animate__animated animate__fadeOutUp',
        backdrop: 'swal2-backdrop-hide'
    }
});

/**
 * تابع Confirm اصلی - دقیقاً مثل sweet_combine در demo
 */
window.showConfirm = function(title, text, icon = 'warning', options = {}) {
    const defaultOptions = {
        title: title,
        text: text,
        icon: icon,
        showCancelButton: true,
        confirmButtonText: 'بله، تأیید',
        cancelButtonText: 'انصراف',
        buttonsStyling: false,
        customClass: {
            confirmButton: 'btn btn-success',
            cancelButton: 'btn btn-danger'
        },
        reverseButtons: true, // برای RTL
        focusCancel: true     // focus روی دکمه انصراف
    };

    // ترکیب تنظیمات پیش‌فرض با تنظیمات ارسالی
    const finalOptions = Object.assign({}, defaultOptions, options);
    
    return swalInit.fire(finalOptions);
};

/**
 * پیام موفقیت
 */
window.showSuccess = function(title, text = '', options = {}) {
    const defaultOptions = {
        title: title,
        text: text,
        icon: 'success',
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false
    };
    
    return swalInit.fire(Object.assign({}, defaultOptions, options));
};

/**
 * پیام خطا
 */
window.showError = function(title, text = '', options = {}) {
    const defaultOptions = {
        title: title,
        text: text,
        icon: 'error',
        confirmButtonText: 'باشه'
    };
    
    return swalInit.fire(Object.assign({}, defaultOptions, options));
};

/**
 * پیام هشدار
 */
window.showWarning = function(title, text = '', options = {}) {
    const defaultOptions = {
        title: title,
        text: text,
        icon: 'warning',
        confirmButtonText: 'متوجه شدم'
    };
    
    return swalInit.fire(Object.assign({}, defaultOptions, options));
};

/**
 * پیام اطلاعاتی
 */
window.showInfo = function(title, text = '', options = {}) {
    const defaultOptions = {
        title: title,
        text: text,
        icon: 'info',
        confirmButtonText: 'متوجه شدم'
    };
    
    return swalInit.fire(Object.assign({}, defaultOptions, options));
};

/**
 * Toast notification
 */
window.showToast = function(text, icon = 'success', position = 'top-end', timer = 3000) {
    return swalInit.fire({
        text: text,
        icon: icon,
        toast: true,
        position: position,
        showConfirmButton: false,
        timer: timer,
        timerProgressBar: true
    });
};

/**
 * Loading dialog
 */
window.showLoading = function(title = 'لطفاً صبر کنید...', text = 'در حال پردازش...') {
    return swalInit.fire({
        title: title,
        text: text,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });
};

/**
 * بستن همه dialog ها
 */
window.closeAllAlerts = function() {
    Swal.close();
};

// لاگ موفقیت
console.log('🎯 RMS SweetAlert2 (Limitless Style) initialized successfully');

// Export برای استفاده در ماژول‌ها
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showConfirm,
        showSuccess,
        showError,
        showWarning,
        showInfo,
        showToast,
        showLoading,
        closeAllAlerts,
        swalInit
    };
}