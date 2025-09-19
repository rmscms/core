/**
 * RMS Sidebar Mobile Plugin
 * 
 * @version 1.0.0
 * @author RMS Core Team
 */

class RMSSidebarMobile {
    constructor(options = {}) {
        this.options = {
            selectors: {
                sidebar: '.sidebar.sidebar-expand-lg.sidebar-main',
                toggles: '.sidebar-mobile-main-toggle'
            },
            classes: {
                expanded: 'sidebar-mobile-expanded',
                bodyLock: 'sidebar-mobile-open'
            },
            breakpoint: 992,
            ...options
        };
        
        this.isInitialized = false;
        this.init();
    }
    
    init() {
        if (this.isInitialized) {
            console.log('⚠️ RMS Sidebar Mobile already initialized');
            return;
        }
        
        console.log('🚀 Initializing RMS Sidebar Mobile');
        
        // بررسی المان‌های مورد نیاز
        const sidebar = document.querySelector(this.options.selectors.sidebar);
        const toggles = document.querySelectorAll(this.options.selectors.toggles);
        
        if (!sidebar) {
            console.error('❌ Sidebar not found');
            return;
        }
        
        if (toggles.length === 0) {
            console.error('❌ No toggle buttons found');
            return;
        }
        
        this.setupEventListeners();
        this.isInitialized = true;
        console.log('✅ RMS Sidebar Mobile initialized successfully');
    }
    
    // بررسی حالت موبایل
    isMobile() {
        return window.innerWidth < this.options.breakpoint;
    }
    
    // toggle sidebar
    toggleSidebar() {
        const sidebar = document.querySelector(this.options.selectors.sidebar);
        if (!sidebar) return;
        
        const isOpen = sidebar.classList.contains(this.options.classes.expanded);
        
        if (isOpen) {
            // بستن sidebar
            console.log('🔒 Closing sidebar');
            sidebar.classList.remove(this.options.classes.expanded);
            document.body.classList.remove(this.options.classes.bodyLock);
            this.removeBackdrop();
        } else {
            // باز کردن sidebar (فقط در موبایل)
            if (!this.isMobile()) return;
            console.log('🔓 Opening sidebar');
            sidebar.classList.add(this.options.classes.expanded);
            document.body.classList.add(this.options.classes.bodyLock);
            this.createBackdrop();
        }
    }
    
    // بستن sidebar
    closeSidebar() {
        const sidebar = document.querySelector(this.options.selectors.sidebar);
        if (!sidebar) return;
        
        sidebar.classList.remove(this.options.classes.expanded);
        document.body.classList.remove(this.options.classes.bodyLock);
        this.removeBackdrop();
    }
    
    // ایجاد backdrop
    createBackdrop() {
        this.removeBackdrop(); // حذف backdrop قبلی
        
        const backdrop = document.createElement('div');
        backdrop.className = 'sidebar-mobile-backdrop';
        backdrop.setAttribute('data-backdrop', 'true');
        
        // کلیک برای بستن
        backdrop.addEventListener('click', () => this.closeSidebar());
        backdrop.addEventListener('touchstart', () => this.closeSidebar());
        
        document.body.appendChild(backdrop);
    }
    
    // حذف backdrop
    removeBackdrop() {
        const backdrop = document.querySelector('.sidebar-mobile-backdrop');
        if (backdrop) {
            backdrop.remove();
        }
    }
    
    // راه‌اندازی event listeners
    setupEventListeners() {
        // Toggle buttons
        document.querySelectorAll(this.options.selectors.toggles).forEach(toggle => {
            // حذف listeners قبلی
            const newToggle = toggle.cloneNode(true);
            toggle.parentNode.replaceChild(newToggle, toggle);
            
            // اضافه کردن listener جدید
            newToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleSidebar();
            });
        });
        
        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isMobile()) {
                this.closeSidebar();
            }
        });
        
        // Window resize - بستن sidebar در دسکتاپ
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                if (!this.isMobile()) {
                    this.closeSidebar();
                }
            }, 150);
        });
        
        console.log('✅ Event listeners setup complete');
    }
    
    // Public methods
    toggle() {
        this.toggleSidebar();
    }
    
    close() {
        this.closeSidebar();
    }
    
    reinit() {
        this.isInitialized = false;
        this.init();
    }
}

// Global instance
window.RMSSidebarMobile = RMSSidebarMobile;

// Auto-initialize
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.rmsSidebarMobile === 'undefined') {
            window.rmsSidebarMobile = new RMSSidebarMobile();
        }
    });
} else {
    if (typeof window.rmsSidebarMobile === 'undefined') {
        window.rmsSidebarMobile = new RMSSidebarMobile();
    }
}