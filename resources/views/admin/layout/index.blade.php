<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield("title", trans("auth.login"))</title>
    <!-- Global stylesheets -->
    <link href="{{ asset(config('cms.admin_theme').'/css/fonts/font-face.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme').'/css/modern-navbar.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme').'/css/icons/fontawesome/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme').'/css/icons/icomoon/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme').'/css/icons/material/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme').'/css/icons/phosphor/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset(config('cms.admin_theme').'/css/rtl/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">
    <style>
        body {
            font-family: 'Vazirmatn', sans-serif;
        }
    </style>
    <!-- Core JS files -->
    <script src="{{ asset(config('cms.admin_theme').'/demo/demo_configurator.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme').'/js/jquery.min.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme').'/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Theme JS files -->
    <script src="{{ asset(config('cms.admin_theme').'/js/persian-number-converter.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme').'/js/modal.js') }}"></script>
    <script src="{{ asset(config('cms.admin_theme').'/js/app.js') }}"></script>

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
