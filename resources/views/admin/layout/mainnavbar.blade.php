<div class="navbar navbar-dark navbar-expand-lg navbar-static shadow">
    <div class="container-fluid">
        <div class="d-flex d-lg-none me-2">
            <button type="button" class="navbar-toggler sidebar-mobile-main-toggle rounded-pill">
                <i class="ph-list"></i>
            </button>
        </div>

        <div class="navbar-brand flex-1 flex-lg-0">
            <a href="{{ url('/admin') }}" class="d-inline-flex align-items-center">
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
                    <div class="dropdown-menu w-100" data-color-theme="light">
                        <button type="button" class="dropdown-item">
                            <div class="text-center w-32px me-3">
                                <i class="ph-magnifying-glass"></i>
                            </div>
                            <span>{{ trans('auth.search_everywhere', ['term' => '<span class="fw-bold">in</span>']) }}</span>
                        </button>

                        <div class="dropdown-divider"></div>

                        <div class="dropdown-menu-scrollable-lg">
                            <div class="dropdown-header">
                                {{ trans('auth.contacts') }}
                                <a href="#" class="float-end">
                                    {{ trans('auth.see_all') }}
                                    <i class="ph-arrow-circle-right ms-1"></i>
                                </a>
                            </div>

                            <div class="dropdown-item cursor-pointer">
                                <div class="me-3">
                                    <img src="{{ asset(config('cms.admin_theme') . '/images/demo/users/face3.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <div class="fw-semibold">Christ<mark>in</mark>e Johnson</div>
                                    <span class="fs-sm text-muted">c.johnson@awesomecorp.com</span>
                                </div>
                                <div class="d-inline-flex">
                                    <a href="#" class="text-body ms-2">
                                        <i class="ph-user-circle"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="dropdown-item cursor-pointer">
                                <div class="me-3">
                                    <img src="{{ asset(config('cms.admin_theme') . '/images/demo/users/face24.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <div class="fw-semibold">Cl<mark>in</mark>ton Sparks</div>
                                    <span class="fs-sm text-muted">c.sparks@awesomecorp.com</span>
                                </div>
                                <div class="d-inline-flex">
                                    <a href="#" class="text-body ms-2">
                                        <i class="ph-user-circle"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <div class="dropdown-header">
                                {{ trans('auth.clients') }}
                                <a href="#" class="float-end">
                                    {{ trans('auth.see_all') }}
                                    <i class="ph-arrow-circle-right ms-1"></i>
                                </a>
                            </div>

                            <div class="dropdown-item cursor-pointer">
                                <div class="me-3">
                                    <img src="{{ asset(config('cms.admin_theme') . '/images/brands/adobe.svg') }}" class="w-32px h-32px rounded-pill" alt="">
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <div class="fw-semibold">Adobe <mark>In</mark>c.</div>
                                    <span class="fs-sm text-muted">{{ trans('auth.enterprise_license') }}</span>
                                </div>
                                <div class="d-inline-flex">
                                    <a href="#" class="text-body ms-2">
                                        <i class="ph-briefcase"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="dropdown-item cursor-pointer">
                                <div class="me-3">
                                    <img src="{{ asset(config('cms.admin_theme') . '/images/brands/holiday-inn.svg') }}" class="w-32px h-32px rounded-pill" alt="">
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <div class="fw-semibold">Holiday-<mark>In</mark>n</div>
                                    <span class="fs-sm text-muted">{{ trans('auth.on_premise_license') }}</span>
                                </div>
                                <div class="d-inline-flex">
                                    <a href="#" class="text-body ms-2">
                                        <i class="ph-briefcase"></i>
                                    </a>
                                </div>
                            </div>

                            <div class="dropdown-item cursor-pointer">
                                <div class="me-3">
                                    <img src="{{ asset(config('cms.admin_theme') . '/images/brands/ing.svg') }}" class="w-32px h-32px rounded-pill" alt="">
                                </div>
                                <div class="d-flex flex-column flex-grow-1">
                                    <div class="fw-semibold"><mark>IN</mark>G Group</div>
                                    <span class="fs-sm text-muted">{{ trans('auth.perpetual_license') }}</span>
                                </div>
                                <div class="d-inline-flex">
                                    <a href="#" class="text-body ms-2">
                                        <i class="ph-briefcase"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="position-static">
                    <a href="#" class="navbar-nav-link align-items-center justify-content-center w-40px h-32px position-absolute end-0 top-50 translate-middle-y p-0 me-1" data-bs-toggle="dropdown" data-bs-auto-close="outside">
                        <i class="ph-faders-horizontal"></i>
                    </a>

                    <div class="dropdown-menu w-100 p-3">
                        <div class="d-flex align-items-center mb-3">
                            <h6 class="mb-0">{{ trans('auth.search_options') }}</h6>
                            <a href="#" class="text-body rounded-pill ms-auto">
                                <i class="ph-clock-counter-clockwise"></i>
                            </a>
                        </div>

                        <div class="mb-3">
                            <label class="d-block form-label">{{ trans('auth.category') }}</label>
                            <label class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input" checked>
                                <span class="form-check-label">{{ trans('auth.invoices') }}</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input">
                                <span class="form-check-label">{{ trans('auth.files') }}</span>
                            </label>
                            <label class="form-check form-check-inline">
                                <input type="checkbox" class="form-check-input">
                                <span class="form-check-label">{{ trans('auth.users') }}</span>
                            </label>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ trans('auth.addition') }}</label>
                            <div class="input-group">
                                <select class="form-select w-auto flex-grow-0">
                                    <option value="1" selected>{{ trans('auth.has') }}</option>
                                    <option value="2">{{ trans('auth.has_not') }}</option>
                                </select>
                                <input type="text" class="form-control" placeholder="{{ trans('auth.enter_words') }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ trans('auth.status') }}</label>
                            <div class="input-group">
                                <select class="form-select w-auto flex-grow-0">
                                    <option value="1" selected>{{ trans('auth.is') }}</option>
                                    <option value="2">{{ trans('auth.is_not') }}</option>
                                </select>
                                <select class="form-select">
                                    <option value="1" selected>{{ trans('auth.active') }}</option>
                                    <option value="2">{{ trans('auth.inactive') }}</option>
                                    <option value="3">{{ trans('auth.new') }}</option>
                                    <option value="4">{{ trans('auth.expired') }}</option>
                                    <option value="5">{{ trans('auth.pending') }}</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex">
                            <button type="button" class="btn btn-light">{{ trans('auth.reset') }}</button>
                            <div class="ms-auto">
                                <button type="button" class="btn btn-light">{{ trans('auth.cancel') }}</button>
                                <button type="button" class="btn btn-primary ms-2">{{ trans('auth.apply') }}</button>
                            </div>
                        </div>
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
            <li class="nav-item">
                <a href="#" class="navbar-nav-link navbar-nav-link-icon rounded-pill" data-bs-toggle="offcanvas" data-bs-target="#notifications">
                    <i class="ph-bell"></i>
                    <span class="badge bg-yellow text-black position-absolute top-0 end-0 translate-middle-top zindex-1 rounded-pill mt-1 me-1">2</span>
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
                    <div class="dropdown-item text-center py-2">
                        <small class="text-muted">تغییرات بلافاصله اعمال می‌شوند</small>
                    </div>
                </div>
            </li>

            <li class="nav-item nav-item-dropdown-lg dropdown">
                <a href="#" class="navbar-nav-link align-items-center rounded-pill p-1" data-bs-toggle="dropdown">
                    <div class="status-indicator-container">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/profile/profile.jpg') }}" class="w-32px h-32px rounded-pill" alt="">
                        <span class="status-indicator bg-success"></span>
                    </div>
                    <span class="d-none d-lg-inline-block mx-lg-2">{{ Auth::guard('admin')->user()->name ?? 'Guest' }}</span>
                </a>

                <div class="dropdown-menu dropdown-menu-end">
                    <a href="#" class="dropdown-item">
                        <i class="ph-user-circle me-2"></i>
                        {{ trans('auth.my_profile') }}
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="ph-currency-circle-dollar me-2"></i>
                        {{ trans('auth.my_subscription') }}
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="ph-shopping-cart me-2"></i>
                        {{ trans('auth.my_orders') }}
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="ph-envelope-open me-2"></i>
                        {{ trans('auth.my_inbox') }}
                        <span class="badge bg-primary rounded-pill ms-auto">26</span>
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" class="dropdown-item">
                        <i class="ph-gear me-2"></i>
                        {{ trans('auth.account_settings') }}
                    </a>
                    <a href="#" class="dropdown-item">
                        <i class="ph-sign-out me-2"></i>
                        {{ trans('auth.logout') }}
                    </a>
                </div>
            </li>
        </ul>
    </div>
</div>
