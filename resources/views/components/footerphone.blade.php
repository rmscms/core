<nav class="navbar fixed-bottom shadow-lg p-0 border-0 d-block d-md-none modern-navbar">
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center w-100 position-relative">
            <x-menu-item href="#" icon="ph-chart-line" label="{{ trans('auth.dashboard') }}" class="flex-fill text-center py-3">
                <div class="nav-icon-wrapper mb-1">
                    <div class="nav-icon-circle bg-primary d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-chart-line"></i>
                    </div>
                </div>
            </x-menu-item>

            <x-menu-item href="#" icon="ph-alarm" label="{{ trans('auth.expireds') }}" class="flex-fill text-center py-3">
                <div class="nav-icon-wrapper mb-1">
                    <div class="nav-icon-circle bg-danger d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-alarm"></i>
                    </div>
                </div>
            </x-menu-item>

            <div class="position-absolute top-0 start-50 translate-middle-x center-add-btn">
                <div class="floating-menu" id="floatingMenu">
                    <x-floating-menu-item href="#" label="{{ trans('auth.accounts_wireguard') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/wg.webp') }}" alt="وایرگارد" class="rounded-circle" width="35" height="35">
                    </x-floating-menu-item>
                    <x-floating-menu-item href="#" label="{{ trans('auth.accounts_amnezia') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/amneziavpn.webp') }}" alt="امنیزیا" class="rounded-circle" width="35" height="35">
                    </x-floating-menu-item>
                    <x-floating-menu-item href="#" label="{{ trans('auth.accounts_open_vpn') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/open.webp') }}" alt="اوپن وی پی ان" class="rounded-circle" width="35" height="35">
                    </x-floating-menu-item>
                    <x-floating-menu-item href="#" label="{{ trans('auth.v2rayng') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/v2rayng.webp') }}" alt="وی‌تو‌ری‌ان‌جی" class="rounded-circle" width="35" height="35">
                    </x-floating-menu-item>
                </div>
                <button class="btn btn-dark border-0 rounded-circle d-flex align-items-center justify-content-center" id="addButton">
                    <i class="ph-plus" id="addIcon"></i>
                </button>
            </div>

            <x-menu-item href="#" icon="ph-headset" label="{{ trans('auth.tickets') }}" class="flex-fill text-center py-3 text-light">
                <div class="nav-icon-wrapper mb-1">
                    <div class="nav-icon-circle bg-warning d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-headset"></i>
                    </div>
                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 translate-middle-top rounded-pill">13</span>
                </div>
            </x-menu-item>

            <x-menu-item href="#" icon="ph-currency-dollar" label="{{ trans('auth.charging_panel') }}" class="flex-fill text-center py-3 text-light">
                <div class="nav-icon-wrapper mb-1">
                    <div class="nav-icon-circle bg-success d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-currency-dollar"></i>
                    </div>
                    <span class="badge bg-warning text-dark position-absolute top-0 end-0 translate-middle-top rounded-pill">4</span>
                </div>
            </x-menu-item>
        </div>
    </div>
</nav>

<script src="{{ asset(config('cms.admin_theme') . '/js/modern-navbar.js') }}"></script>
