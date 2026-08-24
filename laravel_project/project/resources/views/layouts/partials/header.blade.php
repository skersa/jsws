<div id="kt_app_header" class="app-header modern-header" data-kt-sticky="true" data-kt-sticky-activate="{default: true, lg: true}">

    <div class="app-container container-xxl d-flex align-items-center justify-content-between" style="gap:8px; padding-left:12px; padding-right:12px;">

        <div class="d-flex align-items-center d-lg-none">
            <div class="btn modern-icon" id="kt_app_sidebar_mobile_toggle">
                <i class="bi bi-list fs-3"></i>
            </div>
        </div>

        <div class="d-flex align-items-center mhdr-search-wrap position-relative">
            <div class="search-box position-relative">
                <i class="bi bi-search search-icon"></i>
                <input type="text" id="mhdr-input" class="form-control search-input" name="q" placeholder="Müzayede, ilan veya kullanıcı ara..." value="{{ request()->get('q', '') }}" autocomplete="off">
                <div id="search-results" class="mhdr-search-results d-none"></div>

            </div>
        </div>

        <div class="d-flex align-items-center" style="gap:4px; flex-shrink:0;">

            <button class="btn modern-icon" id="themeToggle">
                <i class="bi bi-moon fs-5"></i>
            </button>

            @auth
            @role('seller')
            <a href="{{ route('seller.auctions.create') }}" class="btn btn-primary btn-sm modern-btn d-none d-lg-inline-flex align-items-center">
                <i class="bi bi-plus-lg me-1 d-flex"></i>
                <span>İlan Ver</span>
            </a>
            <a href="{{ route('seller.auctions.create') }}" class="btn modern-icon d-flex d-lg-none" style="color:#155eef">
                <i class="bi bi-plus-lg fs-5"></i>
            </a>
            @endrole

            @auth
            @unless(auth()->user()->isAdmin())
            <div class="d-flex align-items-center gap-2 me-1">

                <a href="{{ route('general.balance.index') }}" class="balance-pill d-none d-md-flex align-items-center">
                    <i class="bi bi-wallet2 me-1"></i>
                    <span>{{ number_format(auth()->user()->balance ?? 0, 2, ',', '.') }} ₺</span>
                </a>

                <a href="{{ route('general.balance.index') }}" class="btn modern-icon d-flex d-md-none" title="Bakiye Yükle">
                    <i class="bi bi-wallet2 fs-5"></i>
                </a>

            </div>
            @endunless
            @endauth

            <div class="dropdown">
                <button class="btn modern-icon position-relative" data-bs-toggle="dropdown" id="notifToggle">
                    <i class="bi bi-bell fs-5"></i>
                    @auth
                    @if(auth()->user()->unreadNotifications->count() > 0)
                    <span class="notif-dot"></span>
                    @endif
                    @endauth
                </button>

                <div class="dropdown-menu dropdown-menu-end modern-dropdown" style="width:340px; padding:0;">

                    <div class="user-box fw-semibold d-flex align-items-center justify-content-between">
                        <span>Bildirimler</span>
                        <a href="{{ route('notifications.index') }}" class="fs-8 text-muted text-decoration-none">
                            Tümünü gör
                        </a>
                    </div>

                    @auth
                    @php
                    $headerNotifs = auth()->user()->notifications()->latest()->take(6)->get();
                    @endphp

                    @if($headerNotifs->isEmpty())
                    <div class="text-center text-muted py-4" style="font-size:12px;">
                        <i class="bi bi-bell-slash d-block mb-2 opacity-50 fs-5"></i>
                        Yeni bildirim yok
                    </div>
                    @else
                    <div style="max-height:340px; overflow-y:auto;">
                        @foreach($headerNotifs as $notif)
                        @php
                        $data = $notif->data;
                        $type = $data['type'] ?? 'follow';
                        $unread = is_null($notif->read_at);

                        $meta = match($type) {
                        'follow' => ['bi-person-plus-fill', '#155eef'],
                        'new_bid' => ['bi-currency-lira', '#10b981'],
                        'auction_approved' => ['bi-check-circle-fill', '#22c55e'],
                        'auction_rejected' => ['bi-x-circle-fill', '#ef4444'],
                        'auction_ended' => ['bi-flag-fill', '#6b7280'],
                        'buy_now' => ['bi-lightning-fill', '#f59e0b'],
                        default => ['bi-bell-fill', '#155eef'],
                        };
                        [$icon, $color] = $meta;

                        $avatarName = $data['follower_name'] ?? $data['bidder_name'] ?? $data['buyer_name'] ?? null;
                        $avatarImg = $data['follower_avatar'] ?? $data['bidder_avatar'] ?? $data['buyer_avatar'] ?? null;
                        $avatarUser = $data['follower_username'] ?? $data['bidder_username'] ?? $data['buyer_username'] ?? null;

                        $link = match($type) {
                        'follow' => $avatarUser ? route('profile.public', $avatarUser) : '#',
                        'new_bid','auction_approved','auction_rejected',
                        'auction_ended','buy_now' => isset($data['auction_slug']) ? route('seller.auctions.show', $data['auction_slug']) : '#',
                        default => '#',
                        };
                        @endphp

                        <a href="{{ $link }}" class="dropdown-item d-flex align-items-center gap-2 py-2 px-3" style="white-space:normal; background: {{ $unread ? '#155eef0a' : 'transparent' }};">

                            <div style="position:relative; width:34px; height:34px; flex-shrink:0;">
                                @if($avatarImg)
                                <img src="{{ $avatarImg }}" style="width:34px;height:34px;border-radius:50%;object-fit:cover;" alt="">
                                @elseif($avatarName)
                                <div style="width:34px;height:34px;border-radius:50%;background:#155eef;
                                            color:#fff;font-weight:700;font-size:13px;
                                            display:flex;align-items:center;justify-content:center;">
                                    {{ strtoupper(mb_substr($avatarName, 0, 1)) }}
                                </div>
                                @else
                                <div style="width:34px;height:34px;border-radius:50%;background:{{ $color }}22;
                                            color:{{ $color }};font-size:14px;
                                            display:flex;align-items:center;justify-content:center;">
                                    <i class="bi {{ $icon }}"></i>
                                </div>
                                @endif

                                <div style="position:absolute;bottom:-2px;right:-3px;
                                        width:16px;height:16px;border-radius:50%;
                                        background:{{ $color }};border:2px solid var(--search-bg);
                                        display:flex;align-items:center;justify-content:center;">
                                    <i class="bi {{ $icon }}" style="font-size:7px;color:#fff;"></i>
                                </div>
                            </div>

                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12.5px; {{ $unread ? 'font-weight:600;' : '' }} color:var(--search-text-main); line-height:1.35;">
                                    {{ $data['message'] }}
                                </div>
                                <div style="font-size:11px;color:var(--search-text-muted);margin-top:2px;">
                                    {{ $notif->created_at->diffForHumans() }}
                                </div>
                            </div>

                            @if($unread)
                            <div style="width:7px;height:7px;border-radius:50%;background:#155eef;flex-shrink:0;"></div>
                            @endif
                        </a>
                        @endforeach
                    </div>

                    <div class="px-3 py-2 border-top" style="border-color:var(--search-border)!important;">
                        <a href="{{ route('notifications.index') }}" class="d-block text-center text-decoration-none" style="font-size:12px;color:var(--search-text-muted);">
                            Tüm bildirimleri gör
                        </a>
                    </div>
                    @endif
                    @endauth

                </div>
            </div>

            <div class="dropdown">
                <a class="d-flex align-items-center justify-content-center" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="width:36px; height:36px; border-radius:50%; overflow:hidden; cursor:pointer; flex-shrink:0;">
                    @if(auth()->user()->avatar)
                    <img src="{{ asset('storage/' . auth()->user()->avatar) }}" alt="{{ auth()->user()->name }}" style="width:36px; height:36px; object-fit:cover;">
                    @else
                    <div class="bg-primary text-white fw-bold d-flex align-items-center justify-content-center w-100 h-100">
                        {{ strtoupper(mb_substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    @endif
                </a>
                <div class="dropdown-menu dropdown-menu-end modern-dropdown">
                    <div class="user-box">
                        <div class="fw-bold">{{ auth()->user()->name }}</div>
                        <div class="text-muted fs-8">{{ auth()->user()->email }}</div>
                    </div>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">Profil</a>
                    <a class="dropdown-item" href="{{ route('seller.auctions.index') }}">İlanlarım</a>
                    <a class="dropdown-item" href="/my-bids">Tekliflerim</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="dropdown-item">Çıkış Yap</button>
                    </form>
                </div>
            </div>

            @else
            <div class="mhdr-divider d-none d-sm-block"></div>
            <a href="/login" class="btn btn-light btn-sm">Giriş</a>
            <a href="/register" class="btn btn-primary btn-sm">Kayıt</a>
            @endauth

        </div>
    </div>
</div>
<script src="{{ asset('assets/js/custom/header.js') }}"></script>


