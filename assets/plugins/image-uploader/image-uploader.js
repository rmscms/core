// git-trigger
/**
 * RMS Image Uploader
 *
 * Professional image upload component with preview, drag & drop
 * Built with Bootstrap 5 and modern JavaScript
 *
 * Features:
 * - Drag & drop upload
 * - Image preview with thumbnails
 * - File size validation
 * - Image type validation
 * - Multiple upload support
 * - Responsive design
 * - RTL support
 * - Error handling
 *
 * @version 1.0.0
 * @author RMS Core Team
 */

class RMSImageUploader {
    constructor(options = {}) {
        this.options = {
            maxSize: 2 * 1024 * 1024, // 2MB default
            allowedTypes: ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'],
            multiple: false,
            dragDrop: true,
            preview: true,
            crop: false,
            resize: { width: 800, height: 600 },
            thumbnail: { width: 150, height: 150 },
            uploadUrl: '/admin/upload/image',
            deleteUrl: '/admin/delete/image',
            ajaxUpload: false,           // Enable AJAX upload mode
            modelId: null,              // Model ID for AJAX uploads
            fieldName: null,            // Field name for AJAX uploads
            enableAssignButton: false,   // Enable "Assign to Combination" button
            combinationSelectorId: 'combination-selector', // ID of combination selector element
            texts: {
                browse: 'انتخاب تصویر',
                dragDrop: 'تصاویر را اینجا رها کنید یا کلیک کنید',
                fileTooBig: 'سایز فایل بیش از حد مجاز است',
                invalidType: 'نوع فایل مجاز نیست',
                uploadSuccess: 'آپلود موفقیت‌آمیز',
                uploadError: 'خطا در آپلود',
                deleteSuccess: 'حذف شد',
                deleteError: 'خطا در حذف',
                loading: 'در حال آپلود...'
            },
            ...options
        };

        this.files = [];
        this.uploading = false;

        this.init();
    }

    /**
     * Initialize the image uploader
     */
    init() {
        this.setupEventListeners();
        this.initializeElements();

        // Log initialization success (non-blocking)
        if (console && console.log) {
            console.log('🖼️ RMS Image Uploader initialized successfully');
        }
    }

    /**
     * Setup event listeners for all image upload elements
     */
    setupEventListeners() {
        // Auto-initialize all .image-uploader elements
        document.addEventListener('DOMContentLoaded', () => {
            this.initializeElements();
        });

        // Handle dynamically added elements
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) { // Element node
                        const uploaders = node.querySelectorAll ? node.querySelectorAll('.image-uploader:not(.initialized)') : [];
                        uploaders.forEach(uploader => this.initializeUploader(uploader));
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    }

    /**
     * Initialize all image uploader elements on page
     */
    initializeElements() {
        const uploaders = document.querySelectorAll('.image-uploader:not(.initialized)');
        uploaders.forEach(uploader => this.initializeUploader(uploader));
    }

    /**
     * Initialize a single uploader element
     */
    initializeUploader(element) {
        if (element.classList.contains('initialized')) return;

        const input = element.querySelector('input[type="file"]');
        if (!input) return;

        // Mark as initialized
        element.classList.add('initialized');

        // Get configuration from element data attributes
        const config = this.getElementConfig(input);

        // Create uploader structure
        this.createUploaderHTML(element, input, config);

        // Setup events
        this.setupUploaderEvents(element, input, config);

        // Check for existing files and show preview
        this.checkAndShowExistingFiles(element, input, config);

        // Uploader initialized successfully (logged to console only)
    }

    /**
     * Get configuration from element data attributes
     */
    getElementConfig(input) {
        return {
            maxSize: this.parseSize(input.dataset.maxSize || '2MB'),
            preview: input.dataset.preview !== 'false',
            multiple: input.hasAttribute('multiple'),
            dragDrop: input.dataset.dragDrop !== 'false',
            crop: input.dataset.crop === 'true',
            resize: this.safeJsonParse(input.dataset.resize, {width: 800, height: 600}),
            thumbnail: this.safeJsonParse(input.dataset.thumbnail, {width: 150, height: 150}),
            // AJAX upload configuration
            ajaxUpload: input.dataset.ajaxUpload === 'true',
            modelId: input.dataset.modelId || null,
            fieldName: input.name || input.dataset.fieldName || null,
            // Cover selection support
            enableCover: input.dataset.enableCover === 'true',
            coverUrl: input.dataset.coverUrl || null,
            currentCover: input.dataset.currentCover || null,
            // Existing file data (single file)
            existingFile: input.dataset.existingFile || null,
            existingFilename: input.dataset.existingFilename || null,
            existingPath: input.dataset.existingPath || null,
            existingSize: input.dataset.existingSize || null,
            // Existing files data (multiple files) - decode HTML entities first
            existingFiles: this.safeJsonParse(this.decodeHtmlEntities(input.dataset.existingFiles), null)
        };
    }

    /**
     * Check for existing files and show preview if present (supports both single and multiple)
     */
    checkAndShowExistingFiles(container, input, config) {
        if (!config.preview) {
            return; // Preview disabled
        }

        const wrapper = container.querySelector('.rms-image-uploader-wrapper');
        const uploadArea = wrapper.querySelector('.rms-upload-area');
        const previewArea = wrapper.querySelector('.rms-preview-area');
        let hasExistingFiles = false;

        // Handle multiple existing files
        if (config.multiple && config.existingFiles && Array.isArray(config.existingFiles)) {
            // Create grid container for multiple files
            const gridContainer = document.createElement('div');
            gridContainer.className = 'row g-3';
            
            config.existingFiles.forEach(fileData => {
                const existingPreview = this.createExistingMultipleFilePreview(fileData, config);
                // Add assigned combo labels if available
                try {
                    const assigned = (window.RMS && window.RMS.images && window.RMS.images.assigned) || {};
                    const labels = (window.RMS && window.RMS.images && window.RMS.images.combo_labels) || {};
                    const list = assigned[fileData.path] || [];
                    if (list.length) {
                        const info = existingPreview.querySelector('.preview-info');
                        const tag = document.createElement('div');
                        tag.className = 'mt-1 small text-muted assigned-combos';
                        tag.innerHTML = 'برای: ' + list.map(id=> `<span class="badge bg-secondary me-1">${labels[id] || ('#'+id)}</span>`).join('');
                        info && info.appendChild(tag);
                    }
                } catch(e){}
                gridContainer.appendChild(existingPreview);
                hasExistingFiles = true;
            });
            
            previewArea.appendChild(gridContainer);
            
            if (hasExistingFiles && console && console.log) {
                console.log('🖼️ Multiple existing files preview loaded:', config.existingFiles.length + ' files');
            }
        }
        // Handle single existing file
        else if (!config.multiple && config.existingFile) {
            const existingPreview = this.createExistingFilePreview(config);
            previewArea.appendChild(existingPreview);
            hasExistingFiles = true;
            
            if (console && console.log) {
                console.log('🖼️ Existing file preview loaded:', config.existingFilename);
            }
        }

        // Show/hide areas based on existing files
        if (hasExistingFiles) {
            previewArea.style.display = 'block';
            // For single files, hide upload area; for multiple files, keep it visible for additional uploads
            if (!config.multiple) {
                uploadArea.style.display = 'none';
            }
        }
    }

