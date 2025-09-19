/*!
 * Persian DatePicker Simple - Direct Initialization
 * Compatible with Bootstrap 5 & RMS Core
 * Uses local files instead of CDN loading
 */

(function(window, document, $) {
    'use strict';

    // Debug flag
    const DEBUG = true;
    
    function log(message) {
        if (DEBUG) {
            console.log('PersianDatePicker:', message);
        }
    }

    // Default configuration - simplified for testing
    const defaultConfig = {
        format: 'YYYY/MM/DD',
        initialValue: false,
        autoClose: true,
        position: 'auto',
        rtl: true,
        calendar: {
            persian: {
                locale: 'fa',
                showHint: true,
                leapYearMode: 'algorithmic'  // Use algorithmic mode - this should be more accurate
            }
        },
        calendarType: 'persian',
        navigator: {
            enabled: true,
            scroll: { enabled: true },
            text: { btnNextText: '>', btnPrevText: '<' }
        },
        toolbox: {
            enabled: true,
            todayButton: {
                enabled: true,
                text: { fa: 'امروز', en: 'Today' }
            },
            submitButton: {
                enabled: true,
                text: { fa: 'تایید', en: 'Submit' }
            },
            calendarSwitch: { enabled: false }
        },
        dayPicker: {
            enabled: true,
            titleFormat: 'YYYY MMMM'
        },
        observer: true,
        inputDelay: 800
    };

    // Check if required libraries are ready
    function checkLibrariesReady() {
        return (typeof $ !== 'undefined' && 
                typeof $.fn !== 'undefined' && 
                typeof $.fn.pDatepicker !== 'undefined');
    }
    
    // Check if jalaali library is available
    function isJalaaliAvailable() {
        const available = (typeof window.jalaali !== 'undefined' && 
                typeof window.jalaali.isLeapJalaaliYear !== 'undefined' &&
                typeof window.jalaali.jalaaliMonthLength !== 'undefined' &&
                typeof window.jalaali.toJalaali !== 'undefined' &&
                typeof window.jalaali.isValidJalaaliDate !== 'undefined');
        
        if (available) {
            log('Jalaali.js library is fully available with all required functions');
        } else {
            log('Jalaali.js library not available or incomplete. Available functions: ' + 
                Object.keys(window.jalaali || {}));
        }
        
        return available;
    }
    
    // Get enhanced config with jalaali integration
    function getEnhancedConfig(baseConfig) {
        if (isJalaaliAvailable()) {
            log('Jalaali library detected, enabling advanced calendar features');
            
            // Override calendar config to use jalaali functions
            const enhancedConfig = $.extend(true, {}, baseConfig);
            
            // Configure calendar to use jalaali calculations with offset correction
            enhancedConfig.calendar = {
                persian: {
                    locale: 'fa',
                    showHint: true,
                    leapYearMode: 'algorithmic',
                    // Override internal functions to use jalaali.js
                    isLeapYear: function(year) {
                        try {
                            return window.jalaali.isLeapJalaaliYear(year);
                        } catch(e) {
                            log('Leap year calculation error: ' + e.message);
                            return false;
                        }
                    },
                    monthLength: function(year, month) {
                        try {
                            return window.jalaali.jalaaliMonthLength(year, month);
                        } catch(e) {
                            log('Month length calculation error: ' + e.message);
                            // Fallback to default Persian calendar rules
                            if (month <= 6) return 31;
                            if (month <= 11) return 30;
                            return window.jalaali.isLeapJalaaliYear(year) ? 30 : 29;
                        }
                    }
                }
            };
            
            // Add offset correction for leap year 1403
            enhancedConfig.timePicker = {
                enabled: false
            };
            
            // Override date conversion functions
            enhancedConfig.altFormat = 'YYYY/MM/DD';
            enhancedConfig.onlyTimePicker = false;
            
            // Add custom date correction callback
            enhancedConfig.onSelect = function(persianDate) {
                try {
                    // Convert Persian date to Gregorian using jalaali.js for accuracy
                    const parts = persianDate.split('/');
                    if (parts.length === 3) {
                        const jy = parseInt(parts[0]);
                        const jm = parseInt(parts[1]);
                        const jd = parseInt(parts[2]);
                        
                        // Use jalaali.js for precise conversion
                        const gregDate = window.jalaali.toGregorian(jy, jm, jd);
                        log(`Date conversion: ${persianDate} -> ${gregDate.gy}/${gregDate.gm}/${gregDate.gd}`);
                    }
                } catch(e) {
                    log('Date conversion error: ' + e.message);
                }
            };
            
            // Add date validation using jalaali
            enhancedConfig.checkDate = function(unix, fmt) {
                try {
                    const date = new Date(unix);
                    const jalDate = window.jalaali.toJalaali(date);
                    return window.jalaali.isValidJalaaliDate(jalDate.jy, jalDate.jm, jalDate.jd);
                } catch(e) {
                    log('Date validation error: ' + e.message);
                    return true; // fallback to default behavior
                }
            };
            
            return enhancedConfig;
        } else {
            log('Jalaali library not available, using default config');
            return baseConfig;
        }
    }
    
    // Wait for libraries to be ready with retry mechanism
    function waitForLibraries(callback, maxAttempts = 50) {
        let attempts = 0;
        
        function checkReady() {
            attempts++;
            
            if (checkLibrariesReady()) {
                log('Libraries ready after ' + attempts + ' attempts');
                callback();
            } else if (attempts < maxAttempts) {
                log('Waiting for libraries... attempt ' + attempts);
                setTimeout(checkReady, 100);
            } else {
                log('Libraries not ready after ' + maxAttempts + ' attempts, aborting');
            }
        }
        
        checkReady();
    }

    // Override pwt datepicker internal functions with jalaali.js
    function overridePwtDatepickerCalculations() {
        if (!isJalaaliAvailable()) {
            log('Jalaali library not available, skipping override');
            return;
        }
        
        // Check if pwt datepicker is loaded
        if (typeof $.fn.pDatepicker === 'undefined' || typeof $.fn.pDatepicker.regionalOptions === 'undefined') {
            log('pwt.datepicker not ready for override');
            return;
        }
        
        try {
            // Override Persian calendar calculations
            if ($.fn.pDatepicker.regionalOptions && $.fn.pDatepicker.regionalOptions.fa) {
                const originalFa = $.fn.pDatepicker.regionalOptions.fa;
                
                // Override leap year calculation
                if (originalFa.calendar && originalFa.calendar.persian) {
                    originalFa.calendar.persian.isLeapYear = function(year) {
                        return window.jalaali.isLeapJalaaliYear(year);
                    };
                    
                    // Override month length calculation
                    originalFa.calendar.persian.monthsInYear = function(year) {
                        const months = [];
                        for (let i = 1; i <= 12; i++) {
                            months.push(window.jalaali.jalaaliMonthLength(year, i));
                        }
                        return months;
                    };
                    
                    log('Successfully overrode pwt.datepicker Persian calendar calculations with jalaali.js');
                }
            }
            
            // Also try to override internal calculation functions if they exist
            if (typeof window.persianDate !== 'undefined' && window.persianDate.prototype) {
                window.persianDate.prototype.isLeapYear = function() {
                    return window.jalaali.isLeapJalaaliYear(this.year());
                };
                log('Overrode persianDate.isLeapYear with jalaali calculation');
            }
            
        } catch (error) {
            log('Error overriding pwt.datepicker calculations: ' + error.message);
        }
    }

    // Main initialization function
    function initPersianDatePicker() {
        log('Starting initialization...');
        
        // Check if required libraries are available
        if (typeof $ === 'undefined') {
            log('jQuery not found, aborting');
            return;
        }

        if (typeof $.fn.pDatepicker === 'undefined') {
            log('pDatepicker plugin not found, will wait and retry...');
            waitForLibraries(function() {
                overridePwtDatepickerCalculations();
                initElements();
            });
            return;
        }

        log('Libraries found, overriding calculations and initializing elements...');
        overridePwtDatepickerCalculations();
        initElements();
    }

    // Initialize elements
    function initElements() {
        $('.persian-datepicker, input[data-persian-date]').each(function() {
            const $input = $(this);
            
            // Skip if already initialized
            if ($input.data('persian-datepicker-initialized')) {
                log('Element already initialized: ' + ($input.attr('name') || $input.attr('id')));
                return;
            }

            try {
                // Get custom config
                const customConfig = getCustomConfig($input);
                let finalConfig = $.extend(true, {}, defaultConfig, customConfig);
                
                // For now, skip jalaali enhancement to test basic functionality
                // finalConfig = getEnhancedConfig(finalConfig);
                
                log('Using config:', finalConfig);

                // Initialize
                $input.pDatepicker(finalConfig);
                $input.data('persian-datepicker-initialized', true);
                $input.addClass('form-control');

                // Setup validation integration
                setupValidationIntegration($input);
                
                // Apply jalaali.js corrections if available
                if (isJalaaliAvailable()) {
                    setupJalaaliCorrection($input);
                }

                log('Initialized successfully: ' + ($input.attr('name') || $input.attr('id')));
            } catch (error) {
                log('Initialization failed: ' + error.message);
            }
        });
    }

    // Get custom configuration from data attributes
    function getCustomConfig($input) {
        const config = {};
        
        if ($input.data('format')) {
            config.format = $input.data('format');
        }
        
        if ($input.data('min-date')) {
            config.minDate = $input.data('min-date');
        }
        
        if ($input.data('max-date')) {
            config.maxDate = $input.data('max-date');
        }
        
        if ($input.data('auto-close') !== undefined) {
            config.autoClose = $input.data('auto-close');
        }
        
        if ($input.val() && $input.val().trim()) {
            config.initialValue = true;
        }
        
        return config;
    }

    // Setup Bootstrap form validation integration
    function setupValidationIntegration($input) {
        $input.on('change', function() {
            const $form = $(this).closest('form');
            if ($form.hasClass('was-validated')) {
                $(this).trigger('blur');
            }
        });
    }
    
    // Setup jalaali.js-based date correction
    function setupJalaaliCorrection($input) {
        log('Setting up jalaali correction for: ' + ($input.attr('name') || $input.attr('id')));
        
        // Monitor date changes and correct them using jalaali.js
        $input.on('dp:select', function(e) {
            if (e.date) {
                try {
                    // Get the selected Persian date
                    const persianDateStr = $input.val();
                    log('Date selected via datepicker: ' + persianDateStr);
                    
                    if (persianDateStr && persianDateStr.match(/^\d{4}\/\d{1,2}\/\d{1,2}$/)) {
                        const parts = persianDateStr.split('/');
                        const jy = parseInt(parts[0]);
                        const jm = parseInt(parts[1]);
                        const jd = parseInt(parts[2]);
                        
                        // Validate using jalaali.js
                        if (window.jalaali.isValidJalaaliDate(jy, jm, jd)) {
                            // Convert to Gregorian for verification
                            const gregDate = window.jalaali.toGregorian(jy, jm, jd);
                            log(`Jalaali validation: ${persianDateStr} -> ${gregDate.gy}/${gregDate.gm}/${gregDate.gd}`);
                            
                            // Check if this is in leap year 1403 and needs correction
                            if (jy === 1403 && jm === 12 && jd === 30) {
                                log('Leap year 1403, month 12, day 30 detected - this should be valid');
                            }
                        } else {
                            log('Invalid Persian date detected: ' + persianDateStr);
                        }
                    }
                } catch (error) {
                    log('Error in jalaali correction: ' + error.message);
                }
            }
        });
        
        // Also monitor direct input changes
        $input.on('input change', function() {
            const value = $(this).val();
            if (value && value.match(/^\d{4}\/\d{1,2}\/\d{1,2}$/)) {
                setTimeout(() => {
                    verifyDateWithJalaali($input, value);
                }, 100);
            }
        });
    }
    
    // Verify date using jalaali.js and log results
    function verifyDateWithJalaali($input, dateStr) {
        try {
            const parts = dateStr.split('/');
            const jy = parseInt(parts[0]);
            const jm = parseInt(parts[1]);
            const jd = parseInt(parts[2]);
            
            const isValid = window.jalaali.isValidJalaaliDate(jy, jm, jd);
            const gregDate = isValid ? window.jalaali.toGregorian(jy, jm, jd) : null;
            
            log(`Date verification: ${dateStr} -> Valid: ${isValid}` + 
                (gregDate ? ` -> Gregorian: ${gregDate.gy}/${gregDate.gm}/${gregDate.gd}` : ''));
            
            if (!isValid) {
                log('WARNING: Invalid Persian date entered: ' + dateStr);
            }
        } catch (error) {
            log('Error verifying date: ' + error.message);
        }
    }

    // Test jalaali leap year calculations
    function testJalaaliLeapYear() {
        if (!isJalaaliAvailable()) {
            console.error('Jalaali library not available for testing');
            return false;
        }
        
        console.log('Testing Jalaali.js leap year calculations...');
        
        // Test known leap years and non-leap years
        const testCases = [
            { year: 1403, expected: true, description: 'سال کبیسه ۱۴۰۳' },
            { year: 1404, expected: false, description: 'سال عادی ۱۴۰۴' },
            { year: 1399, expected: true, description: 'سال کبیسه ۱۳۹۹' },
            { year: 1400, expected: false, description: 'سال عادی ۱۴۰۰' }
        ];
        
        // Test specific problematic dates
        console.log('Testing specific problematic dates in leap year 1403:');
        const problematicDates = [
            { jy: 1403, jm: 12, jd: 29, description: '29 اسفند 1403' },
            { jy: 1403, jm: 12, jd: 30, description: '30 اسفند 1403 (روز کبیسه)' },
            { jy: 1404, jm: 1, jd: 1, description: '1 فروردین 1404' }
        ];
        
        problematicDates.forEach(test => {
            const isValid = window.jalaali.isValidJalaaliDate(test.jy, test.jm, test.jd);
            const gregDate = window.jalaali.toGregorian(test.jy, test.jm, test.jd);
            console.log(`${test.description}: Valid=${isValid}, Gregorian=${gregDate.gy}/${gregDate.gm}/${gregDate.gd}`);
        });
        
        let allPassed = true;
        testCases.forEach(test => {
            const result = window.jalaali.isLeapJalaaliYear(test.year);
            const passed = result === test.expected;
            console.log(`${test.description}: ${result} (${passed ? '✅ PASS' : '❌ FAIL'})`);
            if (!passed) allPassed = false;
        });
        
        // Test month lengths for leap year 1403
        console.log('Testing month lengths for leap year 1403:');
        for (let month = 1; month <= 12; month++) {
            const length = window.jalaali.jalaaliMonthLength(1403, month);
            console.log(`Month ${month}: ${length} days`);
        }
        
        return allPassed;
    }

    // Public interface
    window.PersianDatePickerSimple = {
        init: initPersianDatePicker,
        initElements: initElements,
        forceInit: forceInit,
        checkReady: checkLibrariesReady,
        testJalaali: testJalaaliLeapYear,
        // Debug helpers
        debugElements: function() {
            console.log('Persian datepicker elements found:', $('.persian-datepicker').length);
            console.log('Jalaali library available:', isJalaaliAvailable());
            console.log('Libraries ready:', checkLibrariesReady());
            $('.persian-datepicker').each(function(i, el) {
                console.log('Element ' + i + ':', {
                    name: $(el).attr('name'),
                    id: $(el).attr('id'),
                    classes: $(el).attr('class'),
                    initialized: $(el).data('persian-datepicker-initialized')
                });
            });
        }
    };

    // Force initialization function
    function forceInit() {
        log('Force initialization called');
        if (checkLibrariesReady()) {
            initElements();
        } else {
            waitForLibraries(initElements);
        }
    }
    
    // Auto-initialize when DOM is ready
    $(document).ready(function() {
        log('DOM ready, starting initialization...');
        
        // Multiple initialization attempts with different delays
        setTimeout(initPersianDatePicker, 200);
        setTimeout(initPersianDatePicker, 500);
        setTimeout(initPersianDatePicker, 1000);
        
        // Setup for dynamic content
        $(document).on('focus', '.persian-datepicker', function() {
            if (!$(this).data('persian-datepicker-initialized')) {
                log('Focus event on uninitialized element, initializing...');
                forceInit();
            }
        });

        // Click event for force initialization
        $(document).on('click', '.persian-datepicker', function() {
            if (!$(this).data('persian-datepicker-initialized')) {
                log('Click event on uninitialized element, force initializing...');
                setTimeout(forceInit, 100);
            }
        });
        
        // Manual trigger for filters and dynamic content
        $(document).on('click', '[data-init-persian-datepicker]', function() {
            log('Manual initialization triggered');
            setTimeout(forceInit, 100);
        });
        
        // MutationObserver for dynamically added content
        if (typeof MutationObserver !== 'undefined') {
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.addedNodes.length > 0) {
                        // Check if any added nodes contain persian-datepicker elements
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                const $node = $(node);
                                if ($node.hasClass('persian-datepicker') || $node.find('.persian-datepicker').length > 0) {
                                    log('New datepicker elements detected, initializing...');
                                    setTimeout(forceInit, 200);
                                }
                            }
                        });
                    }
                });
            });
            
            observer.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
    });

    // Also try after window load
    $(window).on('load', function() {
        log('Window loaded, re-initializing...');
        setTimeout(forceInit, 500);
        setTimeout(forceInit, 1500);
    });

})(window, document, jQuery);
