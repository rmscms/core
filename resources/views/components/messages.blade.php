{{-- Success Messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="ph-check-circle ph-lg me-3 text-success"></i>
            <div class="flex-fill">
                <h6 class="mb-1">موفقیت!</h6>
                <span>{{ session('success') }}</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
    </div>
@endif

{{-- Error Messages --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="ph-x-circle ph-lg me-3 text-danger"></i>
            <div class="flex-fill">
                <h6 class="mb-1">خطا!</h6>
                <span>{{ session('error') }}</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
    </div>
@endif

{{-- Warning Messages --}}
@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="ph-warning ph-lg me-3 text-warning"></i>
            <div class="flex-fill">
                <h6 class="mb-1">هشدار!</h6>
                <span>{{ session('warning') }}</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
    </div>
@endif

{{-- Info Messages --}}
@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
            <i class="ph-info ph-lg me-3 text-info"></i>
            <div class="flex-fill">
                <h6 class="mb-1">اطلاعات</h6>
                <span>{{ session('info') }}</span>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
    </div>
@endif

{{-- Validation Errors --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="ph-warning-circle ph-lg me-3 text-danger flex-shrink-0 mt-1"></i>
            <div class="flex-fill">
                <h6 class="mb-2">خطاهای اعتبارسنجی:</h6>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li class="small">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
    </div>
@endif

{{-- Custom CSS for better spacing --}}
@push('styles')
<style>
.alert {
    border: none;
    border-radius: 0.5rem;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.alert-success {
    background-color: #d1f2eb;
    border-left: 4px solid #00c851;
}

.alert-danger {
    background-color: #f8d7da;
    border-left: 4px solid #dc3545;
}

.alert-warning {
    background-color: #fff3cd;
    border-left: 4px solid #ffc107;
}

.alert-info {
    background-color: #d1ecf1;
    border-left: 4px solid #17a2b8;
}

.alert h6 {
    font-weight: 600;
    color: inherit;
}

.alert .btn-close {
    opacity: 0.6;
    font-size: 0.8rem;
}

.alert .btn-close:hover {
    opacity: 1;
}

@media (max-width: 576px) {
    .alert .d-flex {
        flex-direction: column;
        align-items: flex-start !important;
    }
    
    .alert i {
        margin-bottom: 0.5rem;
    }
}
</style>
@endpush

{{-- JavaScript for enhanced auto-hide with progress bar --}}
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Enhanced auto-hide for success and info messages with progress bar
    const autoHideAlerts = document.querySelectorAll('.alert-success, .alert-info');
    
    autoHideAlerts.forEach(function(alert) {
        // Add progress bar
        const progressContainer = document.createElement('div');
        progressContainer.className = 'alert-progress-container';
        progressContainer.innerHTML = '<div class="alert-progress-bar"></div>';
        alert.appendChild(progressContainer);
        
        const progressBar = progressContainer.querySelector('.alert-progress-bar');
        const duration = 5000; // 5 seconds
        let startTime = Date.now();
        
        // Animate progress bar
        function updateProgress() {
            const elapsed = Date.now() - startTime;
            const progress = Math.min(elapsed / duration, 1);
            
            progressBar.style.width = (progress * 100) + '%';
            
            if (progress < 1) {
                requestAnimationFrame(updateProgress);
            } else {
                // Fade out and close
                alert.style.transition = 'opacity 0.3s ease-out';
                alert.style.opacity = '0';
                setTimeout(() => {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }, 300);
            }
        }
        
        // Start progress animation
        requestAnimationFrame(updateProgress);
        
        // Pause on hover
        alert.addEventListener('mouseenter', function() {
            startTime += Date.now() - startTime; // Pause timer
        });
        
        alert.addEventListener('mouseleave', function() {
            startTime = Date.now() - (duration * (parseFloat(progressBar.style.width) / 100));
        });
    });
    
    // Add toast-like behavior for new messages
    window.showToastMessage = function(type, title, message, duration = 5000) {
        const toastContainer = getOrCreateToastContainer();
        
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show toast-message`;
        toast.setAttribute('role', 'alert');
        
        const iconMap = {
            'success': 'ph-check-circle',
            'error': 'ph-x-circle', 
            'danger': 'ph-x-circle',
            'warning': 'ph-warning',
            'info': 'ph-info'
        };
        
        toast.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${iconMap[type] || 'ph-info'} ph-lg me-3 text-${type}"></i>
                <div class="flex-fill">
                    <h6 class="mb-1">${title}</h6>
                    <span>${message}</span>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="بستن"></button>
            ${(type === 'success' || type === 'info') ? '<div class="alert-progress-container"><div class="alert-progress-bar"></div></div>' : ''}
        `;
        
        toastContainer.appendChild(toast);
        
        // Auto-hide logic for toast
        if (type === 'success' || type === 'info') {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(toast);
                bsAlert.close();
            }, duration);
        }
    };
    
    function getOrCreateToastContainer() {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            container.className = 'toast-notifications-container';
            document.body.appendChild(container);
        }
        return container;
    }
});
</script>
@endpush
