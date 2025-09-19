/**
 * Theme Switcher - RMS Admin
 * Based on Limitless template pattern with localStorage persistence
 */

class ThemeSwitcher {
    constructor() {
        this.init();
    }

    init() {
        // Set up theme detection and initial state
        this.setupInitialTheme();
        
        // Set up theme change listeners
        this.setupThemeListeners();
        
        // Set up system theme change detection
        this.setupSystemThemeDetection();
        
        // Update icon based on current theme
        this.updateThemeIcon();
    }

    /**
     * Setup initial theme based on stored preference or system preference
     */
    setupInitialTheme() {
        const storedTheme = localStorage.getItem('admin-theme') || 'light';
        const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        
        let theme = storedTheme;
        
        // Handle auto theme
        if (storedTheme === 'auto') {
            theme = systemPrefersDark ? 'dark' : 'light';
        }
        
        // Apply theme
        this.applyTheme(theme);
        
        // Update radio buttons
        this.updateRadioButtons(storedTheme);
    }

    /**
     * Setup theme change listeners for radio buttons
     */
    setupThemeListeners() {
        const themeRadios = document.querySelectorAll('input[name="main-theme"]');
        
        if (themeRadios && themeRadios.length > 0) {
            themeRadios.forEach(radio => {
                if (radio) {
                    radio.addEventListener('change', (e) => {
                        if (e.target && e.target.checked) {
                            const selectedTheme = e.target.value;
                            this.handleThemeChange(selectedTheme);
                        }
                    });
                }
            });
        }
    }

    /**
     * Setup system theme change detection for auto mode
     */
    setupSystemThemeDetection() {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        
        mediaQuery.addListener((e) => {
            const storedTheme = localStorage.getItem('admin-theme');
            if (storedTheme === 'auto') {
                const theme = e.matches ? 'dark' : 'light';
                this.applyTheme(theme);
                this.updateThemeIcon(theme);
            }
        });
    }

    /**
     * Handle theme change
     */
    handleThemeChange(selectedTheme) {
        // Store preference
        localStorage.setItem('admin-theme', selectedTheme);
        
        let actualTheme = selectedTheme;
        
        // Handle auto theme
        if (selectedTheme === 'auto') {
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            actualTheme = systemPrefersDark ? 'dark' : 'light';
        }
        
        // Apply theme
        this.applyTheme(actualTheme);
        
        // Update icon
        this.updateThemeIcon(actualTheme);
        
        // Show notification
        this.showThemeChangeNotification(selectedTheme);
    }

    /**
     * Apply theme to document
     */
    applyTheme(theme) {
        const html = document.documentElement;
        
        // Remove existing theme attributes
        html.removeAttribute('data-color-theme');
        
        // Apply new theme
        if (theme === 'dark') {
            html.setAttribute('data-color-theme', 'dark');
        } else {
            html.setAttribute('data-color-theme', 'light');
        }
        
        // Apply to body if it exists
        if (document.body) {
            if (theme === 'dark') {
                document.body.classList.add('dark-theme');
                document.body.classList.remove('light-theme');
            } else {
                document.body.classList.add('light-theme');
                document.body.classList.remove('dark-theme');
            }
        }

        // Dispatch custom event for other components
        document.dispatchEvent(new CustomEvent('themeChanged', {
            detail: { theme }
        }));
    }

    /**
     * Update radio buttons to reflect current selection
     */
    updateRadioButtons(theme) {
        const themeRadios = document.querySelectorAll('input[name="main-theme"]');
        
        if (themeRadios && themeRadios.length > 0) {
            themeRadios.forEach(radio => {
                if (radio) {
                    radio.checked = (radio.value === theme);
                }
            });
        }
    }

    /**
     * Update theme icon in navbar
     */
    updateThemeIcon(theme = null) {
        const iconElement = document.querySelector('.theme-icon');
        if (!iconElement) return;

        // If no theme specified, detect current theme
        if (!theme) {
            const storedTheme = localStorage.getItem('admin-theme') || 'light';
            
            if (storedTheme === 'auto') {
                const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                theme = systemPrefersDark ? 'dark' : 'light';
            } else {
                theme = storedTheme;
            }
        }

        // Update icon
        iconElement.className = theme === 'dark' ? 'ph-sun theme-icon' : 'ph-moon theme-icon';
        
        // Update title attribute
        const iconTitle = theme === 'dark' ? 'تغییر به حالت روز' : 'تغییر به حالت شب';
        const parentLink = iconElement.closest('a');
        if (parentLink) {
            parentLink.setAttribute('title', iconTitle);
        }
    }

    /**
     * Show theme change notification
     */
    showThemeChangeNotification(theme) {
        const messages = {
            'light': 'حالت روز فعال شد',
            'dark': 'حالت شب فعال شد',
            'auto': 'حالت خودکار فعال شد'
        };

        const message = messages[theme] || 'تم تغییر کرد';

        // Create notification (using a simple toast-like notification)
        this.createToast(message, 'success');
    }

    /**
     * Create a simple toast notification
     */
    createToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast-notification toast-${type}`;
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background: ${type === 'success' ? '#059669' : '#0c83ff'};
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 9999;
            font-size: 14px;
            font-weight: 500;
            opacity: 0;
            transition: all 0.3s ease;
        `;
        toast.textContent = message;

        // Only append if body exists
        if (document.body) {
            document.body.appendChild(toast);
        } else {
            // If no body, don't create toast
            return;
        }

        // Show toast
        setTimeout(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(-50%) translateY(10px)';
        }, 100);

        // Hide and remove toast
        setTimeout(() => {
            if (toast && toast.style) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(-50%) translateY(-10px)';
                setTimeout(() => {
                    if (document.body && toast && toast.parentNode) {
                        try {
                            document.body.removeChild(toast);
                        } catch (e) {
                            // Ignore error if element was already removed
                        }
                    }
                }, 300);
            }
        }, 3000);
    }

    /**
     * Get current theme
     */
    getCurrentTheme() {
        const storedTheme = localStorage.getItem('admin-theme') || 'light';
        
        if (storedTheme === 'auto') {
            const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            return systemPrefersDark ? 'dark' : 'light';
        }
        
        return storedTheme;
    }

    /**
     * Toggle between light and dark theme (for programmatic use)
     */
    toggle() {
        const currentTheme = this.getCurrentTheme();
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        this.handleThemeChange(newTheme);
    }
}

// Apply initial theme immediately to prevent flash
(function() {
    const storedTheme = localStorage.getItem('admin-theme') || 'light';
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    let theme = storedTheme;
    if (storedTheme === 'auto') {
        theme = systemPrefersDark ? 'dark' : 'light';
    }
    
    // Apply theme to html immediately (this is always available)
    const html = document.documentElement;
    if (theme === 'dark') {
        html.setAttribute('data-color-theme', 'dark');
    } else {
        html.setAttribute('data-color-theme', 'light');
    }
    
    // Apply theme to body when it's ready
    function applyBodyTheme() {
        if (document.body) {
            if (theme === 'dark') {
                document.body.classList.add('dark-theme');
                document.body.classList.remove('light-theme');
            } else {
                document.body.classList.add('light-theme');
                document.body.classList.remove('dark-theme');
            }
        } else {
            // If body is not ready, try again in next tick
            setTimeout(applyBodyTheme, 10);
        }
    }
    
    applyBodyTheme();
})();

// Initialize theme switcher when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.themeSwitcher = new ThemeSwitcher();
});

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ThemeSwitcher;
}