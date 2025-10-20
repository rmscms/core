<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Custom Plugin Configurations
    |--------------------------------------------------------------------------
    |
    | Define custom plugin loading configurations here. Each plugin can have
    | specific CSS/JS files, dependencies, and load order.
    |
    */

    'persian-datepicker' => [
        'css' => [
            'persian-datepicker.css'         // Fixed CSS from IRAS project with dark theme support
        ],
        'js' => [
            'persian-date.min.js',           // Persian date library with leap year fix from IRAS
            'persian-datepicker.js',         // Main datepicker JS from IRAS (no leap year bug)
            'rms2-persian-datepicker.js'     // RMS2 wrapper for theme integration and Bootstrap 5 compatibility
        ],
        'dependencies' => ['jquery'],
        'load_order' => 1,  // Load early
        'enabled' => true   // Can be disabled without code changes
    ],

    'advanced-select' => [
        'css' => [
            'select2.min.css',                  // Select2 base CSS
            'select2-bootstrap.css'             // Bootstrap 5 integration and dark theme support
        ],
        'js' => [
            'select2.min.js',                   // Select2 library from Limitless
            'select2-init.js'                   // RMS2 Select2 initialization wrapper
        ],
        'dependencies' => ['jquery'],           // Requires jQuery
        'load_order' => 3,  // Load after core plugins
        'plugin_path' => 'select2',             // ← Select2 plugin directory
        'enabled' => true
    ],

    'amount-formatter' => [
        'css' => [
            'amount-formatter.css'              // Amount field styling
        ],
        'js' => [
            'amount-formatter.js'               // Amount field formatting logic
        ],
        'dependencies' => [], // No dependencies - vanilla JS
        'load_order' => 2,  // Load early for form fields
        'plugin_path' => 'amount-formatter',    // Plugin directory name
        'enabled' => true
    ],

    'image-uploader' => [
        'css' => [
            'image-uploader.css'                // Image uploader styling with dark theme support
        ],
        'js' => [
            'image-uploader.js'                 // Professional image upload with preview & validation
        ],
        'dependencies' => [], // No dependencies - vanilla JS with Bootstrap 5
        'load_order' => 3,  // Load after core plugins but before advanced-select
        'plugin_path' => 'image-uploader',      // Plugin directory name
        'enabled' => true
    ],

    'sweetalert2' => [
        'css' => [
            'sweetalert2.css'                   // SweetAlert2 styles for theme with dark mode
        ],
        'js' => [
            'sweet_alert.min.js',               // SweetAlert2 library
            'rms-sweetalert-new.js'             // RMS wrapper for SweetAlert2
        ],
        'dependencies' => [], // No dependencies - vanilla JS
        'load_order' => 1,  // Load very early (before other plugins might need it)
        'plugin_path' => 'sweetalert2',         // Plugin directory name
        'enabled' => true
    ],

    'avatar-viewer' => [
        'css' => [
            'avatar-viewer.css'                 // Avatar thumbnail styles with hover effects
        ],
        'js' => [
            'avatar-viewer.js'                  // Avatar modal viewer with AJAX support
        ],
        'dependencies' => [], // No dependencies - uses SweetAlert2 which loads first
        'load_order' => 5,  // Load after SweetAlert2 and other core plugins
        'plugin_path' => 'avatar-viewer',       // Plugin directory name
        'enabled' => true
    ],

    'sidebar-mobile' => [
        'css' => [
            'sidebar-mobile.css'                // Sidebar mobile fix styles with responsive design
        ],
        'js' => [
            'sidebar-mobile.js'                 // Sidebar mobile functionality for toggle & backdrop
        ],
        'dependencies' => [], // No dependencies - vanilla JS with DOM API
        'load_order' => 1,  // Load early (layout-critical plugin)
        'plugin_path' => 'sidebar-mobile',      // Plugin directory name
        'enabled' => true
    ],

    'mobile-footer-nav' => [
        'css' => [
            'mobile-footer-nav.css'             // Mobile footer navigation styles with Bootstrap 5 integration
        ],
        'js' => [
            'mobile-footer-nav.js'              // Mobile footer nav with tooltips, animations & badge management
        ],
        'dependencies' => [], // No dependencies - works with Bootstrap 5 (already loaded)
        'load_order' => 2,  // Load after core layout plugins
        'plugin_path' => 'mobile-footer-nav',   // Plugin directory name
        'enabled' => true
    ],

    /*
    |--------------------------------------------------------------------------
    | Future Plugins
    |--------------------------------------------------------------------------
    |
    | Add new plugins here without modifying core files
    |
    */

    'persian-number-input' => [
        'css' => ['persian-number.css'],
        'js' => ['persian-number.js'],
        'dependencies' => ['jquery'],
        'enabled' => false  // Disabled by default
    ],

    'jalali-moment' => [
        'js' => ['moment-jalaali.min.js', 'jalali-moment-init.js'],
        'dependencies' => ['moment'],
        'enabled' => false  // Disabled by default
    ],
    'qrcode'=>[
        'js' => ['qrcode.min.js'],
        'dependencies' => [],
        'enabled' => true,
        'plugin_path'=>'qrcode',
    ],

    'confirm-modal' => [
        'js' => ['confirm-modal.js'],
        'css' => [],
        'dependencies' => [], // No dependencies - vanilla JS
        'load_order' => 1,  // Load early for use in other scripts
        'plugin_path' => 'confirm-modal',
        'enabled' => true,
        'description' => 'Beautiful, reusable confirmation modals with dark theme support'
    ],
];
