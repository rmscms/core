// اسکریپت‌های مودال QR Code برای صفحات ویرایش VPN
$(document).ready(function() {

    // نمایش/مخفی کردن رشته کد در فرم اصلی
    $('#showConfigBtn').on('click', function() {
        const configInput = $('#config_code');
        const icon = $(this).find('i');

        if (configInput.attr('type') === 'text') {
            configInput.attr('type', 'textarea');
            icon.removeClass('ph-eye').addClass('ph-eye-slash');
        } else {
            configInput.attr('type', 'text');
            icon.removeClass('ph-eye-slash').addClass('ph-eye');
        }
    });

    // دکمه QR Code - استفاده از تابع openModal
    $('#qrCodeBtn').on('click', function() {
        openModal('qrCodeModal');
    });

    // کپی رشته کد - استفاده از تابع copyToClipboard
    $('#copyConfigBtn').on('click', function() {
        copyToClipboard('configTextarea');
    });

    // نمایش/مخفی کردن رشته کد در مودال
    $('#toggleConfigBtn').on('click', function() {
        const textarea = $('#configTextarea');
        const icon = $(this).find('i');

        if (textarea.css('filter') === 'blur(3px)') {
            textarea.css('filter', 'none');
            icon.removeClass('ph-eye-slash').addClass('ph-eye');
        } else {
            textarea.css('filter', 'blur(3px)');
            icon.removeClass('ph-eye').addClass('ph-eye-slash');
        }
    });

    // دانلود QR Code
    $('#downloadQrBtn').on('click', function() {
        showToast('دانلود QR Code شروع شد', 'info');
    });

    // دکمه حذف
    $('#deleteBtn').on('click', function() {
        const protocol = getCurrentProtocol();
        openConfirmModal(
            'تایید حذف',
            `آیا از حذف این کاربر ${protocol} اطمینان دارید؟ این عملیات غیرقابل بازگشت است.`,
            function() {
                showToast('کاربر با موفقیت حذف شد', 'success');
                setTimeout(() => {
                    window.location.href = getListUrl();
                }, 1500);
            }
        );
    });

    // بررسی آنلاین
    $('#checkOnlineBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true);
        btn.html('<i class="ph-arrows-clockwise me-1"></i>در حال بررسی...');

        setTimeout(() => {
            btn.prop('disabled', false);
            btn.html('<i class="ph-arrows-clockwise me-1"></i>بررسی');
            showToast('کاربر آنلاین است', 'success');
        }, 2000);
    });

    // دکمه تاگل وضعیت
    $('#toggleStatusBtn').on('click', function() {
        const btn = $(this);
        const isActive = btn.hasClass('btn-success');
        const action = isActive ? 'غیرفعال‌سازی' : 'فعال‌سازی';
        const protocol = getCurrentProtocol();

        openConfirmModal(
            'تایید تغییر وضعیت',
            `آیا از ${action} این کاربر ${protocol} اطمینان دارید؟`,
            function() {
                if (isActive) {
                    btn.removeClass('btn-success').addClass('btn-warning');
                    btn.html('<i class="ph-pause-circle me-1"></i>مسدود شده');
                } else {
                    btn.removeClass('btn-warning').addClass('btn-success');
                    btn.html('<i class="ph-check-circle me-1"></i>فعال');
                }
                showToast(`کاربر با موفقیت ${action} شد`, 'success');
            }
        );
    });

    // دکمه تمدید
    $('#extendBtn').on('click', function() {
        const months = prompt('تعداد ماه‌های تمدید را وارد کنید:');
        if (months) {
            showToast(`اشتراک کاربر ${months} ماه تمدید شد`, 'success');
        }
    });

    // سوئیچ پرداخت شده
    $('#isPaid').on('change', function() {
        const status = $(this).is(':checked') ? 'فعال' : 'غیرفعال';
        showToast(`وضعیت پرداخت ${status} شد`, 'info');
    });

    // سوئیچ قفل خودکار
    $('#autoLock').on('change', function() {
        const status = $(this).is(':checked') ? 'فعال' : 'غیرفعال';
        const autoLockSettings = $('#autoLockSettings');

        if ($(this).is(':checked')) {
            autoLockSettings.addClass('show');
        } else {
            autoLockSettings.removeClass('show');
        }

        showToast(`قفل خودکار ${status} شد`, 'info');
    });

    // دکمه برگشت به لیست
    $('#backToListBtn').on('click', function() {
        window.location.href = getListUrl();
    });

    // دکمه ذخیره و ماندن
    $('#saveAndStayBtn').on('click', function() {
        openConfirmModal(
            'تایید ذخیره',
            'آیا از ذخیره تغییرات اطمینان دارید؟',
            function() {
                showToast('تغییرات با موفقیت ذخیره شد', 'success');
            }
        );
    });

    // تابع تشخیص پروتکل فعلی
    function getCurrentProtocol() {
        const path = window.location.pathname;
        if (path.includes('v2ray')) return 'V2Ray';
        if (path.includes('wireguard')) return 'WireGuard';
        if (path.includes('wirezero')) return 'WireZero';
        return 'VPN';
    }

    // تابع دریافت URL لیست
    function getListUrl() {
        const path = window.location.pathname;
        if (path.includes('v2ray')) return 'v2raylist';
        if (path.includes('wireguard')) return 'wireguardlist';
        if (path.includes('wirezero')) return 'wirezerolist';
        return 'vpnlist';
    }

    // انیمیشن‌های Collapse
    $('.card-header[data-bs-toggle="collapse"]').on('click', function() {
        const icon = $(this).find('.ph-caret-down, .ph-caret-up');
        if (icon.hasClass('ph-caret-down')) {
            icon.removeClass('ph-caret-down').addClass('ph-caret-up');
        } else {
            icon.removeClass('ph-caret-up').addClass('ph-caret-down');
        }
    });

    // تنظیم تاریخ انقضا
    $('#subscription_type').on('change', function() {
        const type = $(this).val();
        const today = new Date();
        let expiryDate = new Date();

        switch(type) {
            case '1_month':
                expiryDate.setMonth(today.getMonth() + 1);
                break;
            case '3_month':
                expiryDate.setMonth(today.getMonth() + 3);
                break;
            case '6_month':
                expiryDate.setMonth(today.getMonth() + 6);
                break;
            case '1_year':
                expiryDate.setFullYear(today.getFullYear() + 1);
                break;
        }

        // تبدیل به تاریخ شمسی (فیک)
        const persianDate = convertToPersianDate(expiryDate);
        $('#expiry_date').val(persianDate);
    });

    // تابع تبدیل تاریخ میلادی به شمسی (فیک)
    function convertToPersianDate(date) {
        const persianMonths = [
            'فروردین', 'اردیبهشت', 'خرداد', 'تیر', 'مرداد', 'شهریور',
            'مهر', 'آبان', 'آذر', 'دی', 'بهمن', 'اسفند'
        ];

        const year = date.getFullYear() - 621;
        const month = persianMonths[date.getMonth()];
        const day = date.getDate();

        return `${day} ${month} ${year}`;
    }

    // تنظیم رنگ‌های مودال بر اساس پروتکل
    function setModalColors() {
        const protocol = getCurrentProtocol();
        const modalHeader = $('#qrCodeModal .modal-header');
        const modalTitle = $('#qrCodeModal .modal-title');
        const qrTitle = $('#qrCodeModal .text-primary, #qrCodeModal .text-danger, #qrCodeModal .text-info');
        const copyBtn = $('#copyConfigBtn');
        const downloadBtn = $('#downloadQrBtn');

        switch(protocol) {
            case 'V2Ray':
                modalHeader.removeClass('bg-danger bg-info').addClass('bg-primary');
                modalTitle.html('<i class="ph-qr-code me-2"></i>QR Code و رشته کد V2Ray');
                qrTitle.removeClass('text-danger text-info').addClass('text-primary');
                copyBtn.removeClass('btn-danger btn-info').addClass('btn-primary');
                downloadBtn.removeClass('btn-outline-danger btn-outline-info').addClass('btn-outline-primary');
                break;
            case 'WireGuard':
                modalHeader.removeClass('bg-primary bg-info').addClass('bg-danger');
                modalTitle.html('<i class="ph-qr-code me-2"></i>QR Code و رشته کد WireGuard');
                qrTitle.removeClass('text-primary text-info').addClass('text-danger');
                copyBtn.removeClass('btn-primary btn-info').addClass('btn-danger');
                downloadBtn.removeClass('btn-outline-primary btn-outline-info').addClass('btn-outline-danger');
                break;
            case 'WireZero':
                modalHeader.removeClass('bg-primary bg-danger').addClass('bg-info');
                modalTitle.html('<i class="ph-qr-code me-2"></i>QR Code و رشته کد WireZero');
                qrTitle.removeClass('text-primary text-danger').addClass('text-info');
                copyBtn.removeClass('btn-primary btn-danger').addClass('btn-info');
                downloadBtn.removeClass('btn-outline-primary btn-outline-danger').addClass('btn-outline-info');
                break;
        }
    }

    // تنظیم رنگ‌ها هنگام باز شدن مودال
    $('#qrCodeModal').on('show.bs.modal', function() {
        setModalColors();
    });

    // تنظیم محتوای رشته کد بر اساس پروتکل
    function setConfigContent() {
        const protocol = getCurrentProtocol();
        const textarea = $('#configTextarea');

        switch(protocol) {
            case 'V2Ray':
                textarea.val('vmess://eyJhZGQiOiJzZXJ2ZXIuZXhhbXBsZS5jb20iLCJhaWQiOiIwIiwiaWQiOiIxMjM0NTY3ODkwIiwibmV0Ijoid3MiLCJwb3J0IjoiNDQzIiwicHMiOiJ2bWVzcyIsInRscyI6InRscyIsInR5cGUiOiJub25lIiwidiI6IjIifQ==');
                break;
            case 'WireGuard':
            case 'WireZero':
                textarea.val(`[Interface]
PrivateKey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Address = 10.0.0.2/24
DNS = 8.8.8.8

[Peer]
PublicKey = xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx
Endpoint = server.example.com:51820
AllowedIPs = 0.0.0.0/0
PersistentKeepalive = 25`);
                break;
        }
    }

    // تنظیم محتوا هنگام باز شدن مودال
    $('#qrCodeModal').on('show.bs.modal', function() {
        setConfigContent();
    });
});
