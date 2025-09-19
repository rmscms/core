/**
 * RMS Debug Panel - رابط کاربری حرفه‌ای برای سیستم debug
 * 
 * قابلیت‌ها:
 * - پنل debug با تب‌های مختلف
 * - نمایش real-time اطلاعات
 * - Export debug data
 * - فیلتر و جستجو در logs
 * - تحلیل performance
 * 
 * @author RMS Core Team
 * @version 2.0.0
 */

class RMSDebugPanel {
    constructor(options = {}) {
        this.options = {
            autoRefresh: false,
            refreshInterval: 5000,
            enableFloatingPanel: true,
            enableConsoleOutput: true,
            maxLogEntries: 1000,
            ...options
        };

        this.debugData = null;
        this.isVisible = false;
        this.refreshTimer = null;
        this.logFilters = {
            level: 'all',
            category: 'all',
            search: ''
        };

        this.init();
    }

    /**
     * مقداردهی اولیه
     */
    init() {
        this.createPanelHTML();
        this.bindEvents();
        this.loadDebugData();
        
        if (this.options.autoRefresh) {
            this.startAutoRefresh();
        }

        // Global access
        window.rmsDebugPanel = this;
    }

    /**
     * ایجاد HTML پنل debug
     */
    createPanelHTML() {
        const panelHTML = `
            <div id="rms-debug-panel" class="rms-debug-panel d-none debug-fade-in">
                <div class="debug-panel-container shadow-lg">
                    <div class="debug-panel-header bg-primary text-white">
                        <div class="debug-header-left">
                            <h5 class="mb-0 fw-semibold">
                                <i class="ph-bug ph-lg me-2"></i>
                                RMS Debug Panel
                                <span class="debug-status-indicator ms-2" title="Debug Status">
                                    <i class="ph-circle-fill text-success-emphasis"></i>
                                </span>
                            </h5>
                        </div>
                        <div class="debug-header-right d-flex align-items-center gap-2">
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-outline-light btn-sm" id="debug-refresh-btn" title="تازه‌سازی">
                                    <i class="ph-arrow-clockwise"></i>
                                    <span class="d-none d-md-inline ms-1">Refresh</span>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm" id="debug-export-btn" title="خروجی">
                                    <i class="ph-download"></i>
                                    <span class="d-none d-md-inline ms-1">Export</span>
                                </button>
                                <button type="button" class="btn btn-outline-light btn-sm" id="debug-clear-btn" title="پاک کردن">
                                    <i class="ph-trash"></i>
                                    <span class="d-none d-md-inline ms-1">Clear</span>
                                </button>
                            </div>
                            <button type="button" class="btn-close btn-close-white" id="debug-toggle-btn" aria-label="بستن">
                            </button>
                        </div>
                    </div>

                    <div class="debug-panel-body">
                        <!-- Navigation Tabs - استایل Limitless -->
                        <ul class="nav nav-tabs nav-tabs-highlight debug-nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-medium" id="debug-overview-tab" data-bs-toggle="tab" data-bs-target="#debug-overview" type="button" role="tab" aria-controls="debug-overview" aria-selected="true">
                                    <i class="ph-chart-bar me-2"></i>
                                    <span class="d-none d-sm-inline">نمای کلی</span>
                                    <span class="d-inline d-sm-none">کلی</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-medium" id="debug-form-tab" data-bs-toggle="tab" data-bs-target="#debug-form" type="button" role="tab" aria-controls="debug-form" aria-selected="false">
                                    <i class="ph-textbox me-2"></i>
                                    <span class="d-none d-sm-inline">تحلیل فرم</span>
                                    <span class="d-inline d-sm-none">فرم</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-medium" id="debug-fields-tab" data-bs-toggle="tab" data-bs-target="#debug-fields" type="button" role="tab" aria-controls="debug-fields" aria-selected="false">
                                    <i class="ph-list-checks me-2"></i>
                                    <span class="d-none d-sm-inline">فیلدها</span>
                                    <span class="d-inline d-sm-none">Fields</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-medium" id="debug-performance-tab" data-bs-toggle="tab" data-bs-target="#debug-performance" type="button" role="tab" aria-controls="debug-performance" aria-selected="false">
                                    <i class="ph-speedometer me-2"></i>
                                    <span class="d-none d-sm-inline">عملکرد</span>
                                    <span class="d-inline d-sm-none">Performance</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-medium" id="debug-queries-tab" data-bs-toggle="tab" data-bs-target="#debug-database" type="button" role="tab" aria-controls="debug-database" aria-selected="false">
                                    <i class="ph-database me-2"></i>
                                    <span class="d-none d-sm-inline">پایگاه داده</span>
                                    <span class="d-inline d-sm-none">DB</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-medium" id="debug-memory-tab" data-bs-toggle="tab" data-bs-target="#debug-memory" type="button" role="tab" aria-controls="debug-memory" aria-selected="false">
                                    <i class="ph-cpu me-2"></i>
                                    <span class="d-none d-sm-inline">حافظه</span>
                                    <span class="d-inline d-sm-none">Memory</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-medium" id="debug-logs-tab" data-bs-toggle="tab" data-bs-target="#debug-logs" type="button" role="tab" aria-controls="debug-logs" aria-selected="false">
                                    <i class="ph-file-text me-2"></i>
                                    <span class="d-none d-sm-inline">گزارش‌ها</span>
                                    <span class="d-inline d-sm-none">Logs</span>
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content debug-tab-content" id="debugTabContent">
                            <!-- Overview Tab -->
                            <div class="tab-pane fade show active debug-slide-up" id="debug-overview" role="tabpanel" aria-labelledby="debug-overview-tab">
                                <div class="debug-overview-content p-3">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-light shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title text-primary fw-semibold mb-3">
                                                        <i class="ph-info me-2"></i>اطلاعات جلسه
                                                    </h6>
                                                    <div id="debug-session-info" class="debug-info-content"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="card border-0 bg-light shadow-sm">
                                                <div class="card-body">
                                                    <h6 class="card-title text-success fw-semibold mb-3">
                                                        <i class="ph-speedometer me-2"></i>خلاصه عملکرد
                                                    </h6>
                                                    <div id="debug-performance-summary" class="debug-info-content"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Analysis Tab -->
                            <div class="tab-pane fade" id="debug-form" role="tabpanel" aria-labelledby="debug-form-tab">
                                <div class="p-3">
                                    <div class="alert alert-info border-0" role="alert">
                                        <h6 class="alert-heading mb-2">
                                            <i class="ph-info me-2"></i>تحلیل فرم
                                        </h6>
                                        <p class="mb-0 small">اطلاعات کامل فرم، validation rules و تنظیمات.</p>
                                    </div>
                                    <div id="debug-form-analysis" class="debug-tab-content-inner"></div>
                                </div>
                            </div>

                            <!-- Fields Tab -->
                            <div class="tab-pane fade" id="debug-fields" role="tabpanel" aria-labelledby="debug-fields-tab">
                                <div class="p-3">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h6 class="mb-0 fw-semibold">
                                            <i class="ph-list-checks me-2"></i>تحلیل فیلدها
                                        </h6>
                                        <div class="btn-group btn-group-sm" role="group">
                                            <button type="button" class="btn btn-outline-warning" onclick="window.rmsDebugPanel?.filterFieldsWithIssues()">
                                                <i class="ph-warning me-1"></i>مشکل‌دار
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="window.rmsDebugPanel?.showAllFields()">
                                                <i class="ph-list me-1"></i>همه
                                            </button>
                                        </div>
                                    </div>
                                    <div id="debug-field-analysis" class="debug-tab-content-inner"></div>
                                </div>
                            </div>

                            <!-- Performance Tab -->
                            <div class="tab-pane fade" id="debug-performance" role="tabpanel" aria-labelledby="debug-performance-tab">
                                <div class="p-3">
                                    <h6 class="mb-3 fw-semibold">
                                        <i class="ph-speedometer me-2"></i>گزارش جزئیات عملکرد
                                    </h6>
                                    <div id="debug-performance-details" class="debug-tab-content-inner"></div>
                                </div>
                            </div>

                            <!-- Database Tab -->
                            <div class="tab-pane fade" id="debug-database" role="tabpanel" aria-labelledby="debug-queries-tab">
                                <div class="p-3">
                                    <h6 class="mb-3 fw-semibold">
                                        <i class="ph-database me-2"></i>تحلیل پایگاه داده
                                    </h6>
                                    <div id="debug-database-analysis" class="debug-tab-content-inner"></div>
                                </div>
                            </div>

                            <!-- Memory Tab -->
                            <div class="tab-pane fade" id="debug-memory" role="tabpanel" aria-labelledby="debug-memory-tab">
                                <div class="p-3">
                                    <h6 class="mb-3 fw-semibold">
                                        <i class="ph-cpu me-2"></i>گزارش استفاده از حافظه
                                    </h6>
                                    <div id="debug-memory-tracking" class="debug-tab-content-inner"></div>
                                </div>
                            </div>

                            <!-- Logs Tab -->
                            <div class="tab-pane fade" id="debug-logs" role="tabpanel" aria-labelledby="debug-logs-tab">
                                <div class="p-3">
                                    <div class="debug-logs-controls mb-3">
                                        <div class="row g-2">
                                            <div class="col-md-3">
                                                <label for="debug-log-level-filter" class="form-label small text-muted">سطح:</label>
                                                <select class="form-select form-select-sm" id="debug-log-level-filter">
                                                    <option value="all">همه سطح‌ها</option>
                                                    <option value="info">اطلاعات</option>
                                                    <option value="warning">هشدار</option>
                                                    <option value="error">خطا</option>
                                                    <option value="critical">بحرانی</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label for="debug-log-category-filter" class="form-label small text-muted">دسته:</label>
                                                <select class="form-select form-select-sm" id="debug-log-category-filter">
                                                    <option value="all">همه دسته‌ها</option>
                                                    <option value="form">فرم</option>
                                                    <option value="field">فیلد</option>
                                                    <option value="validation">اعتبارسنجی</option>
                                                    <option value="performance">عملکرد</option>
                                                    <option value="memory">حافظه</option>
                                                    <option value="database">پایگاه داده</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="debug-log-search" class="form-label small text-muted">جستجو:</label>
                                                <input type="text" class="form-control form-control-sm" id="debug-log-search" 
                                                       placeholder="جستجو در گزارش‌ها...">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="form-label small text-muted">&nbsp;</label>
                                                <button type="button" class="btn btn-outline-danger btn-sm w-100" onclick="window.rmsDebugPanel?.clearLogs()">
                                                    <i class="ph-trash me-1"></i>پاک کردن
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="debug-logs-content" class="debug-tab-content-inner border rounded p-3 bg-light" 
                                         style="max-height: 400px; overflow-y: auto; font-family: 'Courier New', monospace; font-size: 0.875rem;">
                                        <div class="text-muted text-center py-4">
                                            <i class="ph-file-text display-6 d-block mb-2"></i>
                                            <p class="mb-0">در حال بارگذاری گزارش‌ها...</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        // Add to body
        document.body.insertAdjacentHTML('beforeend', panelHTML);
    }

    /**
     * اتصال event handlers
     */
    bindEvents() {
        // Panel toggle
        document.getElementById('debug-toggle-btn')?.addEventListener('click', () => {
            this.togglePanel();
        });

        // Refresh button
        document.getElementById('debug-refresh-btn')?.addEventListener('click', () => {
            this.loadDebugData();
        });

        // Export button
        document.getElementById('debug-export-btn')?.addEventListener('click', () => {
            this.exportDebugData();
        });

        // Clear button
        document.getElementById('debug-clear-btn')?.addEventListener('click', () => {
            this.clearDebugData();
        });

        // Log filters
        document.getElementById('debug-log-level-filter')?.addEventListener('change', (e) => {
            this.logFilters.level = e.target.value;
            this.filterLogs();
        });

        document.getElementById('debug-log-category-filter')?.addEventListener('change', (e) => {
            this.logFilters.category = e.target.value;
            this.filterLogs();
        });

        document.getElementById('debug-log-search')?.addEventListener('input', (e) => {
            this.logFilters.search = e.target.value;
            this.filterLogs();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Ctrl+Shift+D for toggle debug panel
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                this.togglePanel();
            }
        });

        // Auto-refresh checkbox (if added)
        document.addEventListener('change', (e) => {
            if (e.target.id === 'debug-auto-refresh') {
                this.options.autoRefresh = e.target.checked;
                if (e.target.checked) {
                    this.startAutoRefresh();
                } else {
                    this.stopAutoRefresh();
                }
            }
        });
    }

    /**
     * Toggle نمایش پنل
     */
    togglePanel() {
        const panel = document.getElementById('rms-debug-panel');
        if (!panel) return;

        this.isVisible = !this.isVisible;
        
        if (this.isVisible) {
            panel.classList.remove('d-none');
            this.loadDebugData();
        } else {
            panel.classList.add('d-none');
        }

        // Update toggle button icon
        const toggleBtn = document.getElementById('debug-toggle-btn');
        const icon = toggleBtn?.querySelector('i');
        if (icon) {
            icon.className = this.isVisible ? 'ph-eye-slash' : 'ph-eye';
        }
    }

    /**
     * بارگذاری debug data
     */
    async loadDebugData() {
        try {
            const response = await fetch('/admin/debug/panel', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                const data = await response.json();
                this.debugData = data.debug_data;
                this.updateUI();
            } else {
                console.warn('Failed to load debug data:', response.statusText);
            }
        } catch (error) {
            console.error('Debug panel error:', error);
            this.showError('Failed to load debug data');
        }
    }

    /**
     * به‌روزرسانی UI
     */
    updateUI() {
        if (!this.debugData) return;

        this.updateOverviewTab();
        this.updateFormAnalysisTab();
        this.updateFieldAnalysisTab();
        this.updatePerformanceTab();
        this.updateDatabaseTab();
        this.updateMemoryTab();
        this.updateLogsTab();
        
        // Update status indicator
        const statusIndicator = document.querySelector('.debug-status-indicator i');
        if (statusIndicator) {
            statusIndicator.className = this.debugData.debug_disabled ? 
                'ph-circle-fill text-danger' : 'ph-circle-fill text-success';
        }
    }

    /**
     * به‌روزرسانی تب Overview
     */
    updateOverviewTab() {
        const sessionInfo = document.getElementById('debug-session-info');
        const performanceSummary = document.getElementById('debug-performance-summary');

        if (sessionInfo && this.debugData.session_info) {
            sessionInfo.innerHTML = this.formatSessionInfo(this.debugData.session_info);
        }

        if (performanceSummary && this.debugData.performance_summary) {
            performanceSummary.innerHTML = this.formatPerformanceSummary(this.debugData.performance_summary);
        }
    }

    /**
     * به‌روزرسانی تب Form Analysis
     */
    updateFormAnalysisTab() {
        const container = document.getElementById('debug-form-analysis');
        if (!container) return;

        const formAnalysis = this.debugData.form_analysis;
        if (!formAnalysis) {
            container.innerHTML = '<p class="text-muted">No form analysis data available.</p>';
            return;
        }

        container.innerHTML = this.formatFormAnalysis(formAnalysis);
    }

    /**
     * به‌روزرسانی تب Field Analysis
     */
    updateFieldAnalysisTab() {
        const container = document.getElementById('debug-field-analysis');
        if (!container) return;

        const fieldAnalysis = this.debugData.field_analysis;
        if (!fieldAnalysis || Object.keys(fieldAnalysis).length === 0) {
            container.innerHTML = '<p class="text-muted">No field analysis data available.</p>';
            return;
        }

        container.innerHTML = this.formatFieldAnalysis(fieldAnalysis);
    }

    /**
     * به‌روزرسانی تب Performance
     */
    updatePerformanceTab() {
        const container = document.getElementById('debug-performance-details');
        if (!container) return;

        const performanceDetails = this.debugData.performance_details;
        if (!performanceDetails || performanceDetails.length === 0) {
            container.innerHTML = '<p class="text-muted">No performance data available.</p>';
            return;
        }

        container.innerHTML = this.formatPerformanceDetails(performanceDetails);
    }

    /**
     * به‌روزرسانی تب Database
     */
    updateDatabaseTab() {
        const container = document.getElementById('debug-database-analysis');
        if (!container) return;

        const databaseAnalysis = this.debugData.database_analysis;
        if (!databaseAnalysis) {
            container.innerHTML = '<p class="text-muted">No database analysis available.</p>';
            return;
        }

        container.innerHTML = this.formatDatabaseAnalysis(databaseAnalysis);
    }

    /**
     * به‌روزرسانی تب Memory
     */
    updateMemoryTab() {
        const container = document.getElementById('debug-memory-tracking');
        if (!container) return;

        const memoryTracking = this.debugData.memory_tracking;
        if (!memoryTracking || memoryTracking.length === 0) {
            container.innerHTML = '<p class="text-muted">No memory tracking data available.</p>';
            return;
        }

        container.innerHTML = this.formatMemoryTracking(memoryTracking);
    }

    /**
     * به‌روزرسانی تب Logs
     */
    updateLogsTab() {
        const container = document.getElementById('debug-logs-content');
        if (!container) return;

        // Load logs from cache/storage (implementation needed)
        container.innerHTML = '<p class="text-muted">Log integration pending...</p>';
    }

    /**
     * فرمت کردن Session Info
     */
    formatSessionInfo(sessionInfo) {
        return `
            <div class="debug-info-list">
                <div class="debug-info-item">
                    <strong>Session ID:</strong>
                    <code>${sessionInfo.session_id || 'N/A'}</code>
                </div>
                <div class="debug-info-item">
                    <strong>Request URI:</strong>
                    <small>${sessionInfo.request_uri || 'N/A'}</small>
                </div>
                <div class="debug-info-item">
                    <strong>Method:</strong>
                    <span class="badge bg-primary">${sessionInfo.request_method || 'GET'}</span>
                </div>
                <div class="debug-info-item">
                    <strong>Laravel Version:</strong>
                    ${sessionInfo.laravel_version || 'N/A'}
                </div>
                <div class="debug-info-item">
                    <strong>PHP Version:</strong>
                    ${sessionInfo.php_version || 'N/A'}
                </div>
                <div class="debug-info-item">
                    <strong>RMS Core Version:</strong>
                    ${sessionInfo.rms_core_version || 'N/A'}
                </div>
            </div>
        `;
    }

    /**
     * فرمت کردن Performance Summary
     */
    formatPerformanceSummary(summary) {
        return `
            <div class="debug-info-list">
                <div class="debug-info-item">
                    <strong>Total Execution Time:</strong>
                    <span class="text-${this.getPerformanceColor(summary.total_execution_time)}">
                        ${summary.total_execution_time || 'N/A'}
                    </span>
                </div>
                <div class="debug-info-item">
                    <strong>Memory Used:</strong>
                    <span class="text-info">${summary.total_memory_used || 'N/A'}</span>
                </div>
                <div class="debug-info-item">
                    <strong>Peak Memory:</strong>
                    <span class="text-warning">${summary.peak_memory_usage || 'N/A'}</span>
                </div>
                <div class="debug-info-item">
                    <strong>Operations Measured:</strong>
                    ${summary.operations_measured || 0}
                </div>
            </div>
        `;
    }

    /**
     * فرمت کردن Form Analysis
     */
    formatFormAnalysis(analysis) {
        let html = `
            <div class="debug-form-info">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6>Form Info</h6>
                                <div class="debug-info-list">
                                    <div class="debug-info-item">
                                        <strong>Mode:</strong>
                                        <span class="badge ${analysis.form_mode === 'edit' ? 'bg-warning' : 'bg-success'}">
                                            ${analysis.form_mode || 'N/A'}
                                        </span>
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Form ID:</strong>
                                        ${analysis.form_id || 'NULL (Create Mode)'}
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Action:</strong>
                                        <code>${analysis.form_action || 'N/A'}</code>
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Method:</strong>
                                        <span class="badge bg-primary">${analysis.form_method || 'POST'}</span>
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Total Fields:</strong>
                                        ${analysis.total_fields || 0}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6>Analysis Results</h6>
                                <div class="debug-info-list">
                                    <div class="debug-info-item">
                                        <strong>Errors:</strong>
                                        <span class="badge bg-danger">${analysis.errors?.length || 0}</span>
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Warnings:</strong>
                                        <span class="badge bg-warning">${analysis.warnings?.length || 0}</span>
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Analysis Time:</strong>
                                        ${analysis.performance?.analysis_time || 'N/A'}
                                    </div>
                                    <div class="debug-info-item">
                                        <strong>Memory Used:</strong>
                                        ${analysis.performance?.memory_used || 'N/A'}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        `;

        // Show errors and warnings
        if (analysis.errors?.length > 0) {
            html += `
                <div class="alert alert-danger">
                    <h6 class="mb-2">Errors:</h6>
                    <ul class="mb-0">
                        ${analysis.errors.map(error => `<li>${error}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        if (analysis.warnings?.length > 0) {
            html += `
                <div class="alert alert-warning">
                    <h6 class="mb-2">Warnings:</h6>
                    <ul class="mb-0">
                        ${analysis.warnings.map(warning => `<li>${warning}</li>`).join('')}
                    </ul>
                </div>
            `;
        }

        html += '</div>';
        return html;
    }

    /**
     * فرمت کردن Field Analysis
     */
    formatFieldAnalysis(fieldAnalysis) {
        let html = '<div class="debug-fields-table">';
        
        Object.keys(fieldAnalysis).forEach(fieldKey => {
            const field = fieldAnalysis[fieldKey];
            const hasIssues = field.potential_issues && field.potential_issues.length > 0;
            
            html += `
                <div class="card mb-3 ${hasIssues ? 'border-warning' : 'border-0'}">
                    <div class="card-header py-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${field.key}</strong>
                                ${field.title ? `<span class="text-muted ms-2">(${field.title})</span>` : ''}
                                <span class="badge bg-secondary ms-2">${field.type_name || 'UNKNOWN'}</span>
                            </div>
                            ${field.required ? '<span class="badge bg-danger">Required</span>' : '<span class="badge bg-secondary">Optional</span>'}
                        </div>
                    </div>
                    <div class="card-body py-2">
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Database Column:</small>
                                <code>${field.database_column || field.key}</code>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Validation Rules:</small>
                                <code>${Array.isArray(field.validation_rules) ? field.validation_rules.join('|') : (field.validation_rules || 'None')}</code>
                            </div>
                        </div>
                        ${field.potential_issues && field.potential_issues.length > 0 ? `
                            <div class="mt-2">
                                <small class="text-warning d-block mb-1">⚠️ Issues:</small>
                                <ul class="list-unstyled mb-0">
                                    ${field.potential_issues.map(issue => `<li><small class="text-warning">• ${issue}</small></li>`).join('')}
                                </ul>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    /**
     * فرمت کردن Performance Details
     */
    formatPerformanceDetails(details) {
        let html = '<div class="debug-performance-table table-responsive">';
        html += `
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Operation</th>
                        <th>Execution Time</th>
                        <th>Memory Used</th>
                        <th>Queries</th>
                        <th>Timestamp</th>
                    </tr>
                </thead>
                <tbody>
        `;
        
        details.forEach(detail => {
            html += `
                <tr>
                    <td>
                        <strong>${detail.operation}</strong>
                        ${detail.context?.action ? `<br><small class="text-muted">${detail.context.action}</small>` : ''}
                    </td>
                    <td>
                        <span class="text-${this.getPerformanceColor(detail.execution_time + 'ms')}">
                            ${detail.execution_time}ms
                        </span>
                    </td>
                    <td>${detail.memory_used}</td>
                    <td>${detail.queries_executed || 0}</td>
                    <td><small>${new Date(detail.timestamp).toLocaleTimeString()}</small></td>
                </tr>
            `;
        });
        
        html += '</tbody></table></div>';
        return html;
    }

    /**
     * فرمت کردن Database Analysis
     */
    formatDatabaseAnalysis(analysis) {
        let html = `
            <div class="debug-database-summary mb-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="card border-0 bg-light text-center">
                            <div class="card-body">
                                <h4 class="text-primary mb-0">${analysis.total_queries || 0}</h4>
                                <small>Total Queries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light text-center">
                            <div class="card-body">
                                <h4 class="text-warning mb-0">${analysis.total_time || 0}ms</h4>
                                <small>Total Time</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light text-center">
                            <div class="card-body">
                                <h4 class="text-danger mb-0">${analysis.slow_queries?.length || 0}</h4>
                                <small>Slow Queries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card border-0 bg-light text-center">
                            <div class="card-body">
                                <h4 class="text-info mb-0">${analysis.duplicate_queries?.length || 0}</h4>
                                <small>Duplicates</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (analysis.slow_queries && analysis.slow_queries.length > 0) {
            html += `
                <div class="alert alert-warning">
                    <h6>Slow Queries:</h6>
                    ${analysis.slow_queries.map(query => `
                        <div class="mb-2">
                            <code>${query.sql}</code>
                            <span class="badge bg-warning ms-2">${query.time}ms</span>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        return html;
    }

    /**
     * فرمت کردن Memory Tracking
     */
    formatMemoryTracking(tracking) {
        let html = '<div class="debug-memory-timeline">';
        
        tracking.forEach((checkpoint, index) => {
            html += `
                <div class="timeline-item mb-3">
                    <div class="d-flex justify-content-between">
                        <strong>${checkpoint.checkpoint}</strong>
                        <span class="text-muted">${new Date(checkpoint.timestamp).toLocaleTimeString()}</span>
                    </div>
                    <div class="mt-2">
                        <div class="row g-2 text-sm">
                            <div class="col-md-4">
                                <span class="text-muted">Current:</span>
                                ${checkpoint.current_usage_formatted}
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted">Peak:</span>
                                ${checkpoint.peak_usage_formatted}
                            </div>
                            <div class="col-md-4">
                                <span class="text-muted">Since Start:</span>
                                ${checkpoint.since_start_formatted}
                            </div>
                        </div>
                    </div>
                    ${index < tracking.length - 1 ? '<hr>' : ''}
                </div>
            `;
        });
        
        html += '</div>';
        return html;
    }

    /**
     * تعیین رنگ performance
     */
    getPerformanceColor(timeString) {
        const time = parseFloat(timeString);
        if (time > 1000) return 'danger';
        if (time > 500) return 'warning';
        if (time > 100) return 'info';
        return 'success';
    }

    /**
     * فیلتر logs
     */
    filterLogs() {
        const logsContainer = document.getElementById('debug-logs-content');
        if (!logsContainer) return;

        const allLogs = logsContainer.querySelectorAll('.log-entry');
        let visibleCount = 0;

        allLogs.forEach(logElement => {
            const level = logElement.dataset.level || 'info';
            const category = logElement.dataset.category || 'general';
            const text = logElement.textContent.toLowerCase();
            const searchTerm = this.logFilters.search.toLowerCase();

            const levelMatch = this.logFilters.level === 'all' || level === this.logFilters.level;
            const categoryMatch = this.logFilters.category === 'all' || category === this.logFilters.category;
            const searchMatch = !searchTerm || text.includes(searchTerm);

            if (levelMatch && categoryMatch && searchMatch) {
                logElement.style.display = 'block';
                visibleCount++;
            } else {
                logElement.style.display = 'none';
            }
        });

        // نمایش تعداد نتایج
        const resultInfo = logsContainer.querySelector('.logs-result-info');
        if (resultInfo) {
            resultInfo.textContent = `نمایش ${visibleCount} از ${allLogs.length} گزارش`;
        }
    }

    /**
     * فیلتر فیلدها - فقط مشکل‌دار
     */
    filterFieldsWithIssues() {
        const fieldsContainer = document.getElementById('debug-field-analysis');
        if (!fieldsContainer) return;

        const allFieldCards = fieldsContainer.querySelectorAll('.card');
        allFieldCards.forEach(card => {
            const hasIssues = card.classList.contains('border-warning');
            card.style.display = hasIssues ? 'block' : 'none';
        });

        this.showToast('نمایش فقط فیلدهای مشکل‌دار', 'info');
    }

    /**
     * نمایش همه فیلدها
     */
    showAllFields() {
        const fieldsContainer = document.getElementById('debug-field-analysis');
        if (!fieldsContainer) return;

        const allFieldCards = fieldsContainer.querySelectorAll('.card');
        allFieldCards.forEach(card => {
            card.style.display = 'block';
        });

        this.showToast('نمایش همه فیلدها', 'info');
    }

    /**
     * پاک کردن لاگ‌ها از UI
     */
    clearLogs() {
        const logsContainer = document.getElementById('debug-logs-content');
        if (!logsContainer) return;

        logsContainer.innerHTML = `
            <div class="text-muted text-center py-4">
                <i class="ph-file-text display-6 d-block mb-2"></i>
                <p class="mb-0">گزارش‌ها پاک شدند</p>
            </div>
        `;

        this.showToast('گزارش‌ها از رابط کاربری پاک شدند', 'success');
    }

    /**
     * Export debug data
     */
    async exportDebugData(format = 'json') {
        try {
            const response = await fetch(`/admin/debug/export?format=${format}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `rms_debug_${new Date().toISOString().slice(0, 19).replace(/:/g, '-')}.${format}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
                
                this.showSuccess('Debug data exported successfully');
            }
        } catch (error) {
            console.error('Export error:', error);
            this.showError('Failed to export debug data');
        }
    }

    /**
     * پاک کردن debug data
     */
    async clearDebugData() {
        if (!confirm('Are you sure you want to clear all debug data?')) {
            return;
        }

        try {
            const response = await fetch('/admin/debug/clear', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                }
            });

            if (response.ok) {
                this.debugData = null;
                this.updateUI();
                this.showSuccess('Debug data cleared successfully');
            }
        } catch (error) {
            console.error('Clear error:', error);
            this.showError('Failed to clear debug data');
        }
    }

    /**
     * شروع auto refresh
     */
    startAutoRefresh() {
        if (this.refreshTimer) {
            this.stopAutoRefresh();
        }
        
        this.refreshTimer = setInterval(() => {
            if (this.isVisible) {
                this.loadDebugData();
            }
        }, this.options.refreshInterval);
    }

    /**
     * توقف auto refresh
     */
    stopAutoRefresh() {
        if (this.refreshTimer) {
            clearInterval(this.refreshTimer);
            this.refreshTimer = null;
        }
    }

    /**
     * نمایش پیام موفقیت
     */
    showSuccess(message) {
        this.showToast(message, 'success');
    }

    /**
     * نمایش پیام خطا
     */
    showError(message) {
        this.showToast(message, 'error');
    }

    /**
     * نمایش toast notification
     */
    showToast(message, type = 'info') {
        // Use existing toast system or create simple notification
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    /**
     * نابودی instance
     */
    destroy() {
        this.stopAutoRefresh();
        const panel = document.getElementById('rms-debug-panel');
        if (panel) {
            panel.remove();
        }
        delete window.rmsDebugPanel;
    }
}

// Auto-initialize if debug mode is enabled
document.addEventListener('DOMContentLoaded', () => {
    // Check if debug mode is enabled
    const debugEnabled = document.querySelector('meta[name="rms-debug-enabled"]')?.getAttribute('content') === 'true';
    const hasDebugParam = new URLSearchParams(window.location.search).has('debug');
    const isAdminPage = window.location.pathname.includes('/admin/');
    
    if (debugEnabled || hasDebugParam || isAdminPage) {
        console.log('🔧 Initializing RMS Debug Panel');
        window.rmsDebugPanel = new RMSDebugPanel({
            enableFloatingPanel: true,
            autoRefresh: false
        });
        
        // Auto-show panel if debug parameter is present
        if (hasDebugParam) {
            setTimeout(() => {
                window.rmsDebugPanel?.togglePanel();
            }, 500);
        }
    }
    
    // Manual trigger for testing
    window.showDebugPanel = function() {
        if (!window.rmsDebugPanel) {
            window.rmsDebugPanel = new RMSDebugPanel();
        }
        window.rmsDebugPanel.togglePanel();
    };
});

// Export for manual initialization
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RMSDebugPanel;
}
     * نمایش toast notification
     */
    showToast(message, type = 'info') {
        // Use existing toast system or create simple notification
        if (window.showToast) {
            window.showToast(message, type);
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    /**
     * نابودی instance
     */
    destroy() {
        this.stopAutoRefresh();
        const panel = document.getElementById('rms-debug-panel');
        if (panel) {
            panel.remove();
        }
        delete window.rmsDebugPanel;
    }
}

// Auto-initialize if debug mode is enabled
document.addEventListener('DOMContentLoaded', () => {
    // Check if debug mode is enabled
    const debugEnabled = document.querySelector('meta[name="rms-debug-enabled"]')?.getAttribute('content') === 'true';
    const hasDebugParam = new URLSearchParams(window.location.search).has('debug');
    const isAdminPage = window.location.pathname.includes('/admin/');
    
    if (debugEnabled || hasDebugParam || isAdminPage) {
        console.log('🔧 Initializing RMS Debug Panel');
        window.rmsDebugPanel = new RMSDebugPanel({
            enableFloatingPanel: true,
            autoRefresh: false
        });
        
        // Auto-show panel if debug parameter is present
        if (hasDebugParam) {
            setTimeout(() => {
                window.rmsDebugPanel?.togglePanel();
            }, 500);
        }
    }
    
    // Manual trigger for testing
    window.showDebugPanel = function() {
        if (!window.rmsDebugPanel) {
            window.rmsDebugPanel = new RMSDebugPanel();
        }
        window.rmsDebugPanel.togglePanel();
    };
});

// Export for manual initialization
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RMSDebugPanel;
}