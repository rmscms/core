{{--
 Mobile Footer Navigation - RMS Core
 
 Optimized mobile bottom navigation with:
 - Clean structure without component complexity
 - Bootstrap tooltips for better UX
 - No duplicate icons
 - Flexible configuration for different projects
 - RTL & Dark theme support
--}}

<nav class="navbar fixed-bottom shadow-lg p-0 border-0 d-block d-md-none mobile-footer-nav">
    <div class="container-fluid px-0">
        <div class="d-flex justify-content-between align-items-center w-100 position-relative">
            
            {{-- Dashboard --}}
            <a href="#" 
               class="flex-fill text-center py-3 nav-item text-decoration-none" 
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               data-bs-title="{{ trans('cms.dashboard') }}">
                <div class="nav-icon-wrapper mb-1">
                    <div class="nav-icon-circle bg-primary d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-chart-line"></i>
                    </div>
                </div>
                <div class="small text-body">{{ trans('cms.dashboard') }}</div>
            </a>

            {{-- Expired Items --}}
            <a href="#" 
               class="flex-fill text-center py-3 nav-item text-decoration-none" 
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               data-bs-title="{{ trans('cms.expired_items') }}">
                <div class="nav-icon-wrapper mb-1">
                    <div class="nav-icon-circle bg-danger d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-alarm"></i>
                    </div>
                </div>
                <div class="small text-body">{{ trans('cms.expired') }}</div>
            </a>

            {{-- Central Add Button with Floating Menu --}}
            <div class="position-absolute top-0 start-50 translate-middle-x center-add-btn">
                <div class="floating-menu" id="floatingMenu">
                    
                    {{-- WireGuard --}}
                    <div class="floating-item" 
                         data-bs-toggle="tooltip" 
                         data-bs-placement="left" 
                         data-bs-title="{{ trans('cms.accounts_wireguard') }}">
                        <div class="floating-icon bg-success d-flex align-items-center justify-content-center">
                            <a href="#" class="text-decoration-none">
                                <img src="{{ asset(config('cms.admin_theme', 'admin') . '/images/vpn/wg.webp') }}" 
                                     alt="WireGuard" 
                                     class="rounded-circle" 
                                     width="35" 
                                     height="35">
                            </a>
                        </div>
                        <span class="floating-label">{{ trans('cms.wireguard') }}</span>
                    </div>

                    {{-- Amnezia VPN --}}
                    <div class="floating-item" 
                         data-bs-toggle="tooltip" 
                         data-bs-placement="left" 
                         data-bs-title="{{ trans('cms.accounts_amnezia') }}">
                        <div class="floating-icon bg-info d-flex align-items-center justify-content-center">
                            <a href="#" class="text-decoration-none">
                                <img src="{{ asset(config('cms.admin_theme', 'admin') . '/images/vpn/amneziavpn.webp') }}" 
                                     alt="Amnezia VPN" 
                                     class="rounded-circle" 
                                     width="35" 
                                     height="35">
                            </a>
                        </div>
                        <span class="floating-label">{{ trans('cms.amnezia') }}</span>
                    </div>

                    {{-- OpenVPN --}}
                    <div class="floating-item" 
                         data-bs-toggle="tooltip" 
                         data-bs-placement="left" 
                         data-bs-title="{{ trans('cms.accounts_openvpn') }}">
                        <div class="floating-icon bg-warning d-flex align-items-center justify-content-center">
                            <a href="#" class="text-decoration-none">
                                <img src="{{ asset(config('cms.admin_theme', 'admin') . '/images/vpn/open.webp') }}" 
                                     alt="OpenVPN" 
                                     class="rounded-circle" 
                                     width="35" 
                                     height="35">
                            </a>
                        </div>
                        <span class="floating-label">{{ trans('cms.openvpn') }}</span>
                    </div>

                    {{-- V2Ray --}}
                    <div class="floating-item" 
                         data-bs-toggle="tooltip" 
                         data-bs-placement="left" 
                         data-bs-title="{{ trans('cms.accounts_v2ray') }}">
                        <div class="floating-icon bg-secondary d-flex align-items-center justify-content-center">
                            <a href="#" class="text-decoration-none">
                                <img src="{{ asset(config('cms.admin_theme', 'admin') . '/images/vpn/v2rayng.webp') }}" 
                                     alt="V2Ray" 
                                     class="rounded-circle" 
                                     width="35" 
                                     height="35">
                            </a>
                        </div>
                        <span class="floating-label">{{ trans('cms.v2ray') }}</span>
                    </div>
                    
                </div>

                <button class="btn btn-dark border-0 rounded-circle d-flex align-items-center justify-content-center" 
                        id="addButton"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        data-bs-title="{{ trans('cms.add_account') }}">
                    <i class="ph-plus" id="addIcon"></i>
                </button>
            </div>

            {{-- Support Tickets --}}
            <a href="#" 
               class="flex-fill text-center py-3 nav-item text-decoration-none"
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               data-bs-title="{{ trans('cms.support_tickets') }}">
                <div class="nav-icon-wrapper mb-1 position-relative">
                    <div class="nav-icon-circle bg-warning d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-headset"></i>
                    </div>
                    @if(($ticketsCount ?? 0) > 0)
                        <span class="badge bg-danger text-white position-absolute top-0 end-0 translate-middle rounded-pill">
                            {{ $ticketsCount > 99 ? '99+' : $ticketsCount }}
                        </span>
                    @endif
                </div>
                <div class="small text-body">{{ trans('cms.tickets') }}</div>
            </a>

            {{-- Account Balance / Deposit --}}
            <a href="#" 
               class="flex-fill text-center py-3 nav-item text-decoration-none"
               data-bs-toggle="tooltip" 
               data-bs-placement="top" 
               data-bs-title="{{ trans('cms.account_balance') }}">
                <div class="nav-icon-wrapper mb-1 position-relative">
                    <div class="nav-icon-circle bg-success d-flex align-items-center justify-content-center mx-auto">
                        <i class="ph-currency-dollar"></i>
                    </div>
                    @if(($pendingDeposits ?? 0) > 0)
                        <span class="badge bg-info text-white position-absolute top-0 end-0 translate-middle rounded-pill">
                            {{ $pendingDeposits > 99 ? '99+' : $pendingDeposits }}
                        </span>
                    @endif
                </div>
                <div class="small text-body">{{ trans('cms.balance') }}</div>
            </a>
            
        </div>
    </div>
</nav>
