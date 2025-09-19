/**
 * Choices.js Initialization for RMS Admin Panel
 * Bootstrap 5 Compatible with Persian/RTL Support
 * 
 * @version 1.0.0
 * @author RMS Team
 */

class ChoicesSelectInitializer {
    constructor() {
        this.defaultConfig = {
            // Persian/RTL Configuration
            allowHTML: false,
            searchEnabled: true,
            searchChoices: true,
            searchResultLimit: 50,
            shouldSort: false,
            placeholder: true,
            placeholderValue: 'انتخاب کنید...',
            searchPlaceholderValue: 'جستجو...',
            noResultsText: 'نتیجه‌ای یافت نشد',
            noChoicesText: 'آیتمی برای انتخاب وجود ندارد',
            itemSelectText: 'برای انتخاب کلیک کنید',
            loadingText: 'در حال بارگیری...',
            
            // Performance & Behavior
            silent: false,
            renderChoiceLimit: 50,
            maxItemCount: -1,
            addItems: true,
            addItemFilter: null,
            removeItems: true,
            removeItemButton: false,
            editItems: false,
            allowDuplicates: false,
            delimiter: ',',
            paste: true,
            searchFloor: 1,
            searchChoices: true,
            searchFields: ['label', 'value'],
            
            // Visual & Accessibility
            position: 'auto',
            resetScrollPosition: true,
            addChoices: true,
            addItems: true,
            removeItems: true,
            highlightItem: true,
            duplicateItemsAllowed: false,
            
            // Bootstrap 5 Compatible Classes
            classNames: {
                containerOuter: 'choices',
                containerInner: 'choices__inner',
                input: 'choices__input',
                inputCloned: 'choices__input--cloned',
                list: 'choices__list',
                listItems: 'choices__list--multiple',
                listSingle: 'choices__list--single',
                listDropdown: 'choices__list--dropdown',
                item: 'choices__item',
                itemSelectable: 'choices__item--selectable',
                itemDisabled: 'choices__item--disabled',
                itemChoice: 'choices__item--choice',
                placeholder: 'choices__placeholder',
                group: 'choices__group',
                groupHeading: 'choices__heading',
                button: 'choices__button',
                activeState: 'is-active',
                focusState: 'is-focused',
                openState: 'is-open',
                disabledState: 'is-disabled',
                highlightedState: 'is-highlighted',
                selectedState: 'is-selected',
                flippedState: 'is-flipped',
                loadingState: 'is-loading'
            }
        };

        this.rtlConfig = {
            position: 'bottom',
            searchEnabled: true,
            classNames: {
                ...this.defaultConfig.classNames,
                containerOuter: 'choices rtl',
                listDropdown: 'choices__list--dropdown rtl',
                itemChoice: 'choices__item--choice rtl'
            }
        };

        this.debugMode = window.RMS_DEBUG || false;
        this.initialized = [];
    }

    /**
     * Initialize all Choices instances
     */
    init() {
        if (typeof Choices === 'undefined') {
            console.error('Choices.js: Library not loaded');
            return;
        }

        this.log('Initializing Enhanced Select instances...');

        // Initialize existing select elements
        this.initializeExistingSelects();
        
        // Setup observers for dynamic content
        this.setupDynamicObserver();

        this.log(`Enhanced Select initialized for ${this.initialized.length} elements`);
    }

