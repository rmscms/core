// اسکریپت‌های صفحه ساخت اکانت OpenVPN
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

    // تولید رمز عبور تصادفی
    $('#generatePasswordBtn').click(function() {
        let password = '';
        for (let i = 0; i < 4; i++) {
            password += Math.floor(Math.random() * 10);
        }
        $('#vpnPassword').val(password);
    });

    // نمایش/مخفی کردن رمز عبور
    $('#togglePasswordBtn').click(function() {
        const passwordField = $('#vpnPassword');
        const icon = $(this).find('i');

        if (passwordField.attr('type') === 'password') {
            passwordField.attr('type', 'text');
            icon.removeClass('ph-eye').addClass('ph-eye-slash');
        } else {
            passwordField.attr('type', 'password');
            icon.removeClass('ph-eye-slash').addClass('ph-eye');
        }
    });

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
    $('#createOpenVpnForm').submit(function(e) {
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

            // اطلاعات اکانت OpenVPN
            vpn_username: $('#vpnUsername').val(),
            vpn_password: $('#vpnPassword').val(),
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
            url: baseUrl + '/openvpn/create',
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
                            window.location.href = baseUrl + '/openvpnlist';
                        });
                    }

                    showToast('اکانت OpenVPN با موفقیت ساخته شد', 'success');
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
            { id: 'vpnUsername', name: 'نام کاربری VPN' },
            { id: 'vpnPassword', name: 'رمز عبور VPN' },
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

        // بررسی طول رمز عبور
        const password = $('#vpnPassword').val();
        if (password && password.length < 4) {
            showToast('رمز عبور باید حداقل ۴ کاراکتر باشد', 'warning');
            $('#vpnPassword').focus();
            isValid = false;
        }

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
        window.location.href = baseUrl + '/openvpnlist';
    });

    // دکمه ساخت اکانت جدید در مودال
    $('#createAnotherBtn').click(function() {
        $('#accountCreatedModal').modal('hide');
        resetForm();
    });
});

function resetForm() {
    $('#createOpenVpnForm')[0].reset();
    $('#lockSettings').hide();
    showToast('فرم پاک شد', 'info');
}

// تابع بهبود یافته نمایش مودال با اطلاعات واقعی
function showAccountCreatedModal(data) {
    // پر کردن اطلاعات مودال با داده‌های واقعی از سرور
    $('#modalUsername').val(data.vpn_username || $('#vpnUsername').val());
    $('#modalPassword').val(data.vpn_password || $('#vpnPassword').val());
    $('#modalGroup').val(data.subscription_group_name || $('#subscriptionGroup option:selected').text());
    $('#modalExpiry').val(data.expiry_date || $('#expiryDate').val());
    $('#modalConnectionLink').val(data.connection_link || 'openvpn://' + (data.vpn_username || $('#vpnUsername').val()) + '@server.com');

    $('#accountCreatedModal').modal('show');
}

// تابع تست مودال (برای تست بدون سرور)
function testModal() {
    const testData = {
        vpn_username: $('#vpnUsername').val() || 'testuser123',
        vpn_password: $('#vpnPassword').val() || 'testpass123',
        subscription_group_name: $('#subscriptionGroup option:selected').text() || '۱ ماهه',
        expiry_date: $('#expiryDate').val() || '2024-12-31',
        connection_link: 'openvpn://testuser123@vpn.example.com'
    };

    showAccountCreatedModal(testData);
}
