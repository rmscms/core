/**
 * RMS Mobile Footer Navigation Plugin
 * 
 * @version 1.0.0
 * @author RMS Core Team
 * 
 * Features:
 * - Bootstrap tooltips integration
 * - Smooth animations and interactions
 * - Badge management
 * - Touch-friendly mobile experience
 * - RTL support
 * - Dark theme compatibility
 */

class RMSMobileFooterNav {
    constructor(options = {}) {
        this.options = {
            selector: '.mobile-footer-nav',
            tooltipConfig: {
                trigger: 'hover focus',
                delay: { show: 500, hide: 200 },
                boundary: 'viewport',
                customClass: 'mobile-footer-nav-tooltip',
                html: false,
                sanitize: true,
                template: '<div class="tooltip mobile-footer-nav-tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
            },
            animationDuration: 300,
            enableBadgeUpdates: true,
            enableVibration: true, // For touch devices
            ...options
        };
        
        this.isInitialized = false;
        this.tooltips = [];
        this.badgeUpdateInterval = null;
        
        this.init();
    }
    
    init() {
        if (this.isInitialized) {
            console.log('⚠️ RMS Mobile Footer Nav already initialized');
            return;
        }
        
        console.log('🚀 Initializing RMS Mobile Footer Navigation');
        
        // Check if we're on mobile
        if (!this.isMobileDevice()) {
            console.log('📱 Not a mobile device, skipping mobile nav initialization');
            return;
        }
        
        const navElement = document.querySelector(this.options.selector);
        if (!navElement) {
            console.error('❌ Mobile footer navigation element not found');
            return;
        }
        
        this.setupTooltips();
        this.setupEventListeners();
        this.setupFloatingMenu();
        this.setupBadgeManager();
        this.setupAccessibility();
        
        this.isInitialized = true;
        console.log('✅ RMS Mobile Footer Navigation initialized successfully');
    }
    
    // Check if we're on a mobile device
    isMobileDevice() {
        return window.innerWidth < 768 || ('ontouchstart' in window);
    }
    
    // Setup Bootstrap tooltips
    setupTooltips() {
        const tooltipElements = document.querySelectorAll(`${this.options.selector} [data-bs-toggle="tooltip"]`);
        
        tooltipElements.forEach(element => {
            const tooltip = new bootstrap.Tooltip(element, this.options.tooltipConfig);
            this.tooltips.push(tooltip);
            
            // Auto-hide tooltip after 3 seconds
            element.addEventListener('shown.bs.tooltip', () => {
                setTimeout(() => {
                    tooltip.hide();
                }, 3000);
            });
        });
        
        console.log(`✅ Initialized ${this.tooltips.length} tooltips with auto-hide`);
    }
    
