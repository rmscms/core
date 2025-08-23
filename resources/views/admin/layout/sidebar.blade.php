```blade
<div class="sidebar sidebar-dark sidebar-main sidebar-expand-lg">
    <!-- Sidebar header -->
    <div class="sidebar-section bg-black bg-opacity-10 border-bottom border-bottom-white border-opacity-10">
        <div class="sidebar-logo d-flex justify-content-center align-items-center">
            <a href="{{ route('admin.dashboard') }}" class="d-inline-flex align-items-center py-2 sidebar-control sidebar-main-resize d-none d-lg-inline-flex">
                <img src="{{ asset(config('cms.admin_theme') . '/images/logo_icon.svg') }}" class="sidebar-logo-icon" alt="">
                <img src="{{ asset(config('cms.admin_theme') . '/images/logo_text_light.svg') }}" class="sidebar-resize-hide ms-3" height="14" alt="">
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
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide"></div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <x-menu-item href="{{ route('admin.dashboard') }}" icon="ph-chart-bar" label="{{ trans('auth.dashboard') }}" active="true" />

                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ trans('auth.support') }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <x-menu-item href="{{ route('admin.deposit') }}" icon="ph-currency-dollar" label="{{ trans('auth.charging_panel') }}" />
                <x-menu-item href="{{ route('admin.depositlist') }}" icon="ph-file-text" label="{{ trans('auth.list_of_charges') }}" />
                <x-menu-item href="{{ route('admin.tickets') }}" icon="ph-headset" label="{{ trans('auth.tickets') }}" />

                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ trans('auth.accounts') }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <x-menu-item icon="ph-user-circle-plus" label="{{ trans('auth.create_an_account') }}">
                    <x-menu-item href="{{ route('admin.cropenvpn') }}" label="{{ trans('auth.accounts_open_vpn') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/open.webp') }}" alt="اوپن وی پی ان" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                    <x-menu-item href="{{ route('admin.crwireguard') }}" label="{{ trans('auth.accounts_wireguard') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/wg.webp') }}" alt="وایرگارد" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                    <x-menu-item href="{{ route('admin.crwirezero') }}" label="{{ trans('auth.accounts_amnezia') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/amneziavpn.webp') }}" alt="امنیزیا" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                    <x-menu-item href="{{ route('admin.crv2ray') }}" label="{{ trans('auth.v2rayng') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/v2rayng.webp') }}" alt="وی‌تو‌ری‌ان‌جی" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                </x-menu-item>
                <x-menu-item icon="ph-user-list" label="{{ trans('auth.list_an_account') }}">
                    <x-menu-item href="{{ route('admin.openvpnlist') }}" label="{{ trans('auth.accounts_open_vpn') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/open.webp') }}" alt="اوپن وی پی ان" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                    <x-menu-item href="{{ route('admin.wireguardlist') }}" label="{{ trans('auth.accounts_wireguard') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/wg.webp') }}" alt="وایرگارد" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                    <x-menu-item href="{{ route('admin.wirezerolist') }}" label="{{ trans('auth.accounts_amnezia') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/amneziavpn.webp') }}" alt="امنیزیا" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                    <x-menu-item href="{{ route('admin.v2raylist') }}" label="{{ trans('auth.v2rayng') }}">
                        <img src="{{ asset(config('cms.admin_theme') . '/images/vpn/v2rayng.webp') }}" alt="وی‌تو‌ری‌ان‌جی" class="rounded-circle" width="24" height="24">
                        <span class="ms-2"></span>
                    </x-menu-item>
                </x-menu-item>

                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ trans('auth.financial_sector') }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <x-menu-item icon="ph-bank" label="{{ trans('auth.finance') }}">
                    <x-menu-item href="{{ route('admin.banks') }}" icon="ph-bank" label="{{ trans('auth.banks') }}" />
                    <x-menu-item href="{{ route('admin.costs') }}" icon="ph-money" label="{{ trans('auth.costs') }}" />
                    <x-menu-item href="{{ route('admin.finance') }}" icon="ph-notebook" label="{{ trans('auth.transactions') }}" />
                    <x-menu-item href="{{ route('admin.debt') }}" icon="ph-currency-circle-dollar" label="{{ trans('auth.debts') }}" />
                    <x-menu-item href="{{ route('admin.sellerstats') }}" icon="ph-chart-line-up" label="{{ trans('auth.statistics_of_agents') }}" />
                    <x-menu-item href="{{ route('admin.marketing') }}" icon="ph-users" label="{{ trans('auth.marketing') }}" />
                </x-menu-item>

                <li class="nav-item-header">
                    <div class="text-uppercase fs-sm lh-sm opacity-50 sidebar-resize-hide">{{ trans('auth.academy') }}</div>
                    <i class="ph-dots-three sidebar-resize-show"></i>
                </li>
                <x-menu-item icon="ph-megaphone" label="{{ trans('auth.academy') }}">
                    <x-menu-item href="{{ route('admin.tutorials') }}" icon="ph-scroll" label="{{ trans('auth.education') }}" />
                    <x-menu-item href="{{ route('admin.broadcasts') }}" icon="ph-chat-circle" label="{{ trans('auth.broadcast') }}" />
                </x-menu-item>
            </ul>
        </div>
        <!-- /main navigation -->
    </div>
</div>
