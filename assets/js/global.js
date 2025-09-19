/**
 * Global JavaScript configurations and utilities for RMS Admin Panel
 * This file should be loaded before all other JavaScript files
 */

$(document).ready(function() {
    
    // ======================================
    // CSRF Token Setup for AJAX Requests
    // ======================================
    
    // Setup CSRF token for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        // Add loading state to AJAX requests
        beforeSend: function(xhr, settings) {
            // Add global loading indicator if needed
            if (settings.showLoader !== false) {
                $('body').addClass('ajax-loading');
            }
        },
        complete: function(xhr, status) {
            // Remove global loading indicator
            $('body').removeClass('ajax-loading');
        },
        error: function(xhr, status, error) {
            // Global AJAX error handler
            handleAjaxError(xhr, status, error);
        }
    });
    
    // ======================================
    // Global AJAX Error Handler
    // ======================================
    
    function handleAjaxError(xhr, status, error) {
        let message = 'خطای ناشناخته رخ داد.';
        
        if (xhr.status === 0) {
            message = 'اتصال به سرور برقرار نشد.';
        } else if (xhr.status === 404) {
            message = 'صفحه مورد نظر یافت نشد.';
        } else if (xhr.status === 500) {
            message = 'خطای داخلی سرور.';
        } else if (xhr.status === 422) {
            // Validation errors
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                message = Object.values(errors).flat().join('<br>');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                message = xhr.responseJSON.message;
            } else {
                message = 'خطای اعتبارسنجی.';
            }
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            message = xhr.responseJSON.message;
        }
        
        // Show error message
        showNotification(message, 'error');
    }
    
    // ======================================
    // Global Notification System
    // ======================================
    
    window.showNotification = function(message, type = 'info', duration = 5000) {
        // Simple notification system - can be replaced with a more advanced one
        const alertClass = type === 'error' ? 'alert-danger' : 
                          type === 'success' ? 'alert-success' : 
                          type === 'warning' ? 'alert-warning' : 'alert-info';
        
        const $notification = $(`
            <div class="alert ${alertClass} alert-dismissible fade show position-fixed" 
                 style="top: 20px; right: 20px; z-index: 9999; max-width: 400px;">
                <div>${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `);
        
        $('body').append($notification);
        
        // Auto-dismiss after duration
        if (duration > 0) {
            setTimeout(() => {
                $notification.alert('close');
            }, duration);
        }
    };
    
    // ======================================
    // Global Loading Indicator
    // ======================================
    
    // Add CSS for loading state
    if (!$('#global-ajax-styles').length) {
        $('head').append(`
            <style id="global-ajax-styles">
                .ajax-loading {
                    cursor: wait !important;
                }
                .ajax-loading * {
                    pointer-events: none;
                }
                .ajax-loading .btn {
                    opacity: 0.7;
                }
                .toggle-btn.toggling {
                    opacity: 0.6;
                    cursor: wait !important;
                    pointer-events: none;
                }
                .toggle-btn.toggling i {
                    animation: spin 1s linear infinite;
                }
                @keyframes spin {
                    from { transform: rotate(0deg); }
                    to { transform: rotate(360deg); }
                }
            </style>
        `);
    }
    
    // ======================================
    // Global Utility Functions
    // ======================================
    
    // Confirmation dialog wrapper
    window.confirmAction = function(message, callback, title = 'تأیید عملیات') {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    };
    
    // Format numbers with Persian digits
    window.toPersianDigits = function(str) {
        const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
        return str.toString().replace(/[0-9]/g, (digit) => persianDigits[digit]);
    };
    
    // Format numbers with English digits
    window.toEnglishDigits = function(str) {
        const persianDigits = '۰۱۲۳۴۵۶۷۸۹';
        const englishDigits = '0123456789';
        return str.toString().replace(/[۰-۹]/g, (digit) => englishDigits[persianDigits.indexOf(digit)]);
    };
    
    // ======================================
    // Debug Mode Check
    // ======================================
    
    // Enable console logging in debug mode
    window.RMS = window.RMS || {};
    window.RMS.debug = window.RMS.debug || false;
    
    window.RMS.log = function(message, data = null) {
        if (window.RMS.debug) {
            console.log('[RMS Debug]', message, data);
        }
    };
    
    // Log successful initialization
    window.RMS.log('Global JavaScript initialized successfully');
});
