<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">

    <!-- Sidebar content -->
    <div class="sidebar-content">

        <!-- Sidebar header -->
        <div class="sidebar-section">
            <div class="sidebar-section-body d-flex justify-content-center">
                <h5 class="sidebar-resize-hide flex-grow-1 my-auto">{{ config('app.name', 'RMS') }}</h5>

                <div>
                    <button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-control sidebar-main-resize d-none d-lg-inline-flex" title="تغییر سایز منو">
                        <i class="ph-arrows-left-right"></i>
                    </button>

                    <button type="button" class="btn btn-flat-white btn-icon btn-sm rounded-pill border-transparent sidebar-mobile-main-toggle d-lg-none" title="بستن منو">
                        <i class="ph-x"></i>
                    </button>
                </div>
            </div>
        </div>
        <!-- /sidebar header -->

        <!-- User Profile Section -->
        @if(Auth::guard('admin')->check())
        <div class="sidebar-section">
            <div class="sidebar-user">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center">
                        <div class="status-indicator-container me-3">
                            <a href="{{ url('/admin/profile') }}">
                                <img src="{{ Auth::guard('admin')->user()->avatar ?? asset($theme.'/images/profile/profile.jpg') }}" width="38" height="38" class="rounded-circle" alt="{{ Auth::guard('admin')->user()->name }}">
                                <span class="status-indicator bg-success" title="آنلاین"></span>
                            </a>
                        </div>
                        <div class="flex-fill">
                            <div class="fw-semibold text-white">{{ Auth::guard('admin')->user()->name ?? 'Administrator' }}</div>
                            <div class="fs-sm text-white-50">{{ Auth::guard('admin')->user()->email ?? 'admin@' . request()->getHost() }}</div>
                            @if(Auth::guard('admin')->user()->last_login_at)
                                <div class="fs-xs text-white-25 mt-1">
                                    <i class="ph-clock me-1"></i>
                                    آخرین بازدید: {{ Auth::guard('admin')->user()->last_login_at->diffForHumans() }}
                                </div>
                            @endif
                        </div>
                        <div class="ms-2">
                            <div class="dropdown">
                                <a href="#" class="text-white-50" data-bs-toggle="dropdown" title="تنظیمات">
                                    <i class="ph-gear"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="{{ url('/admin/profile') }}" class="dropdown-item">
                                        <i class="ph-user-circle me-2"></i>
                                        پروفایل
                                    </a>
                                    <a href="{{ url('/admin/settings') }}" class="dropdown-item">
                                        <i class="ph-gear me-2"></i>
                                        تنظیمات
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <form method="POST" action="{{ url('/admin/logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="ph-sign-out me-2"></i>
                                            خروج
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        <!-- /user profile section -->

        <!-- Main navigation -->
        <div class="sidebar-section">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                {{-- Main Section --}}
                <x-cms::menu-header title="{{ trans('auth.main') ?: 'مین' }}" />

                @php
                    $pendingNotifications = 0; // TODO: Get from notifications count
                    $dashboardDescription = $pendingNotifications > 0 ?
                        'بدون اعلان جدید' :
                        'هیچ اعلان جدیدی ندارید';
                @endphp

                <x-cms::menu-item
                    title="{{ trans('admin.dashboard') ?: 'داشبورد' }}"
                    url="{{ (config('cms.dashboard.enabled', true) && \Illuminate\Support\Facades\Route::has('admin.dashboard')) ? route('admin.dashboard') : url(config('cms.admin_url', 'admin')) }}"
                    icon="ph-house"
                    :routes="['admin.dashboard']"
                    urlPattern="admin/dashboard"
                    description="{{ $dashboardDescription }}"
                />

                {{-- Users Management --}}
                @php
                    $totalUsers = \App\Models\User::count() ?? 0;
                    $activeUsers = \App\Models\User::where('active', 1)->count() ?? 0;
                    $inactiveUsers = $totalUsers - $activeUsers;

                    $usersChildren = [
                        [
                            'title' => 'لیست کاربران',
                            'url' => '/admin/users',
                            'icon' => 'ph-list',
                            'routes' => ['admin.users.index'],
                            'urlPattern' => 'admin/users',
                            'badge' => $totalUsers > 0 ? (string)$totalUsers : null,
                            'badgeColor' => 'text-muted'
                        ],
                        [
                            'title' => 'کاربر جدید',
                            'url' => '/admin/users/create',
                            'icon' => 'ph-user-plus',
                            'routes' => ['admin.users.create'],
                        ]
                    ];

                    if ($totalUsers > 0) {
                        $usersChildren[] = ['divider' => true];
                        $usersChildren[] = [
                            'title' => 'کاربران فعال',
                            'url' => '/admin/users?filter_active=1',
                            'icon' => 'ph-check-circle',
                            'iconColor' => 'success',
                            'badge' => (string)$activeUsers,
                            'badgeColor' => 'text-success'
                        ];

                        if ($inactiveUsers > 0) {
                            $usersChildren[] = [
                                'title' => 'کاربران غیرفعال',
                                'url' => '/admin/users?filter_active=0',
                                'icon' => 'ph-x-circle',
                                'iconColor' => 'danger',
                                'badge' => (string)$inactiveUsers,
                                'badgeColor' => 'text-danger'
                            ];
                        }
                    }
                @endphp

                <x-cms::submenu-item
                    title="{{ trans('admin.users') ?: 'کاربران' }}"
                    icon="ph-users"
                    :badge="$totalUsers > 0 ? (string)$totalUsers : null"
                    :children="$usersChildren"
                />

                {{-- System Management --}}
                <x-cms::menu-header title="{{ trans('auth.system') ?: 'سیستم' }}" />

                <!-- Quick Stats Widget -->
                @php
                    $systemStats = [
                        'users_today' => \App\Models\User::whereDate('created_at', today())->count() ?? 0,
                        'disk_usage' => round((disk_total_space('.') - disk_free_space('.')) / disk_total_space('.') * 100, 1),
                        'memory_usage' => round(memory_get_usage() / 1024 / 1024, 1) // MB
                    ];
                @endphp
                <li class="nav-item">
                    <div class="nav-link d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                            <i class="ph-chart-line-up text-success"></i>
                            <span class="ms-2">کاربر جدید امروز</span>
                        </div>
                        <span class="fw-bold text-success">{{ $systemStats['users_today'] }}</span>
                    </div>
                </li>

                @php
                    $totalAdmins = \RMS\Core\Models\Admin::whereNull('deleted_at')->count() ?? 0;

                    $totalSettings = \RMS\Core\Models\Setting::count() ?? 0;

                    $settingsChildren = [
                        [
                            'title' => 'تنظیمات کلیدی',
                            'url' => '/admin/settings',
                            'icon' => 'ph-sliders',
                            'routes' => ['admin.settings.*'],
                            'urlPattern' => 'admin/settings*',
                            'badge' => $totalSettings > 0 ? (string)$totalSettings : null,
                            'badgeColor' => 'text-muted'
                        ],
                        [
                            'title' => 'مدیریت کش',
                            'url' => '/admin/cache',
                            'icon' => 'ph-database',
                            'routes' => ['admin.cache.*'],
                        ],
                        ['divider' => true],
                        [
                            'title' => 'مدیران',
                            'url' => '/admin/admins',
                            'icon' => 'ph-users-three',
                            'iconColor' => 'danger',
                            'routes' => ['admin.admins.*'],
                            'urlPattern' => 'admin/admins*',
                            'badge' => $totalAdmins > 0 ? (string)$totalAdmins : null,
                            'badgeColor' => 'text-muted'
                        ]
                    ];
                @endphp

                <x-cms::submenu-item
                    title="{{ trans('admin.settings') ?: 'تنظیمات' }}"
                    icon="ph-gear"
                    :children="$settingsChildren"
                />

                <!-- System Status Widget -->
                <li class="nav-item">
                    <div class="nav-link">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fs-sm text-white-75">وضعیت سیستم</span>
                        </div>
                        <div class="progress mb-2" style="height: 4px;">
                            <div class="progress-bar bg-success" style="width: {{ 100 - $systemStats['disk_usage'] }}%"></div>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fs-xs text-white-50">فضای دیسک</span>
                            <span class="fs-xs text-white-75">{{ $systemStats['disk_usage'] }}%</span>
                        </div>
                    </div>
                </li>

                <!-- Quick Actions -->
                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">عملیات سریع</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/admin/users/create') }}" class="nav-link">
                        <i class="ph-user-plus text-primary"></i>
                        <span>کاربر جدید</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/admin/backup') }}" class="nav-link">
                        <i class="ph-download text-warning"></i>
                        <span>پشتیبان گیری</span>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/admin/reports') }}" class="nav-link">
                        <i class="ph-chart-bar text-info"></i>
                        <span>گزارش‌ها</span>
                    </a>
                </li>

                @if(Auth::guard('admin')->user() && Auth::guard('admin')->user()->hasRole('developer'))
                <!-- Developer Section -->
                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">Developer</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/admin/bug-logs') }}" class="nav-link {{ request()->is('admin/bug-logs*') ? 'active' : '' }}">
                        <i class="ph-bug"></i>
                        <span>Bug Logs</span>
                    </a>
                </li>
                @endif

            </ul>

            <!-- Sidebar Footer/Controls -->
            <div class="sidebar-resize-indicator"></div>

        </div>

        <!-- Bottom Action Buttons -->
        <div class="sidebar-footer mt-auto p-3">
            <div class="d-flex flex-column gap-2">
                <!-- Professional Cache Clear Button -->
                <button type="button"
                        class="btn btn-outline-primary btn-sm position-relative"
                        data-cache-action="clear-all"
                        title="پاک کردن تمام کش‌ها (Ctrl+Shift+C)">
                    <i class="ph-broom me-2"></i>
                    <span class="sidebar-resize-hide">پاک کردن کش</span>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning d-none" id="cache-status-badge">
                        <i class="ph-warning fs-xs"></i>
                    </span>
                </button>

                <!-- Cache Status Indicator (compact) -->
                <div class="d-flex align-items-center text-white-50 sidebar-resize-hide" id="cache-quick-status">
                    <div class="d-flex align-items-center">
                        <div class="cache-status-indicator me-2" style="width: 8px; height: 8px; border-radius: 50%; background: #28a745;"></div>
                        <span class="fs-xs">کش سیستم: فعال</span>
                    </div>
                </div>

                <div class="d-flex align-items-center text-white-50 sidebar-resize-hide">
                    <i class="ph-clock me-2"></i>
                    <span class="fs-xs">آخرین به‌روزرسانی: {{ now()->format('H:i') }}</span>
                </div>
            </div>
        </div>

        <!-- /main navigation -->

    </div>
    <!-- /sidebar content -->
</div>

