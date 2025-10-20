/**
 * RMS Confirm Modal Plugin
 * 
 * Beautiful, reusable confirmation modals with dark theme support
 * Compatible with Bootstrap 5 and Limitless template
 * 
 * @version 1.0.0
 * @author RMS Core Team
 * 
 * Usage:
 * const modal = new RMSConfirmModal({
 *     title: 'عنوان',
 *     message: 'پیام اصلی',
 *     description: 'توضیحات اضافی',
 *     icon: 'ph-trash',
 *     iconColor: '#dc3545',
 *     confirmText: 'تایید',
 *     confirmClass: 'btn-danger',
 *     cancelText: 'انصراف'
 * });
 * 
 * const confirmed = await modal.show();
 * if (confirmed) {
 *     // User confirmed
 * }
 */

class RMSConfirmModal {
    constructor(options = {}) {
        this.options = {
            title: 'تایید عملیات',
            message: 'آیا مطمئن هستید؟',
            description: null,
            icon: 'ph-warning',
            iconColor: null,
            iconBackground: null,
            confirmText: 'بله، تایید می‌کنم',
            confirmClass: 'btn-primary',
            confirmIcon: 'ph-check',
            cancelText: 'انصراف',
            cancelClass: 'btn-outline-secondary',
            cancelIcon: 'ph-x',
            closeOnBackdrop: true,
            closeOnEscape: true,
            focusConfirm: true,
            animation: 'modalSlideIn',
            ...options
        };
    }

    /**
     * Show the modal and return promise
     */
    async show() {
        return new Promise((resolve) => {
            this.resolve = resolve;
            this.render();
            this.attachEvents();
        });
    }

    /**
     * Render modal HTML
     */
    render() {
        // Remove existing modals
        document.querySelectorAll('.rms-confirm-modal-container').forEach(modal => modal.remove());

        // Create modal backdrop
        this.backdrop = document.createElement('div');
        this.backdrop.className = 'rms-confirm-modal-container position-fixed w-100 h-100';
        this.backdrop.style.cssText = `
            top: 0;
            left: 0;
            z-index: 10000;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
        `;

        // Create modal content
        const isDarkTheme = document.documentElement.getAttribute('data-color-theme') === 'dark';
        this.modal = document.createElement('div');
        this.modal.className = 'rms-confirm-modal bg-white rounded shadow-lg';
        this.modal.style.cssText = `
            background: ${isDarkTheme ? '#2d2f33' : '#ffffff'} !important;
            color: ${isDarkTheme ? '#ffffff' : '#000000'} !important;
            border-radius: 16px;
            min-width: 400px;
            max-width: 500px;
            padding: 0;
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            animation: ${this.options.animation} 0.3s ease-out;
        `;

        // Add keyframe animations if not exists
        this.addAnimations();

        // Get icon background
        const iconBg = this.options.iconBackground || this.getIconBackground();

        // Build modal HTML
        this.modal.innerHTML = `
            <div class="p-4">
                <div class="text-center mb-3">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" 
                         style="width: 64px; height: 64px; background: ${iconBg}; border-radius: 50%; box-shadow: 0 4px 12px ${this.getIconShadow()};">
                        <i class="${this.options.icon} fs-1 text-white"></i>
                    </div>
                    <h5 class="mb-2 fw-bold">${this.options.title}</h5>
                    <p class="mb-0 text-muted">${this.options.message}</p>
                    ${this.options.description ? `<small class="text-muted d-block mt-2">${this.options.description}</small>` : ''}
                </div>
                
                <div class="d-flex gap-2 justify-content-end mt-4">
                    <button type="button" class="btn ${this.options.cancelClass}" data-action="cancel">
                        <i class="${this.options.cancelIcon} me-2"></i>
                        ${this.options.cancelText}
                    </button>
                    <button type="button" class="btn ${this.options.confirmClass}" data-action="confirm">
                        <i class="${this.options.confirmIcon} me-2"></i>
                        ${this.options.confirmText}
                    </button>
                </div>
            </div>
        `;

        this.backdrop.appendChild(this.modal);
        document.body.appendChild(this.backdrop);

        // Focus management
        if (this.options.focusConfirm) {
            this.modal.querySelector('[data-action="confirm"]').focus();
        } else {
            this.modal.querySelector('[data-action="cancel"]').focus();
        }
    }

