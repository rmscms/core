$(document).ready(function() {
    // تنظیم تاریخ انقضا بر اساس انتخاب گروه اشتراک
    $('#subscriptionGroup').on('change', function() {
        const selectedGroup = $(this).val();
        if (selectedGroup) {
            const expiryDate = calculateExpiryDate(selectedGroup);
            $('#expiryDate').val(expiryDate);
        } else {
            $('#expiryDate').val('');
        }
    });

    // تنظیم تاریخ قفل بر اساس انتخاب مدت قفل
    $('#lockDuration').on('change', function() {
        const selectedDuration = $(this).val();
        if (selectedDuration) {
            const lockDate = calculateLockDate(selectedDuration);
            $('#lockDate').val(lockDate);
        } else {
            $('#lockDate').val('');
        }
    });

    // نمایش/مخفی کردن تنظیمات قفل
    $('#autoLock').on('change', function() {
        if ($(this).is(':checked')) {
            $('#lockSettings').show();
        } else {
            $('#lockSettings').hide();
        }
    });

    // دکمه برگشت به لیست
    $('#backToListBtn').on('click', function() {
        window.location.href = baseUrl + '/wirezerolist';
    });

    // دکمه ذخیره و ماندن
    $('#saveAndStayBtn').on('click', function() {
        saveAccount(true);
    });

    // دکمه ذخیره
    $('#saveBtn').on('click', function(e) {
        e.preventDefault();
        saveAccount(false);
    });

    // دکمه تست مودال
    $('#testModalBtn').on('click', function() {
        testModal();
    });

    // دکمه ساخت اکانت جدید در مودال
    $('#createAnotherBtn').on('click', function() {
        $('#accountCreatedModal').modal('hide');
        // ریست کردن فرم
        $('#createWireZeroForm')[0].reset();
        $('#expiryDate').val('');
        $('#lockDate').val('');
        $('#lockSettings').hide();
    });
});

// تابع ذخیره اکانت
function saveAccount(stayOnPage = false) {
    if (!validateForm()) {
        return;
    }

    // نمایش اسپینر
    $('#saveBtn').prop('disabled', true).html('<i class="ph-spinner ph-spin me-2"></i>در حال ذخیره...');
    $('#saveAndStayBtn').prop('disabled', true).html('<i class="ph-spinner ph-spin me-2"></i>در حال ذخیره...');

    // جمع‌آوری اطلاعات فرم
    const formData = {
        _token: $('input[name="_token"]').val(),
        first_name: $('#firstName').val(),
        last_name: $('#lastName').val(),
        phone: $('#phoneNumber').val(),
        telegram_username: $('#telegramUsername').val(),
        subscription_group: $('#subscriptionGroup').val(),
        expiry_date: $('#expiryDate').val(),
        is_paid: $('#isPaid').is(':checked'),
        auto_lock: $('#autoLock').is(':checked'),
        lock_duration: $('#lockDuration').val(),
        lock_date: $('#lockDate').val()
    };

    // ارسال درخواست AJAX
    $.ajax({
        url: baseUrl + '/wirezero/create',
        type: 'POST',
        data: formData,
        success: function(response) {
            // مخفی کردن اسپینر
            $('#saveBtn').prop('disabled', false).html('<i class="ph-floppy-disk me-2"></i><span class="d-none d-sm-inline">ذخیره</span><span class="d-inline d-sm-none">ذخیره</span>');
            $('#saveAndStayBtn').prop('disabled', false).html('<i class="ph-plus-circle me-2"></i><span class="d-none d-sm-inline">ذخیره و ماندن</span><span class="d-inline d-sm-none">ذخیره و ماندن</span>');

            // نمایش مودال موفقیت
            showAccountCreatedModal(response.data);

            // نمایش پیام موفقیت
            showToast('success', 'اکانت WireZero با موفقیت ساخته شد!');

            // اگر نباید در صفحه بماند، به لیست برو
            if (!stayOnPage) {
                setTimeout(function() {
                    $('#accountCreatedModal').on('hidden.bs.modal', function() {
                        window.location.href = baseUrl + '/wirezerolist';
                    });
                }, 2000);
            }
        },
        error: function(xhr) {
            // مخفی کردن اسپینر
            $('#saveBtn').prop('disabled', false).html('<i class="ph-floppy-disk me-2"></i><span class="d-none d-sm-inline">ذخیره</span><span class="d-inline d-sm-none">ذخیره</span>');
            $('#saveAndStayBtn').prop('disabled', false).html('<i class="ph-plus-circle me-2"></i><span class="d-none d-sm-inline">ذخیره و ماندن</span><span class="d-inline d-sm-none">ذخیره و ماندن</span>');

            let errorMessage = 'خطا در ذخیره اکانت';

            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                errorMessage = Object.values(errors).flat().join('\n');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }

            showToast('error', errorMessage);
        }
    });
}

