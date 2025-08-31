<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
    <!-- Sidebar header -->
    <div class="sidebar-section bg-black bg-opacity-10 border-bottom border-bottom-white border-opacity-10">
        <div class="sidebar-logo d-flex justify-content-center align-items-center">
            <a href="#" class="d-inline-flex align-items-center py-2 sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                <img src="{{ asset(config('cms.admin_theme').'/images/logo_icon.svg') }}" class="sidebar-logo-icon" alt="">
                <img src="{{ asset(config('cms.admin_theme').'/images/logo_text_light.svg') }}" class="sidebar-resize-hide ms-3" height="14" alt="">
            </a>

            <div class="sidebar-resize-hide ms-auto">
                <button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-mobile-main-toggle d-lg-none">
                    <i class="ph-x"></i>
                </button>
            </div>
        </div>
    </div>
    <!-- /sidebar header -->

    <!-- Sidebar content -->
    <div class="sidebar-content">
        <!-- Main navigation -->
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">
                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ trans("auth.main") }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <x-cms::menu-item href="#" icon="ph-chart-bar" label="{{ trans('auth.dashboard') }}" active="true" />
                <x-cms::menu-item href="#" icon="ph-users" label="{{ trans('auth.users') }}" />
                <x-cms::menu-item href="#" icon="ph-user-gear" label="{{ trans('auth.admin') }}" />
            </ul>
        </div>
        <!-- /main navigation -->
    </div>
</div>
