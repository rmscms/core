/**
 * RMS Avatar Viewer Plugin
 * Compatible with Limitless Theme Dark/Light modes
 * 
 * @version 1.1.0
 * @author RMS Core Team
 */

/**
 * Get current theme colors for SweetAlert2
 * Compatible with Limitless theme's dark/light modes
 */
function getLimitlessThemeColors() {
    // Check if dark theme is active
    const isDark = document.documentElement.getAttribute('data-color-theme') === 'dark' ||
                   document.documentElement.getAttribute('data-bs-theme') === 'dark' ||
                   document.body.classList.contains('dark-mode');
    
    if (isDark) {
        return {
            background: '#2d2f33',  // Limitless dark card background
            color: '#ffffff',       // White text for dark mode
            border: '1px solid rgba(255, 255, 255, 0.125)' // Light border
        };
    } else {
        return {
            background: '#ffffff',  // White background for light mode
            color: '#333333',       // Dark text for light mode  
            border: '1px solid rgba(0, 0, 0, 0.125)' // Dark border
        };
    }
}

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
    const themeColors = getLimitlessThemeColors();
    
    Swal.fire({
        title: 'در حال بارگذاری...',
        html: '<div class="d-flex justify-content-center"><div class="spinner-border text-primary" role="status"></div></div>',
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        background: themeColors.background,
        color: themeColors.color,
        customClass: {
            popup: 'limitless-swal-popup'
        }
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
            
            const successThemeColors = getLimitlessThemeColors();
            
            Swal.fire({
                title: `${fieldDisplayName} (ID: ${data.data.item_id})`,
                html: `
                    <div class="text-center">
                        <img src="${data.data.image_url}" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-width: 100%; max-height: 500px; object-fit: contain; border: ${successThemeColors.border};"
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
                    confirmButton: 'btn btn-primary',
                    popup: 'limitless-swal-popup limitless-image-viewer'
                },
                allowOutsideClick: true,
                allowEscapeKey: true,
                background: successThemeColors.background,
                color: successThemeColors.color,
                width: 'auto',
                padding: '1.25rem'
            });
        } else {
            // نمایش پیام خطا
            const errorThemeColors = getLimitlessThemeColors();
            
            Swal.fire({
                title: 'خطا',
                text: data.message || 'تصویر آواتار یافت نشد',
                icon: 'error',
                confirmButtonText: 'تأیید',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary',
                    popup: 'limitless-swal-popup'
                },
                background: errorThemeColors.background,
                color: errorThemeColors.color
            });
        }
    })
    .catch(error => {
        console.error('خطا در دریافت تصویر:', error);
        
        // نمایش خطا
        const catchErrorThemeColors = getLimitlessThemeColors();
        
        Swal.fire({
            title: 'خطا در بارگذاری',
            text: 'خطایی در دریافت تصویر رخ داده است',
            icon: 'error',
            confirmButtonText: 'تأیید',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-primary',
                popup: 'limitless-swal-popup'
            },
            background: catchErrorThemeColors.background,
            color: catchErrorThemeColors.color
        });
    });
}

// Global scope
window.showFullAvatar = showFullAvatar;
window.showImageViewer = showFullAvatar;
