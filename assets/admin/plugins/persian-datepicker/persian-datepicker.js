/*!
 * Persian DatePicker Plugin JS
 * Compatible with Bootstrap 5 & RMS Core
 * Based on babakhani/pwt.datepicker
 */

(function(window, document, $) {
    'use strict';

    // Wait for DOM and external library to be ready
    function initPersianDatePicker() {
        // Check if Persian Date library is loaded
        if (typeof Persian === 'undefined' || typeof $.fn.pDatepicker === 'undefined') {
            console.warn('Persian DatePicker: External library not loaded, trying to load...');
            loadExternalLibrary();
            return;
        }

        // Default configuration
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
                    leapYearMode: 'algorithmic'
                }
            },
            navigator: {
                enabled: true,
                scroll: {
                    enabled: true
                },
                text: {
                    btnNextText: '>',
                    btnPrevText: '<'
                }
            },
            toolbox: {
                enabled: true,
                calendarSwitch: {
                    enabled: true,
                    format: 'MMMM'
                },
                todayButton: {
                    enabled: true,
                    text: {
                        fa: 'امروز',
                        en: 'Today'
                    }
                },
                submitButton: {
                    enabled: true,
                    text: {
                        fa: 'تایید',
                        en: 'Submit'
                    }
                },
                calendarSwitch: {
                    enabled: false
                }
            },
            dayPicker: {
                enabled: true,
                titleFormat: 'YYYY MMMM'
            },
            observer: true,
            altField: false,
            inputDelay: 800
        };

    // Initialize all persian datepickers
    function initDatePickersForElements() {
        $('.persian-datepicker, input[data-persian-date]').each(function() {
            const $input = $(this);
            
            // Skip if already initialized
            if ($input.data('persian-datepicker-initialized')) {
                return;
            }

            // Get custom config from data attributes
            const customConfig = getCustomConfig($input);
            const finalConfig = $.extend(true, {}, defaultConfig, customConfig);

            // Initialize the datepicker
            try {
                $input.pDatepicker(finalConfig);
                $input.data('persian-datepicker-initialized', true);
                
                // Add Bootstrap classes
                $input.addClass('form-control');
                
                // Handle form validation integration
                setupValidationIntegration($input);
                
                console.log('Persian DatePicker initialized for:', $input.attr('name') || $input.attr('id'));
            } catch (error) {
                console.error('Persian DatePicker initialization failed:', error);
            }
        });
    }
    
    // Initial initialization
    initDatePickersForElements();
    
    // Setup MutationObserver for dynamically added elements
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === 1) { // Element node
                        const $node = $(node);
                        // Check if the node itself has persian-datepicker class
                        if ($node.hasClass('persian-datepicker')) {
                            if (!$node.data('persian-datepicker-initialized')) {
                                setTimeout(() => initDatePickersForElements(), 100);
                            }
                        }
                        // Check for child elements with persian-datepicker class
                        if ($node.find('.persian-datepicker').length > 0) {
                            setTimeout(() => initDatePickersForElements(), 100);
                        }
                    }
                });
            });
        });
        
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        console.log('Persian DatePicker: MutationObserver setup for dynamic content');
    }
    
    // Also setup jQuery event delegation for focus
    $(document).on('focus', '.persian-datepicker', function() {
        if (!$(this).data('persian-datepicker-initialized')) {
            const customConfig = getCustomConfig($(this));
            const finalConfig = $.extend(true, {}, defaultConfig, customConfig);
            
            try {
                $(this).pDatepicker(finalConfig);
                $(this).data('persian-datepicker-initialized', true);
                $(this).addClass('form-control');
                setupValidationIntegration($(this));
                
                // Trigger focus again to open the datepicker
                setTimeout(() => $(this).trigger('click'), 100);
                
                console.log('Persian DatePicker initialized on focus for:', $(this).attr('name') || $(this).attr('id'));
            } catch (error) {
                console.error('Persian DatePicker focus initialization failed:', error);
            }
        }
    });
    
    // Manual initialization function for external use
    window.initPersianDatePickers = initDatePickersForElements;
    }

    // Get custom configuration from data attributes
    function getCustomConfig($input) {
        const config = {};
        
        // Format
        if ($input.data('format')) {
            config.format = $input.data('format');
        }
        
        // Min date
        if ($input.data('min-date')) {
            config.minDate = $input.data('min-date');
        }
        
        // Max date
        if ($input.data('max-date')) {
            config.maxDate = $input.data('max-date');
        }
        
        // Disable dates
        if ($input.data('disable')) {
            config.disable = $input.data('disable');
        }
        
        // Auto close
        if ($input.data('auto-close') !== undefined) {
            config.autoClose = $input.data('auto-close');
        }
        
        // Initial value
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

    // Load external Persian DatePicker library
    function loadExternalLibrary() {
        // Load Persian Date library if not loaded
        if (typeof Persian === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/persian-date@latest/dist/persian-date.min.js';
            script.onload = function() {
                console.log('Persian Date library loaded');
                loadDatePickerLibrary();
            };
            script.onerror = function() {
                console.error('Failed to load Persian Date library');
            };
            document.head.appendChild(script);
        } else {
            loadDatePickerLibrary();
        }
    }

    // Load Persian DatePicker library
    function loadDatePickerLibrary() {
        if (typeof $.fn.pDatepicker === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/persian-datepicker@latest/dist/js/persian-datepicker.min.js';
            script.onload = function() {
                console.log('Persian DatePicker library loaded');
                // Retry initialization
                setTimeout(initPersianDatePicker, 100);
            };
            script.onerror = function() {
                console.error('Failed to load Persian DatePicker library');
            };
            document.head.appendChild(script);
        }
    }

    // Utility functions for external use
    window.PersianDatePickerUtils = {
        // Convert Gregorian to Persian
        toPersian: function(gregorianDate) {
            if (typeof Persian !== 'undefined') {
                return new Persian(gregorianDate).format('YYYY/MM/DD');
            }
            return gregorianDate;
        },
        
        // Convert Persian to Gregorian
        toGregorian: function(persianDate) {
            if (typeof Persian !== 'undefined') {
                return Persian.parse(persianDate).toDate();
            }
            return new Date(persianDate);
        },
        
        // Reinitialize datepickers (useful for dynamic content)
        reinit: function() {
            initPersianDatePicker();
        },
        
        // Destroy datepicker
        destroy: function(selector) {
            $(selector).each(function() {
                if ($(this).data('persian-datepicker-initialized')) {
                    $(this).pDatepicker('destroy');
                    $(this).removeData('persian-datepicker-initialized');
                }
            });
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Small delay to ensure all resources are loaded
        setTimeout(initPersianDatePicker, 200);
    });

    // Reinitialize on AJAX content load (common in admin panels)
    $(document).on('DOMNodeInserted', function(e) {
        if ($(e.target).find('.persian-datepicker, input[data-persian-date]').length > 0) {
            setTimeout(initPersianDatePicker, 100);
        }
    });

    // Handle Bootstrap modal show event
    $(document).on('shown.bs.modal', '.modal', function() {
        setTimeout(function() {
            initPersianDatePicker();
        }, 100);
    });

})(window, document, jQuery);