// تابع اعتبارسنجی فرم
function validateForm() {
    const subscriptionGroup = $('#subscriptionGroup').val();
    const phoneNumber = $('#phoneNumber').val();

    // بررسی گروه اشتراک
    if (!subscriptionGroup) {
        showToast('error', 'لطفاً گروه اشتراک را انتخاب کنید');
        $('#subscriptionGroup').focus();
        return false;
    }

    // بررسی شماره تماس (اگر وارد شده باشد)
    if (phoneNumber && !phoneNumber.match(/^09\d{9}$/)) {
        showToast('error', 'شماره تماس باید با 09 شروع شود و 11 رقم باشد');
        $('#phoneNumber').focus();
        return false;
    }

    return true;
}

// تابع نمایش مودال اکانت ساخته شده
function showAccountCreatedModal(data) {
    // پر کردن فیلدهای مودال
    $('#modalGroup').val(data.group || $('#subscriptionGroup option:selected').text());
    $('#modalExpiry').val(data.expiry_date || $('#expiryDate').val());

    // تولید فایل کانفیگ WireZero
    const configFile = generateWireZeroConfig(data);
    $('#modalConfigFile').val(configFile);

    // تولید QR کد
    generateQRCode(configFile);

    // نمایش مودال
    $('#accountCreatedModal').modal('show');
}

// تابع تولید فایل کانفیگ WireZero
function generateWireZeroConfig(data) {
    const privateKey = data.private_key || 'dGVzdF9wcml2YXRlX2tleV9mb3Jfd2lyZXplcm8=';
    const publicKey = data.public_key || 'dGVzdF9wdWJsaWNfa2V5X2Zvcl93aXJlemVybw==';
    const address = data.address || '10.0.0.2/24';
    const serverEndpoint = data.server_endpoint || 'wirezero.example.com:51820';
    const serverPublicKey = data.server_public_key || 'c2VydmVyX3B1YmxpY19rZXlfZm9yX3dpcmV6ZXJv';

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
    const qrCodeContainer = document.getElementById('qrCode');
    qrCodeContainer.innerHTML = '';

    QRCode.toCanvas(qrCodeContainer, text, {
        width: 200,
        margin: 2,
        color: {
            dark: '#000000',
            light: '#FFFFFF'
        }
    }, function(error) {
        if (error) {
            console.error('خطا در تولید QR کد:', error);
            qrCodeContainer.innerHTML = '<p class="text-danger">خطا در تولید QR کد</p>';
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
    a.download = 'wirezero-config.conf';
    document.body.appendChild(a);
    a.click();
    window.URL.revokeObjectURL(url);
    document.body.removeChild(a);

    showToast('success', 'فایل کانفیگ دانلود شد');
}

// تابع تست مودال (برای تست بدون بک‌اند)
function testModal() {
    const testData = {
        group: '۳ ماهه',
        expiry_date: '2024-06-15',
        private_key: 'dGVzdF9wcml2YXRlX2tleV9mb3Jfd2lyZXplcm8=',
        public_key: 'dGVzdF9wdWJsaWNfa2V5X2Zvcl93aXJlemVybw==',
        address: '10.0.0.2/24',
        server_endpoint: 'wirezero.example.com:51820',
        server_public_key: 'c2VydmVyX3B1YmxpY19rZXlfZm9yX3dpcmV6ZXJv'
    };

    showAccountCreatedModal(testData);
    showToast('info', 'مودال تست نمایش داده شد');
}

// تابع محاسبه تاریخ انقضا
function calculateExpiryDate(subscriptionGroup) {
    const today = new Date();
    let expiryDate = new Date(today);

    switch(subscriptionGroup) {
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

    return expiryDate.toISOString().split('T')[0];
}

// تابع محاسبه تاریخ قفل
function calculateLockDate(duration) {
    const today = new Date();
    const lockDate = new Date(today);
    lockDate.setDate(today.getDate() + parseInt(duration));
    return lockDate.toISOString().split('T')[0];
}
