@extends('layouts.app')
@section('title', 'Satıcı Paneli')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/seller-live-card.css') }}">
@endpush

@section('content')

<div class="pf-root container-fluid px-2 px-md-4 py-4">

    {{-- ── Toolbar ── --}}
    <div class="pf-toolbar mb-3">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h1 class="pf-toolbar-title mb-1">Satıcı Paneli</h1>
                <div class="pf-text-muted-sm">
                    Merhaba <strong style="color:var(--text)">{{ auth()->user()->name }}</strong>,
                    performansını ve ilanlarını tek yerden yönet
                </div>
            </div>
            <span class="pf-badge pf-badge-success d-inline-flex align-items-center gap-1" style="font-size:var(--fs-xs); padding:6px 14px; border-radius:20px;">
                <span class="pf-pulse-dot"></span> Canlı
            </span>
        </div>
    </div>

    {{-- ── Canlı Yayın Hızlı Erişim Kartı ── --}}
    @if($liveAuctions->count() > 0)
        {{-- Şu an canlı yayında olan ilanlar --}}
        <div class="admin-card seller-live-card seller-live-card--on mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="seller-live-icon seller-live-icon--pulse">
                        <i class="bi bi-broadcast-pin"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:var(--fs-lg); color:var(--text)">
                            Canlı Yayındasın · {{ $liveAuctions->count() }} ilan
                        </div>
                        <div class="pf-text-muted-sm">Yayın paneline dön veya yeni yayın başlat</div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    @foreach($liveAuctions->take(3) as $la)
                        <a href="{{ route('seller.auctions.broadcast', $la) }}"
                           class="pf-btn-save d-flex align-items-center gap-2"
                           style="padding:10px 14px;">
                            <span class="pf-pulse-dot" style="background:#fff"></span>
                            {{ Str::limit($la->title, 20) }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @elseif($broadcastableAuctions->count() > 0)
        {{-- Yayına başlanabilecek ilanlar var --}}
        <div class="admin-card seller-live-card mb-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="seller-live-icon">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <div>
                        <div class="fw-bold" style="font-size:var(--fs-lg); color:var(--text)">
                            Canlı Yayına Başla
                        </div>
                        <div class="pf-text-muted-sm">
                            {{ $broadcastableAuctions->count() }} aktif ilanın kamera açmayı bekliyor
                        </div>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    @if($broadcastableAuctions->count() === 1)
                        @php $ba = $broadcastableAuctions->first(); @endphp
                        <a href="{{ route('seller.auctions.broadcast', $ba) }}"
                           class="pf-btn-save d-flex align-items-center gap-2"
                           style="padding:10px 18px; font-weight:600;"
                           data-testid="seller-quick-broadcast-btn">
                            <i class="bi bi-camera-video"></i>
                            "{{ Str::limit($ba->title, 24) }}" için yayın aç
                        </a>
                    @else
                        <div class="seller-live-list d-flex gap-2 flex-wrap">
                            @foreach($broadcastableAuctions->take(4) as $ba)
                                <a href="{{ route('seller.auctions.broadcast', $ba) }}"
                                   class="pf-btn-secondary d-flex align-items-center gap-2"
                                   style="padding:8px 14px; text-decoration:none; font-size:var(--fs-sm);">
                                    <i class="bi bi-camera-video" style="color:var(--primary)"></i>
                                    {{ Str::limit($ba->title, 18) }}
                                </a>
                            @endforeach
                            @if($broadcastableAuctions->count() > 4)
                                <a href="{{ route('seller.auctions.index') }}"
                                   class="pf-btn-secondary d-flex align-items-center"
                                   style="padding:8px 14px; text-decoration:none; font-size:var(--fs-sm);">
                                    +{{ $broadcastableAuctions->count() - 4 }} daha
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ── 4 Stat Kartı ── --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-xl-3">
            <div class="pf-stat-card" style="position:relative;overflow:hidden;">
                <div class="pf-stat-icon-wrapper" style="background:rgba(21,94,239,.12)">
                    <i class="bi bi-box-seam" style="color:var(--primary); font-size:var(--fs-md)"></i>
                </div>
                <div>
                    <div class="pf-stat-number">{{ $stats['auctions'] ?? 0 }}</div>
                    <div class="pf-stat-label">Toplam İlan</div>
                    <div class="pf-text-muted-sm" style="color:#10b981;margin-top:3px">
                        ↑ {{ $stats['auctions_this_month'] ?? 0 }} bu ay
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper" style="background:rgba(16,185,129,.12)">
                    <i class="bi bi-broadcast" style="color:#10b981; font-size:var(--fs-md)"></i>
                </div>
                <div>
                    <div class="pf-stat-number">{{ $stats['active'] ?? 0 }}</div>
                    <div class="pf-stat-label">Aktif İlan</div>
                    <div class="pf-text-muted-sm" style="color:#10b981;margin-top:3px">
                        ↑ {{ $stats['active_this_week'] ?? 0 }} bu hafta
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper" style="background:rgba(251,191,36,.12)">
                    <i class="bi bi-hand-index-thumb" style="color:#fbbf24; font-size:var(--fs-md)"></i>
                </div>
                <div>
                    <div class="pf-stat-number">{{ $stats['bids'] ?? 0 }}</div>
                    <div class="pf-stat-label">Toplam Teklif</div>
                    <div class="pf-text-muted-sm" style="color:#10b981;margin-top:3px">
                        ↑ {{ $stats['bids_today'] ?? 0 }} bugün
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="pf-stat-card">
                <div class="pf-stat-icon-wrapper" style="background:rgba(6,182,212,.12)">
                    <i class="bi bi-cash-coin" style="color:#06b6d4; font-size:var(--fs-md)"></i>
                </div>
                <div>
                    <div class="pf-stat-number">{{ $stats['sales'] ?? 0 }}</div>
                    <div class="pf-stat-label">Satış</div>
                    <div class="pf-text-muted-sm" style="color:#06b6d4;margin-top:3px">
                        {{ $stats['sales_this_month'] ?? 0 }} bu ay
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Mini İstatistikler ── --}}
    <div class="row g-3 mb-3">
        <div class="col-4">
            <div class="pf-stat-card flex-column text-center gap-1" style="padding:14px 8px;">
                <div class="pf-stat-number" style="font-size:var(--fs-xl); color:#10b981">
                    {{ $stats['completion_rate'] ?? 0 }}%
                </div>
                <div class="pf-stat-label">Tamamlanma</div>
                <div class="pf-text-muted-sm">{{ $stats['sales'] ?? 0 }} satış</div>
            </div>
        </div>
        <div class="col-4">
            <div class="pf-stat-card flex-column text-center gap-1" style="padding:14px 8px;">
                <div class="pf-stat-number" style="font-size:var(--fs-xl)">
                    {{ $stats['seller_rating'] ?? '0.0' }} ★
                </div>
                <div class="pf-stat-label">Satıcı Puanı</div>
                <div class="pf-text-muted-sm">{{ $stats['review_count'] ?? 0 }} değerlendirme</div>
            </div>
        </div>
        <div class="col-4">
            <div class="pf-stat-card flex-column text-center gap-1" style="padding:14px 8px;">
                <div class="pf-stat-number" style="font-size:var(--fs-xl); color:var(--primary)">
                    ₺{{ number_format($stats['avg_price'] ?? 0, 0, ',', '.') }}
                </div>
                <div class="pf-stat-label">Ort. Fiyat</div>
                <div class="pf-text-muted-sm">Aktif ilanlar</div>
            </div>
        </div>
    </div>

    {{-- ── Grafik + Cüzdan ── --}}
    <div class="row g-3 mb-3">
        <div class="col-12 col-lg-8">
            <div class="admin-card h-100">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-graph-up-arrow" style="color:var(--primary)"></i>
                        Satış Performansı
                    </div>
                    <span class="pf-text-muted-sm">Son 30 gün</span>
                </div>
                <div style="padding:16px 20px;">
                    <div style="position:relative; width:100%; height:200px;">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="admin-card h-100 d-flex flex-column">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-wallet2" style="color:var(--primary)"></i>
                        Cüzdan
                    </div>
                </div>
                <div style="padding:18px 20px; flex:1; display:flex; flex-direction:column; justify-content:space-between;">
                    <div>
                        <div style="font-size:var(--fs-2xl); font-weight:800; color:var(--primary); line-height:1; margin-bottom:4px;">
                            {{ number_format($walletBalance, 2, ',', '.') }} ₺
                        </div>
                        <div class="pf-text-muted-sm mb-3">Kullanılabilir bakiye</div>
                        <div style="height:5px; border-radius:10px; background:var(--border); margin-bottom:18px; overflow:hidden;">
                            @php $pct = $walletBalance > 0 ? min(100, round(($walletBalance / 10000) * 100)) : 3; @endphp
                            <div style="height:100%; border-radius:10px; background:var(--primary); width:{{ $pct }}%;"></div>
                        </div>

                        {{-- Cüzdan mini istatistikleri --}}
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="s-info-item text-center">
                                    <div class="s-info-lbl" style="font-size:var(--fs-xs)">Bu ay kazanılan</div>
                                    <div class="s-info-val" style="font-size:var(--fs-sm); color:#10b981">
                                        ₺{{ number_format($stats['earned_this_month'] ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="s-info-item text-center">
                                    <div class="s-info-lbl" style="font-size:var(--fs-xs)">Bekleyen</div>
                                    <div class="s-info-val" style="font-size:var(--fs-sm); color:#fbbf24">
                                        ₺{{ number_format($stats['pending_balance'] ?? 0, 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-7">
                            <a href="#" class="pf-btn-save w-100 d-flex align-items-center justify-content-center gap-1" style="padding:10px 0;">
                                <i class="bi bi-arrow-down-circle"></i> Para Çek
                            </a>
                        </div>
                        <div class="col-5">
                            <a href="#" class="pf-btn-secondary w-100 d-flex align-items-center justify-content-center gap-1" style="padding:10px 0; text-decoration:none; font-size:var(--fs-sm);">
                                <i class="bi bi-clock-history"></i> Geçmiş
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── En Çok Teklif Alan + Aktivite ── --}}
    <div class="row g-3 mb-3">

        {{-- En çok teklif --}}
        <div class="col-12 col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-trophy" style="color:#fbbf24"></i>
                        En Çok Teklif Alan İlanlar
                    </div>
                    <a href="{{ route('seller.auctions.index') }}" class="pf-link-primary" style="font-size:var(--fs-xs); text-decoration:none;">Tümü →</a>
                </div>
                <div style="padding:6px 20px 16px;">
                    @php $topAuctions = $topBidAuctions ?? collect(); @endphp
                    @forelse($topAuctions as $i => $item)
                    @php
                        $bidCount = is_array($item) ? $item['bids'] : $item->bids_count;
                        $maxBid   = is_array($topAuctions->first()) ? $topAuctions->first()['bids'] : $topAuctions->first()->bids_count;
                        $title    = is_array($item) ? $item['title'] : $item->title;
                        $pct      = $maxBid > 0 ? round(($bidCount / $maxBid) * 100) : 0;
                    @endphp
                    <div class="d-flex align-items-center gap-3 py-2" style="border-bottom:1px solid var(--border)">
                        <span class="pf-text-muted-sm" style="width:18px; text-align:center; font-weight:700;">{{ $i+1 }}</span>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:var(--fs-sm); font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                {{ Str::limit($title, 32) }}
                            </div>
                            <div style="height:3px; border-radius:4px; background:var(--border); margin-top:5px; overflow:hidden;">
                                <div style="height:100%; border-radius:4px; background:var(--primary); width:{{ $pct }}%;"></div>
                            </div>
                        </div>
                        <span class="pf-badge pf-badge-success">{{ $bidCount }}</span>
                    </div>
                    @empty
                    <div class="pf-empty" style="padding:26px 0;">
                        <div class="pf-empty-icon"><i class="bi bi-trophy"></i></div>
                        <div class="pf-empty-title">Henüz teklif alan ilan yok</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Son Aktivite --}}
        <div class="col-12 col-lg-6">
            <div class="admin-card h-100">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-activity" style="color:var(--primary)"></i>
                        Son Aktivite
                    </div>
                </div>
                <div style="padding:4px 20px 16px;">
                    @php $activities = $recentActivities ?? collect(); @endphp
                    @forelse($activities as $act)
                    @php
                        $actColor = is_array($act) ? $act['color'] : ($act->color ?? '#10b981');
                        $actText  = is_array($act) ? $act['text']  : $act->text;
                        $actTime  = is_array($act) ? $act['time']  : $act->created_at->diffForHumans();
                    @endphp
                    <div class="d-flex align-items-start gap-3 py-2" style="border-bottom:1px solid var(--border)">
                        <div style="width:8px;height:8px;border-radius:50%;background:{{ $actColor }};flex-shrink:0;margin-top:5px;"></div>
                        <div style="flex:1;">
                            <div style="font-size:var(--fs-sm); color:var(--text); line-height:1.5;">{!! $actText !!}</div>
                            <div class="pf-text-muted-sm" style="margin-top:2px;">{{ $actTime }}</div>
                        </div>
                    </div>
                    @empty
                    <div class="pf-empty" style="padding:26px 0;">
                        <div class="pf-empty-icon"><i class="bi bi-activity"></i></div>
                        <div class="pf-empty-title">Henüz aktivite yok</div>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- ── Son İlanlar + Hızlı İşlemler ── --}}
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="admin-card h-100">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-box-seam" style="color:var(--primary)"></i>
                        Son İlanlar
                    </div>
                    <a href="{{ route('seller.auctions.index') }}" class="pf-link-primary" style="font-size:var(--fs-xs); text-decoration:none;">
                        Tümünü Gör →
                    </a>
                </div>

                @php
                    $sMap = [
                        'active'    => 'pf-badge-success',
                        'draft'     => 'pf-badge-warning',
                        'ended'     => 'pf-badge-dark',
                        'sold'      => 'pf-badge-cyan',
                        'cancelled' => 'pf-badge-danger',
                        'rejected'  => 'pf-badge-danger',
                    ];
                    $sLbl = [
                        'active'    => 'Aktif',
                        'draft'     => 'Taslak',
                        'ended'     => 'Bitti',
                        'sold'      => 'Satıldı',
                        'cancelled' => 'İptal',
                        'rejected'  => 'Reddedildi',
                    ];
                @endphp

                @if($latestAuctions->isEmpty())
                    <div class="pf-empty">
                        <div class="pf-empty-icon"><i class="bi bi-box-seam"></i></div>
                        <div class="pf-empty-title">Henüz ilan yok</div>
                        <div class="pf-empty-sub">İlk ilanını oluşturmak için "Yeni İlan Oluştur" butonunu kullan.</div>
                    </div>
                @else
                    {{-- Masaüstü tablo --}}
                    <div class="d-none d-md-block">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th>İlan</th>
                                    <th class="text-end">Fiyat</th>
                                    <th class="text-center">Durum</th>
                                    <th class="text-center">Teklif</th>
                                    <th class="text-center">Süre</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($latestAuctions as $auction)
                                <tr>
                                    <td>
                                        <div class="pf-cat-info">
                                            <img src="{{ $auction->coverUrl() }}"
                                                 class="pf-cat-img"
                                                 alt="{{ $auction->title }}">
                                            <div>
                                                <div class="pf-cat-name">{{ Str::limit($auction->title, 38) }}</div>
                                                <div class="pf-cat-slug">{{ $auction->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end" style="font-weight:700; color:var(--text);">
                                        {{ $auction->displayPrice() }}
                                    </td>
                                    <td class="text-center">
                                        <span class="pf-badge {{ $sMap[$auction->status] ?? 'pf-badge-dark' }}">
                                            {{ $sLbl[$auction->status] ?? ucfirst($auction->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center" style="font-weight:600; color:var(--text);">
                                        {{ $auction->bidCount() }}
                                    </td>
                                    <td class="text-center pf-text-muted-sm">
                                        @if($auction->status === 'active' && $auction->ends_at)
                                            {{ $auction->ends_at->diffForHumans() }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Mobil liste --}}
                    <div class="d-flex flex-column gap-2 d-md-none p-3">
                        @foreach($latestAuctions as $auction)
                        <div class="d-flex align-items-center gap-3 p-2"
                             style="border:1px solid var(--border); border-radius:12px; background:var(--bg-soft);">
                            <img src="{{ $auction->coverUrl() }}"
                                 style="width:44px;height:44px;border-radius:10px;object-fit:cover;flex-shrink:0;border:1px solid var(--border);"
                                 alt="{{ $auction->title }}">
                            <div style="flex:1;min-width:0;">
                                <div class="pf-cat-name">{{ Str::limit($auction->title, 26) }}</div>
                                <div style="font-size:var(--fs-sm); font-weight:700; color:var(--primary);">{{ $auction->displayPrice() }}</div>
                            </div>
                            <span class="pf-badge {{ $sMap[$auction->status] ?? 'pf-badge-dark' }}" style="flex-shrink:0;">
                                {{ $sLbl[$auction->status] ?? ucfirst($auction->status) }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Hızlı İşlemler --}}
        <div class="col-12 col-lg-4">
            <div class="admin-card h-100">
                <div class="admin-card-head">
                    <div class="admin-card-title">
                        <i class="bi bi-lightning-charge" style="color:#fbbf24"></i>
                        Hızlı İşlemler
                    </div>
                </div>
                <div style="padding:16px 20px; display:flex; flex-direction:column; gap:10px;">
                    <a href="{{ route('seller.auctions.create') }}"
                       class="pf-btn-save w-100 d-flex align-items-center justify-content-center gap-2"
                       style="padding:12px;">
                        <i class="bi bi-plus-lg"></i> Yeni İlan Oluştur
                    </a>

                    <div class="s-action-grid" style="grid-template-columns:1fr 1fr;">
                        <a href="{{ route('seller.auctions.index') }}" class="s-action-btn text-decoration-none">
                            <i class="bi bi-list-ul" style="color:var(--primary); font-size:var(--fs-md)"></i>
                            <div class="s-info-lbl mt-1" style="font-size:10px;">İlanlar</div>
                        </a>
                        <a href="{{ route('seller.profile.edit') }}" class="s-action-btn text-decoration-none">
                            <i class="bi bi-person" style="color:var(--primary); font-size:var(--fs-md)"></i>
                            <div class="s-info-lbl mt-1" style="font-size:10px;">Profil</div>
                        </a>
                        <a href="#" class="s-action-btn text-decoration-none">
                            <i class="bi bi-graph-up" style="color:#10b981; font-size:var(--fs-md)"></i>
                            <div class="s-info-lbl mt-1" style="font-size:10px;">Raporlar</div>
                        </a>
                        <a href="#" class="s-action-btn text-decoration-none">
                            <i class="bi bi-chat-dots" style="color:#06b6d4; font-size:var(--fs-md)"></i>
                            <div class="s-info-lbl mt-1" style="font-size:10px;">Mesajlar</div>
                        </a>
                        <a href="#" class="s-action-btn text-decoration-none">
                            <i class="bi bi-gear" style="color:var(--muted); font-size:var(--fs-md)"></i>
                            <div class="s-info-lbl mt-1" style="font-size:10px;">Ayarlar</div>
                        </a>
                        <a href="#" class="s-action-btn text-decoration-none">
                            <i class="bi bi-question-circle" style="color:var(--muted); font-size:var(--fs-md)"></i>
                            <div class="s-info-lbl mt-1" style="font-size:10px;">Yardım</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="{{ asset('assets/js/custom/seller-dashboard.js') }}"></script>
@endpush
