<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield("title", trans("auth.login"))</title>
    <!-- Global stylesheets -->
    <link href="{{ asset($theme.'/css/fonts/font-face.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/modern-navbar.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/icons/fontawesome/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/icons/icomoon/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/icons/material/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/icons/phosphor/styles.min.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/rtl/all.min.css') }}" id="stylesheet" rel="stylesheet" type="text/css">

    <!-- Theme System CSS -->
    <link href="{{ asset($theme.'/css/sidebar-enhanced.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset($theme.'/css/theme-dark.css') }}" rel="stylesheet" type="text/css">

    <!-- Dynamic CSS files from controller -->
    @if(isset($css) && is_array($css))
        @foreach($css as $cssFile)
            <link href="{{ asset($cssFile) }}" rel="stylesheet" type="text/css">
        @endforeach
    @endif

    @stack('styles')

    <!-- JavaScript Variables -->
    @if(isset($js_vars) && is_array($js_vars) && count($js_vars) > 0)
        <script type="text/javascript">
            window.RMS = window.RMS || {};
            @foreach($js_vars as $key => $value)
                window.RMS.{{ $key }} = @json($value);
            @endforeach
        </script>
    @endif

    <script src="{{ asset($theme.'/js/jquery.min.js') }}"></script>
    <!-- Global RMS JavaScript (must be loaded after jQuery and before other scripts) -->
    <script src="{{ asset($theme.'/js/global.js') }}"></script>
    <script src="{{ asset($theme.'/js/bootstrap/bootstrap.bundle.min.js') }}"></script>
    <!-- Theme JS files -->
    <script src="{{ asset($theme.'/js/persian-number-converter.js') }}"></script>
    <script src="{{ asset($theme.'/js/modal.js') }}"></script>
    <script src="{{ asset($theme.'/js/app.js') }}"></script>

    <!-- Theme Switcher -->
    <script src="{{ asset($theme.'/js/theme-switcher.js') }}"></script>

    <!-- Cache Manager -->
    <script src="{{ asset($theme.'/js/cache-manager.js') }}"></script>

    <!-- Sidebar Mobile Fix -->

    <!-- Debug System - فقط در حالت debug -->
    @if(config('app.debug') && config('rms.debug.enabled', true))
        <meta name="rms-debug-enabled" content="true">
        <link href="{{ asset($theme.'/css/debug-panel.css') }}" rel="stylesheet" type="text/css">
        <script src="{{ asset($theme.'/js/debug-panel.js') }}"></script>
    @endif

    <!-- Dynamic JS files from controller (must be loaded after jQuery and all dependencies) -->
    @if(isset($js) && is_array($js))
        @foreach($js as $jsFile)
            <script src="{{ asset($jsFile) }}"></script>
        @endforeach
    @endif

    @yield('assets')
</head>
<body>

<!-- Main navbar -->
@include('cms::admin.layout.mainnavbar')

<!-- Page content -->
<div class="page-content">
    <!-- Sidebar -->
    @include('cms::admin.layout.sidebar')
    <!-- Main content -->
    <div class="content-wrapper">
        <!-- Inner content -->
        <div class="content-inner">
            <!-- Content area -->
            <div class="content pt-0">
                <!-- Messages Component -->
                @include('cms::components.messages')

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

@stack('scripts')

</body>
</html>
