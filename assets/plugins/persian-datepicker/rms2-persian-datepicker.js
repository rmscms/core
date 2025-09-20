/*!
 * RMS2 Persian DatePicker Wrapper
 * Integrates persian-datepicker with RMS2 theme system and Bootstrap 5
 * Compatible with leap year fix from IRAS project
 */

(function(window, document, $) {
    'use strict';

    // Check if required libraries are loaded
    if (typeof $ === 'undefined') {
        console.error('RMS2 Persian DatePicker: jQuery is required');
        return;
    }

    // Persian date library will be loaded by the plugin system
    // No need to check it here as it loads before this wrapper

    // RMS2 Persian DatePicker Configuration
    const RMS2PersianDatePicker = {
        
        // Default configuration optimized for RMS2
        defaultConfig: {
            // Calendar configuration with leap year fix
            calendar: {
                persian: {
                    leapYearMode: 'astronomical'  // Fix for leap year issue
                }
            },
            
            // Date format
            format: 'YYYY/MM/DD',
            
            // UI settings
            initialValue: false,
            autoClose: true,
            
            // Navigator settings
            navigator: {
                enabled: true,
                text: {
                    btnNextText: '‹',
                    btnPrevText: '›'
                }
            },
            
            // Toolbox configuration
            toolbox: {
                enabled: true,
                todayButton: {
                    enabled: true,
                    text: {
                        fa: 'امروز'
                    }
                },
                submitButton: {
                    enabled: true,
                    text: {
                        fa: 'تایید'
                    }
                }
            },
            
            // Observer for dynamic content
            observer: true,
            
            // Alt field support
            altField: false
        },

        // Initialize all Persian DatePicker elements
        init: function() {
            this.initElements();
            this.setupObservers();
            this.bindEvents();
            console.log('RMS2 Persian DatePicker initialized');
        },

        // Initialize elements with persian-datepicker class
        initElements: function() {
            const self = this;
            $('.persian-datepicker, input[data-persian-date]').each(function() {
                self.initElement($(this));
            });
        },

        // Initialize individual element
        initElement: function($element) {
            // Skip if already initialized
            if ($element.data('rms2-datepicker-init')) {
                return;
            }

            try {
                // Get custom configuration from data attributes
                const customConfig = this.getDataConfig($element);
                const finalConfig = $.extend(true, {}, this.defaultConfig, customConfig);

                // Initialize the Persian DatePicker
                $element.pDatepicker(finalConfig);
                
                // Mark as initialized
                $element.data('rms2-datepicker-init', true);
                
                // Add Bootstrap classes
                if (!$element.hasClass('form-control')) {
                    $element.addClass('form-control');
                }

                // Setup form validation integration
                this.setupValidation($element);

                // Setup theme integration
                this.setupThemeIntegration($element);

                console.log('Persian DatePicker initialized for:', $element.attr('name') || $element.attr('id') || 'unnamed input');

            } catch (error) {
                console.error('Persian DatePicker initialization failed:', error);
            }
        },

        // Get configuration from data attributes
        getDataConfig: function($element) {
            const config = {};

            // Format
            if ($element.data('format')) {
                config.format = $element.data('format');
            }

            // Min date
            if ($element.data('min-date')) {
                config.minDate = $element.data('min-date');
            }

            // Max date  
            if ($element.data('max-date')) {
                config.maxDate = $element.data('max-date');
            }

            // Disable dates
            if ($element.data('disable-dates')) {
                config.disable = $element.data('disable-dates');
            }

            // Auto close
            if ($element.data('auto-close') !== undefined) {
                config.autoClose = $element.data('auto-close') === true || $element.data('auto-close') === 'true';
            }

            // Initial value
            if ($element.val() && $element.val().trim()) {
                config.initialValue = true;
            }

            // Time picker
            if ($element.data('time-picker')) {
                config.timePicker = {
                    enabled: true,
                    hour: { enabled: true },
                    minute: { enabled: true },
                    second: { enabled: false }
                };
                config.format = 'YYYY/MM/DD HH:mm';
            }

            return config;
        },

        // Setup Bootstrap form validation integration
        setupValidation: function($element) {
            $element.on('change', function() {
                const $form = $(this).closest('form');
                if ($form.hasClass('was-validated')) {
                    $(this).trigger('blur');
                }
            });
        },

        // Setup theme integration
        setupThemeIntegration: function($element) {
            const self = this;
            
            // Listen for theme changes
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'data-color-theme') {
                        setTimeout(() => {
                            self.updateTheme($element);
                        }, 100);
                    }
                });
            });

            // Observe theme changes on body
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['data-color-theme']
            });
        },

        // Update theme for datepicker
        updateTheme: function($element) {
            // Theme update is handled by CSS
            // This method can be extended for JavaScript-based theme updates
            console.log('Theme updated for datepicker');
        },

        // Setup observers for dynamic content
        setupObservers: function() {
            const self = this;

            // MutationObserver for dynamically added elements
            if (typeof MutationObserver !== 'undefined') {
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        mutation.addedNodes.forEach(function(node) {
                            if (node.nodeType === 1) { // Element node
                                const $node = $(node);
                                
                                // Check if the node itself needs datepicker
                                if ($node.hasClass('persian-datepicker') || $node.is('[data-persian-date]')) {
                                    if (!$node.data('rms2-datepicker-init')) {
                                        setTimeout(() => self.initElement($node), 100);
                                    }
                                }
                                
                                // Check for child elements
                                const $children = $node.find('.persian-datepicker, [data-persian-date]');
                                if ($children.length > 0) {
                                    setTimeout(() => {
                                        $children.each(function() {
                                            if (!$(this).data('rms2-datepicker-init')) {
                                                self.initElement($(this));
                                            }
                                        });
                                    }, 100);
                                }
                            }
                        });
                    });
                });

                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });

                console.log('RMS2 Persian DatePicker: MutationObserver setup for dynamic content');
            }
        },

        // Bind events
        bindEvents: function() {
            const self = this;

            // Focus event delegation for elements not yet initialized
            $(document).on('focus', '.persian-datepicker, [data-persian-date]', function() {
                if (!$(this).data('rms2-datepicker-init')) {
                    self.initElement($(this));
                    // Trigger focus again to open the datepicker
                    setTimeout(() => $(this).trigger('click'), 100);
                }
            });

            // Bootstrap modal show event
            $(document).on('shown.bs.modal', '.modal', function() {
                setTimeout(() => {
                    $(this).find('.persian-datepicker, [data-persian-date]').each(function() {
                        if (!$(this).data('rms2-datepicker-init')) {
                            self.initElement($(this));
                        }
                    });
                }, 100);
            });
        },

        // Public API methods
        reinit: function() {
            console.log('Reinitializing RMS2 Persian DatePicker...');
            this.initElements();
        },

        destroy: function(selector) {
            $(selector).each(function() {
                const $this = $(this);
                if ($this.data('rms2-datepicker-init')) {
                    try {
                        $this.pDatepicker('destroy');
                        $this.removeData('rms2-datepicker-init');
                        console.log('Persian DatePicker destroyed for:', $this.attr('name') || $this.attr('id'));
                    } catch (error) {
                        console.error('Error destroying datepicker:', error);
                    }
                }
            });
        },

        // Utility functions
        utils: {
            // Convert Gregorian to Persian
            toPersian: function(gregorianDate) {
                try {
                    // Try different possible global names for persian-date library
                    if (typeof persianDate !== 'undefined') {
                        return new persianDate(gregorianDate).format('YYYY/MM/DD');
                    }
                    if (typeof PersianDate !== 'undefined') {
                        return new PersianDate(gregorianDate).format('YYYY/MM/DD');
                    }
                    return gregorianDate;
                } catch (error) {
                    console.warn('Persian date conversion failed:', error);
                    return gregorianDate;
                }
            },

            // Convert Persian to Gregorian
            toGregorian: function(persianDateStr) {
                try {
                    if (typeof persianDate !== 'undefined') {
                        return new persianDate(persianDateStr).toDate();
                    }
                    if (typeof PersianDate !== 'undefined') {
                        return new PersianDate(persianDateStr).toDate();
                    }
                    return new Date(persianDateStr);
                } catch (error) {
                    console.warn('Persian date conversion failed:', error);
                    return new Date(persianDateStr);
                }
            },

            // Validate Persian date
            isValid: function(dateStr) {
                try {
                    if (typeof persianDate !== 'undefined') {
                        const pd = new persianDate(dateStr);
                        return pd.isValid();
                    }
                    if (typeof PersianDate !== 'undefined') {
                        const pd = new PersianDate(dateStr);
                        return pd.isValid();
                    }
                    return false;
                } catch (error) {
                    return false;
                }
            }
        }
    };

    // Initialize when document is ready
    $(document).ready(function() {
        // Small delay to ensure all resources are loaded
        setTimeout(() => {
            RMS2PersianDatePicker.init();
        }, 200);
    });

    // Expose to global scope
    window.RMS2PersianDatePicker = RMS2PersianDatePicker;

    // Legacy compatibility
    window.initPersianDatePickers = function() {
        RMS2PersianDatePicker.reinit();
    };

    // Utility functions for backward compatibility
    window.PersianDatePickerUtils = RMS2PersianDatePicker.utils;

})(window, document, jQuery);