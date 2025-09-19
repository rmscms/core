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
            <div id="rms-debug-panel" class="rms-debug-panel d-none">
                <div class="debug-panel-container">
                    <div class="debug-panel-header">
                        <div class="debug-header-left">
                            <h5 class="mb-0">
                                <i class="ph-bug ph-lg me-2"></i>
                                RMS Debug Panel
                                <span class="debug-status-indicator ms-2" title="Debug Status">
                                    <i class="ph-circle-fill text-success"></i>
                                </span>
                            </h5>
                        </div>
                        <div class="debug-header-right">
                            <div class="btn-group btn-group-sm me-2">
                                <button type="button" class="btn btn-outline-light" id="debug-refresh-btn">
                                    <i class="ph-arrow-clockwise"></i>
                                    <span class="d-none d-sm-inline ms-1">Refresh</span>
                                </button>
                                <button type="button" class="btn btn-outline-light" id="debug-export-btn">
                                    <i class="ph-download"></i>
                                    <span class="d-none d-sm-inline ms-1">Export</span>
                                </button>
                                <button type="button" class="btn btn-outline-light" id="debug-clear-btn">
                                    <i class="ph-trash"></i>
                                    <span class="d-none d-sm-inline ms-1">Clear</span>
                                </button>
                            </div>
                            <button type="button" class="btn btn-light btn-sm" id="debug-toggle-btn">
                                <i class="ph-x"></i>
                            </button>
                        </div>
                    </div>

                <div class="debug-panel-body">
                    <!-- Navigation Tabs -->
                    <ul class="nav nav-tabs debug-nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#debug-overview-tab">
                                <i class="ph-chart-bar me-1"></i>
                                Overview
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#debug-form-tab">
                                <i class="ph-textbox me-1"></i>
                                Form Analysis
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#debug-fields-tab">
                                <i class="ph-list-checks me-1"></i>
                                Fields
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#debug-performance-tab">
                                <i class="ph-speedometer me-1"></i>
                                Performance
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#debug-queries-tab">
                                <i class="ph-database me-1"></i>
                                Database
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#debug-memory-tab">
                                <i class="ph-hard-drives me-1"></i>
                                Memory
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#debug-logs-tab">
                                <i class="ph-file-text me-1"></i>
                                Logs
                            </button>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content debug-tab-content">
                        <!-- Overview Tab -->
                        <div class="tab-pane fade show active" id="debug-overview-tab">
                            <div class="debug-overview-content">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">Session Info</h6>
                                                <div id="debug-session-info" class="debug-info-content"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border-0 bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">Performance Summary</h6>
                                                <div id="debug-performance-summary" class="debug-info-content"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Form Analysis Tab -->
                        <div class="tab-pane fade" id="debug-form-tab">
                            <div id="debug-form-analysis" class="debug-tab-content-inner"></div>
                        </div>

                        <!-- Fields Tab -->
                        <div class="tab-pane fade" id="debug-fields-tab">
                            <div id="debug-field-analysis" class="debug-tab-content-inner"></div>
                        </div>

                        <!-- Performance Tab -->
                        <div class="tab-pane fade" id="debug-performance-tab">
                            <div id="debug-performance-details" class="debug-tab-content-inner"></div>
                        </div>

                        <!-- Database Tab -->
                        <div class="tab-pane fade" id="debug-queries-tab">
                            <div id="debug-database-analysis" class="debug-tab-content-inner"></div>
                        </div>

                        <!-- Memory Tab -->
                        <div class="tab-pane fade" id="debug-memory-tab">
                            <div id="debug-memory-tracking" class="debug-tab-content-inner"></div>
                        </div>

                        <!-- Logs Tab -->
                        <div class="tab-pane fade" id="debug-logs-tab">
                            <div class="debug-logs-controls mb-3">
                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" id="debug-log-level-filter">
                                            <option value="all">All Levels</option>
                                            <option value="info">Info</option>
                                            <option value="warning">Warning</option>
                                            <option value="error">Error</option>
                                            <option value="critical">Critical</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-select form-select-sm" id="debug-log-category-filter">
                                            <option value="all">All Categories</option>
                                            <option value="form">Form</option>
                                            <option value="field">Field</option>
                                            <option value="validation">Validation</option>
                                            <option value="performance">Performance</option>
                                            <option value="memory">Memory</option>
                                            <option value="database">Database</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="text" class="form-control form-control-sm" id="debug-log-search" 
                                               placeholder="جستجو در logs...">
                                    </div>
                                </div>
                            </div>
                            <div id="debug-logs-content" class="debug-tab-content-inner"></div>
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
        // Implementation for filtering logs
        console.log('Filtering logs:', this.logFilters);
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
    
    if (debugEnabled || new URLSearchParams(window.location.search).has('debug')) {
        window.rmsDebugPanel = new RMSDebugPanel({
            enableFloatingPanel: true,
            autoRefresh: false
        });
    }
});

// Export for manual initialization
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RMSDebugPanel;
}