    // Setup event listeners
    setupEventListeners() {
        const navElement = document.querySelector(this.options.selector);
        
        // Add touch feedback
        navElement.addEventListener('touchstart', (e) => {
            this.handleTouchStart(e);
        });
        
        navElement.addEventListener('touchend', (e) => {
            this.handleTouchEnd(e);
        });
        
        // Handle clicks with animation
        navElement.addEventListener('click', (e) => {
            this.handleClick(e);
        });
        
        // Handle window resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.handleResize();
            }, 150);
        });
        
        console.log('✅ Event listeners setup complete');
    }
    
    // Setup floating menu functionality
    setupFloatingMenu() {
        const addButton = document.querySelector('#addButton');
        const floatingMenu = document.querySelector('#floatingMenu');
        
        if (!addButton || !floatingMenu) {
            console.log('⚠️ Floating menu elements not found');
            return;
        }
        
        let isMenuOpen = false;
        
        // Toggle floating menu
        const toggleMenu = () => {
            isMenuOpen = !isMenuOpen;
            floatingMenu.classList.toggle('show', isMenuOpen);
            addButton.classList.toggle('active', isMenuOpen);
            
            // Rotate add icon
            const addIcon = addButton.querySelector('#addIcon');
            if (addIcon) {
                addIcon.style.transform = isMenuOpen ? 'rotate(45deg)' : 'rotate(0deg)';
            }
            
            // Haptic feedback
            if (this.options.enableVibration && 'vibrate' in navigator) {
                navigator.vibrate(15);
            }
        };
        
        // Close menu
        const closeMenu = () => {
            if (isMenuOpen) {
                isMenuOpen = false;
                floatingMenu.classList.remove('show');
                addButton.classList.remove('active');
                
                const addIcon = addButton.querySelector('#addIcon');
                if (addIcon) {
                    addIcon.style.transform = 'rotate(0deg)';
                }
            }
        };
        
        // Add button click
        addButton.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            toggleMenu();
        });
        
        // Close on outside click
        document.addEventListener('click', (e) => {
            if (isMenuOpen && !addButton.contains(e.target) && !floatingMenu.contains(e.target)) {
                closeMenu();
            }
        });
        
        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && isMenuOpen) {
                closeMenu();
            }
        });
        
        console.log('✅ Floating menu setup complete');
    }
    
    // Handle touch start with haptic feedback
    handleTouchStart(e) {
        const target = e.target.closest('.nav-item, .floating-item, #addButton');
        if (target) {
            target.classList.add('touching');
            
            // Haptic feedback on supported devices
            if (this.options.enableVibration && 'vibrate' in navigator) {
                navigator.vibrate(10);
            }
        }
    }
    
    // Handle touch end
    handleTouchEnd(e) {
        const target = e.target.closest('.nav-item, .floating-item, #addButton');
        if (target) {
            setTimeout(() => {
                target.classList.remove('touching');
            }, 100);
        }
    }
    
    // Handle clicks with smooth animations
    handleClick(e) {
        const target = e.target.closest('.nav-item, .floating-item');
        if (target) {
            this.animateClick(target);
        }
    }
    
    // Animate click feedback
    animateClick(element) {
        const icon = element.querySelector('.nav-icon-circle, .floating-icon');
        if (icon) {
            icon.style.transform = 'scale(0.9)';
            setTimeout(() => {
                icon.style.transform = '';
            }, 150);
        }
    }
    
    // Handle window resize
    handleResize() {
        if (!this.isMobileDevice()) {
            this.destroy();
            return;
        }
        
        // Refresh tooltips position
        this.tooltips.forEach(tooltip => {
            tooltip.update();
        });
    }
    
    // Setup badge management
    setupBadgeManager() {
        if (!this.options.enableBadgeUpdates) return;
        
        this.updateBadges();
        
        // Update badges every 30 seconds
        this.badgeUpdateInterval = setInterval(() => {
            this.updateBadges();
        }, 30000);
        
        console.log('✅ Badge manager setup complete');
    }
    
    // Update badges with current data
    updateBadges() {
        const badges = document.querySelectorAll(`${this.options.selector} .badge`);
        
        badges.forEach(badge => {
            // Add loading animation
            badge.classList.add('loading');
            
            // Remove loading after animation
            setTimeout(() => {
                badge.classList.remove('loading');
            }, 1500);
        });
    }
    
    // Setup accessibility features
    setupAccessibility() {
        const navItems = document.querySelectorAll(`${this.options.selector} .nav-item, ${this.options.selector} .floating-item`);
        
        navItems.forEach(item => {
            // Ensure proper focus management
            if (!item.hasAttribute('tabindex')) {
                item.setAttribute('tabindex', '0');
            }
            
            // Add ARIA labels if missing
            const link = item.querySelector('a');
            if (link && !link.hasAttribute('aria-label')) {
                const title = item.getAttribute('data-bs-title') || 'Navigation item';
                link.setAttribute('aria-label', title);
            }
        });
        
        console.log('✅ Accessibility features setup complete');
    }
    
    // Public methods
    refreshTooltips() {
        this.tooltips.forEach(tooltip => {
            tooltip.dispose();
        });
        this.tooltips = [];
        this.setupTooltips();
    }
    
    updateBadgeCount(selector, count) {
        const badge = document.querySelector(`${this.options.selector} ${selector} .badge`);
        if (badge) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.style.display = count > 0 ? 'block' : 'none';
        }
    }
    
    showBadgeLoading(selector) {
        const badge = document.querySelector(`${this.options.selector} ${selector} .badge`);
        if (badge) {
            badge.classList.add('loading');
        }
    }
    
    hideBadgeLoading(selector) {
        const badge = document.querySelector(`${this.options.selector} ${selector} .badge`);
        if (badge) {
            badge.classList.remove('loading');
        }
    }
    
    // Cleanup
    destroy() {
        if (!this.isInitialized) return;
        
        this.tooltips.forEach(tooltip => {
            tooltip.dispose();
        });
        this.tooltips = [];
        
        if (this.badgeUpdateInterval) {
            clearInterval(this.badgeUpdateInterval);
        }
        
        this.isInitialized = false;
        console.log('🗑️ RMS Mobile Footer Navigation destroyed');
    }
    
    // Reinitialize
    reinit() {
        this.destroy();
        this.init();
    }
}

// Global instance
window.RMSMobileFooterNav = RMSMobileFooterNav;

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.rmsMobileFooterNav === 'undefined') {
            window.rmsMobileFooterNav = new RMSMobileFooterNav();
        }
    });
} else {
    if (typeof window.rmsMobileFooterNav === 'undefined') {
        window.rmsMobileFooterNav = new RMSMobileFooterNav();
    }
}

// Additional utility functions
window.RMSMobileFooterNav.utils = {
    // Update specific badge
    updateBadge: function(type, count) {
        if (window.rmsMobileFooterNav) {
            const selectors = {
                tickets: '[href*="tickets"]',
                deposits: '[href*="deposits"]'
            };
            
            if (selectors[type]) {
                window.rmsMobileFooterNav.updateBadgeCount(selectors[type], count);
            }
        }
    },
    
    // Show loading for specific badge
    showLoading: function(type) {
        if (window.rmsMobileFooterNav) {
            const selectors = {
                tickets: '[href*="tickets"]',
                deposits: '[href*="deposits"]'
            };
            
            if (selectors[type]) {
                window.rmsMobileFooterNav.showBadgeLoading(selectors[type]);
            }
        }
    }
};