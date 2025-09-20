/**
 * Simple Select2 Initialization for RMS Admin Panel
 * Based on Limitless template approach
 *
 * @version 2.0.0 (Simplified)
 * @author RMS Team
 */

var Select2RMS = function() {

    var initialized = false;

    // Check if Select2 is loaded
    var _componentSelect2 = function() {
        if (!$().select2) {
            console.warn('Warning - select2.min.js is not loaded.');
            return;
        }

        if (initialized) {
            console.log('Select2RMS already initialized, skipping...');
            return;
        }

        // Clean up any existing Select2 instances first
        $('.enhanced-select.select2-hidden-accessible').each(function() {
            if ($(this).data('select2')) {
                $(this).select2('destroy');
            }
        });

        // Simple initialization like Limitless
        $('.enhanced-select').select2();
        
        // Setup theme watcher
        setupThemeWatcher();
        
        // Mark as initialized
        initialized = true;
        console.log('Select2RMS initialized successfully');
    };

    return {
        init: function() {
            _componentSelect2();
        },
        reset: function() {
            initialized = false;
            $('.enhanced-select.select2-hidden-accessible').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
            });
        }
    }
}();

// Initialize on DOM ready
$(function (){
    Select2RMS.init();
});
