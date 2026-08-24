<div id="kt_app_sidebar" class="app-sidebar flex-column" data-kt-drawer="true" data-kt-drawer-name="app-sidebar" data-kt-drawer-activate="{default: true, lg: false}" data-kt-drawer-overlay="true" data-kt-drawer-width="280px" data-kt-drawer-direction="start" data-kt-drawer-toggle="#kt_app_sidebar_mobile_toggle">

    <div class="px-4 py-3 d-flex align-items-center">
    <a href="{{ route('index') }}" class="d-flex align-items-center gap-2 text-decoration-none">
        <img src="{{ asset('assets/media/logos/logo-dark.svg') }}"
             class="logo-dark"
             height="52">
        <img src="{{ asset('assets/media/logos/logo-light.svg') }}"
             class="logo-light"
             height="52">
    </a>
</div>

    <div class="px-3 py-3" style="height:calc(100vh - 160px); overflow-y:auto;">

        <a href="/" class="sidebar-link {{ request()->is('/') ? 'active' : '' }}">
            <i class="bi bi-house"></i>
            Ana Sayfa
        </a>

        <a href="{{ route('browse.auctions') }}" class="sidebar-link {{ request()->is('browse/auctions') ? 'active' : '' }}">
            <i class="bi bi-tag"></i>
            Müzayedeler
        </a>

        <a href="{{ route('browse.live') }}" class="sidebar-link {{ request()->is('browse/live') ? 'active' : '' }}">
            <i class="bi bi-broadcast"></i>
            Canlı Açık Artırma
        </a>

        <a href="{{ route('browse.explore') }}" class="sidebar-link {{ request()->is('browse/explore') ? 'active' : '' }}">
            <i class="bi bi-compass"></i>
            Keşfet
        </a>

        @auth

        <a href="{{ route('messages.index') }}" class="sidebar-link {{ request()->is('messages*') ? 'active' : '' }}" data-testid="sidebar-messages">
            <i class="bi bi-chat-dots"></i>
            Mesajlar
            @php $unreadMsg = auth()->user()->unreadMessagesCount(); @endphp
            @if($unreadMsg > 0)
                <span class="sidebar-badge">{{ $unreadMsg }}</span>
            @endif
        </a>

        @role('admin')
        <div class="sidebar-title">
            Admin Paneli
        </div>

        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->is('admin/dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i>
            Dashboard
        </a>

        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->is('admin/users*') ? 'active' : '' }}">
            <i class="bi bi-people"></i>
            Kullanıcılar
        </a>

        <a href="{{ route('admin.categories.index') }}" class="sidebar-link {{ request()->is('admin/categories*') ? 'active' : '' }}">
            <i class="bi bi-box"></i>
            Kategoriler
        </a>

        <a href="{{ route('admin.auctions.index') }}" class="sidebar-link {{ request()->is('admin/auctions*') ? 'active' : '' }}">
            <i class="bi bi-hammer"></i>
            Müzayede Yönetimi
        </a>

        <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->is('admin/orders*') ? 'active' : '' }}" data-testid="sidebar-admin-orders">
            <i class="bi bi-box-seam"></i>
            Siparişler & Anlaşmazlık
        </a>

        @endrole


        @role('seller')
        <div class="sidebar-title">
            Satıcı Paneli
        </div>

        <a href="{{ route('seller.dashboard') }}" class="sidebar-link {{ request()->is('seller/dashboard*') ? 'active' : '' }}">
            <i class="bi bi-grid"></i>
            Panel
        </a>

        <a href="{{ route('seller.auctions.index') }}" class="sidebar-link {{ request()->is('seller/auctions*') ? 'active' : '' }}">
            <i class="bi bi-hammer"></i>
            İlanlarım
        </a>

        @php
            $sellerLiveBadge = auth()->user()->auctions()
                ->where('status', 'active')
                ->where('stream_mode', 'live')
                ->count();
        @endphp
        <a href="{{ route('seller.dashboard') }}#canliya-basla"
           class="sidebar-link sidebar-link-broadcast"
           data-testid="sidebar-seller-broadcast">
            <i class="bi bi-broadcast"></i>
            Canlı Yayın
            @if($sellerLiveBadge > 0)
                <span class="sidebar-live-badge">{{ $sellerLiveBadge }}</span>
            @endif
        </a>

        <a href="{{ route('seller.sales.index') }}" class="sidebar-link {{ request()->is('seller/sales*') ? 'active' : '' }}" data-testid="sidebar-seller-sales">
            <i class="bi bi-box-seam"></i>
            Satışlarım
        </a>
        @endrole


        @role('buyer')
        <div class="sidebar-title">
            Hesabım
        </div>

        <a href="/dashboard" class="sidebar-link">
            <i class="bi bi-grid"></i>
            Dashboard
        </a>

        <a href="/my-bids" class="sidebar-link">
            <i class="bi bi-graph-up"></i>
            Tekliflerim
        </a>

        <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->is('orders*') ? 'active' : '' }}" data-testid="sidebar-buyer-orders">
            <i class="bi bi-box-seam"></i>
            Siparişlerim
        </a>

        <a href="/favorites" class="sidebar-link">
            <i class="bi bi-heart"></i>
            Favoriler
        </a>

        <a href="/notifications" class="sidebar-link">
            <i class="bi bi-bell"></i>
            Bildirimler
        </a>
        @endrole

        @role('admin')
        <a href="{{ route('admin.support.index') }}" class="sidebar-link {{ request()->is('admin/support*') ? 'active' : '' }}">
            <i class="bi bi-headset"></i>
            Destek
        </a>
        @else
        <a href="{{ route('support.index') }}" class="sidebar-link {{ request()->is('support*') ? 'active' : '' }}">
            <i class="bi bi-headset"></i>
            Destek
        </a>
        @endrole


        <div class="sidebar-title">
            Profil
        </div>

        <a href="{{route('profile.edit')}}" class="sidebar-link {{ request()->is('profile') ? 'active' : '' }}">
            <i class="bi bi-person"></i>
            Profilim
        </a>
        @role('admin')

        <a href="{{ route('admin.settings.index') }}" class="sidebar-link {{ request()->is('admin/settings') ? 'active' : '' }}">
            <i class="bi bi-gear"></i>
            Ayarlar
        </a>
        @endrole

        @endauth

    </div>

    @auth

    <div class="px-6 py-3 sidebar-user dropdown">

        <div class="d-flex align-items-center gap-3" data-bs-toggle="dropdown" style="cursor:pointer;">

            <div class="symbol symbol-35px">

                @if (auth()->user()->avatar)
                <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" class="rounded object-fit-cover">
                @else
                <div class="symbol-label bg-primary text-white fw-bold">
                    {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endif

            </div>

            <div class="flex-grow-1 overflow-hidden">

                <div class="text-white fs-7 fw-semibold text-truncate">
                    {{ auth()->user()->name }}
                </div>

                <div class="text-muted fs-8 text-truncate">
                    {{ auth()->user()->email }}
                </div>

            </div>

            <i class="bi bi-chevron-down text-muted"></i>

        </div>

        <ul class="dropdown-menu sidebar-dropdown dropdown-menu-end shadow">

            <li class="sidebar-dropdown-header">
                <div class="name">
                    {{ auth()->user()->name }}
                </div>
                <div class="email">
                    {{ auth()->user()->email }}
                </div>
            </li>

            <li>
                <hr class="dropdown-divider">
            </li>


            <li>
                <a class="dropdown-item" href="{{route('profile.edit')}}">
                    <i class="bi bi-person"></i>
                    Profilim
                </a>
            </li>
            @role('seller')
            <li>
                <a class="dropdown-item" href="{{ route('seller.profile.edit') }}">
                    <i class="bi bi-gear"></i>
                    Ayarlar
                </a>
            </li>
            @endrole

            <li>
                <hr class="dropdown-divider">
            </li>

            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <button class="dropdown-item text-danger">
                        <i class="bi bi-box-arrow-right"></i>
                        Çıkış Yap
                    </button>
                </form>
            </li>

        </ul>

    </div>

    @endauth

</div>
