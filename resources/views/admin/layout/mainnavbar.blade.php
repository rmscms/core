<div class="navbar navbar-dark navbar-expand-lg navbar-static shadow">
    <div class="container-fluid">
        <div class="d-flex d-lg-none me-2">
            <button type="button" class="navbar-toggler sidebar-mobile-main-toggle rounded-pill">
                <i class="ph-list"></i>
            </button>
        </div>

        <div class="navbar-brand flex-1 flex-lg-0">
            <a href="{{ (config('cms.dashboard.enabled', true) && \Illuminate\Support\Facades\Route::has('admin.dashboard')) ? route('admin.dashboard') : url(config('cms.admin_url', 'admin')) }}" class="d-inline-flex align-items-center">
                <span class="text-white fw-bold">{{ config('app.name', 'RMS') }}</span>
            </a>
        </div>

        <div class="navbar-collapse justify-content-center flex-lg-1 order-2 order-lg-1 collapse" id="navbar_search">
            <div class="navbar-search flex-fill position-relative mt-2 mt-lg-0 mx-lg-3">
                <div class="form-control-feedback-start flex-grow-1" data-color-theme="dark">
                    <input type="text" class="form-control bg-transparent rounded-pill" placeholder="{{ trans('auth.search') }}" data-bs-toggle="dropdown">
                    <div class="form-control-feedback-icon">
                        <i class="ph-magnifying-glass"></i>
                    </div>
                    <div class="dropdown-menu w-100" data-color-theme="light" style="max-height: 420px; overflow-y: auto; overscroll-behavior: contain; scrollbar-width: thin;">
                        {{-- Results injected dynamically by admin search script --}}
                    </div>
                </div>
            </div>
        </div>

        <ul class="nav flex-row order-1 order-lg-2">
            <li class="nav-item d-lg-none">
                <a href="#navbar_search" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="collapse">
                    <i class="ph-magnifying-glass"></i>
                </a>
            </li>
            <li class="nav-item position-relative">
                <a href="#" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="offcanvas" data-bs-target="#notifications" aria-label="Notifications">
                    <i class="ph-bell"></i>
                    <span id="notif-badge" class="badge bg-yellow text-black position-absolute top-0 end-0 translate-middle-top zindex-1 rounded-pill mt-1 me-1 d-none">0</span>
                </a>
            </li>

            <!-- Theme Switcher -->
            <li class="nav-item nav-item-dropdown-lg dropdown">
                <a href="#" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                    <i class="ph-moon theme-icon"></i>
                </a>

                <div class="dropdown-menu dropdown-menu-end" style="min-width: 280px;">
                    <div class="dropdown-header d-flex align-items-center py-2">
                        <h6 class="mb-0">تنظیمات نمایش</h6>
                    </div>

                    <div class="dropdown-item-text">
                        <!-- Light Theme -->
                        <label class="form-check d-flex align-items-center py-2 cursor-pointer">
                            <div class="d-flex flex-fill me-2">
                                <div class="form-check-label d-flex me-2">
                                    <i class="ph-sun ph-lg me-3 text-warning"></i>
                                    <div>
                                        <span class="fw-bold">حالت روز</span>
                                        <div class="fs-sm text-muted">حالت پیش‌فرض روشن</div>
                                    </div>
                                </div>
                            </div>
                            <input type="radio" class="form-check-input cursor-pointer ms-auto" name="main-theme" value="light" checked>
                        </label>

                        <!-- Dark Theme -->
                        <label class="form-check d-flex align-items-center py-2 cursor-pointer">
                            <div class="d-flex flex-fill me-2">
                                <div class="form-check-label d-flex me-2">
                                    <i class="ph-moon ph-lg me-3 text-info"></i>
                                    <div>
                                        <span class="fw-bold">حالت شب</span>
                                        <div class="fs-sm text-muted">حالت تیره</div>
                                    </div>
                                </div>
                            </div>
                            <input type="radio" class="form-check-input cursor-pointer ms-auto" name="main-theme" value="dark">
                        </label>

                        <!-- Auto Theme -->
                        <label class="form-check d-flex align-items-center py-2 cursor-pointer">
                            <div class="d-flex flex-fill me-2">
                                <div class="form-check-label d-flex me-2">
                                    <i class="ph-desktop ph-lg me-3 text-primary"></i>
                                    <div>
                                        <span class="fw-bold">خودکار</span>
                                        <div class="fs-sm text-muted">بر اساس تنظیمات سیستم</div>
                                    </div>
                                </div>
                            </div>
                            <input type="radio" class="form-check-input cursor-pointer ms-auto" name="main-theme" value="auto">
                        </label>
                    </div>

                    <div class="dropdown-divider"></div>

                    <div class="dropdown-item-text py-2">
                        <label class="form-label mb-1">فونت رابط کاربری</label>
                        <select id="navbarFontSelect" class="form-select form-select-sm">
                            <option value="yekan">IRANYekanX FaNum</option>
                            <option value="iransans">IRANSansX FaNum</option>
                            <option value="vazir">Vazirmatn / Vazir</option>
                            <option value="pinar">Pinar</option>
                            <option value="inter">Inter (Latin)</option>
                        </select>
                        <div class="form-text mt-1">برای ذخیره دائمی از localStorage استفاده می‌شود</div>
                    </div>

                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item text-center py-2">
                        <small class="text-muted">تغییرات بلافاصله اعمال می‌شوند</small>
                    </div>
                </div>
            </li>

            <li class="nav-item nav-item-dropdown-lg dropdown">
                <a href="#" class="navbar-nav-link align-items-center rounded-pill p-1" data-bs-toggle="dropdown">
                    <div class="status-indicator-container">
                        @php
                            $admin = Auth::guard('admin')->user();
                            $avatarUrl = $admin && $admin->avatar
                                ? (\Illuminate\Support\Str::startsWith($admin->avatar, ['http://', 'https://']) ? $admin->avatar : asset('storage/' . ltrim($admin->avatar, '/')))
                                : asset(config('cms.admin_theme') . '/images/profile/profile.jpg');
                        @endphp
                        <img src="{{ $avatarUrl }}" class="w-32px h-32px rounded-pill" alt="{{ $admin->name ?? 'Admin' }}" style="object-fit: cover;">
                        <span class="status-indicator bg-success"></span>
                    </div>
                    <span class="d-none d-lg-inline-block mx-lg-2">{{ $admin->name ?? 'Guest' }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    @if($admin)
                        @if(\Illuminate\Support\Facades\Route::has('admin.admins.edit'))
                            <a href="{{ route('admin.admins.edit', $admin->id) }}" class="dropdown-item">
                                <i class="ph-user-circle me-2"></i>
                                {{ trans('auth.my_profile') }}
                            </a>
                        @endif
                        @if(\Illuminate\Support\Facades\Route::has('admin.settings.index'))
                            <a href="{{ route('admin.settings.index') }}" class="dropdown-item">
                                <i class="ph-gear me-2"></i>
                                {{ trans('auth.account_settings') }}
                            </a>
                        @endif
                        <div class="dropdown-divider"></div>
                        @if(\Illuminate\Support\Facades\Route::has('admin.logout'))
                            <form action="{{ route('admin.logout') }}" method="POST" style="display: inline;">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="ph-sign-out me-2"></i>
                                    {{ trans('auth.logout') }}
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ url(config('cms.admin_url', 'admin') . '/login') }}" class="dropdown-item">
                            <i class="ph-sign-in me-2"></i>
                            {{ trans('auth.login') }}
                        </a>
                    @endif
                </div>
            </li>
        </ul>
    </div>
</div>
