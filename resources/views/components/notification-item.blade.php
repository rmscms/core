<div class="offcanvas offcanvas-end" tabindex="-1" id="notifications">
    <div class="offcanvas-header py-0">
        <h5 class="offcanvas-title py-3">{{ trans("auth.activity") }}</h5>
        <button type="button" class="btn btn-light btn-sm btn-icon border-transparent rounded-pill" data-bs-dismiss="offcanvas">
            <i class="ph-x"></i>
        </button>
    </div>

    <div class="offcanvas-body p-0">
        <div class="bg-light fw-medium py-2 px-3">{{ trans("auth.new_notifications") }}</div>
        <div class="p-3">
            <div class="d-flex align-items-start mb-3">
                <a href="#" class="status-indicator-container me-3">
                    <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face1.jpg') }}" class="w-40px h-40px rounded-pill" alt="">
                    <span class="status-indicator bg-success"></span>
                </a>
                <div class="flex-fill">
                    {{ trans("auth.james_completed_task", ["name" => "<a href=\"#\" class=\"fw-semibold\">James</a>", "task" => "<a href=\"#\">Submit documents</a>", "list" => "<a href=\"#\">Onboarding</a>"]) }}
                    <div class="bg-light rounded p-2 my-2">
                        <label class="form-check ms-1">
                            <input type="checkbox" class="form-check-input" checked disabled>
                            <del class="form-check-label">{{ trans("auth.submit_personal_documents") }}</del>
                        </label>
                    </div>
                    <div class="fs-sm text-muted mt-1">{{ trans("auth.hours_ago", ["count" => 2]) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-start mb-3">
                <a href="#" class="status-indicator-container me-3">
                    <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face3.jpg') }}" class="w-40px h-40px rounded-pill" alt="">
                    <span class="status-indicator bg-warning"></span>
                </a>
                <div class="flex-fill">
                    {{ trans("auth.margo_added_users", ["name" => "<a href=\"#\" class=\"fw-semibold\">Margo</a>", "channel" => "<span class=\"fw-semibold\">Customer enablement</span>"]) }}
                    <div class="d-flex my-2">
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face10.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-danger"></span>
                        </a>
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face11.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-success"></span>
                        </a>
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face12.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-success"></span>
                        </a>
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face13.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-success"></span>
                        </a>
                        <button type="button" class="btn btn-light btn-icon d-inline-flex align-items-center justify-content-center w-32px h-32px rounded-pill p-0">
                            <i class="ph-plus ph-sm"></i>
                        </button>
                    </div>
                    <div class="fs-sm text-muted mt-1">{{ trans("auth.hours_ago", ["count" => 3]) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-start mb-3">
                <div class="me-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-pill">
                        <i class="ph-warning p-2"></i>
                    </div>
                </div>
                <div class="flex-fill">
                    {{ trans("auth.subscription_cancelled", ["subscription" => "<a href=\"#\">#466573</a>", "case" => "<a href=\"#\">#4492</a>"]) }}
                    <div class="fs-sm text-muted mt-1">{{ trans("auth.hours_ago", ["count" => 4]) }}</div>
                </div>
            </div>
        </div>

        <div class="bg-light fw-medium py-2 px-3">{{ trans("auth.older_notifications") }}</div>
        <div class="p-3">
            <div class="d-flex align-items-start mb-3">
                <a href="#" class="status-indicator-container me-3">
                    <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face25.jpg') }}" class="w-40px h-40px rounded-pill" alt="">
                    <span class="status-indicator bg-success"></span>
                </a>
                <div class="flex-fill">
                    {{ trans("auth.nick_requested_feedback", ["name" => "<a href=\"#\" class=\"fw-semibold\">Nick</a>", "request" => "<a href=\"#\">#458</a>"]) }}
                    <div class="my-2">
                        <a href="#" class="btn btn-success btn-sm me-1">
                            <i class="ph-checks ph-sm me-1"></i>
                            {{ trans("auth.approve") }}
                        </a>
                        <a href="#" class="btn btn-light btn-sm">
                            {{ trans("auth.review") }}
                        </a>
                    </div>
                    <div class="fs-sm text-muted mt-1">{{ trans("auth.days_ago", ["count" => 3]) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-start mb-3">
                <a href="#" class="status-indicator-container me-3">
                    <img src="{{ asset(config('cms.admin_theme').'/images/demo/users/face24.jpg') }}" class="w-40px h-40px rounded-pill" alt="">
                    <span class="status-indicator bg-grey"></span>
                </a>
                <div class="flex-fill">
                    {{ trans("auth.mike_added_files", ["name" => "<a href=\"#\" class=\"fw-semibold\">Mike</a>", "project" => "<a href=\"#\">Product management</a>"]) }}
                    <div class="bg-light rounded p-2 my-2">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <img src="{{ asset(config('cms.admin_theme').'/images/icons/pdf.svg') }}" width="34" height="34" alt="">
                            </div>
                            <div class="flex-fill">
                                new_contract.pdf
                                <div class="fs-sm text-muted">112KB</div>
                            </div>
                            <div class="ms-2">
                                <button type="button" class="btn btn-flat-dark text-body btn-icon btn-sm border-transparent rounded-pill">
                                    <i class="ph-arrow-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="fs-sm text-muted mt-1">{{ trans("auth.days_ago", ["count" => 1]) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-start mb-3">
                <div class="me-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-pill">
                        <i class="ph-calendar-plus p-2"></i>
                    </div>
                </div>
                <div class="flex-fill">
                    {{ trans("auth.all_hands_meeting") }}
                    <div class="my-2">
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="ph-calendar-plus ph-sm me-1"></i>
                            {{ trans("auth.add_to_calendar") }}
                        </a>
                    </div>
                    <div class="fs-sm text-muted mt-1">{{ trans("auth.days_ago", ["count" => 2]) }}</div>
                </div>
            </div>

            <div class="d-flex align-items-start mb-3">
                <a href="#" class
