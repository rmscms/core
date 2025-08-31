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
            <x-cms::notification-item
                image='<img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face1.jpg") }}" class="w-40px h-40px rounded-pill" alt="">'
                iconBg="bg-success"
                content='{{ trans("auth.james_completed_task", ["name" => "<a href=\"#\" class=\"fw-semibold\">James</a>", "task" => "<a href=\"#\">Submit documents</a>", "list" => "<a href=\"#\">Onboarding</a>"]) }}
                    <div class="bg-light rounded p-2 my-2">
                        <label class="form-check ms-1">
                            <input type="checkbox" class="form-check-input" checked disabled>
                            <del class="form-check-label">{{ trans("auth.submit_personal_documents") }}</del>
                        </label>
                    </div>'
                time='{{ trans("auth.hours_ago", ["count" => 2]) }}'
            />

            <x-cms::notification-item
                image='<img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face3.jpg") }}" class="w-40px h-40px rounded-pill" alt="">'
                iconBg="bg-warning"
                content='{{ trans("auth.margo_added_users", ["name" => "<a href=\"#\" class=\"fw-semibold\">Margo</a>", "channel" => "<span class=\"fw-semibold\">Customer enablement</span>"]) }}
                    <div class="d-flex my-2">
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face10.jpg") }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-danger"></span>
                        </a>
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face11.jpg") }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-success"></span>
                        </a>
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face12.jpg") }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-success"></span>
                        </a>
                        <a href="#" class="status-indicator-container me-1">
                            <img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face13.jpg") }}" class="w-32px h-32px rounded-pill" alt="">
                            <span class="status-indicator bg-success"></span>
                        </a>
                        <button type="button" class="btn btn-light btn-icon d-inline-flex align-items-center justify-content-center w-32px h-32px rounded-pill p-0">
                            <i class="ph-plus ph-sm"></i>
                        </button>
                    </div>'
                time='{{ trans("auth.hours_ago", ["count" => 3]) }}'
            />

            <x-cms::notification-item
                icon="ph-warning"
                iconBg="bg-warning bg-opacity-10 text-warning"
                content='{{ trans("auth.subscription_cancelled", ["subscription" => "<a href=\"#\">#466573</a>", "case" => "<a href=\"#\">#4492</a>"]) }}'
                time='{{ trans("auth.hours_ago", ["count" => 4]) }}'
            />
        </div>

        <div class="bg-light fw-medium py-2 px-3">{{ trans("auth.older_notifications") }}</div>
        <div class="p-3">
            <x-cms::notification-item
                image='<img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face25.jpg") }}" class="w-40px h-40px rounded-pill" alt="">'
                iconBg="bg-success"
                content='{{ trans("auth.nick_requested_feedback", ["name" => "<a href=\"#\" class=\"fw-semibold\">Nick</a>", "request" => "<a href=\"#\">#458</a>"]) }}'
                actions='<a href="#" class="btn btn-success btn-sm me-1">
                            <i class="ph-checks ph-sm me-1"></i>
                            {{ trans("auth.approve") }}
                        </a>
                        <a href="#" class="btn btn-light btn-sm">
                            {{ trans("auth.review") }}
                        </a>'
                time='{{ trans("auth.days_ago", ["count" => 3]) }}'
            />

            <x-cms::notification-item
                image='<img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face24.jpg") }}" class="w-40px h-40px rounded-pill" alt="">'
                iconBg="bg-grey"
                content='{{ trans("auth.mike_added_files", ["name" => "<a href=\"#\" class=\"fw-semibold\">Mike</a>", "project" => "<a href=\"#\">Product management</a>"]) }}
                    <div class="bg-light rounded p-2 my-2">
                        <div class="d-flex align-items-center">
                            <div class="me-2">
                                <img src="{{ asset(config("cms.admin_theme")."/images/icons/pdf.svg") }}" width="34" height="34" alt="">
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
                    </div>'
                time='{{ trans("auth.days_ago", ["count" => 1]) }}'
            />

            <x-cms::notification-item
                icon="ph-calendar-plus"
                iconBg="bg-success bg-opacity-10 text-success"
                content='{{ trans("auth.all_hands_meeting") }}
                    <div class="my-2">
                        <a href="#" class="btn btn-primary btn-sm">
                            <i class="ph-calendar-plus ph-sm me-1"></i>
                            {{ trans("auth.add_to_calendar") }}
                        </a>
                    </div>'
                time='{{ trans("auth.days_ago", ["count" => 2]) }}'
            />

            <x-cms::notification-item
                image='<img src="{{ asset(config("cms.admin_theme")."/images/demo/users/face23.jpg") }}" class="w-40px h-40px rounded-pill" alt="">'
                iconBg="bg-danger"
                content='{{ trans("auth.christine_commented", ["name" => "<a href=\"#\" class=\"fw-semibold\">Christine</a>", "post" => "<a href=\"#\">post</a>"]) }}'
                time='{{ trans("auth.days_ago", ["count" => 2]) }}'
            />

            <x-cms::notification-item
                icon="ph-users-four"
                iconBg="bg-primary bg-opacity-10 text-primary"
                content='{{ trans("auth.hr_survey", ["department" => "<span class=\"fw-semibold\">HR department</span>"]) }}'
                time='{{ trans("auth.days_ago", ["count" => 3]) }}'
            />

            <div class="text-center">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ trans("auth.loading") }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
