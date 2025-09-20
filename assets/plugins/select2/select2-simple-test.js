/**
 * Simple Select2 Test - No fancy config, just basic functionality
 */

$(document).ready(function() {
    console.log('Simple Select2 Test Starting...');
    
    // Find all enhanced-select elements
    $('.enhanced-select').each(function() {
        const element = this;
        console.log('Found enhanced-select element:', element);
        console.log('Options count:', element.options?.length || 0);
        console.log('Element HTML:', element.outerHTML.substring(0, 200) + '...');
        
        try {
            // Simple initialization
            $(element).select2({
                theme: 'bootstrap-5',
                width: '100%',
                dir: 'rtl',
                language: {
                    noResults: function() {
                        return 'نتیجه‌ای یافت نشد';
                    }
                }
            });
            
            console.log('✅ Select2 initialized successfully for element');
            
        } catch (error) {
            console.error('❌ Select2 initialization failed:', error);
        }
    });
    
    console.log('Simple Select2 Test Completed');
});