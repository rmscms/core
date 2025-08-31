// اسکریپت‌های صفحه ساخت اکانت WireGuard
$(document).ready(function() {

    // انیمیشن‌های Collapse
    $('#newUserCollapse').on('show.bs.collapse', function() {
        $(this).prev().find('.ph-caret-down').removeClass('ph-caret-down').addClass('ph-caret-up');
    });

    $('#newUserCollapse').on('hide.bs.collapse', function() {
        $(this).prev().find('.ph-caret-up').removeClass('ph-caret-up').addClass('ph-caret-down');
    });

    $('#extraSettingsCollapse').on('show.bs.collapse', function() {
        $(this).prev().find('.ph-caret-down').removeClass('ph-caret-down').addClass('ph-caret-up');
    });

    $('#extraSettingsCollapse').on('hide.bs.collapse', function() {
        $(this).prev().find('.ph-caret-up').removeClass('ph-caret-up').addClass('ph-caret-down');
    });

    // تنظیم تاریخ انقضا بر اساس گروه انتخاب شده
    $('#subscriptionGroup').change(function() {
        const group = $(this).val();
        const today = new Date();
        let expiryDate = new Date();

        switch(group) {
            case '1month':
                expiryDate.setMonth(today.getMonth() + 1);
                break;
            case '2month':
                expiryDate.setMonth(today.getMonth() + 2);
                break;
            case '3month':
                expiryDate.setMonth(today.getMonth() + 3);
                break;
            case '6month':
                expiryDate.setMonth(today.getMonth() + 6);
                break;
            case '1year':
                expiryDate.setFullYear(today.getFullYear() + 1);
                break;
        }

        $('#expiryDate').val(expiryDate.toISOString().split('T')[0]);
    });

    // نمایش/مخفی کردن تنظیمات قفل
    $('#autoLock').change(function() {
        if ($(this).is(':checked')) {
            $('#lockSettings').show();
            updateLockDate();
        } else {
            $('#lockSettings').hide();
        }
    });

    // بروزرسانی تاریخ قفل
    $('#lockDuration').change(function() {
        updateLockDate();
    });

    function updateLockDate() {
        const duration = parseInt($('#lockDuration').val());
        const today = new Date();
        const lockDate = new Date();
        lockDate.setDate(today.getDate() + duration);
        $('#lockDate').val(lockDate.toISOString().split('T')[0]);
    }

    // جستجوی کاربر (فیک)
    $('#searchUserBtn').click(function() {
        const searchTerm = $('#userSearch').val();
        if (searchTerm.trim() === '') {
            showToast('لطفاً عبارت جستجو را وارد کنید', 'warning');
            return;
        }

        // شبیه‌سازی جستجو
        setTimeout(() => {
            showToast('جستجو انجام شد', 'info');
        }, 500);
    });

    // ارسال فرم
    $('#createWireGuardForm').submit(function(e) {
        e.preventDefault();
        saveAccount(false);
    });

    // دکمه ذخیره و ماندن
    $('#saveAndStayBtn').click(function() {
        saveAccount(true);
    });

    // دکمه تست مودال (برای تست)
    $('#testModalBtn').click(function() {
        testModal();
    });

    function saveAccount(stayOnPage) {
        // بررسی validation فرم
        if (!validateForm()) {
            return;
        }

        // نمایش اسپینر
        showLoadingSpinner();

        // جمع‌آوری داده‌های فرم
        const formData = {
            // اطلاعات کاربر جدید
            first_name: $('#firstName').val(),
            last_name: $('#lastName').val(),
            phone: $('#phoneNumber').val(),
            telegram_username: $('#telegramUsername').val(),

            // اطلاعات اکانت WireGuard
            subscription_group: $('#subscriptionGroup').val(),
            expiry_date: $('#expiryDate').val(),

            // تنظیمات اضافی
            is_paid: $('#isPaid').is(':checked'),
            auto_lock: $('#autoLock').is(':checked'),
            lock_duration: $('#lockDuration').val(),
            lock_date: $('#lockDate').val(),

            // CSRF token
            _token: $('meta[name="csrf-token"]').attr('content')
        };

        // ارسال درخواست AJAX
        $.ajax({
            url: baseUrl + '/wireguard/create',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                hideLoadingSpinner();

                if (response.success) {
                    // نمایش مودال موفقیت با اطلاعات واقعی
                    showAccountCreatedModal(response.data);

                    if (!stayOnPage) {
                        $('#accountCreatedModal').on('hidden.bs.modal', function() {
                            window.location.href = baseUrl + '/wireguardlist';
                        });
                    }

                    showToast('اکانت WireGuard با موفقیت ساخته شد', 'success');
                } else {
                    showToast(response.message || 'خطا در ساخت اکانت', 'error');
                }
            },
            error: function(xhr, status, error) {
                hideLoadingSpinner();

                let errorMessage = 'خطا در ارتباط با سرور';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    // نمایش خطاهای validation
                    const errors = xhr.responseJSON.errors;
                    const errorList = Object.values(errors).flat().join('\n');
                    errorMessage = 'خطاهای اعتبارسنجی:\n' + errorList;
                }

                showToast(errorMessage, 'error');
            }
        });
    }

    // تابع validation فرم
    function validateForm() {
        let isValid = true;

        // بررسی فیلدهای اجباری
        const requiredFields = [
            { id: 'subscriptionGroup', name: 'گروه اشتراک' }
        ];

        requiredFields.forEach(field => {
            const value = $('#' + field.id).val();
            if (!value || value.trim() === '') {
                showToast(field.name + ' الزامی است', 'warning');
                $('#' + field.id).focus();
                isValid = false;
                return false;
            }
        });

        // بررسی شماره تماس
        const phone = $('#phoneNumber').val();
        if (phone && !/^09\d{9}$/.test(phone)) {
            showToast('شماره تماس باید با ۰۹ شروع شود و ۱۱ رقم باشد', 'warning');
            $('#phoneNumber').focus();
            isValid = false;
        }

        return isValid;
    }

    // دکمه برگشت به لیست
    $('#backToListBtn').click(function() {
        window.location.href = baseUrl + '/wireguardlist';
    });

    // دکمه ساخت اکانت جدید در مودال
    $('#createAnotherBtn').click(function() {
        $('#accountCreatedModal').modal('hide');
        resetForm();
    });
});

