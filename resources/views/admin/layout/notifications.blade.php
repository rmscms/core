<div class="offcanvas offcanvas-end" tabindex="-1" id="notifications">
    <div class="offcanvas-header py-0 border-bottom border-white-10">
        <h5 class="offcanvas-title py-3">{{ trans("auth.activity") }}</h5>
        <button type="button" class="btn btn-info text-white btn-sm btn-icon border-0 rounded-pill" data-bs-dismiss="offcanvas">
            <i class="ph-x"></i>
        </button>
    </div>

    <div class="offcanvas-body p-0">
        <div class="fw-medium py-2 px-3 d-flex align-items-center border-bottom border-white-10">
            <span>{{ trans("admin.new_notifications") }}</span>
            <button type="button" id="notif-mark-all" class="btn btn-sm btn-info ms-auto">
                {{ trans('admin.mark_all_read') }}
            </button>
        </div>
        <div class="p-3">
            <div id="notif-spinner" class="d-none text-center my-3">
                <div class="spinner-border" role="status">
                    <span class="visually-hidden">{{ trans('auth.loading') }}</span>
                </div>
            </div>
            <div id="notif-empty" class="text-muted text-center my-3 d-none">
                {{ trans('auth.no_new_notifications') }}
            </div>
            <div id="notif-list"></div>
        </div>

    </div>
</div>
