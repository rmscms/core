/**
 * Amount Formatter Plugin for RMS
 * Auto-format numbers with commas and allow only numeric input
 * Version: 1.0.0
 * 
 * Features:
 * - Only allows numeric input (0-9)
 * - Auto adds comma separator every 3 digits
 * - Handles copy/paste with auto-formatting
 * - Works with both form fields and filter fields
 * - RTL support for Persian/Farsi interface
 */

class AmountFormatterRMS {
    constructor() {
        this.initialized = false;
        this.selectors = [
            'input[name="amount"]',           // Form field
            'input[name="filter[amount]"]',   // Filter field (old style)
            'input[id*="filter_amount"]',     // Filter field by ID (new style)
            'input[name*="filter_amount"]',   // Filter field by name pattern
            'input.amount-field',             // Custom class
            'input[data-type="amount"]'       // Custom data attribute
        ];
        
        this.init();
    }

    /**
     * Initialize the amount formatter
     */
    init() {
        if (this.initialized) return;
        
        // Wait for DOM to be ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.attachEvents());
        } else {
            this.attachEvents();
        }
        
        this.initialized = true;
        console.log('💰 Amount Formatter initialized');
    }

    /**
     * Attach events to amount fields
     */
    attachEvents() {
        // Find all amount fields
        const amountFields = this.findAmountFields();
        
        amountFields.forEach(field => {
            this.setupField(field);
        });

        // Setup event delegation for dynamic fields
        this.setupEventDelegation();
        
        // Setup observer for dynamically added fields (like in filters)
        this.setupDynamicObserver();
    }

    /**
     * Find all amount fields in the document
     */
    findAmountFields() {
        const fields = [];
        
        this.selectors.forEach(selector => {
            const elements = document.querySelectorAll(selector);
            elements.forEach(el => {
                if (!fields.includes(el)) {
                    fields.push(el);
                }
            });
        });

        return fields;
    }

    /**
     * Setup events for a single field
     */
    setupField(field) {
        if (field.hasAttribute('data-amount-formatted')) {
            return; // Already processed
        }

        // Mark as processed
        field.setAttribute('data-amount-formatted', 'true');
        
        // Add CSS class for styling
        field.classList.add('amount-input');
        
        // Format initial value if exists
        if (field.value) {
            field.value = this.formatAmount(field.value);
        }

        // Add placeholder if not exists
        if (!field.placeholder) {
            field.placeholder = '0';
        }

        // Event listeners
        field.addEventListener('input', (e) => this.handleInput(e));
        field.addEventListener('paste', (e) => this.handlePaste(e));
        field.addEventListener('keydown', (e) => this.handleKeyDown(e));
        field.addEventListener('focus', (e) => this.handleFocus(e));
        field.addEventListener('blur', (e) => this.handleBlur(e));

        console.log('💰 Amount field setup:', field);
    }

    /**
     * Handle input event
     */
    handleInput(event) {
        const field = event.target;
        const cursorPosition = field.selectionStart;
        const oldValue = field.value;
        
        // Remove non-numeric characters except comma
        let cleanValue = field.value.replace(/[^0-9]/g, '');
        
        // Format with commas
        const formattedValue = this.formatAmount(cleanValue);
        
        // Update field value
        field.value = formattedValue;
        
        // Restore cursor position (adjust for added/removed characters)
        const lengthDiff = formattedValue.length - oldValue.length;
        const newPosition = Math.max(0, cursorPosition + lengthDiff);
        
        // Set cursor position after a small delay
        setTimeout(() => {
            field.setSelectionRange(newPosition, newPosition);
        }, 1);
    }

    /**
     * Handle paste event
     */
    handlePaste(event) {
        event.preventDefault();
        
        const field = event.target;
        const pastedData = (event.clipboardData || window.clipboardData).getData('text');
        
        // Extract numbers only
        const cleanValue = pastedData.replace(/[^0-9]/g, '');
        
        // Format and set value
        field.value = this.formatAmount(cleanValue);
        
        // Trigger input event for consistency
        field.dispatchEvent(new Event('input', { bubbles: true }));
    }

    /**
     * Handle keydown event
     */
    handleKeyDown(event) {
        const allowedKeys = [
            'Backspace', 'Delete', 'Tab', 'Escape', 'Enter',
            'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
            'Home', 'End', 'PageUp', 'PageDown'
        ];

        // Allow control keys
        if (event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        // Allow allowed keys
        if (allowedKeys.includes(event.key)) {
            return;
        }

        // Allow numeric keys (0-9)
        if (/^[0-9]$/.test(event.key)) {
            return;
        }

        // Block everything else
        event.preventDefault();
    }

    /**
     * Handle focus event
     */
    handleFocus(event) {
        const field = event.target;
        field.classList.add('amount-focused');
        
        // Select all text on focus for easy editing
        setTimeout(() => {
            field.select();
        }, 1);
    }

    /**
     * Handle blur event
     */
    handleBlur(event) {
        const field = event.target;
        field.classList.remove('amount-focused');
        
        // Ensure proper formatting on blur
        if (field.value) {
            field.value = this.formatAmount(field.value.replace(/[^0-9]/g, ''));
        }
    }

    /**
     * Format amount with comma separators
     */
    formatAmount(value) {
        if (!value || value === '0') {
            return '';
        }

        // Remove any non-numeric characters
        const cleanValue = value.toString().replace(/[^0-9]/g, '');
        
        if (cleanValue === '' || cleanValue === '0') {
            return '';
        }

        // Add comma separators
        return cleanValue.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
    }

    /**
     * Get numeric value (without commas)
     */
    getNumericValue(formattedValue) {
        return formattedValue.replace(/[^0-9]/g, '');
    }

    /**
     * Setup event delegation for dynamic fields
     */
    setupEventDelegation() {
        // Use event delegation to handle dynamically added fields
        document.body.addEventListener('focus', (event) => {
            const target = event.target;
            if (target.tagName === 'INPUT' && target.name && target.name.includes('amount')) {
                if (!target.hasAttribute('data-amount-formatted')) {
                    console.log('💰 Event delegation caught focus on amount field:', target.name);
                    this.setupField(target);
                }
            }
        }, true);
        
        // Also catch input events
        document.body.addEventListener('input', (event) => {
            const target = event.target;
            if (target.tagName === 'INPUT' && target.name && target.name.includes('amount')) {
                if (!target.hasAttribute('data-amount-formatted')) {
                    console.log('💰 Event delegation caught input on amount field:', target.name);
                    this.setupField(target);
                    // Reprocess the current input event
                    this.handleInput(event);
                }
            }
        }, true);
        
        console.log('💰 Event delegation setup complete');
    }
    
    /**
     * Setup observer for dynamically added fields
     */
    setupDynamicObserver() {
        // Create observer for new fields
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === Node.ELEMENT_NODE) {
                        // Check if the added node is an amount field
                        this.selectors.forEach(selector => {
                            if (node.matches && node.matches(selector)) {
                                console.log('💰 Observer found matching field:', node.name);
                                this.setupField(node);
                            }
                        });

                        // Check for amount fields within the added node
                        this.selectors.forEach(selector => {
                            const fields = node.querySelectorAll ? node.querySelectorAll(selector) : [];
                            fields.forEach(field => {
                                console.log('💰 Observer found nested field:', field.name);
                                this.setupField(field);
                            });
                        });
                        
                        // Also check for any input with "amount" in the name (broader search)
                        if (node.querySelectorAll) {
                            const amountInputs = node.querySelectorAll('input[name*="amount"]');
                            amountInputs.forEach(field => {
                                if (!field.hasAttribute('data-amount-formatted')) {
                                    console.log('💰 Observer found general amount field:', field.name);
                                    this.setupField(field);
                                }
                            });
                        }
                    }
                });
            });
        });

        // Start observing
        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
        
        // Also setup a periodic check for missed fields (fallback)
        setInterval(() => {
            const allAmountInputs = document.querySelectorAll('input[name*="amount"]');
            allAmountInputs.forEach(field => {
                if (!field.hasAttribute('data-amount-formatted')) {
                    console.log('💰 Periodic check found unprocessed field:', field.name);
                    this.setupField(field);
                }
            });
        }, 2000); // Check every 2 seconds

        console.log('💰 Dynamic observer setup complete');
    }

    /**
     * Manually format all amount fields (for external use)
     */
    refreshAll() {
        const fields = this.findAmountFields();
        fields.forEach(field => {
            if (field.value) {
                field.value = this.formatAmount(field.value.replace(/[^0-9]/g, ''));
            }
        });
        console.log('💰 All amount fields refreshed');
    }

    /**
     * Get debug info
     */
    debug() {
        const fields = this.findAmountFields();
        console.log('💰 Amount Formatter Debug Info:');
        console.log('- Initialized:', this.initialized);
        console.log('- Selectors:', this.selectors);
        console.log('- Found fields:', fields.length);
        fields.forEach((field, index) => {
            console.log(`  Field ${index + 1}:`, {
                name: field.name,
                value: field.value,
                placeholder: field.placeholder,
                classes: field.className,
                processed: field.hasAttribute('data-amount-formatted')
            });
        });
        
        // Also check for filter fields specifically
        const filterFields = document.querySelectorAll('input[name*="amount"]');
        console.log('- All amount-related fields found:', filterFields.length);
        filterFields.forEach((field, index) => {
            console.log(`  All Field ${index + 1}:`, {
                name: field.name,
                id: field.id,
                classes: field.className,
                processed: field.hasAttribute('data-amount-formatted')
            });
        });
    }
    
    /**
     * Force process all amount fields (for debugging)
     */
    forceProcess() {
        console.log('💰 Force processing all amount fields...');
        const allAmountFields = document.querySelectorAll('input[name*="amount"]');
        allAmountFields.forEach(field => {
            if (!field.hasAttribute('data-amount-formatted')) {
                console.log('💰 Processing field:', field.name);
                this.setupField(field);
            }
        });
    }
}

// Auto-initialize when script loads
const AmountFormatter = new AmountFormatterRMS();

// Export for manual use
window.AmountFormatterRMS = AmountFormatter;

// Add global functions for easy debugging and manual control
window.debugAmountFormatter = () => AmountFormatter.debug();
window.forceProcessAmountFields = () => AmountFormatter.forceProcess();
window.refreshAmountFields = () => AmountFormatter.refreshAll();

// Quick test function
window.testAmountFormatter = () => {
    console.log('💰 Testing Amount Formatter...');
    console.log('=== Debug Info ===');
    AmountFormatter.debug();
    console.log('=== Force Processing ===');
    AmountFormatter.forceProcess();
    console.log('💰 Test completed! Check console for details.');
};

// Auto-run after DOM is fully loaded and after a short delay for dynamic content
document.addEventListener('DOMContentLoaded', () => {
    // Check for fields that might have been missed during initial load
    setTimeout(() => {
        console.log('💰 Post-load amount field check...');
        AmountFormatter.forceProcess();
    }, 1000);
});
