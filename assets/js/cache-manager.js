/**
 * RMS Cache Manager
 * 
 * Professional cache management system with Ajax functionality
 * Provides easy cache clearing with beautiful toast notifications
 * 
 * Features:
 * - Clear all cache
 * - Clear specific cache types  
 * - Cache status monitoring
 * - Professional UI feedback
 * - Error handling
 * 
 * @version 1.0.0
 * @author RMS Core Team
 */

class RMSCacheManager {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/admin/cache',
            toastDuration: 5000,
            showSuccessDetails: true,
            enableSounds: false,
            confirmBeforeClear: true,
            autoRefreshStatus: false,
            refreshInterval: 30000, // 30 seconds
            ...options
        };
        
        this.isProcessing = false;
        this.statusInterval = null;
        
        this.init();
    }

    /**
     * Initialize the cache manager
     */
    init() {
        this.setupEventListeners();
        
        if (this.options.autoRefreshStatus) {
            this.startStatusRefresh();
        }

        // Load initial status if status container exists
        if (document.querySelector('.cache-status-container')) {
            this.refreshStatus();
        }

        console.log('🧹 RMS Cache Manager initialized');
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Clear all cache button with improved delegation
        const handleCacheClick = async (e) => {
            // Check if clicked element or its parent has the data attribute
            let target = e.target;
            let found = false;
            
            // More thorough search up the DOM tree
            while (target && target !== document && !found) {
                const cacheAction = target.getAttribute && target.getAttribute('data-cache-action');
                
                if (cacheAction === 'clear-all') {
                    console.log('🧹 Cache clear button clicked!', target);
                    e.preventDefault();
                    e.stopPropagation();
                    found = true;
                    await this.clearAllCache();
                    return;
                }
                target = target.parentElement;
            }
        };
        
        // Add event listeners with capture phase for better reliability
        document.addEventListener('click', handleCacheClick, true);
        document.addEventListener('click', handleCacheClick, false);

        // Clear specific cache buttons with improved handling
        const handleSpecificCacheClick = async (e) => {
            let target = e.target;
            let found = false;
            
            while (target && target !== document && !found) {
                const cacheAction = target.getAttribute && target.getAttribute('data-cache-action');
                
                if (cacheAction === 'clear-specific') {
                    const cacheType = target.getAttribute('data-cache-type');
                    if (cacheType) {
                        console.log('🗂️ Specific cache clear clicked:', cacheType);
                        e.preventDefault();
                        e.stopPropagation();
                        found = true;
                        await this.clearSpecificCache(cacheType);
                        return;
                    }
                }
                target = target.parentElement;
            }
        };
        
        document.addEventListener('click', handleSpecificCacheClick, true);
        document.addEventListener('click', handleSpecificCacheClick, false);

        // Refresh status button with improved handling
        const handleRefreshClick = async (e) => {
            let target = e.target;
            let found = false;
            
            while (target && target !== document && !found) {
                const cacheAction = target.getAttribute && target.getAttribute('data-cache-action');
                
                if (cacheAction === 'refresh-status') {
                    console.log('🔄 Refresh status clicked!');
                    e.preventDefault();
                    e.stopPropagation();
                    found = true;
                    await this.refreshStatus();
                    return;
                }
                target = target.parentElement;
            }
        };
        
        document.addEventListener('click', handleRefreshClick, true);
        document.addEventListener('click', handleRefreshClick, false);

        // Clear cache keyboard shortcut (Ctrl+Shift+C)
        document.addEventListener('keydown', async (e) => {
            if (e.ctrlKey && e.shiftKey && e.code === 'KeyC') {
                e.preventDefault();
                await this.clearAllCache();
            }
        });
    }

    /**
     * Clear all cache types
     */
    async clearAllCache() {
        if (this.isProcessing) {
            this.showToast('⏳ در حال پردازش...', 'warning');
            return;
        }

        if (this.options.confirmBeforeClear) {
            if (!await this.showModernConfirm('🧹 پاک کردن کش', 'آیا مطمئن هستید که می‌خواهید تمام کش‌های سیستم را پاک کنید؟', 'این عمل قابل بازگشت نیست و ممکن است سرعت اولیه سایت را کاهش دهد.')) {
                return;
            }
        }

        this.isProcessing = true;
        this.updateButtonStates(true);

        try {
            const response = await this.makeRequest('POST', `${this.options.baseUrl}/clear`);
            
            if (response.success) {
                this.showSuccessToast('تمام کش‌ها پاک شدند! 🚀', response.details);
                this.refreshStatus(1000); // Refresh after 1 second
                this.playSound('success');
                
                // Log clear action
                console.log('✅ Cache cleared successfully:', response.results);
            } else {
                this.showToast(response.message || '❌ خطا در پاک کردن کش‌ها', 'error');
                this.playSound('error');
            }
        } catch (error) {
            this.handleError(error, 'خطا در پاک کردن کش‌ها');
        } finally {
            this.isProcessing = false;
            this.updateButtonStates(false);
        }
    }

    /**
     * Clear specific cache type
     */
    async clearSpecificCache(type) {
        if (this.isProcessing) {
            this.showToast('⏳ در حال پردازش...', 'warning');
            return;
        }

        this.isProcessing = true;
        this.updateButtonStates(true, type);

        try {
            const response = await this.makeRequest('POST', `${this.options.baseUrl}/clear/${type}`);
            
            if (response.success) {
                this.showToast(response.message, 'success');
                this.refreshStatus(500); // Quick refresh
                this.playSound('success');
                
                console.log(`✅ ${type} cache cleared successfully`);
            } else {
                this.showToast(response.message || `❌ خطا در پاک کردن کش ${type}`, 'error');
                this.playSound('error');
            }
        } catch (error) {
            this.handleError(error, `خطا در پاک کردن کش ${type}`);
        } finally {
            this.isProcessing = false;
            this.updateButtonStates(false, type);
        }
    }

    /**
     * Refresh cache status
     */
    async refreshStatus(delay = 0) {
        if (delay > 0) {
            setTimeout(() => this._doRefreshStatus(), delay);
        } else {
            await this._doRefreshStatus();
        }
    }

    async _doRefreshStatus() {
        try {
            const response = await this.makeRequest('GET', `${this.options.baseUrl}/status`);
            
            if (response.success) {
                this.updateStatusDisplay(response.status);
                console.log('📊 Cache status updated');
            }
        } catch (error) {
            console.warn('Failed to refresh cache status:', error);
        }
    }

    /**
     * Update status display
     */
    updateStatusDisplay(status) {
        Object.keys(status).forEach(cacheType => {
            const cache = status[cacheType];
            const statusElement = document.querySelector(`[data-cache-status="${cacheType}"]`);
            
            if (statusElement) {
                // Update status indicator
                const indicator = statusElement.querySelector('.cache-status-indicator');
                if (indicator) {
                    const isActive = cache.active || cache.cached;
                    indicator.className = `cache-status-indicator ${isActive ? 'active' : 'inactive'}`;
                }

                // Update size information
                const sizeElement = statusElement.querySelector('.cache-size');
                if (sizeElement && cache.size) {
                    sizeElement.textContent = cache.size;
                }
            }
        });

        // Update last check time
        const lastCheckElement = document.querySelector('.cache-last-check');
        if (lastCheckElement) {
            lastCheckElement.textContent = new Date().toLocaleTimeString('fa-IR');
        }
    }

    /**
     * Update button states during processing
     */
    updateButtonStates(processing, specificType = null) {
        const buttons = document.querySelectorAll('[data-cache-action]');
        
        buttons.forEach(button => {
            if (processing) {
                button.disabled = true;
                
                if (specificType && button.dataset.cacheType === specificType) {
                    const originalText = button.textContent;
                    button.dataset.originalText = originalText;
                    button.innerHTML = '<i class="ph-spinner ph-spin me-2"></i> در حال پردازش...';
                }
                
                if (!specificType && button.dataset.cacheAction === 'clear-all') {
                    const originalText = button.textContent;
                    button.dataset.originalText = originalText;
                    button.innerHTML = '<i class="ph-spinner ph-spin me-2"></i> در حال پاک کردن...';
                }
            } else {
                button.disabled = false;
                
                if (button.dataset.originalText) {
                    button.textContent = button.dataset.originalText;
                    delete button.dataset.originalText;
                }
            }
        });
    }

    /**
     * Show success toast with details
     */
    showSuccessToast(message, details = null) {
        if (this.options.showSuccessDetails && details) {
            let detailsHtml = '<div class="mt-2 small">';
            Object.keys(details).forEach(key => {
                detailsHtml += `<div>${details[key]}</div>`;
            });
            detailsHtml += '</div>';
            
            this.showToast(message + detailsHtml, 'success', this.options.toastDuration);
        } else {
            this.showToast(message, 'success');
        }
    }

    /**
     * Show toast notification
     */
    showToast(message, type = 'info', duration = null) {
        duration = duration || this.options.toastDuration;

        // Remove existing toasts
        document.querySelectorAll('.rms-cache-toast').forEach(toast => {
            toast.remove();
        });

        // Create toast element
        const toast = document.createElement('div');
        toast.className = `rms-cache-toast alert alert-${this.getBootstrapAlertClass(type)} alert-dismissible fade show position-fixed`;
        
        // Dark theme compatible styles
        const isDarkTheme = document.documentElement.getAttribute('data-color-theme') === 'dark';
        const bgColor = type === 'success' ? (isDarkTheme ? '#1e7e34' : '#d1edff') : 
                       type === 'error' ? (isDarkTheme ? '#721c24' : '#f8d7da') :
                       type === 'warning' ? (isDarkTheme ? '#856404' : '#fff3cd') :
                       (isDarkTheme ? '#1b1e21' : '#d1ecf1');
        const textColor = isDarkTheme ? '#ffffff' : '#000000';
        const borderColor = type === 'success' ? (isDarkTheme ? '#28a745' : '#bee5eb') :
                           type === 'error' ? (isDarkTheme ? '#dc3545' : '#f5c6cb') :
                           type === 'warning' ? (isDarkTheme ? '#ffc107' : '#ffeeba') :
                           (isDarkTheme ? '#6c757d' : '#abdde5');
        
        toast.style.cssText = `
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 9999;
            min-width: 400px;
            max-width: 600px;
            background-color: ${bgColor} !important;
            color: ${textColor} !important;
            border: 1px solid ${borderColor};
            box-shadow: 0 8px 25px rgba(0,0,0,0.3);
            border-radius: 12px;
            padding: 20px;
            font-size: 14px;
            backdrop-filter: blur(10px);
        `;

        const icon = this.getToastIcon(type);
        toast.innerHTML = `
            <div class="d-flex align-items-start">
                <i class="${icon} me-3 fs-4"></i>
                <div class="flex-grow-1">
                    <div class="fw-bold mb-1">${this.getToastTitle(type)}</div>
                    <div>${message}</div>
                </div>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" style="color: ${textColor}; opacity: 0.8;"></button>
            </div>
        `;

        document.body.appendChild(toast);

        // Auto-hide toast
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.classList.remove('show');
                    setTimeout(() => toast.remove(), 300);
                }
            }, duration);
        }
    }

    /**
     * Get Bootstrap alert class for toast type
     */
    getBootstrapAlertClass(type) {
        const mapping = {
            'success': 'success',
            'error': 'danger',
            'warning': 'warning',
            'info': 'info'
        };
        return mapping[type] || 'info';
    }

    /**
     * Get appropriate icon for toast type
     */
    getToastIcon(type) {
        const icons = {
            'success': 'ph-check-circle text-success',
            'error': 'ph-x-circle text-danger', 
            'warning': 'ph-warning text-warning',
            'info': 'ph-info text-info'
        };
        return icons[type] || 'ph-info';
    }

    /**
     * Get appropriate title for toast type
     */
    getToastTitle(type) {
        const titles = {
            'success': '✅ موفقیت',
            'error': '❌ خطا',
            'warning': '⚠️ هشدار',
            'info': '📝 اطلاع'
        };
        return titles[type] || '📝 اطلاع';
    }

    /**
     * Show modern confirmation dialog
     */
    async showModernConfirm(title, message, description = null) {
        return new Promise((resolve) => {
            // Remove existing modals
            document.querySelectorAll('.rms-confirm-modal').forEach(modal => {
                modal.remove();
            });

            // Create modal backdrop
            const backdrop = document.createElement('div');
            backdrop.className = 'rms-confirm-modal position-fixed w-100 h-100';
            backdrop.style.cssText = `
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
            const modal = document.createElement('div');
            modal.className = 'bg-white rounded shadow-lg';
            modal.style.cssText = `
                background: ${isDarkTheme ? '#2d2f33' : '#ffffff'} !important;
                color: ${isDarkTheme ? '#ffffff' : '#000000'} !important;
                border-radius: 16px;
                min-width: 400px;
                max-width: 500px;
                padding: 0;
                box-shadow: 0 20px 40px rgba(0,0,0,0.3);
                animation: modalSlideIn 0.3s ease-out;
            `;

            // Add keyframe animation
            if (!document.querySelector('#modal-animations')) {
                const style = document.createElement('style');
                style.id = 'modal-animations';
                style.textContent = `
                    @keyframes modalSlideIn {
                        from { transform: scale(0.9); opacity: 0; }
                        to { transform: scale(1); opacity: 1; }
                    }
                `;
                document.head.appendChild(style);
            }

            modal.innerHTML = `
                <div class="p-4">
                    <div class="text-center mb-3">
                        <div class="mx-auto mb-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px; background: #ffc107; border-radius: 50%;">
                            <i class="ph-warning fs-1 text-white"></i>
                        </div>
                        <h5 class="mb-2 fw-bold">${title}</h5>
                        <p class="mb-0 text-muted">${message}</p>
                        ${description ? `<small class="text-muted d-block mt-2">${description}</small>` : ''}
                    </div>
                    
                    <div class="d-flex gap-2 justify-content-end mt-4">
                        <button type="button" class="btn btn-outline-secondary" data-action="cancel">
                            <i class="ph-x me-2"></i>
                            انصراف
                        </button>
                        <button type="button" class="btn btn-danger" data-action="confirm">
                            <i class="ph-trash me-2"></i>
                            بله، پاک کن
                        </button>
                    </div>
                </div>
            `;

            modal.querySelector('[data-action="confirm"]').addEventListener('click', () => {
                backdrop.remove();
                resolve(true);
            });

            modal.querySelector('[data-action="cancel"]').addEventListener('click', () => {
                backdrop.remove();
                resolve(false);
            });

            // Close on backdrop click
            backdrop.addEventListener('click', (e) => {
                if (e.target === backdrop) {
                    backdrop.remove();
                    resolve(false);
                }
            });

            // Close on Escape key
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    backdrop.remove();
                    resolve(false);
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);

            backdrop.appendChild(modal);
            document.body.appendChild(backdrop);

            // Focus on cancel button for accessibility
            modal.querySelector('[data-action="cancel"]').focus();
        });
    }

    /**
     * Handle errors
     */
    handleError(error, defaultMessage) {
        console.error('Cache Manager Error:', error);
        
        let errorMessage = defaultMessage;
        if (error.response && error.response.data && error.response.data.message) {
            errorMessage = error.response.data.message;
        } else if (error.message) {
            errorMessage = error.message;
        }

        this.showToast(`❌ ${errorMessage}`, 'error');
        this.playSound('error');
    }

    /**
     * Play notification sound
     */
    playSound(type) {
        if (!this.options.enableSounds) return;

        try {
            const audio = new Audio();
            const soundUrls = {
                'success': '/admin/sounds/success.mp3',
                'error': '/admin/sounds/error.mp3'
            };
            
            if (soundUrls[type]) {
                audio.src = soundUrls[type];
                audio.volume = 0.3;
                audio.play().catch(() => {}); // Ignore play errors
            }
        } catch (error) {
            // Ignore sound errors
        }
    }

    /**
     * Start automatic status refresh
     */
    startStatusRefresh() {
        if (this.statusInterval) {
            clearInterval(this.statusInterval);
        }

        this.statusInterval = setInterval(() => {
            this.refreshStatus();
        }, this.options.refreshInterval);
    }

    /**
     * Stop automatic status refresh
     */
    stopStatusRefresh() {
        if (this.statusInterval) {
            clearInterval(this.statusInterval);
            this.statusInterval = null;
        }
    }

    /**
     * Make HTTP request
     */
    async makeRequest(method, url, data = null) {
        const options = {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        };

        if (data) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(url, options);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        return await response.json();
    }

    /**
     * Get cache statistics
     */
    async getStats() {
        try {
            const response = await this.makeRequest('GET', `${this.options.baseUrl}/stats`);
            return response.success ? response.stats : null;
        } catch (error) {
            console.error('Failed to get cache stats:', error);
            return null;
        }
    }

    /**
     * Test if cache button is working
     */
    testCacheButton() {
        const cacheButton = document.querySelector('[data-cache-action="clear-all"]');
        
        if (cacheButton) {
            console.log('✅ Cache button found:', cacheButton);
            console.log('🔍 Button attributes:', {
                'data-cache-action': cacheButton.getAttribute('data-cache-action'),
                'title': cacheButton.getAttribute('title'),
                'class': cacheButton.className
            });
            
            // Test click programmatically
            console.log('🧪 Testing programmatic click...');
            cacheButton.click();
            
            return true;
        } else {
            console.error('❌ Cache button not found in DOM!');
            return false;
        }
    }
    
    /**
     * Destroy the cache manager instance
     */
    destroy() {
        this.stopStatusRefresh();
        console.log('🧹 RMS Cache Manager destroyed');
    }
}

// Global instance
window.RMSCacheManager = RMSCacheManager;

// Improved auto-initialization
function initializeCacheManager() {
    if (typeof window.rmsCacheManager === 'undefined') {
        console.log('🧹 Initializing RMS Cache Manager...');
        window.rmsCacheManager = new RMSCacheManager();
        
        // Double-check that event listeners are working
        setTimeout(() => {
            const cacheButton = document.querySelector('[data-cache-action="clear-all"]');
            if (cacheButton) {
                console.log('✅ Cache button found and ready');
            } else {
                console.warn('⚠️ Cache button not found in DOM');
            }
        }, 100);
    }
}

// Multiple initialization strategies for better reliability
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializeCacheManager);
} else {
    initializeCacheManager();
}

// Additional fallback initialization
window.addEventListener('load', () => {
    if (typeof window.rmsCacheManager === 'undefined') {
        console.log('🔄 Fallback initialization');
        initializeCacheManager();
    }
});

// Global test functions for debugging
window.testCacheManager = function() {
    console.log('🧪 Testing Cache Manager...');
    
    if (window.rmsCacheManager) {
        return window.rmsCacheManager.testCacheButton();
    } else {
        console.error('❌ Cache Manager not initialized!');
        return false;
    }
};

window.debugCacheManager = function() {
    console.log('🔍 Debug Cache Manager:');
    console.log('- Manager instance:', window.rmsCacheManager);
    console.log('- Cache button:', document.querySelector('[data-cache-action="clear-all"]'));
    console.log('- Is processing:', window.rmsCacheManager ? window.rmsCacheManager.isProcessing : 'N/A');
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RMSCacheManager;
}