    /**
     * Attach event listeners
     */
    attachEvents() {
        // Confirm button
        this.modal.querySelector('[data-action="confirm"]').addEventListener('click', () => {
            this.close(true);
        });

        // Cancel button
        this.modal.querySelector('[data-action="cancel"]').addEventListener('click', () => {
            this.close(false);
        });

        // Close on backdrop click
        if (this.options.closeOnBackdrop) {
            this.backdrop.addEventListener('click', (e) => {
                if (e.target === this.backdrop) {
                    this.close(false);
                }
            });
        }

        // Close on Escape key
        if (this.options.closeOnEscape) {
            this.escapeHandler = (e) => {
                if (e.key === 'Escape') {
                    this.close(false);
                }
            };
            document.addEventListener('keydown', this.escapeHandler);
        }
    }

    /**
     * Close modal and resolve promise
     */
    close(result) {
        if (this.escapeHandler) {
            document.removeEventListener('keydown', this.escapeHandler);
        }
        
        if (this.backdrop && this.backdrop.parentNode) {
            this.backdrop.remove();
        }
        
        if (this.resolve) {
            this.resolve(result);
        }
    }

    /**
     * Add CSS animations
     */
    addAnimations() {
        if (!document.querySelector('#rms-confirm-modal-animations')) {
            const style = document.createElement('style');
            style.id = 'rms-confirm-modal-animations';
            style.textContent = `
                @keyframes modalSlideIn {
                    from { transform: scale(0.9); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
                @keyframes modalFadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes modalSlideDown {
                    from { transform: translateY(-50px); opacity: 0; }
                    to { transform: translateY(0); opacity: 1; }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Get icon background based on confirm class
     */
    getIconBackground() {
        if (this.options.iconColor) {
            return this.options.iconColor;
        }

        const gradients = {
            'btn-danger': 'linear-gradient(135deg, #dc3545 0%, #c82333 100%)',
            'btn-success': 'linear-gradient(135deg, #16a34a 0%, #15803d 100%)',
            'btn-warning': 'linear-gradient(135deg, #ffc107 0%, #e0a800 100%)',
            'btn-info': 'linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%)',
            'btn-primary': 'linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%)',
        };

        return gradients[this.options.confirmClass] || gradients['btn-primary'];
    }

    /**
     * Get icon shadow color
     */
    getIconShadow() {
        const shadows = {
            'btn-danger': 'rgba(220, 53, 69, 0.3)',
            'btn-success': 'rgba(22, 163, 74, 0.3)',
            'btn-warning': 'rgba(255, 193, 7, 0.3)',
            'btn-info': 'rgba(13, 202, 240, 0.3)',
            'btn-primary': 'rgba(13, 110, 253, 0.3)',
        };

        return shadows[this.options.confirmClass] || shadows['btn-primary'];
    }
}

/**
 * Global helper functions for common use cases
 */
window.RMSConfirmModal = RMSConfirmModal;

// Danger/Delete confirmation
window.confirmDelete = async function(itemName = 'این آیتم') {
    const modal = new RMSConfirmModal({
        title: '🗑️ حذف',
        message: `آیا مطمئن هستید که می‌خواهید ${itemName} را حذف کنید؟`,
        description: 'این عملیات قابل بازگشت نیست',
        icon: 'ph-trash',
        confirmText: 'بله، حذف کن',
        confirmClass: 'btn-danger',
        confirmIcon: 'ph-trash'
    });
    return await modal.show();
};

// Success/Unlock confirmation
window.confirmUnlock = async function(userName) {
    const modal = new RMSConfirmModal({
        title: '🔓 رفع مسدودیت',
        message: `آیا مطمئن هستید که می‌خواهید مسدودیت کاربر «${userName}» را رفع کنید؟`,
        description: 'کاربر می‌تواند دوباره واریز انجام دهد',
        icon: 'ph-lock-open',
        confirmText: 'بله، رفع مسدودیت',
        confirmClass: 'btn-success',
        confirmIcon: 'ph-lock-open'
    });
    return await modal.show();
};

// Warning confirmation
window.confirmWarning = async function(message, description = null) {
    const modal = new RMSConfirmModal({
        title: '⚠️ هشدار',
        message: message,
        description: description,
        icon: 'ph-warning',
        confirmText: 'بله، ادامه می‌دهم',
        confirmClass: 'btn-warning',
        confirmIcon: 'ph-check'
    });
    return await modal.show();
};

// Generic confirmation
window.confirmAction = async function(title, message, options = {}) {
    const modal = new RMSConfirmModal({
        title: title,
        message: message,
        ...options
    });
    return await modal.show();
};

console.log('✅ RMS Confirm Modal Plugin loaded');