    /**
     * Initialize existing select elements
     */
    initializeExistingSelects() {
        const selectors = [
            '.enhanced-select',
            'select[data-enhanced]',
            '.form-select[data-enhance="true"]'
        ];

        selectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(element => {
                if (!this.isInitialized(element)) {
                    this.initializeElement(element);
                }
            });
        });
    }

    /**
     * Initialize a single select element
     */
    initializeElement(element, customConfig = {}) {
        if (this.isInitialized(element)) {
            this.log('Element already initialized:', element);
            return;
        }

        try {
            const config = this.buildConfig(element, customConfig);
            const choices = new Choices(element, config);
            
            // Mark as initialized
            element.setAttribute('data-choices-initialized', 'true');
            this.initialized.push(element);

            // Setup event listeners
            this.setupEventListeners(choices, element);

            this.log('Choices initialized for:', element, config);
            return choices;

        } catch (error) {
            console.error('Choices initialization failed:', error, element);
        }
    }

    /**
     * Build configuration for element
     */
    buildConfig(element, customConfig = {}) {
        const config = { ...this.defaultConfig };

        // Element-specific configuration
        const isMultiple = element.hasAttribute('multiple');
        const isRtl = document.dir === 'rtl' || element.dir === 'rtl' || 
                     document.documentElement.getAttribute('dir') === 'rtl';

        // Multiple selection configuration
        if (isMultiple) {
            config.removeItemButton = true;
            config.maxItemText = (maxItemCount) => `تنها ${maxItemCount} آیتم قابل انتخاب است`;
            config.maxItemCount = parseInt(element.getAttribute('data-max-items')) || -1;
        }

        // RTL configuration
        if (isRtl) {
            // Don't merge classNames with spaces, just add rtl class
            config.classNames.containerOuter = 'choices rtl';
            config.position = this.rtlConfig.position;
            config.searchEnabled = this.rtlConfig.searchEnabled;
        }

        // Data attributes configuration
        const placeholder = element.getAttribute('data-placeholder') || 
                          element.getAttribute('placeholder') ||
                          config.placeholderValue;
        if (placeholder) {
            config.placeholderValue = placeholder;
        }

        // Search configuration
        const searchEnabled = element.getAttribute('data-search');
        if (searchEnabled !== null) {
            config.searchEnabled = searchEnabled === 'true';
        }

        // AJAX configuration (basic support)
        const ajax = element.getAttribute('data-ajax');
        if (ajax) {
            // For AJAX, we'll need to populate choices dynamically
            // This is a basic implementation
            config.searchEnabled = true;
            config.shouldSort = false;
        }

        // Create new items configuration
        const allowCreate = element.getAttribute('data-create');
        if (allowCreate === 'true') {
            config.addItems = true;
            config.addChoices = true;
            config.editItems = false;
            config.duplicateItemsAllowed = false;
        }

        // Size configuration (affect via CSS classes)
        const size = element.getAttribute('data-size') || 'default';
        if (size === 'small') {
            config.classNames.containerOuter = 'choices choices-sm';
        } else if (size === 'large') {
            config.classNames.containerOuter = 'choices choices-lg';
        }

        // Merge custom configuration
        return { ...config, ...customConfig };
    }

    /**
     * Setup event listeners for Choices instance
     */
    setupEventListeners(choices, element) {
        // Form validation integration
        element.addEventListener('change', () => {
            // Trigger validation if using form validation library
            if (typeof window.validateField === 'function') {
                window.validateField(element);
            }
            
            // Dispatch custom event
            element.dispatchEvent(new Event('enhanced-select:change', { bubbles: true }));
        });

        // Choices-specific events
        element.addEventListener('choice', (event) => {
            this.log('Choice selected:', event.detail);
        });

        element.addEventListener('search', (event) => {
            this.log('Search:', event.detail);
        });

        element.addEventListener('showDropdown', () => {
            this.log('Dropdown opened');
        });

        element.addEventListener('hideDropdown', () => {
            this.log('Dropdown closed');
        });
    }

    /**
     * Setup observer for dynamically added content
     */
    setupDynamicObserver() {
        if (typeof MutationObserver === 'undefined') return;

        const observer = new MutationObserver((mutations) => {
            let hasNewSelects = false;

            mutations.forEach((mutation) => {
                if (mutation.type === 'childList') {
                    mutation.addedNodes.forEach((node) => {
                        if (node.nodeType === 1) { // Element node
                            // Check if the node itself is a select
                            if (node.matches && this.shouldInitialize(node)) {
                                this.initializeElement(node);
                                hasNewSelects = true;
                            }
                            
                            // Check for selects within the node
                            if (node.querySelectorAll) {
                                const selects = node.querySelectorAll('select, .enhanced-select');
                                selects.forEach(select => {
                                    if (this.shouldInitialize(select)) {
                                        this.initializeElement(select);
                                        hasNewSelects = true;
                                    }
                                });
                            }
                        }
                    });
                }
            });

            if (hasNewSelects) {
                this.log('Dynamic Enhanced Select instances initialized');
            }
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });

        this.log('Dynamic content observer setup complete');
    }

    /**
     * Check if element should be initialized
     */
    shouldInitialize(element) {
        return element.tagName === 'SELECT' && 
               !this.isInitialized(element) &&
               (element.classList.contains('enhanced-select') ||
                element.hasAttribute('data-enhanced') ||
                (element.classList.contains('form-select') && 
                 element.getAttribute('data-enhance') === 'true'));
    }

    /**
     * Check if element is already initialized
     */
    isInitialized(element) {
        return element.hasAttribute('data-choices-initialized') ||
               element.hasAttribute('data-choice');
    }

    /**
     * Destroy Choices instance
     */
    destroy(element) {
        if (element.choices) {
            element.choices.destroy();
            element.removeAttribute('data-choices-initialized');
            const index = this.initialized.indexOf(element);
            if (index > -1) {
                this.initialized.splice(index, 1);
            }
        }
    }

    /**
     * Refresh all Choices instances
     */
    refresh() {
        this.log('Refreshing all Enhanced Select instances...');
        this.initialized.forEach(element => {
            if (element.choices) {
                // Choices.js doesn't have a refresh method, but we can reinitialize
                this.destroy(element);
                this.initializeElement(element);
            }
        });
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.debugMode) {
            console.log('[Enhanced Select]', ...args);
        }
    }

    /**
     * Get configuration info for debugging
     */
    getDebugInfo() {
        return {
            library: 'Choices.js',
            version: '10.2.0',
            initialized: this.initialized.length,
            elements: this.initialized,
            defaultConfig: this.defaultConfig
        };
    }

    /**
     * Public API for manual initialization
     */
    static initialize(selector, config = {}) {
        if (!window.enhancedSelectInstance) {
            window.enhancedSelectInstance = new ChoicesSelectInitializer();
        }

        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            window.enhancedSelectInstance.initializeElement(element, config);
        });

        return window.enhancedSelectInstance;
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Choices !== 'undefined') {
        window.enhancedSelectInstance = new ChoicesSelectInitializer();
        window.enhancedSelectInstance.init();
    } else {
        console.error('Enhanced Select library (Choices.js) not loaded');
    }
});

// Global API for Enhanced Select
window.EnhancedSelectRMS = {
    init: () => window.enhancedSelectInstance?.init(),
    initElement: (element, config) => window.enhancedSelectInstance?.initializeElement(element, config),
    destroy: (element) => window.enhancedSelectInstance?.destroy(element),
    refresh: () => window.enhancedSelectInstance?.refresh(),
    debug: () => window.enhancedSelectInstance?.getDebugInfo(),
    initialize: ChoicesSelectInitializer.initialize
};