function resetForm() {
    $('#createWireGuardForm')[0].reset();
    $('#lockSettings').hide();
    showToast('فرم پاک شد', 'info');
}

// تابع بهبود یافته نمایش مودال با اطلاعات واقعی
function showAccountCreatedModal(data) {
    // پر کردن اطلاعات مودال با داده‌های واقعی از سرور
    $('#modalGroup').val(data.subscription_group_name || $('#subscriptionGroup option:selected').text());
    $('#modalExpiry').val(data.expiry_date || $('#expiryDate').val());

    // فایل کانفیگ WireGuard
    const configFile = data.config_file || generateWireGuardConfig(data);
    $('#modalConfigFile').val(configFile);

    // تولید QR کد
    generateQRCode(configFile);

    $('#accountCreatedModal').modal('show');
}

// تابع تولید فایل کانفیگ WireGuard
function generateWireGuardConfig(data) {
    const privateKey = data.private_key || "YFQoWc0ICMXXD+QR6OaOJbNp0PIhXf4kfX1cJ4JqRkY=";
    const publicKey = data.public_key || "bmXOC+F1FxEMF9dyPCKX7g==";
    const address = data.address || "10.0.0.2/24";
    const serverEndpoint = data.server_endpoint || "wireguard.example.com:51820";
    const serverPublicKey = data.server_public_key || "bmXOC+F1FxEMF9dyPCKX7g==";

    return `[Interface]
PrivateKey = ${privateKey}
Address = ${address}
DNS = 8.8.8.8, 8.8.4.4

[Peer]
PublicKey = ${serverPublicKey}
Endpoint = ${serverEndpoint}
AllowedIPs = 0.0.0.0/0
PersistentKeepalive = 25`;
}

// تابع تولید QR کد
function generateQRCode(text) {
    const qrContainer = document.getElementById('qrCode');
    qrContainer.innerHTML = ''; // پاک کردن QR کد قبلی

    QRCode.toCanvas(qrContainer, text, {
        width: 200,
        height: 200,
        margin: 2,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function(error) {
        if (error) {
            console.error('خطا در تولید QR کد:', error);
            qrContainer.innerHTML = '<div class="text-danger">خطا در تولید QR کد</div>';
        }
    });
}

// تابع دانلود فایل کانفیگ
function downloadConfig() {
    const configContent = document.getElementById('modalConfigFile').value;
    const blob = new Blob([configContent], { type: 'text/plain' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'wireguard.conf';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);

    showToast('فایل کانفیگ دانلود شد', 'success');
}

// تابع تست مودال (برای تست بدون سرور)
function testModal() {
    const testData = {
        subscription_group_name: $('#subscriptionGroup option:selected').text() || '۱ ماهه',
        expiry_date: $('#expiryDate').val() || '2024-12-31',
        private_key: 'YFQoWc0ICMXXD+QR6OaOJbNp0PIhXf4kfX1cJ4JqRkY=',
        public_key: 'bmXOC+F1FxEMF9dyPCKX7g==',
        address: '10.0.0.2/24',
        server_endpoint: 'wireguard.example.com:51820',
        server_public_key: 'bmXOC+F1FxEMF9dyPCKX7g=='
    };

    showAccountCreatedModal(testData);
}
