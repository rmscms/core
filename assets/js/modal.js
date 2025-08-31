// اسکریپت‌های ساده برای modal
function openReceiptModal() {
    var modal = new bootstrap.Modal(document.getElementById('receiptModal'), {
        backdrop: false,
        keyboard: true
    });
    modal.show();
}

function openModal(modalId) {
    var modal = new bootstrap.Modal(document.getElementById(modalId), {
        backdrop: false,
        keyboard: true
    });
    modal.show();
}

function closeModal(modalId) {
    var modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

// مودال با loading
function openLoadingModal(modalId, loadingText = 'در حال بارگذاری...') {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        const modalBody = modalElement.querySelector('.modal-body');
        if (modalBody) {
            modalBody.innerHTML = `
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted">${loadingText}</p>
                </div>
            `;
        }
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: false,
            keyboard: true
        });
        modal.show();
        return modal;
    }
}

// مودال با confirm
function openConfirmModal(title, message, onConfirm, onCancel) {
    const modalId = 'confirmModal';
    let modalElement = document.getElementById(modalId);

    if (!modalElement) {
        modalElement = document.createElement('div');
        modalElement.id = modalId;
        modalElement.className = 'modal fade';
        modalElement.innerHTML = `
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">انصراف</button>
                        <button type="button" class="btn btn-primary confirm-btn">تایید</button>
                    </div>
                </div>
            </div>
        `;
        document.body.appendChild(modalElement);
    }

    modalElement.querySelector('.modal-title').textContent = title;
    modalElement.querySelector('.modal-body p').textContent = message;

    const modal = new bootstrap.Modal(modalElement, {
        backdrop: false,
        keyboard: true
    });
    modal.show();

    const confirmBtn = modalElement.querySelector('.confirm-btn');
    confirmBtn.onclick = () => {
        modal.hide();
        if (onConfirm) onConfirm();
    };

    modalElement.addEventListener('hidden.bs.modal', () => {
        if (onCancel) onCancel();
    });

    return modal;
}

// حل مشکل iOS
document.addEventListener('DOMContentLoaded', function() {
    var style = document.createElement('style');
    style.textContent = `
        .modal-backdrop {
            display: none !important;
        }
        
        @media (max-width: 768px) {
            .modal-dialog {
               Z-INDEX: 9999;
                margin: 10px;
                margin-top: 70px;
                max-width: calc(100% - 20px);
            }
        }
    `;
    document.head.appendChild(style);
});

// توابع مربوط به مودال اکانت OpenVPN
function showAccountCreatedModal() {
    // پر کردن اطلاعات مودال با داده‌های فیک
    $('#modalUsername').val($('#vpnUsername').val() || 'user123');
    $('#modalPassword').val($('#vpnPassword').val() || 'pass123');
    $('#modalGroup').val($('#subscriptionGroup option:selected').text() || '۱ ماهه');
    $('#modalExpiry').val($('#expiryDate').val() || '2024-12-31');
    $('#modalConnectionLink').val('openvpn://' + ($('#vpnUsername').val() || 'user123') + '@server.com');

    $('#accountCreatedModal').modal('show');
}

function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    element.select();
    element.setSelectionRange(0, 99999);
    document.execCommand('copy');

    // نمایش پیام موفقیت
    const originalText = element.value;
    element.value = 'کپی شد!';
    element.style.backgroundColor = '#d4edda';

    setTimeout(() => {
        element.value = originalText;
        element.style.backgroundColor = '';
    }, 1000);

    showToast('متن کپی شد', 'success');
}

function togglePasswordVisibility(elementId) {
    const element = document.getElementById(elementId);
    if (element.type === 'password') {
        element.type = 'text';
    } else {
        element.type = 'password';
    }
}

function showLoadingSpinner() {
    // ایجاد اسپینر
    const spinner = `
        <div id="loadingSpinner" class="position-fixed top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center" style="background-color: rgba(0,0,0,0.5); z-index: 9999;">
            <div class="bg-white rounded p-4 shadow">
                <button type="button" class="btn btn-light" disabled>
                    <div class="spinner-border me-2" role="status">
                        <span class="visually-hidden">در حال ذخیره...</span>
                    </div>
                    در حال ذخیره...
                </button>
            </div>
        </div>
    `;
    $('body').append(spinner);
}

function hideLoadingSpinner() {
    $('#loadingSpinner').remove();
}

function showToast(message, type = 'info') {
    // استفاده از SweetAlert برای نمایش پیام‌ها
    if (typeof Swal !== 'undefined') {
        const iconMap = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };

        Swal.fire({
            title: message,
            icon: iconMap[type] || 'info',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            background: !document.documentElement.getAttribute('data-color-theme') || document.documentElement.getAttribute('data-color-theme') == 'light' ? '#fff' : '#383940',
            color: !document.documentElement.getAttribute('data-color-theme') || document.documentElement.getAttribute('data-color-theme') == 'light' ? '#333' : '#fff'
        });
    } else {
        // Fallback به alert ساده
        alert(message);
    }
}