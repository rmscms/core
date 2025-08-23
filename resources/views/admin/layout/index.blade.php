```blade
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'fa' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('title', trans('auth.login'))</title>
    <!-- Global stylesheets -->
    <link href="{{ asset(config('cms.admin_theme') . '/css/fonts/font-face.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/modern-navbar.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/limitless.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/icons/fontawesome/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/icons/icomoon/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/icons/material/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/icons/phosphor/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme') . '/css/ltr/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">
    <!-- Core JS files -->
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo_configurator.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/bootstrap.bundle.min.js') }}"></script>
    <!-- Theme JS files -->
    <script src="{{ asset(config('cms.admin_theme') . '/js/vendor/visualization/d3/d3.min.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/vendor/visualization/d3/d3_tooltip.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/app.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/pages/dashboard.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/streamgraph.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/sparklines.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/lines.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/areas.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/donuts.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/bars.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/progress.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/heatmaps.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/pies.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme') . '/js/demo/charts/pages/dashboard/bullets.js') }}"></script>
    @yield('assets')
</head>
<body>
<!-- Page content -->
<div class="page-content">
    <!-- Sidebar -->
    @include('cms::admin.layout.sidebar')
    <!-- Main content -->
    <div class="content-wrapper">
        <!-- Main navbar -->
        @include('cms::admin.layout.mainnavbar')
        <!-- Inner content -->
        <div class="content-inner">
            <!-- Content area -->
            <div class="content pt-0">
                @yield('content')
            </div>
            <!-- Footer -->
            @include('cms::admin.layout.footerphone')
            <!-- /footer -->
        </div>
        <!-- /inner content -->
    </div>
    <!-- /main content -->
</div>
<!-- /page content -->
<!-- Notifications -->
@include('cms::admin.layout.notifications')
<!-- /notifications -->
</body>
</html>
