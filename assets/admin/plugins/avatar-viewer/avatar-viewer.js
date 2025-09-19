/**
 * RMS Avatar Viewer Plugin
 * 
 * @version 1.0.0
 * @author RMS Core Team
 */

/**
 * نمایش تصویر آواتار کامل در modal
 * 
 * @param {HTMLElement} element المان thumbnail که کلیک شده
 */
function showFullAvatar(element) {
    // دریافت اطلاعات از data attributes
    const itemId = element.getAttribute('data-item-id');
    const fieldName = element.getAttribute('data-field-name');
    const viewerEndpoint = element.getAttribute('data-viewer-endpoint');
    
    if (!itemId || !fieldName || !viewerEndpoint) {
        console.error('Image viewer data not found in element attributes');
        console.log('Missing data:', { itemId, fieldName, viewerEndpoint });
        return;
    }

    // نمایش loading
    Swal.fire({
        title: 'در حال بارگذاری...',
        html: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        background: 'var(--bs-body-bg)',
        color: 'var(--bs-body-color)'
    });

    // درخواست AJAX برای دریافت اطلاعات کامل
    fetch(viewerEndpoint, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // نمایش modal با تصویر کامل
            const fieldDisplayName = fieldName === 'avatar' ? 'آواتار' : 'تصویر';
            
            Swal.fire({
                title: `${fieldDisplayName} (ID: ${data.data.item_id})`,
                html: `
                    <div class="text-center">
                        <img src="${data.data.image_url}" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-width: 100%; max-height: 500px; object-fit: contain;"
                             alt="${fieldDisplayName}" />
                        <div class="mt-3">
                            <small class="text-muted">نام فایل: ${data.data.filename}</small>
                        </div>
                    </div>
                `,
                showConfirmButton: true,
                confirmButtonText: 'بستن',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                allowOutsideClick: true,
                allowEscapeKey: true,
                background: 'var(--bs-body-bg)',
                color: 'var(--bs-body-color)',
                width: 'auto',
                padding: '1.25rem'
            });
        } else {
            // نمایش پیام خطا
            Swal.fire({
                title: 'خطا',
                text: data.message || 'تصویر آواتار یافت نشد',
                icon: 'error',
                confirmButtonText: 'تأیید',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary'
                },
                background: 'var(--bs-body-bg)',
                color: 'var(--bs-body-color)'
            });
        }
    })
    .catch(error => {
        console.error('خطا در دریافت تصویر:', error);
        
        // نمایش خطا
        Swal.fire({
            title: 'خطا در بارگذاری',
            text: 'خطایی در دریافت تصویر رخ داده است',
            icon: 'error',
            confirmButtonText: 'تأیید',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary'
            },
            background: 'var(--bs-body-bg)',
            color: 'var(--bs-body-color)'
        });
    });
}

// Global scope
window.showFullAvatar = showFullAvatar;
window.showImageViewer = showFullAvatar;