    /**
     * Create preview element for existing file in multiple files mode
     */
    createExistingMultipleFilePreview(fileData, config) {
        const preview = document.createElement('div');
        preview.className = 'rms-image-preview-multiple col-12 col-md-6 mb-3 existing-file';
        
        // اضافه cache busting به فایل موجود
        const existingFileUrl = this.addCacheBusting(fileData.url);

        preview.innerHTML = `
            <div class="d-flex align-items-center p-3 border rounded mb-2 position-relative">
                <div class="position-absolute top-0 end-0 p-2">
                    <span class="badge bg-info">فعلی</span>
                </div>
                <div class="preview-thumbnail me-3 position-relative" style="cursor: pointer;" data-image-url="${existingFileUrl}" data-image-name="${fileData.filename || 'فایل موجود'}">
                    <img src="${existingFileUrl}" class="rounded" style="width: ${config.thumbnail.width}px; height: ${config.thumbnail.height}px; object-fit: cover;">
                    <div class="position-absolute top-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 10px; margin: -5px;">🔍</div>
                </div>
                <div class="preview-info flex-grow-1">
                    <div class="fw-bold text-success">
                        <i class="ph-check-circle me-1"></i>
                        ${fileData.filename || 'فایل موجود'}
                    </div>
                    <div class="text-muted small">
                        <span>${fileData.size || 'نامشخص'}</span>
                        <span class="mx-2">•</span>
                        <span class="badge bg-info bg-opacity-20 text-info">فایل فعلی</span>
                    </div>
                </div>
                <div class="preview-actions ms-2 d-flex align-items-center flex-wrap gap-2">
                    ${config.enableCover ? '<button type="button" class="btn btn-sm btn-outline-primary btn-cover"><i class="ph-star me-1"></i> کاور</button>' : ''}
                    ${config.enableAssignButton ? '<button type="button" class="btn btn-sm btn-outline-secondary btn-assign"><i class="ph-link me-1"></i> اتصال به ترکیب</button>' : ''}
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                        <i class="ph-trash me-1"></i>
                        حذف
                    </button>
                </div>
                <div class="preview-status ms-2">
                    <i class="ph-image text-primary fs-5"></i>
                </div>
            </div>
        `;

        // Setup assign button (multiple existing)
        const assignBtn = preview.querySelector('.btn-assign');
        if (assignBtn) {
            assignBtn.addEventListener('click', () => {
                this.assignToCombination(preview, fileData.path);
            });
        }
        // Setup cover button
        if (config.enableCover) {
            const coverBtn = preview.querySelector('.btn-cover');
            if (coverBtn) {
                coverBtn.addEventListener('click', () => {
                    this.setCoverAjax(preview, fileData.path, config);
                });
            }
            if (config.currentCover && fileData.path === config.currentCover) {
                this.applyCoverBadge(preview, true);
            }
        }
        // Setup remove button
        const removeBtn = preview.querySelector('.btn-remove');
        removeBtn.addEventListener('click', () => {
            this.confirmRemoveExistingMultipleFile(preview, fileData, config);
        });
        
        // Setup image click for modal
        const imageClickable = preview.querySelector('.preview-thumbnail');
        if (imageClickable && existingFileUrl) {
            imageClickable.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.showImageModal(existingFileUrl, fileData.filename || 'فایل موجود', fileData.size || '');
            });
        }

        return preview;
    }

    /**
     * Create preview element for existing file (single file mode)
     */
    createExistingFilePreview(config) {
        const preview = document.createElement('div');
        preview.className = 'rms-image-preview d-flex align-items-center p-3 border rounded mb-2 existing-file';
        
        // اضافه cache busting به فایل موجود
        const existingFileUrl = this.addCacheBusting(config.existingFile);

        preview.innerHTML = `
            <div class="preview-thumbnail me-3">
                <img src="${existingFileUrl}" class="rounded" style="width: ${config.thumbnail.width}px; height: ${config.thumbnail.height}px; object-fit: cover;">
            </div>
            <div class="preview-info flex-grow-1">
                <div class="fw-bold text-success">
                    <i class="ph-check-circle me-1"></i>
                    ${config.existingFilename || 'فایل موجود'}
                </div>
                <div class="text-muted small">
                    <span>${config.existingSize || 'نامشخص'}</span>
                    <span class="mx-2">•</span>
                    <span class="badge bg-info bg-opacity-20 text-info">فایل فعلی</span>
                </div>
                <div class="preview-actions mt-2">
                    <button type="button" class="btn btn-sm btn-outline-primary btn-change">
                        <i class="ph-pencil me-1"></i>
                        تغییر
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove ms-1">
                        <i class="ph-trash me-1"></i>
                        حذف
                    </button>
                </div>
            </div>
            <div class="preview-status ms-2">
                <i class="ph-image text-primary fs-5"></i>
            </div>
        `;

        // Setup change button (show upload area again)
        const changeBtn = preview.querySelector('.btn-change');
        changeBtn.addEventListener('click', () => {
            this.showUploadArea(preview.closest('.rms-image-uploader-wrapper'));
        });

        // Setup remove button
        const removeBtn = preview.querySelector('.btn-remove');
        removeBtn.addEventListener('click', () => {
            this.confirmRemoveExistingFile(preview, config);
        });

        return preview;
    }

    /**
     * Show upload area (hide existing preview) - برای تغییر فایل
     */
    showUploadArea(wrapper) {
        const uploadArea = wrapper.querySelector('.rms-upload-area');
        const previewArea = wrapper.querySelector('.rms-preview-area');
        
        // چک کن که آیا حالت multiple هست (با بررسی وجود grid)
        const gridContainer = previewArea.querySelector('.row.g-3');
        const hasMultiplePreviews = previewArea.querySelector('.rms-image-preview-multiple');
        
        if (hasMultiplePreviews || gridContainer) {
            // حالت multiple: فقط upload area را نمایش بده - preview area را مخفی نکن
            uploadArea.style.display = 'block';
        } else {
            // حالت single: روش قدیمی
            // Hide all existing previews (both existing files and uploaded files)
            previewArea.querySelectorAll('.rms-image-preview').forEach(preview => {
                preview.style.display = 'none';
            });
            
            // Hide preview area if no visible previews remain
            const visiblePreviews = Array.from(previewArea.querySelectorAll('.rms-image-preview'))
                .filter(preview => preview.style.display !== 'none');
            
            if (visiblePreviews.length === 0) {
                previewArea.style.display = 'none';
            }

            // Show upload area
            uploadArea.style.display = 'block';
        }
    }

    /**
     * Confirm removal of existing file from multiple files with SweetAlert
     */
    confirmRemoveExistingMultipleFile(preview, fileData, config) {
        const fileName = fileData.filename || 'این فایل';
        
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'حذف فایل',
                text: `آیا مطمئن هستید که می‌خواهید "${fileName}" را حذف کنید؟`,
                html: `<p>آیا مطمئن هستید که می‌خواهید <strong>"${fileName}"</strong> را حذف کنید؟</p><p class="text-muted small">این عملیات غیرقابل بازگشت است.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                allowOutsideClick: true,
                allowEscapeKey: true,
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('🗑️ User confirmed file deletion:', fileName);
                    this.removeExistingFileFromServer(preview, {
                        existingPath: fileData.path,
                        existingFilename: fileData.filename
                    });
                } else {
                    console.log('❌ User cancelled file deletion');
                }
            });
        } else {
            // Fallback به browser confirm اگر SweetAlert موجود نباشد
            const confirmMessage = `آیا مطمئن هستید که می‌خواهید "${fileName}" را حذف کنید؟\n\nاین عملیات غیرقابل بازگشت است.`;
            if (confirm(confirmMessage)) {
                console.log('🗑️ User confirmed file deletion:', fileName);
                this.removeExistingFileFromServer(preview, {
                    existingPath: fileData.path,
                    existingFilename: fileData.filename
                });
            } else {
                console.log('❌ User cancelled file deletion');
            }
        }
    }

    /**
     * Confirm removal of existing file with SweetAlert (single file mode)
     */
    confirmRemoveExistingFile(preview, config) {
        const fileName = config.existingFilename || 'این فایل';
        
        if (typeof Swal !== 'undefined') {
            // استفاده مستقیم از Swal مثل success message
            Swal.fire({
                title: 'حذف فایل فعلی',
                text: `آیا مطمئن هستید که می‌خواهید "${fileName}" را حذف کنید؟`,
                html: `<p>آیا مطمئن هستید که می‌خواهید <strong>"${fileName}"</strong> را حذف کنید؟</p><p class="text-muted small">این عملیات غیرقابل بازگشت است.</p>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'بله، حذف کن',
                cancelButtonText: 'انصراف',
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-danger',
                    cancelButton: 'btn btn-secondary'
                },
                allowOutsideClick: true,
                allowEscapeKey: true,
                reverseButtons: true,
                focusCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('🗑️ User confirmed file deletion:', fileName);
                    this.removeExistingFileFromServer(preview, config);
                } else {
                    console.log('❌ User cancelled file deletion');
                }
            });
        } else {
            // Fallback به browser confirm اگر SweetAlert موجود نباشد
            const confirmMessage = `آیا مطمئن هستید که می‌خواهید "${fileName}" را حذف کنید؟\n\nاین عملیات غیرقابل بازگشت است.`;
            if (confirm(confirmMessage)) {
                console.log('🗑️ User confirmed file deletion:', fileName);
                this.removeExistingFileFromServer(preview, config);
            } else {
                console.log('❌ User cancelled file deletion');
            }
        }
    }

    /**
     * Remove existing file from server via AJAX
     */
    async removeExistingFileFromServer(preview, config) {
        if (!config.existingPath) {
            this.showMessage('مسیر فایل برای حذف یافت نشد.', 'error', preview.closest('.image-uploader'));
            return;
        }

        try {
            // Extract controller and model ID from current URL
            const pathMatch = window.location.pathname.match(/\/admin\/([^\/]+)\/(\d+|edit)\/?(\d+)?/);
            if (!pathMatch) {
                throw new Error('نمی‌توان اطلاعات مسیر را تشخیص داد');
            }

            const controllerName = pathMatch[1];
            let modelId = pathMatch[2];

            // If it's edit route like /admin/users/edit/123
            if (modelId === 'edit' && pathMatch[3]) {
                modelId = pathMatch[3];
            }

            if (!modelId || modelId === 'edit') {
                throw new Error('شناسه رکورد یافت نشد');
            }

            // Build delete URL
            const fieldName = this.getFieldNameFromInput(preview.closest('.image-uploader'));
            const deleteUrl = `/admin/${controllerName}/${modelId}/ajax-delete/${fieldName}?file_path=${encodeURIComponent(config.existingPath)}`;

            // Show loading state
            const wrapper = preview.closest('.rms-image-uploader-wrapper');
            this.showMessage('در حال حذف فایل...', 'info', wrapper.parentElement);
            preview.style.opacity = '0.5';
            preview.style.pointerEvents = 'none';

            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json'
                }
            });

            // Check if response is JSON
            const contentType = response.headers.get('content-type');
            let result;

            if (contentType && contentType.includes('application/json')) {
                result = await response.json();
            } else {
                // Handle non-JSON responses (like 404 errors)
                const text = await response.text();
                if (response.status === 404) {
                    throw new Error('مسیر حذف فایل یافت نشد. لطفاً صفحه را رفرش کنید.');
                } else {
                    throw new Error(`خطای سرور: ${response.status}`);
                }
            }

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'خطا در حذف فایل از سرور');
            }

            // Successfully deleted from server
            this.removeExistingPreview(preview);
            

            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'حذف موفق',
                    text: result.message || 'فایل با موفقیت حذف شد.',
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success'
                    },
                    allowOutsideClick: true,
                    allowEscapeKey: true
                });
            } else {
                this.showMessage(result.message || 'فایل با موفقیت حذف شد.', 'success', wrapper.parentElement);
            }

        } catch (error) {
            // Reset visual state
            preview.style.opacity = '1';
            preview.style.pointerEvents = 'auto';

            // Show error message
            const errorMsg = error.message || 'خطا در حذف فایل';

            // Close any open SweetAlert first
            if (typeof Swal !== 'undefined') {
                Swal.close();
            }

            // Then show error using SweetAlert or fallback
            if (typeof Swal !== 'undefined') {
                const swalInit = Swal.mixin({
                    buttonsStyling: true,
                    customClass: {
                        confirmButton: 'btn btn-primary',
                        cancelButton: 'btn btn-light'
                    }
                });

                swalInit.fire({
                    title: 'خطا در حذف',
                    text: errorMsg,
                    icon: 'error',
                    confirmButtonText: 'تأید'
                });
            } else {
                this.showMessage(errorMsg, 'error', preview.closest('.image-uploader'));
            }

            if (console && console.error) {
                console.error('❌ File delete failed:', error);
            }
        }
    }

    /**
     * Get field name from input element
     */
    getFieldNameFromInput(container) {
        const input = container.querySelector('input[type="file"]');
        return input ? input.name : 'file';
    }

    /**
     * Remove existing file preview (UI only)
     */
    removeExistingPreview(preview) {
        const isMultiple = preview.classList.contains('rms-image-preview-multiple');
        const wrapper = preview.closest('.rms-image-uploader-wrapper');
        const uploadArea = wrapper.querySelector('.rms-upload-area');
        const previewArea = wrapper.querySelector('.rms-preview-area');

        // Remove the preview
        preview.remove();

        if (isMultiple) {
            // حالت multiple: فقط preview را حذف کن - هیچ کار اضافی نکن
            // preview area همیشه نمایش داده شود
        } else {
            // حالت single: اگر preview ها تمام شد
            if (previewArea.children.length === 0) {
                previewArea.style.display = 'none';
                uploadArea.style.display = 'block';
            }
        }
    }

    /**
     * Show message (info, success, error)
     */
    showMessage(message, type = 'info', container) {
        const alertClass = type === 'error' ? 'alert-danger' :
                          type === 'success' ? 'alert-success' : 'alert-info';
        const icon = type === 'error' ? 'ph-warning-circle' :
                    type === 'success' ? 'ph-check-circle' : 'ph-info-circle';

        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show image-upload-message mb-3`;
        alert.innerHTML = `
            <i class="${icon} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Remove existing messages
        container.querySelectorAll('.image-upload-message').forEach(msg => msg.remove());

        // Insert at the beginning of container (above upload area)
        container.insertBefore(alert, container.firstChild);

        // Auto remove after 4 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 4000);
    }

    /**
     * Parse size string to bytes
     */
    parseSize(sizeStr) {
        const units = { KB: 1024, MB: 1024*1024, GB: 1024*1024*1024 };
        const match = sizeStr.match(/^(\d+(?:\.\d+)?)(KB|MB|GB)$/i);
        return match ? parseFloat(match[1]) * units[match[2].toUpperCase()] : 2*1024*1024;
    }

    /**
     * Safe JSON parse with fallback
     */
    safeJsonParse(jsonString, fallback) {
        try {
            if (!jsonString || jsonString.trim() === '') {
                return fallback;
            }
            return JSON.parse(jsonString);
        } catch (error) {
            // Log warning for debugging only
            if (console && console.warn) {
                console.warn('JSON parse error, using fallback:', error);
            }
            return fallback;
        }
    }

    /**
     * Create uploader HTML structure
     */
    createUploaderHTML(container, input, config) {
        // Hide original input
        input.style.display = 'none';

        // Create wrapper
        const wrapper = document.createElement('div');
        wrapper.className = 'rms-image-uploader-wrapper';
        wrapper.style.display = 'flex';
        wrapper.style.flexDirection = 'column';
        wrapper.style.gap = '1rem'; // Space between upload and preview areas

        // Main upload area
        const uploadArea = document.createElement('div');
        uploadArea.className = `rms-upload-area ${config.dragDrop ? 'drag-enabled' : ''} mb-4`;
        uploadArea.style.position = 'relative';
        uploadArea.style.zIndex = '2'; // Higher z-index to stay above preview
        uploadArea.style.border = '2px dashed #dee2e6';
        uploadArea.style.borderRadius = '8px';

        uploadArea.innerHTML = `
            <div class="upload-content text-center p-4">
                <div class="upload-icon mb-3">
                    <i class="ph-image fs-1 text-muted"></i>
                </div>
                <div class="upload-text">
                    <h6 class="mb-2">${this.options.texts.dragDrop}</h6>
                    <button type="button" class="btn btn-outline-primary btn-browse">
                        <i class="ph-upload me-2"></i>
                        ${this.options.texts.browse}
                    </button>
                </div>
                <div class="upload-info mt-2 small text-muted">
                    <div>حداکثر سایز: ${input.dataset.maxSize || '2MB'}</div>
                    <div>فرمت‌های مجاز: JPG, PNG, GIF, WebP</div>
                </div>
            </div>
            <div class="upload-progress" style="display: none;">
                <div class="progress">
                    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar"></div>
                </div>
                <div class="progress-text text-center mt-2 small">${this.options.texts.loading}</div>
            </div>
        `;

        // Preview area with more spacing to avoid overlap
        const previewArea = document.createElement('div');
        previewArea.className = 'rms-preview-area mt-4';
        previewArea.style.display = 'none';
        previewArea.style.clear = 'both'; // Clear any floats
        previewArea.style.position = 'relative'; // Ensure proper positioning
        previewArea.style.zIndex = '1'; // Make sure it's above other elements

        wrapper.appendChild(uploadArea);
        wrapper.appendChild(previewArea);

        // Insert after original input
        container.appendChild(wrapper);
    }

    /**
     * Setup events for uploader element
     */
    setupUploaderEvents(container, input, config) {
        const wrapper = container.querySelector('.rms-image-uploader-wrapper');
        const uploadArea = wrapper.querySelector('.rms-upload-area');
        const browseBtn = wrapper.querySelector('.btn-browse');
        const previewArea = wrapper.querySelector('.rms-preview-area');

        // Browse button click
        browseBtn.addEventListener('click', (e) => {
            e.preventDefault();
            input.click();
        });

        // File input change
        input.addEventListener('change', (e) => {
            this.handleFileSelect(e.target.files, container, config);
        });

        // Drag and drop events
        if (config.dragDrop) {
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                });
            });

            ['dragenter', 'dragover'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.add('drag-over');
                });
            });

            ['dragleave', 'drop'].forEach(eventName => {
                uploadArea.addEventListener(eventName, () => {
                    uploadArea.classList.remove('drag-over');
                });
            });

            uploadArea.addEventListener('drop', (e) => {
                const files = Array.from(e.dataTransfer.files);
                this.handleFileSelect(files, container, config);
            });
        }
    }

    /**
     * Handle file selection
     */
    handleFileSelect(files, container, config) {
        const validFiles = [];

        Array.from(files).forEach(file => {
            const validation = this.validateFile(file, config);
            if (validation.valid) {
                validFiles.push(file);
            } else {
                this.showError(validation.error, container);
            }
        });

        if (validFiles.length > 0) {
            this.processFiles(validFiles, container, config);
        }
    }

    /**
     * Validate file
     */
    validateFile(file, config) {
        // Check file type
        if (!this.options.allowedTypes.includes(file.type)) {
            return { valid: false, error: this.options.texts.invalidType };
        }

        // Check file size
        if (file.size > config.maxSize) {
            return { valid: false, error: this.options.texts.fileTooBig };
        }

        return { valid: true };
    }

    /**
     * Process valid files
     */
    async processFiles(files, container, config) {
        const wrapper = container.querySelector('.rms-image-uploader-wrapper');
        const uploadArea = wrapper.querySelector('.rms-upload-area');
        const previewArea = wrapper.querySelector('.rms-preview-area');

        // Show loading state
        this.showLoading(uploadArea, true);

        if (config.ajaxUpload && config.multiple) {
            // Multiple AJAX upload - handle all files in one request
            try {
                await this.uploadMultipleFilesAjax(files, container, config);
            } catch (error) {
                this.showError(error.message, container);
            }
        } else {
            // Single file or normal processing
            for (const file of files) {
                try {
                    if (config.ajaxUpload) {
                        // AJAX upload mode (single file)
                        await this.uploadFileAjax(file, container, config);
                    } else {
                        // Normal preview mode
                        await this.processFile(file, previewArea, config);
                    }
                    if (!config.multiple) break; // Only one file for single upload
                } catch (error) {
                    this.showError(error.message, container);
                }
            }
        }

        // Hide loading state
        this.showLoading(uploadArea, false);

        // Show preview area
        if (previewArea.children.length > 0) {
            previewArea.style.display = 'block';
        }
    }

    /**
     * Process single file (normal preview mode)
     */
    async processFile(file, previewArea, config) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();

            reader.onload = (e) => {
                const preview = this.createPreview(file, e.target.result, config);
                previewArea.appendChild(preview);
                resolve();
            };

            reader.onerror = () => reject(new Error('خطا در خواندن فایل'));
            reader.readAsDataURL(file);
        });
    }

    /**
     * Upload multiple files via AJAX
     */
    async uploadMultipleFilesAjax(files, container, config) {
        if (!config.modelId || !config.fieldName) {
            throw new Error('برای AJAX upload باید modelId و fieldName تعیین شود');
        }

        const formData = new FormData();
        
        // Add all files to FormData with array notation
        files.forEach((file, index) => {
            formData.append(`${config.fieldName}[${index}]`, file);
        });
        
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

        try {
            // Build upload URL
            const uploadUrl = `/admin/${this.getControllerName()}/${config.modelId}/ajax-upload/${config.fieldName}`;

            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'خطا در آپلود فایل‌ها');
            }

            const previewArea = container.querySelector('.rms-preview-area');
            
            // Create grid container if it doesn't exist
            let gridContainer = previewArea.querySelector('.row.g-3');
            if (!gridContainer) {
                gridContainer = document.createElement('div');
                gridContainer.className = 'row g-3';
                previewArea.appendChild(gridContainer);
            }
            
            // Create previews for all uploaded files
            if (result.uploaded_files && Array.isArray(result.uploaded_files)) {
                result.uploaded_files.forEach((filePath, index) => {
                    const fileResult = {
                        uploaded_files: filePath,
                        file_info: result.file_info && result.file_info[index] ? result.file_info[index] : null
                    };
                    const preview = this.createUploadedPreview(files[index], fileResult, config);
                    gridContainer.appendChild(preview);
                });
            }
            
            previewArea.style.display = 'block';
            
            // Hide upload area after successful upload (for multiple files, keep it open)
            // Multiple files usually allow additional uploads
            
            // Show success message with merge info if available
            const successMessage = result.message || `${files.length} فایل با موفقیت آپلود شد.`;
            this.showSuccess(successMessage, container);
            
            // Debug: Log merge info
            if (result.total_files_count && result.new_files_count) {
                console.log('📋 File Merge Info:', {
                    new_files: result.new_files_count,
                    total_files: result.total_files_count,
                    field: result.field
                });
            }

        } catch (error) {
            if (console && console.error) {
                console.error('❌ Multiple AJAX upload failed:', error);
            }
            throw error;
        }
    }

    /**
     * Upload file via AJAX (single file)
     */
    async uploadFileAjax(file, container, config) {
        if (!config.modelId || !config.fieldName) {
            throw new Error('برای AJAX upload باید modelId و fieldName تعیین شود');
        }

        const formData = new FormData();
        formData.append(config.fieldName, file);
        formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

        try {
            // Build upload URL
            const uploadUrl = `/admin/${this.getControllerName()}/${config.modelId}/ajax-upload/${config.fieldName}`;

            const response = await fetch(uploadUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'خطا در آپلود فایل');
            }

            const previewArea = container.querySelector('.rms-preview-area');
            
            // حذف preview های موجود (برای جایگزینی فایل)
            previewArea.querySelectorAll('.rms-image-preview').forEach(existingPreview => {
                existingPreview.remove();
            });
            
            // Create preview with uploaded file info (with cache busting)
            const preview = this.createUploadedPreview(file, result, config);
            previewArea.appendChild(preview);
            previewArea.style.display = 'block';
            
            // Hide upload area after successful upload
            const uploadArea = container.querySelector('.rms-upload-area');
            if (uploadArea) {
                uploadArea.style.display = 'none';
            }

            // Show success message above the container (more visible)
            this.showSuccess(result.message, container);

            // File uploaded successfully - already showing success message above

        } catch (error) {
            // Error already handled and shown via SweetAlert
            if (console && console.error) {
                console.error('❌ AJAX upload failed:', error);
            }
            throw error;
        }
    }

    /**
     * Create preview element
     */
    createPreview(file, dataUrl, config) {
        const preview = document.createElement('div');
        preview.className = 'rms-image-preview d-flex align-items-center p-3 border rounded mb-2';

        preview.innerHTML = `
            <div class="preview-thumbnail me-3">
                <img src="${dataUrl}" class="rounded" style="width: ${config.thumbnail.width}px; height: ${config.thumbnail.height}px; object-fit: cover;">
            </div>
            <div class="preview-info flex-grow-1">
                <div class="fw-bold">${file.name}</div>
                <div class="text-muted small">
                    <span>${this.formatFileSize(file.size)}</span>
                    <span class="mx-2">•</span>
                    <span>${file.type.split('/')[1].toUpperCase()}</span>
                </div>
                <div class="preview-actions mt-2">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove">
                        <i class="ph-trash me-1"></i>
                        حذف
                    </button>
                </div>
            </div>
            <div class="preview-status ms-2">
                <i class="ph-check-circle text-success fs-5"></i>
            </div>
        `;

        // Setup remove button
        const removeBtn = preview.querySelector('.btn-remove');
        removeBtn.addEventListener('click', () => {
            this.removePreview(preview);
        });

        return preview;
    }

    /**
     * Remove preview
     */
    removePreview(preview) {
        const isMultiple = preview.classList.contains('rms-image-preview-multiple');
        
        if (isMultiple) {
            // حالت multiple: فقط card را حذف کن - هیچ کار اضافی نکن
            preview.remove();
            // تمام! هیچ کار دیگری نکن
            
        } else {
            // حالت single: روش قدیمی
            const previewContainer = preview.closest('.rms-preview-area');
            preview.remove();
            
            if (previewContainer) {
                const remainingPreviews = previewContainer.querySelectorAll('.rms-image-preview, .rms-image-preview-multiple');
                
                if (remainingPreviews.length === 0) {
                    previewContainer.style.display = 'none';
                    
                    const wrapper = previewContainer.closest('.rms-image-uploader-wrapper');
                    if (wrapper) {
                        const uploadArea = wrapper.querySelector('.rms-upload-area');
                        if (uploadArea) {
                            uploadArea.style.display = 'block';
                        }
                    }
                }
            }
        }
    }

    /**
     * Show loading state
     */
    showLoading(uploadArea, show) {
        const content = uploadArea.querySelector('.upload-content');
        const progress = uploadArea.querySelector('.upload-progress');

        if (show) {
            content.style.display = 'none';
            progress.style.display = 'block';
        } else {
            content.style.display = 'block';
            progress.style.display = 'none';
        }
    }

    /**
     * Show error message
     */
    showError(message, container) {
        // Remove existing error alerts
        container.querySelectorAll('.image-upload-error').forEach(alert => alert.remove());

        const alert = document.createElement('div');
        alert.className = 'alert alert-danger alert-dismissible fade show image-upload-error mb-3';
        alert.innerHTML = `
            <i class="ph-warning-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at the beginning of container (above upload area)
        container.insertBefore(alert, container.firstChild);

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    /**
     * Get controller name from current URL
     */
    getControllerName() {
        const path = window.location.pathname;
        const match = path.match(/\/admin\/([^\/]+)/);
        return match ? match[1] : 'upload';
    }

    /**
     * Assign existing/uploaded image to selected combination via AJAX
     */
    async assignToCombination(previewElement, filePath) {
        try {
            const sel = document.getElementById('combination-selector');
            const comb = sel && sel.value ? sel.value : '';
            if (!comb) { this.showError('ابتدا یک ترکیب انتخاب کنید', previewElement.closest('.image-uploader')); return; }
            const pathMatch = window.location.pathname.match(/\/admin\/([^\/]+)\/(\d+|edit)\/?(\d+)?/);
            if (!pathMatch) throw new Error('شناسه مسیر یافت نشد');
            const controller = pathMatch[1];
            let modelId = pathMatch[2];
            if (modelId === 'edit' && pathMatch[3]) { modelId = pathMatch[3]; }
            const url = `/admin/${controller}/${modelId}/images/assign`;
            const resp = await fetch(url, {
                method: 'POST', headers: {
                    'X-Requested-With':'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '', 'Content-Type':'application/json'
                }, body: JSON.stringify({ combination_id: comb, file_path: filePath })
            });
            const data = await resp.json();
            if (!resp.ok || !data.ok) throw new Error(data.message || 'خطا در اتصال تصویر به ترکیب');
            // Success toast
            this.showSuccess('تصویر به ترکیب وصل شد', previewElement.closest('.image-uploader'));
            // Append combo tag below the preview info
            try {
                const info = previewElement.querySelector('.preview-info');
                if (info) {
                    let wrap = info.querySelector('.assigned-combos');
                    if (!wrap) {
                        wrap = document.createElement('div');
                        wrap.className = 'mt-1 small text-muted assigned-combos';
                        wrap.innerHTML = 'برای: ';
                        info.appendChild(wrap);
                    }
                    const combKey = String(comb);
                    if (!wrap.querySelector(`[data-combination-id="${combKey}"]`)) {
                        const label = (data && data.label) || (window.RMS && window.RMS.images && window.RMS.images.combo_labels && window.RMS.images.combo_labels[comb]) || ('#'+combKey);
                        const span = document.createElement('span');
                        span.className = 'badge bg-secondary me-1';
                        span.setAttribute('data-combination-id', combKey);
                        span.textContent = label;
                        wrap.appendChild(span);
                    }
                }
                // Update global assigned map in-place for future UI refreshes
                const root = (window.RMS = window.RMS || {});
                root.images = root.images || {};
                root.images.assigned = root.images.assigned || {};
                const key = (data && data.file_path) || filePath;
                const list = root.images.assigned[key] = root.images.assigned[key] || [];
                const combVal = isNaN(Number(comb)) ? comb : Number(comb);
                if (!list.includes(combVal)) list.push(combVal);
            } catch(_e){}
        } catch(err) {
            this.showError(err.message || 'خطا در اتصال تصویر', previewElement.closest('.image-uploader'));
        }
    }

    /**
     * Create preview for uploaded file (AJAX mode)
     */
    createUploadedPreview(file, uploadResult, config) {
        const preview = document.createElement('div');
        
        // Use different layout for multiple vs single files
        if (config.multiple) {
            // Grid layout: 1 فایل در موبایل، 2 فایل در تبلت، 2 فایل در دسکتاپ
            preview.className = 'rms-image-preview-multiple col-12 col-md-6 mb-3 uploaded';
        } else {
            preview.className = 'rms-image-preview d-flex align-items-center p-3 border rounded mb-2 uploaded';
        }
        
        preview.dataset.filePath = uploadResult.uploaded_files || '';

        // Use file info from server if available
        const fileInfo = uploadResult.file_info || {};
        let fileUrl = fileInfo.url || '';
        const fileName = fileInfo.name || file.name;
        const fileSize = fileInfo.formatted_size || this.formatFileSize(file.size);
        
        // اضافه cache busting برای جلوگیری از cache مرورگر
        fileUrl = this.addCacheBusting(fileUrl);

        // Generate HTML based on layout type
        if (config.multiple) {
            // طراحی افقی مشابه single برای multiple files
            preview.innerHTML = `
                <div class="d-flex align-items-center p-3 border rounded mb-2 position-relative">
                    <div class="position-absolute top-0 end-0 p-2">
                        <span class="badge bg-success">جدید</span>
                    </div>
                    <div class="preview-thumbnail me-3 position-relative" style="cursor: pointer;" data-image-url="${fileUrl}" data-image-name="${fileName}">
                        ${fileUrl ? `<img src="${fileUrl}" class="rounded" style="width: ${config.thumbnail.width}px; height: ${config.thumbnail.height}px; object-fit: cover;">` :
                                   `<div class="rounded bg-light d-flex align-items-center justify-content-center" style="width: ${config.thumbnail.width}px; height: ${config.thumbnail.height}px;"><i class="ph-image fs-2 text-muted"></i></div>`}
                        <div class="position-absolute top-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 20px; height: 20px; font-size: 10px; margin: -5px;">🔍</div>
                    </div>
                    <div class="preview-info flex-grow-1">
                        <div class="fw-bold text-success">
                            <i class="ph-check-circle me-1"></i>
                            ${fileName}
                        </div>
                        <div class="text-muted small">
                            <span>${fileSize}</span>
                            <span class="mx-2">•</span>
                            <span class="badge bg-success bg-opacity-20 text-success">آپلود شده</span>
                        </div>
                    </div>
                    <div class="preview-actions ms-2 d-flex align-items-center flex-wrap gap-2">
                        ${config.enableCover ? '<button type="button" class="btn btn-sm btn-outline-primary btn-cover"><i class="ph-star me-1"></i> کاور</button>' : ''}
                        ${config.enableAssignButton ? `<button type="button" class="btn btn-sm btn-outline-secondary btn-assign" data-file-path="${uploadResult.uploaded_files || ''}"><i class="ph-link me-1"></i> اتصال به ترکیب</button>` : ''}
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-file-path="${uploadResult.uploaded_files || ''}">
                            <i class="ph-trash me-1"></i>
                            حذف
                        </button>
                    </div>
                    <div class="preview-status ms-2">
                        <i class="ph-check-circle text-success fs-5"></i>
                    </div>
                </div>
            `;
        } else {
            // Original horizontal layout for single files
            preview.innerHTML = `
                <div class="preview-thumbnail me-3">
                    ${fileUrl ? `<img src="${fileUrl}" class="rounded" style="width: ${config.thumbnail.width}px; height: ${config.thumbnail.height}px; object-fit: cover;">` :
                               `<i class="ph-image fs-2 text-muted"></i>`}
                </div>
                <div class="preview-info flex-grow-1">
                    <div class="fw-bold">${fileName}</div>
                    <div class="text-muted small">
                        <span>${fileSize}</span>
                        <span class="mx-2">•</span>
                        <span class="badge bg-success bg-opacity-20 text-success">آپلود شده</span>
                    </div>
                    <div class="preview-actions mt-2">
                        <button type="button" class="btn btn-sm btn-outline-primary btn-change">
                            <i class="ph-pencil me-1"></i>
                            تغییر
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger btn-remove ms-1" data-file-path="${uploadResult.uploaded_files || ''}">
                            <i class="ph-trash me-1"></i>
                            حذف
                        </button>
                    </div>
                </div>
                <div class="preview-status ms-2">
                    <i class="ph-check-circle text-success fs-5"></i>
                </div>
            `;
        }

        // Setup change button (only for single files)
        const changeBtn = preview.querySelector('.btn-change');
        if (changeBtn) {
            changeBtn.addEventListener('click', () => {
                this.showUploadArea(preview.closest('.rms-image-uploader-wrapper'));
            });
        }
        
        // Setup assign button (uploaded)
        const assignBtn = preview.querySelector('.btn-assign');
        if (assignBtn) {
            assignBtn.addEventListener('click', (e) => {
                e.preventDefault(); e.stopPropagation();
                const path = assignBtn.getAttribute('data-file-path') || uploadResult.uploaded_files || '';
                if (path) this.assignToCombination(preview, path);
            });
        }
        // Setup remove button for AJAX delete
        const removeBtn = preview.querySelector('.btn-remove');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                // اطمینان از اینکه element هنوز در DOM هست
                if (preview.parentNode) {
                    this.deleteFileAjax(preview, uploadResult.uploaded_files || '', config);
                }
            });
        }
        // Setup cover button for uploaded file
        if (config.enableCover) {
            const coverBtn = preview.querySelector('.btn-cover');
            if (coverBtn) {
                coverBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const path = uploadResult.uploaded_files || '';
                    if (path) this.setCoverAjax(preview, path, config);
                });
            }
        }
        
        // Setup image click for modal (only for multiple files with images)
        if (config.multiple) {
            const imageClickable = preview.querySelector('.preview-thumbnail');
            if (imageClickable && fileUrl) {
                imageClickable.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    this.showImageModal(fileUrl, fileName, fileSize);
                });
            }
        }

        return preview;
    }

    /**
     * Set selected image as cover via AJAX
     */
    async setCoverAjax(previewElement, filePath, config) {
        try {
            const url = config.coverUrl || `/admin/${this.getControllerName()}/${config.modelId}/cover`;
            const resp = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ file_path: filePath })
            });
            const data = await resp.json();
            if (!resp.ok || !data.success) throw new Error(data.message || 'خطا در تنظیم کاور');
            // Update UI badges
            config.currentCover = filePath;
            this.markAsCover(previewElement.closest('.image-uploader'), previewElement, config);
            this.showSuccess('کاور تنظیم شد.', previewElement.closest('.image-uploader'));
        } catch (err) {
            this.showError(err.message || 'خطا در تنظیم کاور', previewElement.closest('.image-uploader'));
        }
    }

    /**
     * Mark selected preview as cover and remove badge from others
     */
    markAsCover(container, selectedPreview, config) {
        try {
            const all = container.querySelectorAll('.rms-image-preview-multiple');
            all.forEach(el => this.applyCoverBadge(el, false));
            this.applyCoverBadge(selectedPreview, true);
        } catch(_) {}
    }

    /**
     * Apply or remove cover badge on a preview card
     */
    applyCoverBadge(preview, isCover) {
        const host = preview.querySelector('.d-flex.align-items-center.p-3.border.rounded.mb-2.position-relative') || preview;
        let badge = preview.querySelector('.rms-cover-badge');
        if (isCover) {
            if (!badge) {
                badge = document.createElement('div');
                badge.className = 'position-absolute top-0 start-0 p-2 rms-cover-badge';
                badge.innerHTML = '<span class="badge bg-warning text-dark">کاور</span>';
                host.style.position = 'relative';
                host.appendChild(badge);
            }
        } else {
            if (badge) badge.remove();
        }
    }

    /**
     * Delete file via AJAX
     */
    async deleteFileAjax(previewElement, filePath, config) {
        if (!config.modelId || !config.fieldName || !filePath) {
            this.showError('اطلاعات لازم برای حذف فایل موجود نیست', previewElement.closest('.image-uploader'));
            return;
        }
        
        // بررسی وجود SweetAlert
        const hasSwal = typeof Swal !== 'undefined';
        
        // اگر SweetAlert موجود است، تایید بگیر
        if (hasSwal) {
            try {
                const result = await Swal.fire({
                    title: 'حذف فایل',
                    text: 'آیا مطمئن هستید که می‌خواهید این فایل را حذف کنید؟',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'بله، حذف کن',
                    cancelButtonText: 'انصراف',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-danger',
                        cancelButton: 'btn btn-secondary'
                    },
                    reverseButtons: true
                });
                
                if (!result.isConfirmed) {
                    return; // کاربر انصراف داده
                }
            } catch (error) {
                console.error('خطا در لود SweetAlert:', error);
                // ادامه بده بدون تایید
            }
        }

        try {
            const deleteUrl = `/admin/${this.getControllerName()}/${config.modelId}/ajax-delete/${config.fieldName}?file_path=${encodeURIComponent(filePath)}`;

            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'خطا در حذف فایل');
            }

            // Get container before removing preview
            const container = previewElement.closest('.image-uploader');
            const wrapper = previewElement.closest('.rms-image-uploader-wrapper');
            
            // Remove preview element (all logic for showing/hiding areas handled in removePreview method)
            this.removePreview(previewElement);

            // Show success message
            this.showSuccess(result.message, container);

            // File deleted successfully - upload area is now available again

        } catch (error) {
            // Error already handled and shown via SweetAlert
            if (console && console.error) {
                console.error('❌ AJAX delete failed:', error);
            }
            this.showError(error.message, previewElement.closest('.image-uploader'));
        }
    }

    /**
     * Show image in modal
     */
    showImageModal(imageUrl, imageName, imageSize) {
        // بررسی وجود modal یا ایجاد آن
        let modal = document.getElementById('rms-image-modal');
        if (!modal) {
            modal = this.createImageModal();
        }
        
        // پر کردن محتوای modal
        const modalImage = modal.querySelector('.modal-image');
        const modalTitle = modal.querySelector('.modal-title');
        const modalSize = modal.querySelector('.modal-size');
        const loadingSpinner = modal.querySelector('.loading-spinner');
        
        // نمایش loading
        modalImage.style.display = 'none';
        loadingSpinner.style.display = 'flex';
        modalTitle.textContent = imageName || 'تصویر';
        modalSize.textContent = imageSize || '';
        
        // نمایش modal
        const bsModal = new bootstrap.Modal(modal, {
            backdrop: true,
            keyboard: true
        });
        bsModal.show();
        
        // بارگذاری تصویر با سایز کامل
        const fullImage = new Image();
        fullImage.onload = () => {
            modalImage.src = fullImage.src;
            modalImage.style.display = 'block';
            loadingSpinner.style.display = 'none';
        };
        fullImage.onerror = () => {
            loadingSpinner.innerHTML = '<div class="text-danger"><i class="ph-warning-circle me-2"></i>خطا در بارگذاری تصویر</div>';
        };
        fullImage.src = imageUrl;
    }
    
    /**
     * Create image modal HTML
     */
    createImageModal() {
        const modalHTML = `
            <div class="modal fade" id="rms-image-modal" tabindex="-1">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">تصویر</h5>
                            <div class="modal-size text-muted small ms-auto me-3"></div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-0 position-relative">
                            <div class="loading-spinner d-flex align-items-center justify-content-center" style="height: 400px;">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">در حال بارگذاری...</span>
                                </div>
                                <span class="ms-2">در حال بارگذاری...</span>
                            </div>
                            <img class="modal-image img-fluid" style="display: none; max-height: 80vh;" alt="تصویر کامل">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">بستن</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // اضافه کردن به body
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        return document.getElementById('rms-image-modal');
    }

    /**
     * Show success message
     */
    showSuccess(message, container) {
        // Remove existing success alerts
        container.querySelectorAll('.image-upload-success').forEach(alert => alert.remove());

        const alert = document.createElement('div');
        alert.className = 'alert alert-success alert-dismissible fade show image-upload-success mb-3';
        alert.innerHTML = `
            <i class="ph-check-circle me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;

        // Insert at the beginning of container (above upload area)
        container.insertBefore(alert, container.firstChild);

        // Auto remove after 4 seconds (a bit longer for better UX)
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 4000);
    }

    /**
     * Format file size
     */
    formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    /**
     * Add cache busting parameter to URL
     */
    addCacheBusting(url) {
        if (!url) return url;
        
        const cacheBuster = Date.now() + Math.floor(Math.random() * 10000);
        return url + (url.includes('?') ? '&' : '?') + `v=${cacheBuster}`;
    }

    /**
     * Decode HTML entities
     */
    decodeHtmlEntities(str) {
        if (!str) return str;
        
        const textarea = document.createElement('textarea');
        textarea.innerHTML = str;
        return textarea.value;
    }

    /**
     * Get uploaded files data
     */
    getFiles() {
        return this.files;
    }

    /**
     * Clear all files
     */
    clearFiles() {
        this.files = [];
        document.querySelectorAll('.rms-preview-area').forEach(area => {
            area.innerHTML = '';
            area.style.display = 'none';
        });
    }

    /**
     * Static method to initialize uploader on specific element
     */
    static initialize(selector, options = {}) {
        const elements = document.querySelectorAll(selector);
        elements.forEach(element => {
            const uploader = new RMSImageUploader(options);
            uploader.initializeUploader(element);
        });
    }

    /**
     * Debug method
     */
    static debug() {
        const uploaders = document.querySelectorAll('.image-uploader');
        // Show debug info via SweetAlert if available
        if (typeof window.showInfo === 'function') {
            window.showInfo(
                `🖼️ آپلودرهای تصویر یافت شده: ${uploaders.length}`,
                uploaders.length > 0 ? 'جزئیات در کنسول مرورگر موجود است' : 'هیچ آپلودری یافت نشد',
                { showButton: true }
            );
        }

        // Still log to console for debugging
        if (console && console.log) {
            console.log('🖼️ Image Uploaders found:', uploaders.length);
            uploaders.forEach((uploader, index) => {
                console.log(`Uploader ${index + 1}:`, {
                    element: uploader,
                    initialized: uploader.classList.contains('initialized'),
                    input: uploader.querySelector('input[type="file"]')
                });
            });
        }
    }
}

// Global instance
window.RMSImageUploader = RMSImageUploader;

// Auto-initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.rmsImageUploader === 'undefined') {
            window.rmsImageUploader = new RMSImageUploader();
        }
    });
} else {
    if (typeof window.rmsImageUploader === 'undefined') {
        window.rmsImageUploader = new RMSImageUploader();
    }
}

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RMSImageUploader;
}
